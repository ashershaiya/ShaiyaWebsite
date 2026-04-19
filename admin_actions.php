<?php
session_start();
require_once 'db.php';
require_once 'db_pdo.php';


// Security: Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['user'];

// Security: Re-check Admin status (16)
$user_sql = "SELECT Status, UserUID FROM PS_UserData.dbo.Users_Master WHERE UserID = '$username'";
$user_res = odbc_exec($conn, $user_sql);
$user_data = odbc_fetch_array($user_res);
$adminStatus = (int) $user_data['Status'];
$adminUID = (int) $user_data['UserUID'];

if (!in_array($adminStatus, [16, 32])) {
    header("Location: account.php");
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$msg = "";
$status = "error";

switch ($action) {
    case 'giftbox':
        $targetUser = isset($_POST['target_user']) ? trim($_POST['target_user']) : '';
        $targetChar = isset($_POST['target_char']) ? trim($_POST['target_char']) : '';
        $itemid = isset($_POST['itemid']) ? (int) $_POST['itemid'] : 0;
        $count = isset($_POST['count']) ? (int) $_POST['count'] : 1;

        if ((!$targetUser && !$targetChar) || !$itemid) {
            $msg = "UserID or CharName and ItemID are required.";
        } else {
            $targetUID = 0;
            $finalUser = "";

            if ($targetUser) {
                $q = odbc_exec($conn, "SELECT UserUID, UserID FROM PS_UserData.dbo.Users_Master WHERE UserID = '$targetUser'");
                if ($targetData = odbc_fetch_array($q)) {
                    $targetUID = $targetData['UserUID'];
                    $finalUser = $targetData['UserID'];
                }
            } else {
                $q = odbc_exec($conn, "SELECT UserUID, UserID FROM PS_GameData.dbo.Chars WHERE CharName = '$targetChar'");
                if ($targetData = odbc_fetch_array($q)) {
                    $targetUID = $targetData['UserUID'];
                    $finalUser = "Char:$targetChar";
                }
            }

            if ($targetUID > 0) {
                // Find next free slot in GiftBox
                $slotQuery = odbc_exec($conn, "SELECT Slot FROM PS_GameData.dbo.UserStoredPointItems WHERE UserUID = $targetUID ORDER BY Slot ASC");
                $slot = 0;
                while ($sRow = odbc_fetch_array($slotQuery)) {
                    if ($slot != (int) $sRow['Slot'])
                        break;
                    $slot++;
                }

                if ($slot >= 240) {
                    $msg = "User's GiftBox is full!";
                } else {
                    $insertGift = odbc_exec($conn, "INSERT INTO PS_GameData.dbo.UserStoredPointItems (UserUID, Slot, ItemID, ItemCount, BuyDate) VALUES ($targetUID, $slot, $itemid, $count, GETDATE())");
                    if ($insertGift) {
                        $msg = "Successfully added Item $itemid (x$count) to $finalUser's GiftBox (Slot $slot).";
                        $status = "success";
                    } else {
                        $msg = "Error adding item to database.";
                    }
                }
            } else {
                $msg = "Target not found.";
            }
        }
        break;

    case 'guild_rename':
        $guildID = isset($_POST['guild_id']) ? (int) $_POST['guild_id'] : 0;
        $newName = isset($_POST['new_name']) ? trim($_POST['new_name']) : '';

        if (!$guildID || !$newName) {
            $msg = "GuildID and New Name are required.";
        } else {
            $checkName = odbc_exec($conn, "SELECT GuildID FROM PS_GameData.dbo.Guilds WHERE GuildName = '$newName' AND Del = 0 AND GuildID <> $guildID");
            if (odbc_fetch_array($checkName)) {
                $msg = "A guild with that name already exists.";
            } else {
                $update = odbc_exec($conn, "UPDATE PS_GameData.dbo.Guilds SET GuildName = '$newName' WHERE GuildID = $guildID AND Del = 0");
                if ($update) {
                    $msg = "Guild renamed to '$newName' successfully.";
                    $status = "success";
                } else {
                    $msg = "Guild not found.";
                }
            }
        }
        break;

    case 'guild_change_leader':
        $guildID = isset($_POST['guild_id']) ? (int) $_POST['guild_id'] : 0;
        $newLeaderID = isset($_POST['new_leader_id']) ? (int) $_POST['new_leader_id'] : 0;

        if (!$guildID || !$newLeaderID) {
            $msg = "GuildID and New Leader ID are required.";
        } else {
            // Get new leader details
            $q = odbc_exec($conn, "SELECT CharName, UserID, UserUID FROM PS_GameData.dbo.Chars WHERE CharID = $newLeaderID");
            if ($leader = odbc_fetch_array($q)) {
                $update = odbc_exec($conn, "UPDATE PS_GameData.dbo.Guilds SET MasterCharID = $newLeaderID, MasterName = '{$leader['CharName']}', MasterUserID = '{$leader['UserID']}' WHERE GuildID = $guildID AND Del = 0");
                if ($update) {
                    $msg = "Guild leader changed to '{$leader['CharName']}' successfully.";
                    $status = "success";
                } else {
                    $msg = "Error updating guild master.";
                }
            } else {
                $msg = "New leader character not found.";
            }
        }
        break;




    case 'register_toggle':
        if ($adminStatus != 16) {
            $msg = "Unauthorized. Only Status 16 can use this.";
            $status = "error";
            break;
        }

        // Ensure settings table exists
        $checkTable = @odbc_exec($conn, "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) 
            CREATE TABLE PS_UserData.dbo.Web_Settings (SettingKey VARCHAR(50) PRIMARY KEY, SettingValue NVARCHAR(MAX))");

        $regEnabled = isset($_POST['reg_enabled']) ? '1' : '0';

        // Check if key exists
        $checkKey = odbc_exec($conn, "SELECT SettingKey FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'RegistrationEnabled'");
        if (odbc_fetch_array($checkKey)) {
            $update = odbc_exec($conn, "UPDATE PS_UserData.dbo.Web_Settings SET SettingValue = '$regEnabled' WHERE SettingKey = 'RegistrationEnabled'");
        } else {
            $update = odbc_exec($conn, "INSERT INTO PS_UserData.dbo.Web_Settings (SettingKey, SettingValue) VALUES ('RegistrationEnabled', '$regEnabled')");
        }

        if ($update) {
            $msg = "Registration status updated successfully.";
            $status = "success";
        } else {
            $msg = "Failed to update registration status.";
        }
        break;

    case 'downloads_update':
        if ($adminStatus != 16) {
            $msg = "Unauthorized. Only Status 16 can use this.";
            $status = "error";
            break;
        }

        $dlEnabled = isset($_POST['dl_enabled']) ? '1' : '0';
        $linkGoogle = isset($_POST['link_google']) ? trim($_POST['link_google']) : '';
        $linkMirror = isset($_POST['link_mirror']) ? trim($_POST['link_mirror']) : '';
        $linkMega = isset($_POST['link_mega']) ? trim($_POST['link_mega']) : '';

        // Ensure table exists
        @odbc_exec($conn, "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) CREATE TABLE PS_UserData.dbo.Web_Settings (SettingId INT IDENTITY(1,1) PRIMARY KEY, SettingKey VARCHAR(100) NOT NULL UNIQUE, SettingValue VARCHAR(MAX) NOT NULL)");

        $settings = [
            'DownloadsEnabled' => $dlEnabled,
            'Link_GoogleDrive' => $linkGoogle,
            'Link_OfficialMirror' => $linkMirror,
            'Link_Mega' => $linkMega
        ];

        $successCount = 0;
        foreach ($settings as $key => $val) {
            $checkKey = odbc_exec($conn, "SELECT SettingKey FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = '$key'");
            if (odbc_fetch_array($checkKey)) {
                $upd = odbc_exec($conn, "UPDATE PS_UserData.dbo.Web_Settings SET SettingValue = '$val' WHERE SettingKey = '$key'");
            } else {
                $upd = odbc_exec($conn, "INSERT INTO PS_UserData.dbo.Web_Settings (SettingKey, SettingValue) VALUES ('$key', '$val')");
            }
            if ($upd)
                $successCount++;
        }

        if ($successCount === count($settings)) {
            $msg = "Downloads configuration updated successfully.";
            $status = "success";
        } else {
            $msg = "Some settings failed to update.";
        }
        break;

    case 'luckychest_toggle':
        if ($adminStatus != 16) {
            $msg = "Unauthorized. Only Status 16 can use this.";
            $status = "error";
            break;
        }

        $luckyEnabled = isset($_POST['lucky_enabled']) ? '1' : '0';

        // Ensure table exists
        @odbc_exec($conn, "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) CREATE TABLE PS_UserData.dbo.Web_Settings (SettingId INT IDENTITY(1,1) PRIMARY KEY, SettingKey VARCHAR(100) NOT NULL UNIQUE, SettingValue VARCHAR(MAX) NOT NULL)");

        $check = odbc_exec($conn, "SELECT SettingKey FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'LuckyChestEnabled'");
        if (odbc_fetch_array($check)) {
            $upd = odbc_exec($conn, "UPDATE PS_UserData.dbo.Web_Settings SET SettingValue = '$luckyEnabled' WHERE SettingKey = 'LuckyChestEnabled'");
        } else {
            $upd = odbc_exec($conn, "INSERT INTO PS_UserData.dbo.Web_Settings (SettingKey, SettingValue) VALUES ('LuckyChestEnabled', '$luckyEnabled')");
        }

        if ($upd) {
            $msg = "Lucky Chest status updated successfully.";
            $status = "success";
        } else {
            $msg = "Failed to update Lucky Chest status.";
        }
        break;

    case 'luckychest_reset_all':
        if ($adminStatus != 16) {
            $msg = "Unauthorized.";
            $status = "error";
            break;
        }

        $del = odbc_exec($conn, "DELETE FROM PS_UserData.dbo.Web_LuckyCase");
        if ($del) {
            $msg = "All player lucky chest timers have been reset.";
            $status = "success";
        } else {
            $msg = "Failed to reset timers. Ensure the table exists.";
        }
        break;

    case 'luckychest_reset_user':
        if ($adminStatus != 16) {
            $msg = "Unauthorized.";
            $status = "error";
            break;
        }

        $userUID = isset($_POST['user_uid']) ? (int) $_POST['user_uid'] : 0;
        if ($userUID <= 0) {
            $msg = "Invalid UserUID provided.";
            $status = "error";
            break;
        }

        $del = odbc_exec($conn, "DELETE FROM PS_UserData.dbo.Web_LuckyCase WHERE UserUID = $userUID");
        if ($del) {
            $msg = "Lucky chest timer for UserUID $userUID has been reset.";
            $status = "success";
        } else {
            $msg = "Failed to reset timer for UserUID $userUID.";
        }
        break;

    case 'drops_config':
        if ($adminStatus != 16) {
            $msg = "Unauthorized.";
            $status = "error";
            break;
        }

        $dropsMaxGrade = isset($_POST['drops_max_grade']) ? (int) $_POST['drops_max_grade'] : 3072;
        $dropsMaxLevel = isset($_POST['drops_max_level']) ? (int) $_POST['drops_max_level'] : 80;
        $dropsHideZero = isset($_POST['drops_hide_zero']) ? '1' : '0';
        $dropsHideEmpty = isset($_POST['drops_hide_empty']) ? '1' : '0';

        @odbc_exec($conn, "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) CREATE TABLE PS_UserData.dbo.Web_Settings (SettingId INT IDENTITY(1,1) PRIMARY KEY, SettingKey VARCHAR(100) NOT NULL UNIQUE, SettingValue VARCHAR(MAX) NOT NULL)");

        $settings = [
            'DropsMaxGrade' => $dropsMaxGrade,
            'DropsMaxLevel' => $dropsMaxLevel,
            'DropsHideZero' => $dropsHideZero,
            'DropsHideEmpty' => $dropsHideEmpty
        ];

        $successCount = 0;
        foreach ($settings as $key => $val) {
            $checkKey = odbc_exec($conn, "SELECT SettingKey FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = '$key'");
            if (odbc_fetch_array($checkKey)) {
                $upd = odbc_exec($conn, "UPDATE PS_UserData.dbo.Web_Settings SET SettingValue = '$val' WHERE SettingKey = '$key'");
            } else {
                $upd = odbc_exec($conn, "INSERT INTO PS_UserData.dbo.Web_Settings (SettingKey, SettingValue) VALUES ('$key', '$val')");
            }
            if ($upd)
                $successCount++;
        }

        if ($successCount === count($settings)) {
            $msg = "Drop Settings updated successfully.";
            $status = "success";
        } else {
            $msg = "Some settings failed to update.";
        }
        break;

    case 'drops_blacklist_add':
    case 'drops_blacklist_remove':
        if ($adminStatus != 16) {
            $msg = "Unauthorized.";
            $status = "error";
            break;
        }

        $targetItemId = isset($_POST['itemid']) ? (int) $_POST['itemid'] : 0;
        if ($targetItemId <= 0) {
            $msg = "Invalid Item ID.";
            break;
        }

        @odbc_exec($conn, "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) CREATE TABLE PS_UserData.dbo.Web_Settings (SettingId INT IDENTITY(1,1) PRIMARY KEY, SettingKey VARCHAR(100) NOT NULL UNIQUE, SettingValue VARCHAR(MAX) NOT NULL)");

        $blacklistStr = '98005,98006,98007,98009,98012,98018,98019,98020,98025,98022,98013,98010,98011,98001,98002,98014,98015,98026,98023,98024,98004,98008,98021,98017,98003,98016,38147,38149,38151,38150,38148,72037,87037,87017,87057,72017,72057,38170,38170,44032,44039,44033,44034,44035,44036,44037,44038,44040,44041,41164,44138,44141';
        $check = odbc_exec($conn, "SELECT CAST(SettingValue AS VARCHAR(MAX)) as SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'DropsBlacklist'");
        $row = odbc_fetch_array($check);
        if ($row) {
            if ($row['SettingValue'] !== '')
                $blacklistStr = $row['SettingValue'];
        }

        $arr = [];
        if (!empty($blacklistStr)) {
            $arr = array_map('trim', explode(',', $blacklistStr));
            $arr = array_filter($arr, function ($v) {
                return $v !== '';
            });
        }

        $local_error = false;
        if ($action == 'drops_blacklist_add') {
            if (!in_array((string) $targetItemId, $arr)) {
                array_unshift($arr, (string) $targetItemId);
                $msg = "ItemID $targetItemId added to blacklist.";
            } else {
                $msg = "ItemID $targetItemId is already in the blacklist.";
                $local_error = true;
            }
        } else {
            $key = array_search((string) $targetItemId, $arr);
            if ($key !== false) {
                unset($arr[$key]);
                $msg = "ItemID $targetItemId removed from blacklist.";
            } else {
                $msg = "ItemID $targetItemId not found in blacklist.";
                $local_error = true;
            }
        }

        if (!$local_error) {
            $status = "success";
            $newList = implode(',', $arr);
            if ($row) {
                odbc_exec($conn, "UPDATE PS_UserData.dbo.Web_Settings SET SettingValue = '$newList' WHERE SettingKey = 'DropsBlacklist'");
            } else {
                odbc_exec($conn, "INSERT INTO PS_UserData.dbo.Web_Settings (SettingKey, SettingValue) VALUES ('DropsBlacklist', '$newList')");
            }
        } else {
            $status = "error";
        }
        break;


    case 'points_add':
        $targetUser = isset($_POST['target_user']) ? trim($_POST['target_user']) : '';
        $amount = isset($_POST['point_amount']) ? (int) $_POST['point_amount'] : 0;
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        $gmInfo = "$username($adminStatus)";

        if (!$targetUser || $amount <= 0 || !$reason) {
            $msg = "UserID, a positive Point amount, and a Reason are required.";
        } else {
            // Check if user exists
            $checkQ = odbc_exec($conn, "SELECT UserUID FROM PS_UserData.dbo.Users_Master WHERE UserID = '$targetUser'");
            if ($u = odbc_fetch_array($checkQ)) {
                // Update Points
                $update = odbc_exec($conn, "UPDATE PS_UserData.dbo.Users_Master SET Point = Point + $amount WHERE UserID = '$targetUser'");
                if ($update) {
                    // Log to History
                    $logSql = "INSERT INTO PS_UserData.dbo.Web_PointHistory (UserID, PointsAdded, Reason, GM_Account, [Date]) 
                               VALUES ('$targetUser', $amount, '$reason', 'Sent by $gmInfo', GETDATE())";
                    odbc_exec($conn, $logSql);

                    $msg = "Successfully added " . number_format($amount) . " points to $targetUser.";
                    $status = "success";
                } else {
                    $msg = "Error updating points in database.";
                }
            } else {
                $msg = "UserID not found.";
            }
        }
        break;

    case 'restore_dropped_item':
        $logRow = isset($_POST['log_row']) ? (int) $_POST['log_row'] : 0;
        $charID = isset($_POST['char_id']) ? (int) $_POST['char_id'] : 0;

        if (!$logRow || !$charID) {
            $msg = "Invalid parameters for restoration.";
            break;
        }

        // 1. Fetch character and UserUID
        $charQ = odbc_exec($conn, "SELECT UserUID, LoginStatus FROM PS_GameData.dbo.Chars WHERE CharID = $charID");
        $char = odbc_fetch_array($charQ);
        if (!$char) {
            $msg = "Character not found.";
            break;
        }
        if ((int) $char['LoginStatus'] === 1) {
            $msg = "Player is currently online. Please kick them first.";
            break;
        }
        $targetUID = $char['UserUID'];

        // 2. Fetch log entry
        $logQ = odbc_exec($conn, "SELECT * FROM PS_GameLog.dbo.ActionLog WHERE [row] = $logRow");
        $log = odbc_fetch_array($logQ);
        if (!$log || (int) $log['ActionType'] !== 112) {
            $msg = "Valid drop log entry not found.";
            break;
        }

        $itemUID = $log['Value1'];
        $itemID = $log['Value2'];
        $type = floor($itemID / 1000);
        $typeID = $itemID % 1000;
        $details = $log['Text2']; // Template: Gem1,Gem2,Gem3,Gem4,Gem5,Gem6(ItemID) : Craftname

        // 3. Safety Check: Does item already exist?
        $exists1 = odbc_exec($conn, "SELECT TOP 1 ItemUID FROM PS_GameData.dbo.CharItems WHERE ItemUID = $itemUID");
        if (odbc_fetch_array($exists1)) {
            $msg = "Item already exists in a character inventory.";
            break;
        }
        $exists2 = odbc_exec($conn, "SELECT TOP 1 ItemUID FROM PS_GameData.dbo.UserStoredItems WHERE ItemUID = $itemUID");
        if (odbc_fetch_array($exists2)) {
            $msg = "Item already exists in the warehouse.";
            break;
        }
        $exists3 = odbc_exec($conn, "SELECT TOP 1 ItemUID FROM PS_GameData.dbo.MarketItems WHERE ItemUID = $itemUID");
        if (odbc_fetch_array($exists3)) {
            $msg = "Item already exists in the market.";
            break;
        }

        // 4. Parse Gems and Craftname
        $gems = [0, 0, 0, 0, 0, 0];
        $craftname = "";

        $bracketPos = strpos($details, "(");
        if ($bracketPos !== false) {
            $gemsPart = trim(substr($details, 0, $bracketPos));
            $gemsArr = explode(",", $gemsPart);
            for ($i = 0; $i < 6; $i++) {
                if (isset($gemsArr[$i]))
                    $gems[$i] = (int) $gemsArr[$i];
            }
        }

        $colonPos = strpos($details, ":");
        if ($colonPos !== false) {
            $craftname = trim(substr($details, $colonPos + 1, 20));
        }

        // 5. Find free Slot in Warehouse
        $slotQ = odbc_exec($conn, "SELECT Slot FROM PS_GameData.dbo.UserStoredItems WHERE UserUID = $targetUID ORDER BY Slot ASC");
        $slot = 0;
        while ($s = odbc_fetch_array($slotQ)) {
            if ($slot != (int) $s['Slot'])
                break;
            $slot++;
        }
        if ($slot >= 240) {
            $msg = "User's Warehouse is full (Slot $slot).";
            break;
        }

        // 6. Restore Item
        $sql = "INSERT INTO PS_GameData.dbo.UserStoredItems (ServerID, UserUID, ItemID, ItemUID, Type, TypeID, Slot, Quality, Gem1, Gem2, Gem3, Gem4, Gem5, Gem6, Craftname, [Count], Maketime, Maketype, Del)
                VALUES (1, $targetUID, $itemID, $itemUID, $type, $typeID, $slot, 0, $gems[0], $gems[1], $gems[2], $gems[3], $gems[4], $gems[5], '$craftname', 1, GETDATE(), 'X', 0)";

        if (odbc_exec($conn, $sql)) {
            $status = "success";
            $msg = "Item '{$log['Text1']}' successfully restored to Warehouse (Slot $slot).";

            // Log the action (Optional but good)
            $logMsg = "Admin $username restored Dropped Item $itemUID to UserUID $targetUID (Warehouse Slot $slot)";
            // odbc_exec($conn, "INSERT INTO ... Log table if exists");
        } else {
            $msg = "Database error during restoration.";
        }
        break;

    case 'user_kick':
        $userUID = isset($_POST['user_uid']) ? (int) $_POST['user_uid'] : 0;
        if ($userUID <= 0) {
            $msg = "Invalid UserUID.";
            break;
        }

        // Update LoginStatus in DB for all characters of this user
        $updateStatus = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET LoginStatus = 0 WHERE UserUID = ?");
        odbc_execute($updateStatus, [$userUID]);

        // Execute real-time kick via stored procedure if available
        try {
            $cmd = "/kickuid " . $userUID;
            $kickStmt = $pdoConn->prepare("EXEC [PS_GameDefs].[dbo].[Command] @serviceName = N'ps_game', @cmmd = ?");
            if ($kickStmt->execute([$cmd])) {
                $status = "success";
                $msg = "Kick command sent successfully for UserUID $userUID.";
            } else {
                $status = "success";
                $msg = "User marked offline in database (Real-time kick command failed but Status was reset).";
            }
        } catch (Exception $e) {
            $status = "success";
            $msg = "User marked offline in database.";
        }
        break;

    case 'user_update_status':
        $userUID = isset($_POST['user_uid']) ? (int) $_POST['user_uid'] : 0;
        $newStatus = isset($_POST['new_status']) ? (int) $_POST['new_status'] : 0;

        if ($userUID <= 0) {
            $msg = "Invalid UserUID.";
            break;
        }

        $update = odbc_prepare($conn, "UPDATE PS_UserData.dbo.Users_Master SET Status = ? WHERE UserUID = ?");
        if (odbc_execute($update, [$newStatus, $userUID])) {
            $status = "success";
            $msg = "Account status updated to $newStatus.";
        } else {
            $msg = "Failed to update account status.";
        }
        break;

    case 'user_change_password':
        $userUID = isset($_POST['user_uid']) ? (int) $_POST['user_uid'] : 0;
        $newPw = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

        if ($userUID <= 0 || empty($newPw)) {
            $msg = "UserUID and New Password are required.";
            break;
        }

        $update = odbc_prepare($conn, "UPDATE PS_UserData.dbo.Users_Master SET Pw = ? WHERE UserUID = ?");
        if (odbc_execute($update, [$newPw, $userUID])) {
            $status = "success";
            $msg = "Password changed successfully.";
        } else {
            $msg = "Failed to update password.";
        }
        break;


    case 'char_kick':
        $charID = isset($_POST['char_id']) ? (int) $_POST['char_id'] : 0;
        if ($charID <= 0) {
            $msg = "Invalid CharID.";
            break;
        }

        // Get UserUID for real-time kick
        $q = odbc_prepare($conn, "SELECT UserUID, CharName FROM PS_GameData.dbo.Chars WHERE CharID = ?");
        odbc_execute($q, [$charID]);
        $charData = odbc_fetch_array($q);

        if ($charData) {
            $targetUID = $charData['UserUID'];
            // Update DB status
            $u = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET LoginStatus = 0 WHERE CharID = ?");
            odbc_execute($u, [$charID]);

            // Real-time kick attempt
            try {
                $cmd = "/kickuid " . $targetUID;
                $kickStmt = $pdoConn->prepare("EXEC [PS_GameDefs].[dbo].[Command] @serviceName = N'ps_game', @cmmd = ?");
                if ($kickStmt->execute([$cmd])) {
                    $status = "success";
                    $msg = "Character '{$charData['CharName']}' kicked successfully.";
                } else {
                    $status = "success";
                    $msg = "Character '{$charData['CharName']}' marked offline in database (Real-time kick failed).";
                }
            } catch (Exception $e) {
                $status = "success";
                $msg = "Character '{$charData['CharName']}' marked offline in database.";
            }
        } else {
            $msg = "Character not found.";
        }
        break;

    case 'char_delete':
        $charID = isset($_POST['char_id']) ? (int) $_POST['char_id'] : 0;
        if ($charID <= 0) {
            $msg = "Invalid CharID.";
            break;
        }

        $update = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET Del = 1, DeleteDate = GETDATE() WHERE CharID = ?");
        if (odbc_execute($update, [$charID])) {
            $status = "success";
            $msg = "Character deleted successfully.";
        } else {
            $msg = "Failed to delete character.";
        }
        break;

    case 'char_restore':
        $charID = isset($_POST['char_id']) ? (int) $_POST['char_id'] : 0;
        if ($charID <= 0) {
            $msg = "Invalid CharID.";
            break;
        }

        $update = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET Del = 0, DeleteDate = NULL WHERE CharID = ?");
        if (odbc_execute($update, [$charID])) {
            $status = "success";
            $msg = "Character restored successfully.";
        } else {
            $msg = "Failed to restore character.";
        }
        break;

    case 'warehouse_item_delete':
        $userUID = isset($_POST['user_uid']) ? (int) $_POST['user_uid'] : 0;
        $itemUID = isset($_POST['item_uid']) ? (int) $_POST['item_uid'] : 0;

        if ($userUID <= 0 || $itemUID <= 0) {
            $msg = "Invalid parameters for deletion.";
            break;
        }

        $delete = odbc_prepare($conn, "DELETE FROM PS_GameData.dbo.UserStoredItems WHERE ItemUID = ? AND UserUID = ?");
        if (odbc_execute($delete, [$itemUID, $userUID])) {
            $status = "success";
            $msg = "Item permanently deleted from warehouse.";
        } else {
            $msg = "Failed to delete item.";
        }
        break;

    case 'guild_warehouse_item_delete':
        $guildID = isset($_POST['guild_id']) ? (int) $_POST['guild_id'] : 0;
        $itemUID = isset($_POST['item_uid']) ? (int) $_POST['item_uid'] : 0;

        if ($guildID <= 0 || $itemUID <= 0) {
            $msg = "Invalid parameters for guild item deletion.";
            break;
        }

        $delete = odbc_prepare($conn, "DELETE FROM PS_GameData.dbo.GuildStoredItems WHERE ItemUID = ? AND GuildID = ?");
        if (odbc_execute($delete, [$itemUID, $guildID])) {
            $status = "success";
            $msg = "Guild item permanently deleted.";
        } else {
            $msg = "Failed to delete guild item.";
        }
        break;

    case 'send_notice':
        $noticeText = isset($_POST['notice_text']) ? trim($_POST['notice_text']) : '';
        if (empty($noticeText)) {
            $msg = "Notice text cannot be empty.";
            break;
        }

        // Limit to 150 characters
        $noticeText = substr($noticeText, 0, 150);

        try {
            $cmd = "/nt " . $noticeText;
            $noticeStmt = $pdoConn->prepare("EXEC [PS_GameDefs].[dbo].[Command] @serviceName = N'ps_game', @cmmd = ?");
            if ($noticeStmt->execute([$cmd])) {
                $status = "success";
                $msg = "Server notice sent successfully!";
            } else {
                $msg = "Failed to execute notice command.";
            }
        } catch (Exception $e) {
            $msg = "Error sending notice: " . $e->getMessage();
        }
        break;

    default:
        $msg = "Invalid action.";
}

// Global redirect logic
if ($status == 'success') {
    $_SESSION['admin_success'] = $msg;
} else {
    $_SESSION['admin_error'] = $msg;
}

$redirect = "admin.php";
$returnView = isset($_POST['return_view']) ? $_POST['return_view'] : 'Dashboard';
if ($returnView) {
    $redirect .= "?view=" . urlencode($returnView);
    // Add additional parameters for specific views
    if ($returnView === 'GUILD_OVERVIEW' && isset($_POST['guild_id'])) {
        $redirect .= "&id=" . (int) $_POST['guild_id'];
    }
    if ($returnView === 'CharEdit' && isset($_POST['char_id'])) {
        $redirect .= "&id=" . (int) $_POST['char_id'];
        if (isset($_POST['tab'])) {
            $redirect .= "&tab=" . urlencode($_POST['tab']);
        }
    }
    if ($returnView === 'UserEdit' && isset($_POST['user_uid'])) {
        $redirect .= "&uid=" . (int) $_POST['user_uid'];
        if (isset($_POST['tab'])) {
            $redirect .= "&tab=" . urlencode($_POST['tab']);
        }
    }
}
header("Location: $redirect");
exit;
?>