<?php
session_start();
ob_start();
require_once 'db.php';
require_once 'db_pdo.php';

function formatRelativeTime($date, $limit = 2)
{
    if (!$date || strpos($date, '1900') !== false)
        return "Never";
    $diff = time() - strtotime($date);
    if ($diff < 60)
        return "Just now";

    $days = floor($diff / 86400);
    $diff %= 86400;
    $hours = floor($diff / 3600);
    $diff %= 3600;
    $minutes = floor($diff / 60);

    $parts = [];
    if ($days > 0)
        $parts[] = $days . "d";
    if ($hours > 0)
        $parts[] = $hours . "h";
    if ($minutes > 0)
        $parts[] = $minutes . "m";

    if (empty($parts))
        return "Just now";

    return implode(", ", array_slice($parts, 0, $limit)) . " ago";
}

function getJobName($jobId)
{
    $jobs = [
        0 => 'Fighter/Warrior',
        1 => 'Defender/Guardian',
        2 => 'Ranger/Assassin',
        3 => 'Archer/Hunter',
        4 => 'Mage/Pagan',
        5 => 'Priest/Oracle'
    ];
    return $jobs[$jobId] ?? 'Unknown';
}

function getRaceName($familyId)
{
    $races = [
        0 => 'Human',
        1 => 'Elf',
        2 => 'Vail',
        3 => 'Deatheater'
    ];
    return $races[$familyId] ?? 'Unknown';
}

function formatPlaytime($seconds)
{
    if ($seconds <= 0)
        return "0h. 0m.";
    $days = floor($seconds / 86400);
    $seconds %= 86400;
    $hours = floor($seconds / 3600);
    $seconds %= 3600;
    $minutes = floor($seconds / 60);

    $res = "";
    if ($days > 0)
        $res .= $days . "d. ";
    if ($hours > 0 || $days > 0)
        $res .= $hours . "h. ";
    $res .= $minutes . "m.";
    return trim($res);
}

function getAdminRankIcon($kills)
{
    if ($kills < 1)
        return '';
    $ranks = [
        31 => 1000000,
        30 => 900000,
        29 => 850000,
        28 => 800000,
        27 => 750000,
        26 => 700000,
        25 => 650000,
        24 => 600000,
        23 => 550000,
        22 => 500000,
        21 => 450000,
        20 => 400000,
        19 => 350000,
        18 => 300000,
        17 => 250000,
        16 => 200000,
        15 => 150000,
        14 => 130000,
        13 => 110000,
        12 => 90000,
        11 => 70000,
        10 => 50000,
        9 => 36320,
        8 => 26320,
        7 => 19320,
        6 => 14320,
        71 => 10520,
        4 => 7720,
        3 => 5620,
        2 => 50,
        1 => 1
    ];
    foreach ($ranks as $rankNum => $minKills) {
        if ($kills >= $minKills) {
            $yPos = -($rankNum - 1) * 32;
            return '<span style="background: url(\'assets/ranks.png\') no-repeat 0px ' . $yPos . 'px; display: inline-block; width: 32px; height: 16px; vertical-align: middle; margin-left:8px; opacity:0.8;"></span>';
        }
    }
    return '';
}

function getMapName($mapid)
{
    $maps = [
        0 => 'D-Water',
        1 => 'Map 1 Light',
        2 => 'Map 1 Dark',
        3 => 'D1',
        4 => 'D1.2',
        5 => 'Cornwells 1',
        6 => 'Cornwells 2',
        7 => 'Argilla 1',
        8 => 'Argilla 2',
        9 => 'D2',
        10 => 'D2.2',
        11 => 'D2 Floor 3',
        12 => 'Cloron 1',
        13 => 'Cloron 2',
        14 => 'Cloron 3',
        15 => 'Fantasma 1',
        16 => 'Fantasma 2',
        17 => 'Fantasma 3',
        18 => 'Proelium',
        19 => 'Willieoseu',
        20 => 'Keuraijen',
        21 => 'Maitreian 1',
        22 => 'Maitreian 2',
        23 => 'Aidion 1',
        24 => 'Aidion 2',
        25 => 'Elemental Cave',
        26 => 'Ruber Chaos',
        27 => 'Ruber Chaos',
        28 => 'Map 3 Light',
        29 => 'Map 3 Dark',
        30 => 'Cantabilian',
        31 => '20-30 Dung Light',
        32 => '20-30 Dung Dark',
        33 => 'Fedion',
        34 => 'Kalamus',
        35 => 'Apulune',
        36 => 'Iris',
        37 => 'Stigma',
        38 => 'Aurizen',
        43 => 'Skulleron',
        44 => 'Astenes',
        45 => 'Deep Desert 1',
        46 => 'Deep Desert 2',
        47 => 'Jungle',
        70 => 'Kanos Illum',
        80 => 'Canyon of Greed'
    ];
    return $maps[$mapid] ?? "Map $mapid";
}

function getChatTypeName($type)
{
    $types = [
        0 => 'Whisper',
        1 => 'Normal',
        2 => 'Yell/Other',
        3 => 'Guild',
        4 => 'Party',
        5 => 'Trade',
        6 => 'Yell/Other',
        7 => 'Area',
        8 => 'GM'
    ];
    return $types[$type] ?? "Type $type";
}

function castToUInt($int)
{
    return $int < 0 ? 4294967296 + $int : $int;
}

function getCraftnameLabel($craft)
{
    if (empty($craft) || $craft === '00000000000000000000')
        return '';
    $s = [
        'STR' => (int) substr($craft, 0, 2),
        'DEX' => (int) substr($craft, 2, 2),
        'REC' => (int) substr($craft, 4, 2),
        'INT' => (int) substr($craft, 6, 2),
        'WIS' => (int) substr($craft, 8, 2),
        'LUC' => (int) substr($craft, 10, 2)
    ];
    $labels = [];
    foreach ($s as $k => $v)
        if ($v > 0)
            $labels[] = "$k+$v";
    return implode(' ', $labels);
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['user'];

// Secure fetch: Re-check Admin status (16)
$user_sql = "SELECT Status FROM PS_UserData.dbo.Users_Master WHERE UserID = ?";
$user_res = odbc_prepare($conn, $user_sql);
odbc_execute($user_res, [$username]);
$user_data = odbc_fetch_array($user_res);
$userStatus = (int) ($user_data['Status'] ?? 0);

if (!in_array($userStatus, [16, 32, 48])) {
    header("Location: account.php"); // Bounce non-admins back to safety
    exit;
}

// Load Permissions
$gm_perms_query = odbc_exec($conn, "SELECT SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'GM_Permissions'");
$gm_perms_row = odbc_fetch_array($gm_perms_query);
$gm_perms = ($gm_perms_row && !empty($gm_perms_row['SettingValue'])) ? json_decode($gm_perms_row['SettingValue'], true) : [];

$gma_perms_query = odbc_exec($conn, "SELECT SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'GMA_Permissions'");
$gma_perms_row = odbc_fetch_array($gma_perms_query);
$gma_perms = ($gma_perms_row && !empty($gma_perms_row['SettingValue'])) ? json_decode($gma_perms_row['SettingValue'], true) : [];

$my_perms = [];
$is_full_admin = ($userStatus === 16);
if ($userStatus === 32) $my_perms = $gm_perms;
if ($userStatus === 48) $my_perms = $gma_perms;

function hasAdminAccess($reqView) {
    global $is_full_admin, $my_perms;
    if ($is_full_admin) return true;
    
    $reqView = strtoupper($reqView);
    if ($reqView === 'USEREDIT') $reqView = 'USERS';
    if ($reqView === 'CHAREDIT') $reqView = 'CHARS';
    if ($reqView === 'ITEMEDIT') $reqView = 'ITEMS';
    
    return in_array($reqView, $my_perms);
}

// Enforce Access
$current_view = strtoupper($_GET['view'] ?? 'DASHBOARD');
if (!hasAdminAccess($current_view)) {
    if (!$is_full_admin && count($my_perms) > 0) {
        $fallback = strtolower($my_perms[0]);
        header("Location: admin.php?view=$fallback");
    } else {
        header("Location: account.php");
    }
    exit;
}

// --- Handle Admin Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $action = $_POST['admin_action'];
    $charID = isset($_POST['char_id']) ? (int) $_POST['char_id'] : 0;

    if ($action === 'save_permissions') {
        if ($userStatus === 16) { // Only ADM
            $p_32 = isset($_POST['perm_32']) && is_array($_POST['perm_32']) ? $_POST['perm_32'] : [];
            $p_48 = isset($_POST['perm_48']) && is_array($_POST['perm_48']) ? $_POST['perm_48'] : [];
            
            $j_32 = json_encode(array_map('strtoupper', $p_32));
            $j_48 = json_encode(array_map('strtoupper', $p_48));

            // GM
            $c32 = odbc_exec($conn, "SELECT 1 FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'GM_Permissions'");
            if (odbc_fetch_array($c32)) {
                $stmt = odbc_prepare($conn, "UPDATE PS_UserData.dbo.Web_Settings SET SettingValue = ? WHERE SettingKey = 'GM_Permissions'");
                odbc_execute($stmt, [$j_32]);
            } else {
                $stmt = odbc_prepare($conn, "INSERT INTO PS_UserData.dbo.Web_Settings (SettingKey, SettingValue) VALUES ('GM_Permissions', ?)");
                odbc_execute($stmt, [$j_32]);
            }

            // GMA
            $c48 = odbc_exec($conn, "SELECT 1 FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'GMA_Permissions'");
            if (odbc_fetch_array($c48)) {
                $stmt = odbc_prepare($conn, "UPDATE PS_UserData.dbo.Web_Settings SET SettingValue = ? WHERE SettingKey = 'GMA_Permissions'");
                odbc_execute($stmt, [$j_48]);
            } else {
                $stmt = odbc_prepare($conn, "INSERT INTO PS_UserData.dbo.Web_Settings (SettingKey, SettingValue) VALUES ('GMA_Permissions', ?)");
                odbc_execute($stmt, [$j_48]);
            }

            $_SESSION['admin_success'] = "Permissions have been successfully saved.";
        } else {
            $_SESSION['admin_error'] = "Only ADM (Status 16) can modify permissions.";
        }
        header("Location: admin.php?view=permissions_management");
        exit;
    }

    if ($action === 'rename' && $charID > 0) {
        $newName = isset($_POST['new_name']) ? trim($_POST['new_name']) : '';
        if (!empty($newName)) {
            // Update CharName in PS_GameData.dbo.Chars
            $stmt = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET CharName = ? WHERE CharID = ?");
            if (odbc_execute($stmt, [$newName, $charID])) {
                header("Location: admin.php?view=CharEdit&id=$charID&msg=renamed");
                exit;
            }
        }
    }

    if ($action === 'update_level' && $charID > 0) {
        $newLevel = isset($_POST['new_level']) ? (int) $_POST['new_level'] : 0;
        if ($newLevel >= 1 && $newLevel <= 80) { // Max level is now 80
            $stmt = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET [Level] = ? WHERE CharID = ?");
            if (odbc_execute($stmt, [$newLevel, $charID])) {
                header("Location: admin.php?view=CharEdit&id=$charID&msg=level_updated");
                exit;
            }
        }
    }

    if ($action === 'change_map' && $charID > 0) {
        $newMap = isset($_POST['new_map']) ? (int) $_POST['new_map'] : 0;
        if ($newMap >= 0 && $newMap <= 109) {
            // Update Map and reset coordinates to safe defaults
            $stmt = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET [Map] = ?, PosX = 50, PosY = 0, PosZ = 50 WHERE CharID = ?");
            if (odbc_execute($stmt, [$newMap, $charID])) {
                header("Location: admin.php?view=CharEdit&id=$charID&msg=map_changed");
                exit;
            }
        }
    }

    if ($action === 'send_gold' && $charID > 0) {
        $goldToAdd = isset($_POST['gold_amount']) ? (int) $_POST['gold_amount'] : 0;

        if ($goldToAdd > 500000000) {
            header("Location: admin.php?view=CharEdit&id=$charID&msg=gold_limit");
            exit;
        }

        // Fetch current money
        $res = odbc_prepare($conn, "SELECT Money FROM PS_GameData.dbo.Chars WHERE CharID = ?");
        odbc_execute($res, [$charID]);
        if ($currentData = odbc_fetch_array($res)) {
            $currentMoney = (int) $currentData['Money'];
            $newMoney = $currentMoney + $goldToAdd;

            // Clamp between 0 and 2,000,000,000 (Max INT is 2.1B)
            if ($newMoney < 0)
                $newMoney = 0;
            if ($newMoney > 2000000000)
                $newMoney = 2000000000;

            $stmt = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET Money = ? WHERE CharID = ?");
            if (odbc_execute($stmt, [$newMoney, $charID])) {
                header("Location: admin.php?view=CharEdit&id=$charID&msg=gold_sent");
                exit;
            }
        }
    }

    if ($action === 'delete_item' && isset($_POST['item_uid'])) {
        $itemUID = (int) $_POST['item_uid'];
        if ($itemUID > 0) {
            // Soft delete the item from inventory
            $stmt = odbc_prepare($conn, "UPDATE PS_GameData.dbo.CharItems SET Del = 1 WHERE ItemUID = ? AND CharID = ?");
            if (odbc_execute($stmt, [$itemUID, $charID])) {
                header("Location: admin.php?view=CharEdit&id=$charID&tab=Inventory&msg=item_deleted");
                exit;
            }
        }
    }

    if ($action === 'update_item' && isset($_POST['item_uid'])) {
        $itemUID = (int) $_POST['item_uid'];
        $charID = (int) $_POST['char_id'];

        $str = str_pad(min(50, max(0, (int) ($_POST['stat_str'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $dex = str_pad(min(50, max(0, (int) ($_POST['stat_dex'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $rec = str_pad(min(50, max(0, (int) ($_POST['stat_rec'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $int = str_pad(min(50, max(0, (int) ($_POST['stat_int'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $wis = str_pad(min(50, max(0, (int) ($_POST['stat_wis'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $luc = str_pad(min(50, max(0, (int) ($_POST['stat_luc'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $hp = str_pad(min(50, max(0, (int) ($_POST['stat_hp'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $sp = str_pad(min(50, max(0, (int) ($_POST['stat_sp'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $mp = str_pad(min(50, max(0, (int) ($_POST['stat_mp'] ?? 0))), 2, '0', STR_PAD_LEFT);
        $ench = str_pad(min(20, max(0, (int) ($_POST['stat_ench'] ?? 0))), 2, '0', STR_PAD_LEFT);

        $newCraftname = $str . $dex . $rec . $int . $wis . $luc . $hp . $sp . $mp . $ench;

        $gem1 = (int) ($_POST['gem1'] ?? 0);
        $gem2 = (int) ($_POST['gem2'] ?? 0);
        $gem3 = (int) ($_POST['gem3'] ?? 0);
        $gem4 = (int) ($_POST['gem4'] ?? 0);
        $gem5 = (int) ($_POST['gem5'] ?? 0);
        $gem6 = (int) ($_POST['gem6'] ?? 0);

        $updateSql = "UPDATE PS_GameData.dbo.CharItems 
                      SET Craftname = ?, Gem1 = ?, Gem2 = ?, Gem3 = ?, Gem4 = ?, Gem5 = ?, Gem6 = ? 
                      WHERE ItemUID = ? AND CharID = ?";
        $stmt = odbc_prepare($conn, $updateSql);
        if (odbc_execute($stmt, [$newCraftname, $gem1, $gem2, $gem3, $gem4, $gem5, $gem6, $itemUID, $charID])) {
            header("Location: admin.php?view=CharEdit&id=$charID&tab=Inventory&msg=item_updated");
            exit;
        }
    }


    if ($action === 'delete_char' && $charID > 0) {
        $stmt = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET Del = 1 WHERE CharID = ?");
        if (odbc_execute($stmt, [$charID])) {
            header("Location: admin.php?view=CharEdit&id=$charID&msg=char_deleted");
            exit;
        }
    }

    if ($action === 'resurrect_char' && $charID > 0) {
        // Find UserUID first
        $q = odbc_prepare($conn, "SELECT UserUID FROM PS_GameData.dbo.Chars WHERE CharID = ?");
        odbc_execute($q, [$charID]);
        if ($cData = odbc_fetch_array($q)) {
            $uUID = $cData['UserUID'];

            // Check occupied slots
            $occupied = [];
            $sRes = odbc_prepare($conn, "SELECT [Slot] FROM PS_GameData.dbo.Chars WHERE UserUID = ? AND Del = 0");
            odbc_execute($sRes, [$uUID]);
            while ($sRow = odbc_fetch_array($sRes)) {
                $occupied[] = (int) $sRow['Slot'];
            }

            $newSlot = -1;
            for ($i = 0; $i <= 4; $i++) {
                if (!in_array($i, $occupied)) {
                    $newSlot = $i;
                    break;
                }
            }

            if ($newSlot !== -1) {
                $upd = odbc_prepare($conn, "UPDATE PS_GameData.dbo.Chars SET Del = 0, [Slot] = ? WHERE CharID = ?");
                if (odbc_execute($upd, [$newSlot, $charID])) {
                    header("Location: admin.php?view=CharEdit&id=$charID&msg=char_resurrected");
                    exit;
                }
            } else {
                header("Location: admin.php?view=CharEdit&id=$charID&msg=resurrect_no_slot");
                exit;
            }
        }
    }
}

// --- Fetch Top Bar Stats ---
$charsOnline = 0;
$usersToday = 0;
$totalAccounts = 0;
$totalChars = 0;

// Chars Online
$qOnline = odbc_exec($conn, "SELECT COUNT(*) as Total FROM PS_GameData.dbo.Chars WHERE LoginStatus = 1");
if ($row = odbc_fetch_array($qOnline))
    $charsOnline = (int) $row['Total'];

// Users Registered Today
$qUsersToday = odbc_exec($conn, "SELECT COUNT(*) as Total FROM PS_UserData.dbo.Users_Master WHERE CAST(JoinDate AS DATE) = CAST(GETDATE() AS DATE)");
if ($row = odbc_fetch_array($qUsersToday))
    $usersToday = (int) $row['Total'];

// Total Metrics
$qTotalAcc = odbc_exec($conn, "SELECT COUNT(*) as Total FROM PS_UserData.dbo.Users_Master");
if ($row = odbc_fetch_array($qTotalAcc))
    $totalAccounts = (int) $row['Total'];

$qTotalChars = odbc_exec($conn, "SELECT COUNT(*) as Total FROM PS_GameData.dbo.Chars WHERE Del = 0");
if ($row = odbc_fetch_array($qTotalChars))
    $totalChars = (int) $row['Total'];
// --------------------------
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>

<body class="admin-page">
    <?php $active_page = 'admin'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box admin-container"
            style="padding: 0; overflow: hidden; border: 1px solid rgba(232, 200, 129, 0.1);">
            <!-- Admin Dashboard Header (Integrated) -->
            <div class="admin-top-stats">
                <div class="stats-left">
                    <div class="stat-badge bg-green"><?php echo ($charsOnline == 1) ? 'CHAR' : 'CHARS'; ?> ONLINE: <?php echo $charsOnline; ?></div>
                    <div class="stat-badge bg-blue">USERS REGISTERED TODAY: <?php echo $usersToday; ?></div>
                </div>
                <div class="stats-right">
                    <span>TOTAL ACCOUNTS: <b><?php echo $totalAccounts; ?></b></span>
                    <span>TOTAL CHARS: <b><?php echo $totalChars; ?></b></span>
                </div>
            </div>

            <!-- Horizontal Nav -->
            <nav class="admin-horizontal-nav">
                <?php
                // Note: User's selected view is now cached in $current_view
                function isActive($v, $current)
                {
                    return ($v === $current) ? 'active' : '';
                }
                ?>
                <ul>
                    <?php if (hasAdminAccess('DASHBOARD')): ?><li><a href="admin.php?view=Dashboard" class="<?php echo isActive('DASHBOARD', $current_view); ?>">DASHBOARD</a></li><?php endif; ?>
                    <?php if (hasAdminAccess('SEND_NOTICE')): ?><li><a href="admin.php?view=send_notice" class="<?php echo isActive('SEND_NOTICE', $current_view); ?>">SERVER NOTICE</a></li><?php endif; ?>
                    <?php if (hasAdminAccess('USERS')): ?><li><a href="admin.php?view=Users" class="<?php echo isActive('USERS', $current_view); ?>">USERS</a></li><?php endif; ?>
                    <?php if (hasAdminAccess('CHARS')): ?><li><a href="admin.php?view=Chars" class="<?php echo isActive('CHARS', $current_view); ?>">CHARS</a></li><?php endif; ?>
                    <?php if (hasAdminAccess('ITEMS')): ?><li><a href="admin.php?view=Items" class="<?php echo isActive('ITEMS', $current_view); ?>">ITEMS</a></li><?php endif; ?>
                    <?php if (hasAdminAccess('GUILDS')): ?><li><a href="admin.php?view=Guilds" class="<?php echo isActive('GUILDS', $current_view); ?>">GUILDS</a></li><?php endif; ?>
                    <?php if (hasAdminAccess('GIFTBOX')): ?><li><a href="admin.php?view=Giftbox" class="<?php echo isActive('GIFTBOX', $current_view); ?>">GIFTBOX</a></li><?php endif; ?>
                    <?php if (hasAdminAccess('POINTS')): ?><li><a href="admin.php?view=Points" class="<?php echo isActive('POINTS', $current_view); ?>">POINTS</a></li><?php endif; ?>
                    
                    <?php 
                    $hasWebsiteChild = hasAdminAccess('DROPS_BLACKLIST_ADD') || hasAdminAccess('LUCKYCHEST_TOGGLE') || hasAdminAccess('DOWNLOADS_UPDATE') || hasAdminAccess('REGISTER_TOGGLE') || hasAdminAccess('PERMISSIONS_MANAGEMENT');
                    if ($hasWebsiteChild): 
                    ?>
                    <li class="admin-has-dropdown">
                        <a href="#"
                            class="<?php echo ($current_view === 'OTHER' || strpos($current_view, 'DROPS') !== false || strpos($current_view, 'LUCKYCHEST') !== false || $current_view === 'DOWNLOADS_UPDATE' || $current_view === 'REGISTER_TOGGLE' || $current_view === 'PERMISSIONS_MANAGEMENT') ? 'active' : ''; ?>">WEBSITE
                            <i class="fas fa-caret-down"></i></a>
                        <ul class="admin-dropdown-menu">
                            <?php if (hasAdminAccess('DROPS_BLACKLIST_ADD')): ?><li><a href="admin.php?view=drops_blacklist_add">Drops Management</a></li><?php endif; ?>
                            <?php if (hasAdminAccess('LUCKYCHEST_TOGGLE')): ?><li><a href="admin.php?view=luckychest_toggle">LuckyChest Management</a></li><?php endif; ?>
                            <?php if (hasAdminAccess('DOWNLOADS_UPDATE')): ?><li><a href="admin.php?view=downloads_update">Downloads Management</a></li><?php endif; ?>
                            <?php if (hasAdminAccess('REGISTER_TOGGLE')): ?><li><a href="admin.php?view=register_toggle">Register Management</a></li><?php endif; ?>
                            <?php if (hasAdminAccess('PERMISSIONS_MANAGEMENT')): ?><li><a href="admin.php?view=permissions_management">Permissions Management</a></li><?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasAdminAccess('ACTIONLOG')): ?>
                    <li><a href="admin.php?view=ActionLog"
                            class="<?php echo (strpos($current_view, 'LOG') !== false) ? 'active' : ''; ?>">LOGS</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <section class="admin-content-area" style="padding: 30px;">
                <?php if (isset($_SESSION['admin_success'])): ?>
                    <div class="alert alert-success"
                        style="background: rgba(72, 187, 120, 0.1); color: #48bb78; padding: 15px; border: 1px solid rgba(72, 187, 120, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $_SESSION['admin_success'];
                        unset($_SESSION['admin_success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['admin_error'])): ?>
                    <div class="alert alert-error"
                        style="background: rgba(229, 62, 62, 0.1); color: #e53e3e; padding: 15px; border: 1px solid rgba(229, 62, 62, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $_SESSION['admin_error'];
                        unset($_SESSION['admin_error']); ?>
                    </div>
                <?php endif; ?>
                <?php
                $view = $_GET['view'] ?? 'DASHBOARD';
                $view = strtoupper($view);
                $view_title = str_replace('_', ' ', $view);
                $view_title = str_replace(['TOGGLE', 'DOWNLOADS MANAGEMENT'], ['MANAGEMENT', 'DOWNLOADS MANAGEMENT'], $view_title);
                // Only replace standalone UPDATE or specific ones if needed, 
                // but let's just make it simpler:
                if (strpos($view, 'DOWNLOADS') !== false) {
                    $view_title = str_replace('UPDATE', 'MANAGEMENT', $view_title);
                }
                ?>


                <?php if ($view === 'DASHBOARD'): ?>
                    <?php
                    // --- Dashboard Stats ---
                    $topCharsQ = odbc_exec($conn, "SELECT TOP 5 CharID, CharName, Level, Job, K1 as Kills FROM PS_GameData.dbo.Chars WHERE Del = 0 ORDER BY K1 DESC");
                    $topChars = [];
                    while ($tc = odbc_fetch_array($topCharsQ)) $topChars[] = $tc;

                    $recentUsersQ = odbc_exec($conn, "SELECT TOP 8 UserID, Status, JoinDate, Point FROM PS_UserData.dbo.Users_Master ORDER BY UserUID DESC");
                    $recentUsers = [];
                    while ($ru = odbc_fetch_array($recentUsersQ)) $recentUsers[] = $ru;
                    ?>

                    <!-- Bottom Grid: Top Killers + Recent Registrations -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">

                        <!-- Top Killers -->
                        <div style="background: rgba(10,10,10,0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; overflow: hidden;">
                            <div style="padding: 20px 25px; border-bottom: 1px solid rgba(255,255,255,0.04); display: flex; align-items: center; gap: 12px;">
                                <i class="fas fa-skull" style="color: #e8c881; font-size: 14px;"></i>
                                <span style="font-family: 'Futura PT', sans-serif; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: #aaa;">Top Killers</span>
                            </div>
                            <table style="width: 100%; border-collapse: collapse; font-family: 'Futura PT', sans-serif;">
                                <thead>
                                    <tr style="background: rgba(0,0,0,0.3);">
                                        <th style="padding: 10px 20px; text-align: left; font-size: 10px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">#</th>
                                        <th style="padding: 10px 20px; text-align: left; font-size: 10px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Character</th>
                                        <th style="padding: 10px 20px; text-align: center; font-size: 10px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Level</th>
                                        <th style="padding: 10px 20px; text-align: right; font-size: 10px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Kills</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topChars as $i => $tc): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                        <td style="padding: 13px 20px; color: #444; font-size: 12px; font-weight: 700;"><?php echo $i+1; ?></td>
                                        <td style="padding: 13px 20px;">
                                            <a href="admin.php?view=CharEdit&id=<?php echo $tc['CharID']; ?>" class="user-link" style="font-weight: 500; font-size: 14px;"><?php echo htmlspecialchars($tc['CharName']); ?></a>
                                            <span style="margin-left: 8px; opacity: 0.5;"><img src="assets/class/<?php echo (int)$tc['Job']; ?>.webp" style="width: 16px; vertical-align: middle;"></span>
                                        </td>
                                        <td style="padding: 13px 20px; text-align: center; color: #e8c881; font-size: 13px;"><?php echo $tc['Level']; ?></td>
                                        <td style="padding: 13px 20px; text-align: right; color: #48bb78; font-weight: 700; font-size: 14px;"><?php echo number_format($tc['Kills']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($topChars)): ?>
                                    <tr><td colspan="4" style="padding: 40px; text-align: center; color: #444;">No data available.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Recent Registrations -->
                        <div style="background: rgba(10,10,10,0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; overflow: hidden;">
                            <div style="padding: 20px 25px; border-bottom: 1px solid rgba(255,255,255,0.04); display: flex; align-items: center; gap: 12px;">
                                <i class="fas fa-user-clock" style="color: #e8c881; font-size: 14px;"></i>
                                <span style="font-family: 'Futura PT', sans-serif; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: #aaa;">Recent Registrations</span>
                            </div>
                            <table style="width: 100%; border-collapse: collapse; font-family: 'Futura PT', sans-serif;">
                                <thead>
                                    <tr style="background: rgba(0,0,0,0.3);">
                                        <th style="padding: 10px 20px; text-align: left; font-size: 10px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Username</th>
                                        <th style="padding: 10px 20px; text-align: center; font-size: 10px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Status</th>
                                        <th style="padding: 10px 20px; text-align: right; font-size: 10px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $ru): ?>
                                    <?php
                                        $ruStatus = (int)($ru['Status'] ?? 0);
                                        $ruLabel = 'Player'; $ruColor = '#555';
                                        if ($ruStatus == 16) { $ruLabel = 'ADM'; $ruColor = '#48bb78'; }
                                        elseif ($ruStatus == 32) { $ruLabel = 'GM'; $ruColor = '#3498db'; }
                                        elseif ($ruStatus == 48) { $ruLabel = 'GMA'; $ruColor = '#e8c881'; }
                                    ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                        <td style="padding: 13px 20px;">
                                            <a href="admin.php?view=USERS&search_user=<?php echo urlencode($ru['UserID']); ?>" class="user-link" style="font-weight: 500; font-size: 14px;"><?php echo htmlspecialchars($ru['UserID']); ?></a>
                                        </td>
                                        <td style="padding: 13px 20px; text-align: center;">
                                            <span style="font-size: 10px; font-weight: 700; color: <?php echo $ruColor; ?>; text-transform: uppercase; letter-spacing: 1px;"><?php echo $ruLabel; ?></span>
                                        </td>
                                        <td style="padding: 13px 20px; text-align: right; color: #666; font-size: 12px;"><?php echo date('d M Y', strtotime($ru['JoinDate'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentUsers)): ?>
                                    <tr><td colspan="3" style="padding: 40px; text-align: center; color: #444;">No data available.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                <?php elseif ($view === 'PERMISSIONS_MANAGEMENT'): ?>
                    <?php
                    // Get current settings
                    $q_32 = odbc_exec($conn, "SELECT SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'GM_Permissions'");
                    $r_32 = odbc_fetch_array($q_32);
                    $perms_32 = ($r_32 && !empty($r_32['SettingValue'])) ? json_decode($r_32['SettingValue'], true) : [];

                    $q_48 = odbc_exec($conn, "SELECT SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'GMA_Permissions'");
                    $r_48 = odbc_fetch_array($q_48);
                    $perms_48 = ($r_48 && !empty($r_48['SettingValue'])) ? json_decode($r_48['SettingValue'], true) : [];
                    
                    $pagesList = [
                        'DASHBOARD' => 'Dashboard Overview',
                        'SEND_NOTICE' => 'Server Notice (Announcement)',
                        'USERS' => 'Users Management & Edit',
                        'CHARS' => 'Chars Management & Edit',
                        'ITEMS' => 'Items Management',
                        'GUILDS' => 'Guilds Management',
                        'GIFTBOX' => 'Giftbox Management',
                        'POINTS' => 'Points Management',
                        'DROPS_BLACKLIST_ADD' => 'Drops Blacklist Management',
                        'LUCKYCHEST_TOGGLE' => 'LuckyChest Management',
                        'DOWNLOADS_UPDATE' => 'Downloads Management',
                        'REGISTER_TOGGLE' => 'Registration Toggle',
                        'PERMISSIONS_MANAGEMENT' => 'Permissions Management',
                        'ACTIONLOG' => 'Action Logs Viewer'
                    ];
                    ?>
                    <div class="char-edit-section" style="max-width: 1000px; margin: 40px auto; padding: 30px; background: rgba(15,15,15,0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
                        <h3 style="font-size: 20px; color: #e8c881; text-align: center; margin-bottom: 10px;">PERMISSIONS MANAGEMENT</h3>
                        <p style="color: #888; text-align: center; font-size: 13px; margin-bottom: 40px;">Check the boxes to allow staff ranks access to specific admin panel sections. ADM (16) always has full access.</p>
                        
                        <form action="admin.php" method="POST">
                            <input type="hidden" name="admin_action" value="save_permissions">
                            
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff; font-size: 14px;">Panel Section</th>
                                        <th style="text-align: center; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); color: #3498db; font-size: 14px; width: 150px;">GM (Status 32)</th>
                                        <th style="text-align: center; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); color: #e8c881; font-size: 14px; width: 150px;">GMA (Status 48)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagesList as $k => $label): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); background: rgba(255,255,255,0.01);">
                                        <td style="padding: 15px; color: #ccc; font-size: 13px;"><?php echo htmlspecialchars($label); ?> <span style="color:#555; font-size:10px; margin-left:10px;">(<?php echo $k; ?>)</span></td>
                                        <td style="padding: 15px; text-align: center; border-left: 1px solid rgba(255,255,255,0.02);">
                                            <input type="checkbox" name="perm_32[]" value="<?php echo $k; ?>" <?php echo in_array($k, $perms_32) ? 'checked' : ''; ?> style="transform: scale(1.3); cursor: pointer;">
                                        </td>
                                        <td style="padding: 15px; text-align: center; border-left: 1px solid rgba(255,255,255,0.02);">
                                            <input type="checkbox" name="perm_48[]" value="<?php echo $k; ?>" <?php echo in_array($k, $perms_48) ? 'checked' : ''; ?> style="transform: scale(1.3); cursor: pointer;">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div style="margin-top: 40px; text-align: center;">
                                <button type="submit" class="admin-btn" style="padding: 10px 40px; font-size: 14px;">SAVE PERMISSIONS</button>
                            </div>
                        </form>
                    </div>
                <?php elseif ($view === 'GUILD_OVERVIEW'): ?>
                    <?php
                    $guildID = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    $tab = $_GET['tab'] ?? 'Overview';
                    $q = odbc_exec($conn, "SELECT * FROM PS_GameData.dbo.Guilds WHERE GuildID = $guildID AND Del = 0");
                    $g = odbc_fetch_array($q);

                    if (!$g) {
                        echo '<div class="admin-error">Guild not found.</div>';
                    } else {
                        // Get member count
                        $countQ = odbc_exec($conn, "SELECT COUNT(*) as member_count FROM PS_GameData.dbo.GuildChars WHERE GuildID = $guildID AND Del = 0");
                        $memberCount = (odbc_fetch_array($countQ))['member_count'] ?? 0;
                        ?>
                        <div class="guild-overview-container" style="max-width: 1100px; margin: 20px auto;">
                            <!-- Header -->
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <h2
                                    style="color: #fff; font-size: 22px; font-weight: 800; font-family: 'Futura PT', sans-serif; letter-spacing: 2px; margin: 0; text-transform: uppercase;">
                                    <?php echo htmlspecialchars($g['GuildName']); ?>
                                </h2>
                                <div class="faction-badge"
                                    style="background: <?php echo ($g['Country'] == 0 ? 'rgba(52, 152, 219, 0.1)' : 'rgba(231, 76, 60, 0.1)'); ?>; color: <?php echo ($g['Country'] == 0 ? '#3498db' : '#e74c3c'); ?>; padding: 5px 15px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid <?php echo ($g['Country'] == 0 ? 'rgba(52, 152, 219, 0.2)' : 'rgba(231, 76, 60, 0.2)'); ?>;">
                                    <img src="assets/<?php echo ($g['Country'] == 0 ? 'aol.webp' : 'uof.webp'); ?>"
                                        style="width: 14px; margin-right: 8px; vertical-align: middle;">
                                    <?php echo ($g['Country'] == 0 ? 'ALLIANCE OF LIGHT' : 'UNION OF FURY'); ?>
                                </div>
                            </div>

                            <!-- Tabs -->
                            <div class="admin-tabs"
                                style="margin-bottom: 30px; display: flex; gap: 5px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1px;">
                                <a href="admin.php?view=GUILD_OVERVIEW&id=<?php echo $guildID; ?>&tab=Overview"
                                    class="tab-link <?php echo ($tab === 'Overview') ? 'active' : ''; ?>"
                                    style="padding: 12px 25px; color: <?php echo ($tab === 'Overview' ? '#e8c881' : '#666'); ?>; text-decoration: none; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid <?php echo ($tab === 'Overview' ? '#e8c881' : 'transparent'); ?>; transition: all 0.2s;">General
                                    Info</a>
                                <a href="admin.php?view=GUILD_OVERVIEW&id=<?php echo $guildID; ?>&tab=Members"
                                    class="tab-link <?php echo ($tab === 'Members') ? 'active' : ''; ?>"
                                    style="padding: 12px 25px; color: <?php echo ($tab === 'Members' ? '#e8c881' : '#666'); ?>; text-decoration: none; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid <?php echo ($tab === 'Members' ? '#e8c881' : 'transparent'); ?>; transition: all 0.2s;">Members
                                    (<?php echo $memberCount; ?>)</a>
                                <a href="admin.php?view=GUILD_OVERVIEW&id=<?php echo $guildID; ?>&tab=Warehouse"
                                    class="tab-link <?php echo ($tab === 'Warehouse') ? 'active' : ''; ?>"
                                    style="padding: 12px 25px; color: <?php echo ($tab === 'Warehouse' ? '#e8c881' : '#666'); ?>; text-decoration: none; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid <?php echo ($tab === 'Warehouse' ? '#e8c881' : 'transparent'); ?>; transition: all 0.2s;">Guild
                                    Warehouse</a>
                            </div>

                            <?php if ($tab === 'Overview'): ?>
                                <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
                                    <div>
                                        <div class="char-edit-section"
                                            style="margin-bottom: 30px; padding: 25px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); border-radius: 8px;">
                                            <h3 style="font-size: 14px; margin-bottom: 20px;">RENAME GUILD</h3>
                                            <form class="char-edit-form" action="admin_actions.php" method="POST"
                                                style="display: flex; gap: 15px; align-items: flex-end;">
                                                <input type="hidden" name="action" value="guild_rename">
                                                <input type="hidden" name="guild_id" value="<?php echo $guildID; ?>">
                                                <input type="hidden" name="return_view" value="GUILD_OVERVIEW">
                                                <div class="char-input-group" style="flex: 1; margin: 0;">
                                                    <input type="text" name="new_name" class="char-input"
                                                        value="<?php echo htmlspecialchars($g['GuildName']); ?>" required
                                                        style="height: 40px; background: rgba(0,0,0,0.3);">
                                                </div>
                                                <button type="submit" class="btn-action btn-sm"
                                                    style="height: 40px; min-width: 120px;">UPDATE</button>
                                            </form>
                                        </div>

                                        <div class="char-edit-section"
                                            style="padding: 25px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); border-radius: 8px;">
                                            <h3 style="font-size: 14px; margin-bottom: 20px;">CHANGE LEADER</h3>
                                            <form class="char-edit-form" action="admin_actions.php" method="POST"
                                                style="display: flex; gap: 15px; align-items: flex-end;">
                                                <input type="hidden" name="action" value="guild_change_leader">
                                                <input type="hidden" name="guild_id" value="<?php echo $guildID; ?>">
                                                <input type="hidden" name="return_view" value="GUILD_OVERVIEW">
                                                <div class="char-input-group" style="flex: 1; margin: 0;">
                                                    <select name="new_leader_id" class="char-input"
                                                        style="height: 40px; background: rgba(0,0,0,0.3);">
                                                        <?php
                                                        $membersQ = odbc_exec($conn, "SELECT GC.CharID, C.CharName, C.Job FROM PS_GameData.dbo.GuildChars GC JOIN PS_GameData.dbo.Chars C ON C.CharID = GC.CharID WHERE GC.GuildID = $guildID AND GC.Del = 0 ORDER BY C.CharName ASC");
                                                        while ($m = odbc_fetch_array($membersQ)) {
                                                            $selected = ($m['CharID'] == $g['MasterCharID']) ? 'selected' : '';
                                                            echo "<option value='{$m['CharID']}' $selected>" . htmlspecialchars($m['CharName']) . " (ID: {$m['CharID']})</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn-action btn-sm"
                                                    style="height: 40px; min-width: 120px; background: rgba(52, 152, 219, 0.2); border-color: #3498db;">CHANGE</button>
                                            </form>
                                        </div>
                                    </div>

                                    <div>
                                        <table class="char-info-table" style="margin: 0; background: rgba(0,0,0,0.2);">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" class="header-main" style="font-size: 11px; padding: 12px;">
                                                        STATISTICS & INFO</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="color: #666; font-size: 11px; width: 40%;">Guild ID</td>
                                                    <td style="color: #eee; font-weight: 600;"><?php echo $guildID; ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #666; font-size: 11px;">Leader</td>
                                                    <td style="color: #e8c881; font-weight: 600;">
                                                        <a href="admin.php?view=CharEdit&id=<?php echo $g['MasterCharID']; ?>"
                                                            class="user-link"
                                                            style="color: inherit;"><?php echo htmlspecialchars($g['MasterName']); ?></a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #666; font-size: 11px;">Members</td>
                                                    <td><?php echo $memberCount; ?> / 250</td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #666; font-size: 11px;">Guild Points</td>
                                                    <td style="color: #48bb78;"><?php echo number_format($g['GuildPoint']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #666; font-size: 11px;">Etin Amount</td>
                                                    <td><?php echo number_format($g['Etin'] ?? 0); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #666; font-size: 11px;">Created</td>
                                                    <td style="color: #888; font-size: 10px;">
                                                        <?php echo date('Y-m-d H:i', strtotime($g['CreateDate'])); ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            <?php elseif ($tab === 'Members'): ?>
                                <div class="chars-table-container">
                                    <table class="chars-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">Slot</th>
                                                <th style="width: 40px;"></th>
                                                <th>Name</th>
                                                <th>Level</th>
                                                <th>Class</th>
                                                <th>Join Date</th>
                                                <th style="width: 100px; text-align: center;">Leader</th>
                                                <th style="text-align: right;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $membersQ = odbc_exec($conn, "SELECT GC.*, C.CharName, C.Job, C.Level, C.RegDate FROM PS_GameData.dbo.GuildChars GC JOIN PS_GameData.dbo.Chars C ON C.CharID = GC.CharID WHERE GC.GuildID = $guildID AND GC.Del = 0 ORDER BY GC.GuildLevel ASC, C.CharName ASC");
                                            while ($m = odbc_fetch_array($membersQ)):
                                                $isLeader = ($m['CharID'] == $g['MasterCharID']);
                                                ?>
                                                <tr>
                                                    <td style="color: #555;"><?php echo $m['GuildLevel']; ?></td>
                                                    <td><img src="assets/class/<?php echo (int) $m['Job']; ?>.webp"
                                                            style="width: 20px; vertical-align: middle; opacity: 0.8;"></td>
                                                    <td><a href="admin.php?view=CharEdit&id=<?php echo $m['CharID']; ?>"
                                                            class="user-link"><?php echo htmlspecialchars($m['CharName']); ?></a></td>
                                                    <td><?php echo $m['Level']; ?></td>
                                                    <td style="font-size: 11px; color: #888;"><?php echo getJobName((int) $m['Job']); ?>
                                                    </td>
                                                    <td style="color: #666; font-size: 11px;">
                                                        <?php echo date('Y-m-d', strtotime($m['RegDate'])); ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <?php if ($isLeader): ?>
                                                            <i class="fas fa-crown" style="color: #e8c881; font-size: 14px;"
                                                                title="Guild Master"></i>
                                                        <?php else: ?>
                                                            <span style="color: #333;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <a href="admin.php?view=CharEdit&id=<?php echo $m['CharID']; ?>"
                                                            class="inventory-icon-btn btn-edit" title="Edit Character"><i
                                                                class="fas fa-user-cog"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php elseif ($tab === 'Warehouse'): ?>
                                <div class="styled-inventory-container">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                                        <h3
                                            style="font-size: 14px; text-transform: uppercase; letter-spacing: 2px; color: #e8c881; border-left: 3px solid #e8c881; padding-left: 15px;">
                                            Guild Bank Contents</h3>
                                    </div>

                                    <table class="users-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 350px; text-align: left; padding-left: 20px;">ITEM NAME</th>
                                                <th style="width: 80px; text-align: center;">SLOT</th>
                                                <th style="width: 80px; text-align: center;">COUNT</th>
                                                <th style="text-align: center;">SOCKETS / GEMS</th>
                                                <th style="width: 150px; text-align: center;">TIMESTAMP</th>
                                                <th style="width: 80px; text-align: center;">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $warehouseSql = "
                                                SELECT 
                                                    ci.ItemUID, ci.ItemID, ci.Slot, ci.Count, ci.Maketime, ci.Craftname,
                                                    ci.Gem1, ci.Gem2, ci.Gem3, ci.Gem4, ci.Gem5, ci.Gem6,
                                                    i.ItemName, i.Type, i.TypeID, i.ReqLevel, i.Defensefighter, i.Defensemage,
                                                    i.Attrib, i.Range, i.ConstHP, i.ConstSP, i.ConstMP,
                                                    i.ConstStr, i.ConstDex, i.ConstRec, i.ConstInt, i.ConstWis, i.ConstLuc
                                                FROM [PS_GameData].[dbo].[GuildStoredItems] ci
                                                INNER JOIN [PS_GameDefs].[dbo].[Items] i ON i.ItemID = ci.ItemID
                                                WHERE ci.GuildID = ?
                                                ORDER BY ci.Slot ASC
                                            ";
                                            $wStmt = odbc_prepare($conn, $warehouseSql);
                                            odbc_execute($wStmt, [$guildID]);

                                            $foundItems = false;
                                            while ($item = odbc_fetch_array($wStmt)):
                                                $foundItems = true;
                                                $gems = [];
                                                for ($i = 1; $i <= 6; $i++) {
                                                    $gemID = (int) $item["Gem$i"];
                                                    if ($gemID > 0) {
                                                        $actualGemID = 30000 + $gemID;
                                                        $gQ = odbc_prepare($conn, "SELECT ItemName FROM PS_GameDefs.dbo.Items WHERE ItemID = ?");
                                                        odbc_execute($gQ, [$actualGemID]);
                                                        if ($gRes = odbc_fetch_array($gQ)) {
                                                            $gems[] = ['name' => $gRes['ItemName'], 'id' => $gemID];
                                                        }
                                                    }
                                                }

                                                $baseStats = [
                                                    'HP' => (int) ($item['ConstHP'] ?? 0),
                                                    'SP' => (int) ($item['ConstSP'] ?? 0),
                                                    'MP' => (int) ($item['ConstMP'] ?? 0),
                                                    'STR' => (int) ($item['ConstStr'] ?? 0),
                                                    'DEX' => (int) ($item['ConstDex'] ?? 0),
                                                    'REC' => (int) ($item['ConstRec'] ?? 0),
                                                    'INT' => (int) ($item['ConstInt'] ?? 0),
                                                    'WIS' => (int) ($item['ConstWis'] ?? 0),
                                                    'LUC' => (int) ($item['ConstLuc'] ?? 0),
                                                ];

                                                $bonusStats = [
                                                    'Enchant' => 0,
                                                    'STR' => 0,
                                                    'DEX' => 0,
                                                    'REC' => 0,
                                                    'INT' => 0,
                                                    'WIS' => 0,
                                                    'LUC' => 0,
                                                    'HP' => 0,
                                                    'SP' => 0,
                                                    'MP' => 0
                                                ];

                                                if (!empty($item['Craftname'])) {
                                                    $cn = $item['Craftname'];
                                                    $bonusStats['STR'] = (int) substr($cn, 0, 2);
                                                    $bonusStats['DEX'] = (int) substr($cn, 2, 2);
                                                    $bonusStats['REC'] = (int) substr($cn, 4, 2);
                                                    $bonusStats['INT'] = (int) substr($cn, 6, 2);
                                                    $bonusStats['WIS'] = (int) substr($cn, 8, 2);
                                                    $bonusStats['LUC'] = (int) substr($cn, 10, 2);
                                                    $bonusStats['HP'] = (int) substr($cn, 12, 2) * 100;
                                                    $bonusStats['SP'] = (int) substr($cn, 14, 2) * 100;
                                                    $bonusStats['MP'] = (int) substr($cn, 16, 2) * 100;

                                                    $enchantVal = (int) substr($cn, 18, 2);
                                                    if ($enchantVal >= 50)
                                                        $enchantVal -= 50;
                                                    $bonusStats['Enchant'] = $enchantVal;
                                                }
                                                ?>
                                                <tr>
                                                    <td style="text-align: left; padding-left: 20px; overflow: visible;">
                                                        <div class="item-name-wrapper">
                                                            <div style="color: #eee; font-weight: 500; font-size: 13px;">
                                                                <?php echo htmlspecialchars($item['ItemName']); ?>
                                                                <?php if ($bonusStats['Enchant'] > 0): ?>
                                                                    <span
                                                                        class="enchant-badge">[<?php echo $bonusStats['Enchant']; ?>]</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div style="color: #555; font-size: 10px;">UID:
                                                                <?php echo $item['ItemUID']; ?> | ID: <?php echo $item['ItemID']; ?>
                                                            </div>

                                                            <div class="item-stats-tooltip">
                                                                <div class="tooltip-header">
                                                                    <span><?php echo htmlspecialchars($item['ItemName']); ?></span>
                                                                    <?php if ($bonusStats['Enchant'] > 0): ?>
                                                                        <span
                                                                            class="enchant-badge">[<?php echo $bonusStats['Enchant']; ?>]</span>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <?php if (($item['Defensefighter'] ?? 0) > 0): ?>
                                                                    <div class="stat-line" style="color: #fff;">Phys. defense
                                                                        <?php echo $item['Defensefighter']; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if (($item['Defensemage'] ?? 0) > 0): ?>
                                                                    <div class="stat-line" style="color: #fff;">Mag. defense
                                                                        <?php echo $item['Defensemage']; ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php
                                                                $allStats = ['HP' => 'hp', 'SP' => 'sp', 'MP' => 'mp', 'STR' => 'str', 'DEX' => 'dex', 'REC' => 'rec', 'INT' => 'int', 'WIS' => 'wis', 'LUC' => 'luc'];
                                                                foreach ($allStats as $label => $cssClass):
                                                                    $base = $baseStats[$label];
                                                                    $bonus = $bonusStats[$label];
                                                                    if ($base > 0 || $bonus > 0): ?>
                                                                        <div class="stat-line">
                                                                            <span
                                                                                class="stat-label-<?php echo $cssClass; ?>"><?php echo $label; ?></span>
                                                                            <span class="stat-v-base">+<?php echo $base + $bonus; ?></span>
                                                                            <?php if ($bonus > 0): ?><span
                                                                                    class="stat-v-bonus">+<?php echo $bonus; ?></span><?php endif; ?>
                                                                        </div>
                                                                    <?php endif; endforeach; ?>

                                                                <?php if (isset($item['ReqLevel']) && $item['ReqLevel'] > 0): ?>
                                                                    <div class="req-level"
                                                                        style="color:#eee; margin-top: 10px; font-size: 11px; opacity: 0.6;">
                                                                        Requires Level <?php echo $item['ReqLevel']; ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td style="text-align: center; color: #888; font-size: 11px;">
                                                        #<?php echo $item['Slot']; ?></td>
                                                    <td style="text-align: center; color: #da9f50; font-weight: 700;">
                                                        <?php echo number_format($item['Count']); ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <?php if (empty($gems)): ?><span style="color: #333;">-</span><?php else: ?>
                                                            <div
                                                                style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                                                                <?php foreach ($gems as $gem): ?>
                                                                    <div class="gem-badge"
                                                                        style="font-size: 9px; color: #e8c881; background: rgba(232, 200, 129, 0.05); padding: 2px 8px; border-radius: 10px; border: 1px solid rgba(232, 200, 129, 0.1); width: fit-content;">
                                                                        <i class="fas fa-gem"
                                                                            style="font-size: 7px; margin-right: 4px; opacity: 0.6;"></i>
                                                                        <?php echo htmlspecialchars($gem['name']); ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align: center; color: #666; font-size: 11px;">
                                                        <?php echo date('Y-m-d H:i', strtotime($item['Maketime'])); ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <form action="admin_actions.php" method="POST"
                                                            onsubmit="return confirm('PERMANENTLY delete this guild item?');">
                                                            <input type="hidden" name="action" value="guild_warehouse_item_delete">
                                                            <input type="hidden" name="guild_id" value="<?php echo $guildID; ?>">
                                                            <input type="hidden" name="item_uid"
                                                                value="<?php echo $item['ItemUID']; ?>">
                                                            <input type="hidden" name="return_view" value="GUILD_OVERVIEW">
                                                            <input type="hidden" name="tab" value="Warehouse">
                                                            <button type="submit" class="inventory-icon-btn btn-delete"
                                                                title="Delete Permanent"><i class="fas fa-trash-alt"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            <?php if (!$foundItems): ?>
                                                <tr>
                                                    <td colspan="6" style="text-align: center; padding: 60px; color: #444;">
                                                        <i class="fas fa-warehouse"
                                                            style="font-size: 30px; opacity: 0.1; margin-bottom: 15px;"></i><br>
                                                        Guild warehouse is empty.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php } ?>
                <?php elseif ($view === 'GUILDS'): ?>
                    <?php
                    $searchGuild = isset($_GET['search_guild']) ? trim($_GET['search_guild']) : '';
                    ?>
                    <div class="guilds-section">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px;">
                            <div style="display: flex; align-items: center; gap: 20px;">
                                <form action="admin.php" method="GET" style="display: flex; gap: 10px;">
                                    <input type="hidden" name="view" value="Guilds">
                                    <div style="position: relative;">
                                        <i class="fas fa-search"
                                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #444; font-size: 13px;"></i>
                                        <input type="text" name="search_guild" class="char-input"
                                            value="<?php echo htmlspecialchars($searchGuild); ?>"
                                            placeholder="Search Guild..."
                                            style="background: rgba(255,255,255,0.02); border-color: #222; color: #fff; height: 38px; padding-left: 45px; width: 250px; font-size: 13px; border-radius: 4px;">
                                    </div>
                                    <button type="submit" class="btn-action btn-sm">SEARCH</button>
                                </form>
                            </div>
                        </div>

                        <div class="chars-table-container">
                            <table class="chars-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th style="width: 80px;"></th>
                                        <th>Guild Name</th>
                                        <th>Guild Master</th>
                                        <th>Points</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $guildQuery = "SELECT GuildID, Country, GuildName, MasterName, MasterCharID, GuildPoint, CreateDate FROM PS_GameData.dbo.Guilds WHERE Del = 0";
                                    if ($searchGuild) {
                                        $guildQuery .= " AND GuildName LIKE '%$searchGuild%'";
                                    }
                                    $guildQuery .= " ORDER BY GuildPoint DESC";

                                    $q = odbc_exec($conn, $guildQuery);
                                    $found = false;
                                    while ($row = odbc_fetch_array($q)) {
                                        $found = true;
                                        $factionImg = ($row['Country'] == 0) ? 'aol.webp' : 'uof.webp';
                                        $formattedDate = date('d M H:i', strtotime($row['CreateDate']));
                                        ?>
                                        <tr>
                                            <td style="color: #666;"><?php echo $row['GuildID']; ?></td>
                                            <td>
                                                <div class="faction-icon">
                                                    <img src="assets/<?php echo $factionImg; ?>"
                                                        style="width: 20px; vertical-align: middle;">
                                                </div>
                                            </td>
                                            <td>
                                                <a href="admin.php?view=GUILD_OVERVIEW&id=<?php echo $row['GuildID']; ?>"
                                                    class="user-link"
                                                    style="color: #ccd; font-weight: 500; font-size: 14px; text-decoration: none;"><?php echo htmlspecialchars($row['GuildName']); ?></a>
                                            </td>
                                            <td>
                                                <a href="admin.php?view=CharEdit&id=<?php echo $row['MasterCharID']; ?>"
                                                    class="user-link"><?php echo htmlspecialchars($row['MasterName']); ?></a>
                                            </td>
                                            <td style="font-family: 'Futura PT', sans-serif;">
                                                <?php echo number_format($row['GuildPoint']); ?>
                                            </td>
                                            <td style="color: #888; font-size: 13px;"><?php echo $formattedDate; ?></td>
                                        </tr>
                                        <?php
                                    }
                                    if (!$found) {
                                        echo '<tr><td colspan="6" style="text-align: center; padding: 50px; color: #444;">No guilds found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($view === 'GIFTBOX'): ?>
                    <?php
                    $searchItem = isset($_GET['search_item']) ? trim($_GET['search_item']) : '';
                    ?>
                    <div class="char-edit-section">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                            <div>
                                <p style="color: #666; font-size: 13px;">Send items directly to a player's GiftBox
                                    (Bank/Mall storage).</p>
                            </div>

                        </div>

                        <div style="display: grid; grid-template-columns: 350px 1fr; gap: 40px; align-items: start;">
                            <!-- Column 1: Send Item Form -->
                            <div
                                style="background: rgba(255,255,255,0.02); padding: 35px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                                <h4
                                    style="color: #fff; margin-bottom: 30px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
                                    SEND ITEM</h4>
                                <form action="admin_actions.php" method="POST" style="display: block;">
                                    <input type="hidden" name="action" value="giftbox">
                                    <input type="hidden" name="return_view" value="GIFTBOX">

                                    <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                            <div class="char-input-group" style="display: block;">
                                                <label
                                                    style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase;">By
                                                    UserID</label>
                                                <input type="text" name="target_user" class="char-input"
                                                    placeholder="UserID..." value="<?php echo isset($_GET['target_user']) ? htmlspecialchars($_GET['target_user']) : ''; ?>"
                                                    style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%; height: 40px;">
                                            </div>
                                            <div class="char-input-group" style="display: block;">
                                                <label
                                                    style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase;">By
                                                    Char Name</label>
                                                <input type="text" name="target_char" class="char-input"
                                                    placeholder="CharName..."
                                                    style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%; height: 40px;">
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: 1fr 80px; gap: 15px;">
                                            <div class="char-input-group" style="display: block;">
                                                <label
                                                    style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase;">ItemID</label>
                                                <input type="number" name="itemid" id="gift_itemid" class="char-input"
                                                    placeholder="Paste ID here..." required
                                                    style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%; height: 40px;">
                                            </div>
                                            <div class="char-input-group" style="display: block;">
                                                <label
                                                    style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase;">Count</label>
                                                <input type="number" name="count" class="char-input" value="1" required
                                                    style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%; height: 40px; text-align: center;">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-action">SEND TO GIFTBOX</button>
                                    <div
                                        style="margin-top: 20px; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 4px; border: 1px solid rgba(255,255,255,0.03);">
                                        <p style="color: #666; font-size: 11px; line-height: 1.5; margin: 0;">Provide either
                                            UserID or Char Name. ItemID is required. Items are sent to the Mall storage tab.
                                        </p>
                                    </div>
                                </form>
                            </div>

                            <!-- Column 2: Item Search Helper -->
                            <div
                                style="background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid rgba(232, 200, 129, 0.05); padding: 30px; display: flex; flex-direction: column; min-height: 500px;">
                                <h4
                                    style="color: #fff; margin-bottom: 25px; font-size: 15px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
                                    ITEM SEARCH
                                    <i class="fas fa-search" style="color: #666; font-size: 16px;"></i>
                                </h4>

                                <form action="admin.php" method="GET" style="margin-bottom: 25px;">
                                    <input type="hidden" name="view" value="Giftbox">
                                    <div style="display: flex; gap: 10px;">
                                        <input type="text" name="search_item" class="char-input"
                                            value="<?php echo htmlspecialchars($searchItem); ?>"
                                            placeholder="Search ItemName..."
                                            style="background: rgba(255,255,255,0.03); border-color: #333; color: #fff; height: 40px;">
                                        <button type="submit" class="btn-action btn-sm"><i
                                                class="fas fa-search"></i></button>
                                    </div>
                                </form>

                                <div class="blacklist-scroll"
                                    style="flex-grow: 1; max-height: 350px; overflow-y: auto; padding-right: 10px;">
                                    <?php if ($searchItem): ?>
                                        <?php
                                        $q = odbc_exec($conn, "SELECT TOP 30 ItemID, ItemName FROM PS_GameDefs.dbo.Items WHERE ItemName LIKE '%$searchItem%' ORDER BY ItemName ASC");
                                        $found = false;
                                        while ($iRow = odbc_fetch_array($q)) {
                                            $found = true;
                                            echo '<div onclick="document.getElementById(\'gift_itemid\').value = \'' . $iRow['ItemID'] . '\'" style="background: rgba(255,255,255,0.02); padding: 12px 15px; border: 1px solid rgba(255,255,255,0.05); border-radius: 6px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor=\'#e8c881\'" onmouseout="this.style.borderColor=\'rgba(255,255,255,0.05)\'">';
                                            echo '<span><span style="color: #e8c881; font-weight: 500;">' . htmlspecialchars($iRow['ItemName']) . '</span> <span style="color: #555; font-size: 11px; margin-left: 10px;">ID: ' . $iRow['ItemID'] . '</span></span>';
                                            echo '<i class="fas fa-plus-circle" style="color: #333; font-size: 14px;"></i>';
                                            echo '</div>';
                                        }
                                        if (!$found) {
                                            echo '<div style="text-align: center; padding: 50px 0; color: #444;">No items found.</div>';
                                        }
                                        ?>
                                    <?php else: ?>
                                        <div style="text-align: center; padding: 80px 0; color: #444;">
                                            <i class="fas fa-box-open"
                                                style="font-size: 40px; margin-bottom: 20px; opacity: 0.2;"></i><br>
                                            Search for items to get IDs.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($view === 'POINTS'): ?>
                    
                    <div class="char-edit-section">
                        <p style="color: #666; font-size: 13px; margin-bottom: 30px;">Manage user points and view transaction history.</p>

                        <!-- Two-Column Layout: Form | Transaction History -->
                        <div style="display: grid; grid-template-columns: 380px 1fr; gap: 35px; align-items: start;">

                            <!-- Column 1: Add Points Form -->
                            <div style="background: rgba(255,255,255,0.02); padding: 35px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                                <h4 style="color: #fff; margin-bottom: 30px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
                                    <i class="fas fa-coins" style="color: #e8c881; margin-right: 10px;"></i>ADD POINTS</h4>
                                <form action="admin_actions.php" method="POST" style="display: block;">
                                    <input type="hidden" name="action" value="points_add">
                                    <input type="hidden" name="return_view" value="POINTS">

                                    <div style="display: flex; flex-direction: column; gap: 22px; margin-bottom: 30px;">
                                        <div class="char-input-group" style="display: block;">
                                            <label style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">UserID</label>
                                            <input type="text" name="target_user" class="char-input"
                                                placeholder="Enter UserID..." list="user_list" required
                                                value="<?php echo isset($_GET['target_user']) ? htmlspecialchars($_GET['target_user']) : ''; ?>"
                                                style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%; height: 42px; font-size: 14px;">
                                            <datalist id="user_list">
                                                <?php
                                                $uList = odbc_exec($conn, "SELECT TOP 100 UserID FROM PS_UserData.dbo.Users_Master ORDER BY UserID ASC");
                                                while ($uRow = odbc_fetch_array($uList)) {
                                                    echo '<option value="' . htmlspecialchars($uRow['UserID']) . '">';
                                                }
                                                ?>
                                            </datalist>
                                        </div>

                                        <div class="char-input-group" style="display: block;">
                                            <label style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">SP (Amount)</label>
                                            <input type="number" name="point_amount" class="char-input" value="100" min="1" required
                                                style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%; height: 42px; text-align: center; font-size: 18px; font-weight: 700; color: #e8c881;">
                                        </div>

                                        <div class="char-input-group" style="display: block;">
                                            <label style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">Reason</label>
                                            <input type="text" name="reason" class="char-input" placeholder="Required reason..." required
                                                style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%; height: 42px; font-size: 14px;">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-action" style="width: 100%;">ACCEPT</button>
                                </form>
                            </div>

                            <!-- Column 2: Transaction History -->
                            <div style="background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid rgba(232, 200, 129, 0.05); padding: 30px; display: flex; flex-direction: column;">
                                <h4 style="color: #fff; margin-bottom: 25px; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; border-left: 3px solid #da9f50; padding-left: 15px; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-history" style="color: #da9f50; font-size: 14px;"></i> Transaction History</h4>

                        <div class="chars-table-container">
                            <table class="chars-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">#</th>
                                        <th style="text-align: left; padding-left: 20px;">UserID</th>
                                        <th>Date</th>
                                        <th>Points</th>
                                        <th style="text-align: left;">Reason</th>
                                        <th>Admin / GM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $perPage = 50;
                                    $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
                                    $offset = ($page - 1) * $perPage;

                                    $countQArr = odbc_fetch_array(odbc_exec($conn, "SELECT COUNT(*) as Total FROM PS_UserData.dbo.Web_PointHistory"));
                                    $totalHistory = (int) ($countQArr['Total'] ?? 0);
                                    $totalHistoryPages = ceil($totalHistory / $perPage);

                                    $historyQ = odbc_exec($conn, "SELECT RowID, UserID, Date, PointsAdded, Reason, GM_Account 
                                                        FROM PS_UserData.dbo.Web_PointHistory 
                                                        ORDER BY RowID DESC 
                                                        OFFSET $offset ROWS FETCH NEXT $perPage ROWS ONLY");
                                    $count = 0;
                                    while ($h = odbc_fetch_array($historyQ)):
                                        $count++;
                                        ?>
                                        <tr>
                                            <td style="font-family: 'monospace'; color: #888;">#<?php echo $h['RowID']; ?></td>
                                            <td style="text-align: left; padding-left: 20px;">
                                                <a href="admin.php?view=USERS&search_user=<?php echo urlencode($h['UserID']); ?>" class="user-link"><?php echo htmlspecialchars($h['UserID']); ?></a>
                                            </td>
                                            <td>
                                                <?php echo date('Y M d', strtotime($h['Date'])); ?> <span style="color: #888; font-size: 11px;"><?php echo date('H:i', strtotime($h['Date'])); ?></span>
                                            </td>
                                            <td style="color: #e8c881; font-weight: 700;">
                                                +<?php echo number_format($h['PointsAdded']); ?>
                                            </td>
                                            <td style="text-align: left; color: #aaa; font-style: italic;">
                                                <?php echo htmlspecialchars($h['Reason'] ?: '-'); ?>
                                            </td>
                                            <td>
                                                <span class="status-badge" style="background: rgba(106, 142, 193, 0.1); color: #6a8ec1; border: 1px solid rgba(106, 142, 193, 0.2); font-size: 11px; padding: 3px 10px;">
                                                    <i class="fas fa-user-shield" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($h['GM_Account']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if ($count === 0): ?>
                                        <tr>
                                            <td colspan="6" style="padding: 100px; color: #444; text-align: center; font-style: italic; letter-spacing: 1px;">
                                                <i class="fas fa-info-circle" style="margin-right: 10px;"></i> No transaction records found in database.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                                </div><!-- /transaction history column -->
                        </div><!-- /two-column grid -->
                    </div><!-- /char-edit-section -->
                    <?php elseif ($view === 'SEND_NOTICE'): ?>
                        <div class="char-edit-section" style="max-width: 800px; margin: 0 auto; padding: 20px;">
                            <div style="text-align: center; margin-bottom: 40px;">
                                <h3 style="margin-bottom: 10px; color: #e8c881;">SEND SERVER NOTICE</h3>
                                <p style="color: #666; font-size: 14px;">The message will be broadcasted to all online
                                    players
                                    instantly.</p>
                            </div>

                            <div class="item-editor-card"
                                style="padding: 40px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px;">
                                <form action="admin_actions.php" method="POST">
                                    <input type="hidden" name="action" value="send_notice">
                                    <input type="hidden" name="return_view" value="SEND_NOTICE">

                                    <div class="char-input-group" style="margin-bottom: 30px; display: block;">
                                        <label
                                            style="display: block; color: #888; font-size: 11px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1px;">Notice
                                            Message (Max 150 chars)</label>
                                        <textarea name="notice_text" class="char-input"
                                            placeholder="Type your message here..." required maxlength="150"
                                            style="width: 100%; height: 120px; background: rgba(0,0,0,0.3); border-color: #333; color: #fff; padding: 15px; font-size: 15px; resize: none;"></textarea>
                                    </div>

                                    <div style="display: flex; justify-content: center;">
                                        <button type="submit" class="btn-action" style="width: 100%; max-width: 300px;">
                                            <i class="fas fa-bullhorn"></i> BROADCAST NOTICE
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php elseif (in_array($view, ['DROPS_BLACKLIST_ADD', 'DROPS_BLACKLIST_REMOVE', 'DROPS_CONFIG'])): ?>
                        
                        <div class="char-edit-section">
                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                                <div>
                                    <h3 style="margin-bottom: 5px; color: #e8c881;">DROPS MANAGEMENT</h3>
                                    <p style="color: #666; font-size: 13px;">Manage global drop settings and item blacklists
                                        from this central hub.</p>
                                </div>

                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start;">
                                <!-- Column 1: Config & Blacklist Add -->
                                <div style="display: flex; flex-direction: column; gap: 40px;">
                                    <?php
                                    $q = odbc_exec($conn, "SELECT SettingKey, SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey IN ('DropsMaxGrade', 'DropsMaxLevel', 'DropsHideZero', 'DropsHideEmpty')");
                                    $settings = [];
                                    while ($row = odbc_fetch_array($q)) {
                                        $settings[$row['SettingKey']] = $row['SettingValue'];
                                    }
                                    ?>
                                    <div
                                        style="background: rgba(255,255,255,0.02); padding: 25px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                                        <h4
                                            style="color: #fff; margin-bottom: 25px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                                            GLOBAL CONFIGURATION</h4>
                                        <form action="admin_actions.php" method="POST">
                                            <input type="hidden" name="action" value="drops_config">
                                            <input type="hidden" name="return_view" value="<?php echo $view; ?>">

                                            <div
                                                style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                                                <div class="char-input-group">
                                                    <label
                                                        style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase;">Max
                                                        Grade</label>
                                                    <input type="number" name="drops_max_grade" class="char-input"
                                                        value="<?php echo $settings['DropsMaxGrade'] ?? '3072'; ?>"
                                                        style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%;">
                                                </div>
                                                <div class="char-input-group">
                                                    <label
                                                        style="display: block; color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase;">Max
                                                        Level</label>
                                                    <input type="number" name="drops_max_level" class="char-input"
                                                        value="<?php echo $settings['DropsMaxLevel'] ?? '80'; ?>"
                                                        style="background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%;">
                                                </div>
                                            </div>

                                            <div style="display: flex; gap: 40px; margin-bottom: 30px; padding: 0 5px;">
                                                <label
                                                    style="display: flex; align-items: center; color: #ccc; font-size: 13px; cursor: pointer; user-select: none;">
                                                    <input type="checkbox" name="drops_hide_zero" <?php echo ($settings['DropsHideZero'] ?? '1') == '1' ? 'checked' : ''; ?>
                                                        style="width: 18px; height: 18px; margin-right: 12px; accent-color: #e8c881;">
                                                    Hide 0% Chance
                                                </label>
                                                <label
                                                    style="display: flex; align-items: center; color: #ccc; font-size: 13px; cursor: pointer; user-select: none;">
                                                    <input type="checkbox" name="drops_hide_empty" <?php echo ($settings['DropsHideEmpty'] ?? '1') == '1' ? 'checked' : ''; ?>
                                                        style="width: 18px; height: 18px; margin-right: 12px; accent-color: #e8c881;">
                                                    Hide Empty
                                                </label>
                                            </div>
                                            <button type="submit" class="btn-action">SAVE SETTINGS</button>
                                        </form>
                                    </div>

                                    <div
                                        style="background: rgba(255,255,255,0.02); padding: 25px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                                        <h4
                                            style="color: #fff; margin-bottom: 25px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                                            BLACKLIST OPERATIONS</h4>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                            <form action="admin_actions.php" method="POST">
                                                <input type="hidden" name="action" value="drops_blacklist_add">
                                                <input type="hidden" name="return_view" value="<?php echo $view; ?>">
                                                <div class="char-input-group" style="margin-bottom: 12px;">
                                                    <input type="number" name="itemid" class="char-input"
                                                        placeholder="ItemID to add..." required
                                                        style="background: rgba(45, 90, 39, 0.05); border-color: rgba(45, 90, 39, 0.3); color: #fff; width: 100%; height: 40px;">
                                                </div>
                                                <button type="submit" class="btn-action btn-sm">ADD TO LIST</button>
                                            </form>

                                            <form action="admin_actions.php" method="POST">
                                                <input type="hidden" name="action" value="drops_blacklist_remove">
                                                <input type="hidden" name="return_view" value="<?php echo $view; ?>">
                                                <div class="char-input-group" style="margin-bottom: 12px;">
                                                    <input type="number" name="itemid" class="char-input"
                                                        placeholder="ItemID to remove..." required
                                                        style="background: rgba(165, 39, 39, 0.05); border-color: rgba(165, 39, 39, 0.3); color: #fff; width: 100%; height: 40px;">
                                                </div>
                                                <button type="submit" class="btn-action btn-sm">REMOVE ITEM</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Column 2: Current Blacklist -->
                                <div
                                    style="background: rgba(0,0,0,0.25); border-radius: 12px; border: 1px solid rgba(232, 200, 129, 0.05); padding: 30px; height: 100%; min-height: 520px; display: flex; flex-direction: column;">
                                    <h4
                                        style="color: #fff; margin-bottom: 25px; font-size: 15px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
                                        CURRENT BLACKLIST
                                        <span
                                            style="background: rgba(232,200,129,0.1); color: #e8c881; font-size: 10px; padding: 4px 10px; border-radius: 20px; letter-spacing: 1px;">LOCKED
                                            ITEMS</span>
                                    </h4>
                                    <div class="blacklist-scroll"
                                        style="flex-grow: 1; max-height: 480px; overflow-y: auto; padding-right: 10px;">
                                        <?php
                                        $check = odbc_exec($conn, "SELECT CAST(SettingValue AS VARCHAR(MAX)) as SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'DropsBlacklist'");
                                        if ($row = odbc_fetch_array($check)) {
                                            $idList = trim($row['SettingValue']);
                                            if (!empty($idList)) {
                                                $ids = array_map('intval', explode(',', $idList));
                                                $id_string = implode(',', $ids);
                                                $q = odbc_exec($conn, "SELECT ItemID, ItemName FROM PS_GameDefs.dbo.Items WHERE ItemID IN ($id_string)");
                                                $names = [];
                                                while ($iRow = odbc_fetch_array($q)) {
                                                    $names[$iRow['ItemID']] = $iRow['ItemName'];
                                                }

                                                echo '<div style="display: flex; flex-direction: column; gap: 12px;">';
                                                foreach ($ids as $id) {
                                                    $name = $names[$id] ?? 'Unknown Item';
                                                    echo '<div style="background: rgba(255,255,255,0.02); padding: 15px 20px; border: 1px solid rgba(255,255,255,0.03); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;">';
                                                    echo '<span><span style="color: #e8c881; font-weight: 600; font-size: 14px;">' . htmlspecialchars($name) . '</span> <span style="color: #555; font-size: 11px; margin-left: 10px;">ID: ' . $id . '</span></span>';
                                                    echo '<i class="fas fa-lock" style="color: #333; font-size: 14px;"></i>';
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            } else {
                                                echo '<div style="text-align: center; padding: 100px 0; color: #444;"><i class="fas fa-shield-alt" style="font-size: 40px; margin-bottom: 15px; opacity: 0.2;"></i><br>Blacklist is empty.</div>';
                                            }
                                        } else {
                                            echo '<div style="text-align: center; padding: 100px 0; color: #444;"><i class="fas fa-shield-alt" style="font-size: 40px; margin-bottom: 15px; opacity: 0.2;"></i><br>Blacklist is empty.</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif (strpos($view, 'LUCKYCHEST') !== false): ?>
                        <?php
                        $q = odbc_exec($conn, "SELECT SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'LuckyChestEnabled'");
                        $luckyEnabled = (odbc_fetch_array($q))['SettingValue'] ?? '0';
                        ?>
                        <div class="char-edit-section" style="max-width: 900px; margin: 0 auto; padding: 20px;">
                            <div style="text-align: center; margin-bottom: 40px;">
                                <h3 style="margin-bottom: 10px; font-size: 24px; color: #e8c881;">LUCKY CHEST MANAGEMENT
                                </h3>
                                <p style="color: #666; font-size: 14px;">Control global availability and reset player
                                    participation timers.</p>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: stretch;">
                                <!-- Global Status Card -->
                                <div
                                    style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 40px; display: flex; flex-direction: column; align-items: center; text-align: center; justify-content: center;">
                                    <div
                                        style="width: 70px; height: 70px; background: <?php echo $luckyEnabled == '1' ? 'rgba(72,187,120,0.1)' : 'rgba(229,62,62,0.1)'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 25px; border: 1px solid <?php echo $luckyEnabled == '1' ? 'rgba(72,187,120,0.2)' : 'rgba(229,62,62,0.2)'; ?>;">
                                        <i class="fas fa-box-open"
                                            style="font-size: 28px; color: <?php echo $luckyEnabled == '1' ? '#48bb78' : '#e53e3e'; ?>;"></i>
                                    </div>
                                    <h4 style="color: #fff; margin-bottom: 20px; font-size: 16px; letter-spacing: 1px;">
                                        AVAILABILITY</h4>
                                    <form action="admin_actions.php" method="POST" style="width: 100%;">
                                        <input type="hidden" name="action" value="luckychest_toggle">
                                        <input type="hidden" name="return_view" value="<?php echo $view; ?>">
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                                            <label class="switch" style="transform: scale(1.6);">
                                                <input type="checkbox" name="lucky_enabled" <?php echo $luckyEnabled == '1' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                <span class="slider round"></span>
                                            </label>
                                            <div
                                                style="font-size: 13px; font-weight: 600; color: <?php echo $luckyEnabled == '1' ? '#48bb78' : '#e53e3e'; ?>; letter-spacing: 2px; text-transform: uppercase; margin-top: 10px;">
                                                <?php echo $luckyEnabled == '1' ? 'System Active' : 'System Disabled'; ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Actions Card -->
                                <div
                                    style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 40px; display: flex; flex-direction: column;">
                                    <h4
                                        style="color: #e8c881; margin-bottom: 25px; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid rgba(232, 200, 129, 0.1); padding-bottom: 15px;">
                                        DATABASE OPERATIONS</h4>

                                    <form action="admin_actions.php" method="POST"
                                        style="margin-bottom: 35px; display: block;">
                                        <input type="hidden" name="action" value="luckychest_reset_user">
                                        <input type="hidden" name="return_view" value="<?php echo $view; ?>">
                                        <label
                                            style="display: block; color: #888; font-size: 11px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">Reset
                                            Single User</label>
                                        <div style="display: flex; gap: 10px;">
                                            <input type="number" name="user_uid" class="char-input"
                                                placeholder="Enter UserUID..." required
                                                style="height: 45px; background: rgba(0,0,0,0.3); border-color: #333; color: #fff; width: 100%; border-radius: 4px;">
                                            <button type="submit" class="btn-action btn-sm">RESET</button>
                                        </div>
                                    </form>

                                    <div
                                        style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                                        <form action="admin_actions.php" method="POST"
                                            onsubmit="return confirm('CRITICAL ACTION: This will reset the timer for ALL players locally. Continue?');"
                                            style="display: block;">
                                            <input type="hidden" name="action" value="luckychest_reset_all">
                                            <input type="hidden" name="return_view" value="<?php echo $view; ?>">
                                            <button type="submit" class="btn-action"
                                                style="background: rgba(165,39,39,0.8); border-color: #a52727;">
                                                <i class="fas fa-redo-alt"></i> RESET ALL TIMERS
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($view === 'DOWNLOADS_UPDATE'): ?>
                        <?php
                        $q = odbc_exec($conn, "SELECT SettingKey, SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey IN ('DownloadsEnabled', 'Link_GoogleDrive', 'Link_OfficialMirror', 'Link_Mega')");
                        $settings = [];
                        while ($row = odbc_fetch_array($q)) {
                            $settings[$row['SettingKey']] = $row['SettingValue'];
                        }
                        ?>
                        <div class="char-edit-section" style="max-width: 800px; margin: 0 auto; padding: 20px;">
                            <div style="text-align: center; margin-bottom: 40px;">
                                <h3 style="margin-bottom: 10px; color: #e8c881;">DOWNLOADS MANAGEMENT</h3>
                                <p style="color: #666; font-size: 14px;">Update client download links and page visibility
                                    settings.</p>
                            </div>

                            <form action="admin_actions.php" method="POST" class="char-edit-form"
                                style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 40px; display: block;">
                                <input type="hidden" name="action" value="downloads_update">
                                <input type="hidden" name="return_view" value="DOWNLOADS_UPDATE">

                                <!-- Status Section -->
                                <div
                                    style="background: rgba(0,0,0,0.3); padding: 25px; border-radius: 8px; border: 1px solid rgba(232,200,129,0.1); display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px; gap: 20px;">
                                    <div style="flex: 1;">
                                        <div style="color: #fff; font-size: 16px; font-weight: 600; margin-bottom: 4px;">
                                            Page
                                            Visibility</div>
                                        <div style="color: #666; font-size: 12px; line-height: 1.4;">When disabled, users
                                            cannot
                                            access the downloads page. This is useful during maintenance or client updates.
                                        </div>
                                    </div>
                                    <div style="flex-shrink: 0;">
                                        <label class="switch">
                                            <input type="checkbox" name="dl_enabled" <?php echo ($settings['DownloadsEnabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Links Section -->
                                <div style="display: flex; flex-direction: column; gap: 30px;">
                                    <div class="char-input-group" style="display: block;">
                                        <label
                                            style="display: block; color: #888; font-size: 11px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Google
                                            Drive Client</label>
                                        <div
                                            style="display: flex; align-items: center; background: rgba(0,0,0,0.3); border: 1px solid #333; border-radius: 4px; padding: 0 15px;">
                                            <i class="fab fa-google-drive"
                                                style="color: #444; font-size: 16px; margin-right: 15px;"></i>
                                            <input type="text" name="link_google" class="char-input"
                                                value="<?php echo htmlspecialchars($settings['Link_GoogleDrive'] ?? ''); ?>"
                                                style="border: none !important; background: transparent !important; padding: 12px 0 !important; width: 100%; box-shadow: none !important; color: #fff;">
                                        </div>
                                    </div>

                                    <div class="char-input-group" style="display: block;">
                                        <label
                                            style="display: block; color: #888; font-size: 11px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Official
                                            Web Mirror</label>
                                        <div
                                            style="display: flex; align-items: center; background: rgba(0,0,0,0.3); border: 1px solid #333; border-radius: 4px; padding: 0 15px;">
                                            <i class="fas fa-server"
                                                style="color: #444; font-size: 16px; margin-right: 15px;"></i>
                                            <input type="text" name="link_mirror" class="char-input"
                                                value="<?php echo htmlspecialchars($settings['Link_OfficialMirror'] ?? ''); ?>"
                                                style="border: none !important; background: transparent !important; padding: 12px 0 !important; width: 100%; box-shadow: none !important; color: #fff;">
                                        </div>
                                    </div>

                                    <div class="char-input-group" style="display: block;">
                                        <label
                                            style="display: block; color: #888; font-size: 11px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Mega.nz
                                            Storage</label>
                                        <div
                                            style="display: flex; align-items: center; background: rgba(0,0,0,0.3); border: 1px solid #333; border-radius: 4px; padding: 0 15px;">
                                            <i class="fas fa-cloud"
                                                style="color: #444; font-size: 16px; margin-right: 15px;"></i>
                                            <input type="text" name="link_mega" class="char-input"
                                                value="<?php echo htmlspecialchars($settings['Link_Mega'] ?? ''); ?>"
                                                style="border: none !important; background: transparent !important; padding: 12px 0 !important; width: 100%; box-shadow: none !important; color: #fff;">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn-action">
                                    SAVE DOWNLOAD CONFIGURATION
                                </button>
                            </form>
                        </div>

                    <?php elseif ($view === 'REGISTER_TOGGLE'): ?>
                        <?php
                        $q = odbc_exec($conn, "SELECT SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'RegistrationEnabled'");
                        $regEnabled = (odbc_fetch_array($q))['SettingValue'] ?? '1';
                        ?>
                        <div class="char-edit-section"
                            style="max-width: 600px; margin: 80px auto; text-align: center; padding: 20px;">
                            <h3 style="margin-bottom: 10px; font-size: 24px; color: #e8c881;">REGISTRATION CONTROL</h3>
                            <p style="color: #666; margin-bottom: 50px;">Global toggle for new account creation via the
                                website.
                            </p>

                            <form action="admin_actions.php" method="POST" style="display: block;">
                                <input type="hidden" name="action" value="register_toggle">
                                <input type="hidden" name="return_view" value="REGISTER_TOGGLE">

                                <div
                                    style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 50px; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
                                    <div
                                        style="width: 70px; height: 70px; background: <?php echo $regEnabled == '1' ? 'rgba(72,187,120,0.1)' : 'rgba(229,62,62,0.1)'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; border: 1px solid <?php echo $regEnabled == '1' ? 'rgba(72,187,120,0.2)' : 'rgba(229,62,62,0.2)'; ?>;">
                                        <i class="fas fa-user-plus"
                                            style="font-size: 28px; color: <?php echo $regEnabled == '1' ? '#48bb78' : '#e53e3e'; ?>;"></i>
                                    </div>

                                    <div
                                        style="font-size: 18px; color: #fff; margin-bottom: 30px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px;">
                                        Registration is: <span
                                            style="color: <?php echo $regEnabled == '1' ? '#48bb78' : '#e53e3e'; ?>;"><?php echo $regEnabled == '1' ? 'OPEN' : 'CLOSED'; ?></span>
                                    </div>

                                    <div style="display: flex; justify-content: center; margin-bottom: 30px;">
                                        <label class="switch" style="transform: scale(1.8);">
                                            <input type="checkbox" name="reg_enabled" <?php echo $regEnabled == '1' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                            <span class="slider round"></span>
                                        </label>
                                    </div>

                                    <div
                                        style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 6px; color: #555; font-size: 12px; line-height: 1.6;">
                                        <?php if ($regEnabled == '1'): ?>
                                            <i class="fas fa-info-circle" style="margin-right: 5px; color: #48bb78;"></i>
                                            PUBLIC:
                                            Users can sign up freely.
                                        <?php else: ?>
                                            <i class="fas fa-lock" style="margin-right: 5px; color: #e53e3e;"></i> PRIVATE: New
                                            account creation is disabled.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php elseif ($view === 'USERS'): ?>
                        <?php
                        // Original searches for compatibility
                        $searchChar = isset($_GET['search_char']) ? trim($_GET['search_char']) : '';
                        $searchUser = isset($_GET['search_user']) ? trim($_GET['search_user']) : '';

                        // New Header Filters
                        $fUserID = isset($_GET['f_userid']) ? trim($_GET['f_userid']) : $searchUser;
                        $fCharName = isset($_GET['f_charname']) ? trim($_GET['f_charname']) : $searchChar;
                        $fUserUID = isset($_GET['f_useruid']) ? trim($_GET['f_useruid']) : '';
                        $fStatus = isset($_GET['f_status']) ? trim($_GET['f_status']) : '';
                        $fFaction = isset($_GET['f_faction']) ? trim($_GET['f_faction']) : '';

                        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
                        $perPage = 50;
                        $offset = ($page - 1) * $perPage;

                        $where = "WHERE 1=1";
                        if ($fCharName) {
                            $q = odbc_exec($conn, "SELECT DISTINCT UserUID FROM PS_GameData.dbo.Chars WHERE CharName LIKE '%" . str_replace("'", "''", $fCharName) . "%'");
                            $uids = [];
                            while ($r = odbc_fetch_array($q))
                                $uids[] = (int) $r['UserUID'];
                            if (count($uids) > 0) {
                                $where .= " AND U.UserUID IN (" . implode(',', $uids) . ")";
                            } else {
                                $where .= " AND 1=0";
                            }
                        }
                        if ($fUserID) {
                            $where .= " AND U.UserID LIKE '%" . str_replace("'", "''", $fUserID) . "%'";
                        }
                        if ($fUserUID !== '') {
                            $where .= " AND U.UserUID = " . (int) $fUserUID;
                        }
                        if ($fStatus !== '') {
                            $where .= " AND U.Status = " . (int) $fStatus;
                        }
                        if ($fFaction !== '') {
                            $where .= " AND MG.Country = " . (int) $fFaction;
                        }

                        // Count total for pagination
                        $countQArr = odbc_fetch_array(odbc_exec($conn, "SELECT COUNT(*) as Total FROM PS_UserData.dbo.Users_Master U LEFT JOIN PS_GameData.dbo.UserMaxGrow MG ON MG.UserUID = U.UserUID $where"));
                        $totalUsers = (int) ($countQArr['Total'] ?? 0);
                        $totalPages = ceil($totalUsers / $perPage);

                        $userQuery = odbc_exec($conn, "SELECT 
                            U.UserUID, U.UserID, U.Status, U.Point, U.JoinDate,
                            C.CharID as MainCharID,
                            C.CharName as MainChar,
                            C.K1 as Kills,
                            C.LoginStatus,
                            C.LeaveDate as OnlineLeaveDate,
                            MG.Country as Faction,
                            (SELECT SUM(UsePoint) FROM PS_GameData.dbo.PointLog WHERE UserUID = U.UserUID) as Spent
                        FROM PS_UserData.dbo.Users_Master U
                        LEFT JOIN PS_GameData.dbo.Chars C ON C.UserUID = U.UserUID AND C.Slot = 0 AND C.Del = 0
                        LEFT JOIN PS_GameData.dbo.UserMaxGrow MG ON MG.UserUID = U.UserUID
                        $where
                        ORDER BY U.UserUID DESC
                        OFFSET $offset ROWS FETCH NEXT $perPage ROWS ONLY");

                        // Optimized batch playtime fetch for the current page
                        $pageUserIDs = [];
                        $allUsers = [];
                        while ($u = odbc_fetch_array($userQuery)) {
                            $pageUserIDs[] = "'" . str_replace("'", "''", $u['UserID']) . "'";
                            $allUsers[] = $u;
                        }

                        $playtimes = [];
                        if (count($pageUserIDs) > 0) {
                            $uidList = implode(',', $pageUserIDs);
                            $p_sql = "SELECT UserID, SUM(DATEDIFF(SECOND, LoginTime, LogoutTime)) as TotalSeconds
                          FROM (
                              SELECT a.UserID, a.ActionTime as LogoutTime, MAX(l.ActionTime) as LoginTime
                              FROM PS_GameLog.dbo.ActionLog a
                              LEFT JOIN PS_GameLog.dbo.ActionLog l ON l.UserID = a.UserID AND l.ActionType = 107 AND l.ActionTime < a.ActionTime
                              WHERE a.UserID IN ($uidList) AND a.ActionType = 108
                              GROUP BY a.UserID, a.ActionTime
                          ) t
                          WHERE LoginTime IS NOT NULL
                          GROUP BY UserID";
                            $p_res = odbc_exec($conn, $p_sql);
                            while ($p_row = odbc_fetch_array($p_res)) {
                                $playtimes[$p_row['UserID']] = (int) $p_row['TotalSeconds'];
                            }
                        }
                        ?>

                        <div class="chars-table-container">
                            <form method="GET" id="userHeaderFilters">
                                <input type="hidden" name="view" value="USERS">

                                <table class="chars-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Faction
                                                <select name="f_faction" class="table-filter-select"
                                                    onchange="this.form.submit()">
                                                    <option value="">All</option>
                                                    <option value="0" <?php echo $fFaction === '0' ? 'selected' : ''; ?>>AOL
                                                    </option>
                                                    <option value="1" <?php echo $fFaction === '1' ? 'selected' : ''; ?>>UOF
                                                    </option>
                                                </select>
                                            </th>
                                            <th style="width: 80px;">UID
                                                <input type="text" name="f_useruid" class="table-filter-input"
                                                    placeholder="ID" value="<?php echo htmlspecialchars($fUserUID); ?>"
                                                    onchange="this.form.submit()">
                                            </th>
                                            <th style="width: 150px;">Username
                                                <input type="text" name="f_userid" class="table-filter-input"
                                                    placeholder="User" value="<?php echo htmlspecialchars($fUserID); ?>"
                                                    onchange="this.form.submit()">
                                            </th>
                                            <th style="width: 100px;">Status
                                                <select name="f_status" class="table-filter-select"
                                                    onchange="this.form.submit()">
                                                    <option value="">All</option>
                                                    <option value="16" <?php echo $fStatus === '16' ? 'selected' : ''; ?>>ADM
                                                    </option>
                                                    <option value="32" <?php echo $fStatus === '32' ? 'selected' : ''; ?>>GM
                                                    </option>
                                                    <option value="48" <?php echo $fStatus === '48' ? 'selected' : ''; ?>>GMA
                                                    </option>
                                                    <option value="0" <?php echo $fStatus === '0' ? 'selected' : ''; ?>>Player
                                                    </option>
                                                </select>
                                            </th>
                                            <th style="width: 100px;">Points</th>
                                            <th style="width: 100px;">Spent</th>
                                            <th style="width: 150px;">Main Char
                                                <input type="text" name="f_charname" class="table-filter-input"
                                                    placeholder="Name" value="<?php echo htmlspecialchars($fCharName); ?>"
                                                    onchange="this.form.submit()">
                                            </th>
                                            <th style="width: 150px;">Kills</th>
                                            <th style="width: 100px;">Playtime</th>
                                            <th style="width: 130px;">Created</th>
                                            <th style="width: 120px;">Last Login</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allUsers as $u): ?>
                                            <tr>
                                                <td>
                                                    <?php if ((int) $u['Faction'] === 1): ?>
                                                        <img src="assets/uof.webp" style="width: 20px;">
                                                    <?php elseif (isset($u['Faction']) && (int) $u['Faction'] === 0 && !empty($u['MainChar'])): ?>
                                                        <img src="assets/aol.webp" style="width: 20px;">
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td style="color: #666;"><?php echo $u['UserUID']; ?></td>
                                                <td>
                                                    <a href="admin.php?view=UserEdit&uid=<?php echo $u['UserUID']; ?>"
                                                        class="user-link"><?php echo htmlspecialchars($u['UserID']); ?></a>
                                                </td>
                                                <td>
                                                    <?php
                                                    $uStatus = (int) ($u['Status'] ?? 0);
                                                    $sLabel = 'Player';
                                                    $sClass = '';
                                                    if ($uStatus == 16) {
                                                        $sLabel = 'ADM';
                                                        $sClass = 'status-active';
                                                    } elseif ($uStatus == 32) {
                                                        $sLabel = 'GM';
                                                        $sClass = 'status-active';
                                                    } elseif ($uStatus == 48) {
                                                        $sLabel = 'GMA';
                                                        $sClass = 'status-active';
                                                    }
                                                    ?>
                                                    <span
                                                        class="status-badge <?php echo $sClass; ?>"><?php echo $sLabel; ?></span>
                                                </td>
                                                <td><?php echo number_format($u['Point']); ?></td>
                                                <td><?php echo number_format($u['Spent'] ?? 0); ?></td>
                                                <td>
                                                    <?php if (!empty($u['MainChar'])): ?>
                                                        <a href="admin.php?view=CharEdit&id=<?php echo $u['MainCharID']; ?>"
                                                            class="user-link"><?php echo htmlspecialchars($u['MainChar']); ?></a>
                                                    <?php else: ?>
                                                        <span style="color: #444; font-size: 13px;">No chars</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo number_format($u['Kills'] ?? 0); ?>
                                                    <?php echo getAdminRankIcon((int) ($u['Kills'] ?? 0)); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $u_esc = $u['UserID'];
                                                    $uPlaytime = $playtimes[$u_esc] ?? 0;
                                                    if ((int) ($u['LoginStatus'] ?? 0) === 1) {
                                                        $s_sql = "SELECT TOP 1 DATEDIFF(SECOND, ActionTime, GETDATE()) as SessionSeconds FROM PS_GameLog.dbo.ActionLog WHERE UserID = '" . str_replace("'", "''", $u_esc) . "' AND ActionType = 107 ORDER BY ActionTime DESC";
                                                        $s_res = odbc_exec($conn, $s_sql);
                                                        if ($s_res && ($s_row = odbc_fetch_array($s_res)))
                                                            $uPlaytime += (int) $s_row['SessionSeconds'];
                                                    }
                                                    echo formatPlaytime($uPlaytime);
                                                    ?>
                                                </td>
                                                <td style="color: #888; font-size: 13px;">
                                                    <?php echo date('d M Y', strtotime($u['JoinDate'])); ?>
                                                </td>
                                                <td class="time-relative">
                                                    <?php echo formatRelativeTime($u['OnlineLeaveDate']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php if ($totalPages > 1): ?>
                                <div class="pagination"
                                    style="display: flex; justify-content: center; gap: 10px; margin-top: 30px;">
                                    <?php
                                    $queryParams = [
                                        'view' => 'USERS',
                                        'f_useruid' => $fUserUID,
                                        'f_userid' => $fUserID,
                                        'f_charname' => $fCharName,
                                        'f_status' => $fStatus,
                                        'f_faction' => $fFaction
                                    ];
                                    $queryString = http_build_query(array_filter($queryParams, function ($v) {
                                        return $v !== '';
                                    }));
                                    $url = "admin.php?" . $queryString;

                                    if ($page > 1): ?>
                                        <a href="<?php echo $url; ?>&p=<?php echo $page - 1; ?>" class="btn-search"
                                            style="padding: 5px 15px;">PREV</a>
                                    <?php endif; ?>
                                    <span style="color: #888; padding: 5px;">Page <?php echo $page; ?> of
                                        <?php echo $totalPages; ?></span>
                                    <?php if ($page < $totalPages): ?>
                                        <a href="<?php echo $url; ?>&p=<?php echo $page + 1; ?>" class="btn-search"
                                            style="padding: 5px 15px;">NEXT</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($view === 'USEREDIT'): ?>
                        <?php
                        $userUID = isset($_GET['uid']) ? (int) $_GET['uid'] : 0;
                        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'Overview';
                        $userData = null;
                        if ($userUID > 0) {
                            // Fetch basic account details
                            $sql = "SELECT U.*, 
                               (SELECT SUM(UsePoint) FROM PS_GameData.dbo.PointLog WHERE UserUID = U.UserUID) as SpentPoints
                        FROM PS_UserData.dbo.Users_Master U 
                        WHERE U.UserUID = ?";
                            $stmt = odbc_prepare($conn, $sql);
                            odbc_execute($stmt, [$userUID]);
                            $userData = odbc_fetch_array($stmt);
                        }

                        if (!$userData):
                            ?>
                            <div class="alert alert-error">Account not found (ID: <?php echo $userUID; ?>).</div>
                        <?php else:
                            // Fetch Main Character and Faction
                            $mainSql = "SELECT TOP 1 C.CharID, C.CharName, C.K1 as Kills, C.LeaveDate, C.LoginStatus, MG.Country as Faction
                            FROM PS_GameData.dbo.Chars C
                            LEFT JOIN PS_GameData.dbo.UserMaxGrow MG ON MG.UserUID = C.UserUID
                            WHERE C.UserUID = ? AND C.Del = 0
                            ORDER BY C.Slot ASC";
                            $mStmt = odbc_prepare($conn, $mainSql);
                            odbc_execute($mStmt, [$userUID]);
                            $mainChar = odbc_fetch_array($mStmt);

                            $isOnline = (int) ($mainChar['LoginStatus'] ?? 0) === 1;

                            // Fetch Reroll Runes Used (Text1 = 'Recreation Rune', Text2 = 'use_item')
                            $runeSql = "SELECT COUNT(*) as RuneCount FROM PS_GameLog.dbo.ActionLog WHERE Text1 = 'Recreation Rune' AND Text2 = 'use_item' AND UserID = ?";
                            $rStmt = odbc_prepare($conn, $runeSql);
                            odbc_execute($rStmt, [$userData['UserID']]);
                            $runeData = odbc_fetch_array($rStmt);
                            $rerollRunesUsed = (int) ($runeData['RuneCount'] ?? 0);

                            // Fetch Log In Count (ActionType 107)
                            $loginSql = "SELECT COUNT(*) as LoginCount FROM PS_GameLog.dbo.ActionLog WHERE ActionType = 107 AND UserID = ?";
                            $lStmt = odbc_prepare($conn, $loginSql);
                            odbc_execute($lStmt, [$userData['UserID']]);
                            $loginData = odbc_fetch_array($lStmt);
                            $loginCount = (int) ($loginData['LoginCount'] ?? 0);

                            // Fetch Chat Message Count
                            $chatSql = "SELECT COUNT(*) as ChatCount FROM PS_ChatLog.dbo.ChatLog WHERE UserUID = ?";
                            $cStmt = odbc_prepare($conn, $chatSql);
                            odbc_execute($cStmt, [$userUID]);
                            $chatData = odbc_fetch_array($cStmt);
                            $chatCount = (int) ($chatData['ChatCount'] ?? 0);

                            // Calculate Total Playtime (Seconds) - Using odbc_exec to bypass driver limitations with complex queries and parameters
                            $uid_esc = str_replace("'", "''", $userData['UserID']);
                            $playtimeSql = "SELECT SUM(DATEDIFF(SECOND, LoginTime, LogoutTime)) as TotalSeconds
                                FROM (
                                    SELECT 
                                        a.ActionTime as LogoutTime,
                                        MAX(l.ActionTime) as LoginTime
                                    FROM PS_GameLog.dbo.ActionLog a
                                    LEFT JOIN PS_GameLog.dbo.ActionLog l ON l.UserID = a.UserID AND l.ActionType = 107 AND l.ActionTime < a.ActionTime
                                    WHERE a.UserID = '$uid_esc' AND a.ActionType = 108
                                    GROUP BY a.ActionTime
                                ) t
                                WHERE LoginTime IS NOT NULL";
                            $pRes = odbc_exec($conn, $playtimeSql);
                            if ($pRes) {
                                $pData = odbc_fetch_array($pRes);
                                $totalSeconds = (int) ($pData['TotalSeconds'] ?? 0);
                            } else {
                                $totalSeconds = 0;
                            }

                            // Add current session if online
                            if ($isOnline) {
                                $activeSessionSql = "SELECT TOP 1 DATEDIFF(SECOND, ActionTime, GETDATE()) as SessionSeconds
                                         FROM PS_GameLog.dbo.ActionLog 
                                         WHERE UserID = '$uid_esc' AND ActionType = 107 
                                         ORDER BY ActionTime DESC";
                                $sRes = odbc_exec($conn, $activeSessionSql);
                                if ($sRes && ($sData = odbc_fetch_array($sRes))) {
                                    $totalSeconds += (int) $sData['SessionSeconds'];
                                }
                            }
                            ?>
                            <!-- User Edit Header -->
                            <div class="char-edit-header">
                                <div class="char-title-section">
                                    <h1><?php echo htmlspecialchars($userData['UserID']); ?> | OVERVIEW</h1>
                                    <p>User Management</p>
                                </div>
                                <div class="char-actions-bar">
                                    <form action="admin_actions.php" method="POST" style="display:inline;"
                                        onsubmit="return confirm('Kick user <?php echo htmlspecialchars($userData['UserID']); ?>?');">
                                        <input type="hidden" name="action" value="user_kick">
                                        <input type="hidden" name="user_uid" value="<?php echo $userUID; ?>">
                                        <input type="hidden" name="return_view" value="UserEdit">
                                        <button type="submit" class="admin-btn btn-kick">USER KICK</button>
                                    </form>
                                    <a href="admin.php?view=Giftbox&target_user=<?php echo urlencode($userData['UserID']); ?>"
                                        class="admin-btn btn-del-item">ADD ITEM</a>
                                    <a href="admin.php?view=Points&target_user=<?php echo urlencode($userData['UserID']); ?>"
                                        class="admin-btn btn-del-item">ADD POINTS</a>
                                    <div class="char-status-badge"
                                        style="<?php echo $isOnline ? '' : 'background: rgba(229, 62, 62, 0.2); color: #e53e3e; border-color: rgba(229, 62, 62, 0.3);'; ?>">
                                        <?php echo $isOnline ? 'ONLINE' : 'OFFLINE'; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs -->
                            <div class="char-tabs">
                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Overview"
                                    class="char-tab <?php echo (!$tab || $tab === 'Overview') ? 'active' : ''; ?>"
                                    style="text-decoration:none;">Overview</a>
                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Characters"
                                    class="char-tab <?php echo ($tab === 'Characters') ? 'active' : ''; ?>"
                                    style="text-decoration:none;">Characters</a>
                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=AuthLog"
                                    class="char-tab <?php echo ($tab === 'AuthLog') ? 'active' : ''; ?>"
                                    style="text-decoration:none;">Auth log</a>
                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=PointLog"
                                    class="char-tab <?php echo ($tab === 'PointLog') ? 'active' : ''; ?>"
                                    style="text-decoration:none;">Point log</a>
                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Purchases"
                                    class="char-tab <?php echo ($tab === 'Purchases') ? 'active' : ''; ?>"
                                    style="text-decoration:none;">Purchases</a>

                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Warehouse"
                                    class="char-tab <?php echo ($tab === 'Warehouse') ? 'active' : ''; ?>"
                                    style="text-decoration:none;">Warehouse</a>
                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Chat"
                                    class="char-tab <?php echo ($tab === 'Chat') ? 'active' : ''; ?>"
                                    style="text-decoration:none;">Chat
                                    Log</a>
                            </div>

                            <?php if (!$tab || $tab === 'Overview'): ?>

                                <!-- Administrative Actions Area -->
                                <div
                                    style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 40px; background: rgba(0,0,0,0.2); padding: 30px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.03);">
                                    <!-- Status Change -->
                                    <form action="admin_actions.php" method="POST"
                                        style="display: flex; align-items: center; gap: 20px;">
                                        <input type="hidden" name="action" value="user_update_status">
                                        <input type="hidden" name="user_uid" value="<?php echo $userUID; ?>">
                                        <input type="hidden" name="return_view" value="UserEdit">
                                        <label style="color: #888; font-size: 13px; width: 120px; text-align: right;">Status</label>
                                        <select name="new_status" class="char-input" style="width: 200px;">
                                            <option value="0" <?php echo (int) $userData['Status'] === 0 ? 'selected' : ''; ?>>Player
                                            </option>
                                            <option value="16" <?php echo (int) $userData['Status'] === 16 ? 'selected' : ''; ?>>ADM
                                                (16)
                                            </option>
                                            <option value="32" <?php echo (int) $userData['Status'] === 32 ? 'selected' : ''; ?>>GM
                                                (32)
                                            </option>
                                            <option value="48" <?php echo (int) $userData['Status'] === 48 ? 'selected' : ''; ?>>GMA
                                                (48)
                                            </option>
                                            <option value="-5" <?php echo (int) $userData['Status'] === -5 ? 'selected' : ''; ?>>
                                                Banned (-5)
                                            </option>
                                        </select>
                                        <button type="submit" class="btn-search"
                                            style="padding: 10px 20px; letter-spacing: 1px;">SET
                                            STATUS</button>
                                    </form>

                                    <!-- Password Change -->
                                    <form action="admin_actions.php" method="POST"
                                        style="display: flex; align-items: center; gap: 20px;">
                                        <input type="hidden" name="action" value="user_change_password">
                                        <input type="hidden" name="user_uid" value="<?php echo $userUID; ?>">
                                        <input type="hidden" name="return_view" value="UserEdit">
                                        <label style="color: #888; font-size: 13px; width: 120px; text-align: right;">Password
                                            change</label>
                                        <input type="text" name="new_password" class="char-input" style="width: 200px;"
                                            placeholder="New password...">
                                        <button type="submit" class="btn-search"
                                            style="padding: 10px 20px; letter-spacing: 1px;">CHANGE
                                            PASSWORD</button>
                                    </form>


                                </div>

                                <!-- Info Grid -->
                                <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
                                    <!-- Account Table -->
                                    <table class="char-info-table">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="header-main">Account</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="width: 30%;">UserUID</td>
                                                <td><?php echo $userUID; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Username</td>
                                                <td><?php echo htmlspecialchars($userData['UserID']); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Status</td>
                                                <td><?php echo (int) $userData['Status']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Points</td>
                                                <td><?php echo number_format($userData['Point']); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Spent points</td>
                                                <td><?php echo number_format($userData['SpentPoints'] ?? 0); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Create date</td>
                                                <td style="color: #888;">
                                                    <?php echo date('l, F d, Y g:i A', strtotime($userData['JoinDate'])); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Last enter date</td>
                                                <td class="time-relative">
                                                    <?php echo formatRelativeTime($mainChar['LeaveDate'] ?? $userData['JoinDate']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Last leave date</td>
                                                <td style="color: #888;">
                                                    <?php echo date('l, F d, Y g:i A', strtotime($mainChar['LeaveDate'] ?? $userData['JoinDate'])); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Main Table -->
                                    <table class="char-info-table">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="header-main" style="background: #e8c881;">Main</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="width: 30%;">Faction</td>
                                                <td>
                                                    <?php if (isset($mainChar['Faction'])): ?>
                                                        <img src="assets/<?php echo ($mainChar['Faction'] == 0 ? 'aol.webp' : 'uof.webp'); ?>"
                                                            style="width: 24px; vertical-align: middle;">
                                                    <?php else: ?>
                                                        <span style="color: #444;">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Main character</td>
                                                <td>
                                                    <?php if ($mainChar): ?>
                                                        <a href="admin.php?view=CharEdit&id=<?php echo $mainChar['CharID']; ?>"
                                                            class="user-link"><?php echo htmlspecialchars($mainChar['CharName']); ?></a>
                                                    <?php else: ?>
                                                        <span style="color: lightblue;">No characters</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Online time</td>
                                                <td><?php echo formatPlaytime($totalSeconds); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Kills</td>
                                                <td><?php echo number_format($mainChar['Kills'] ?? 0); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Other Table (Placeholders) -->
                                    <table class="char-info-table">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="header-main" style="background: #e8c881;">Other</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr>
                                                <td>Reroll Runes used</td>
                                                <td><?php echo number_format($rerollRunesUsed); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Chat Message Sent</td>
                                                <td><?php echo number_format($chatCount); ?></td>
                                            </tr>

                                            <tr>
                                                <td>Log In Count</td>
                                                <td><?php echo number_format($loginCount); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php elseif ($tab === 'Characters'): ?>
                                <?php
                                $charSql = "SELECT C.*, MG.Country as Faction
                                FROM PS_GameData.dbo.Chars C
                                LEFT JOIN PS_GameData.dbo.UserMaxGrow MG ON MG.UserUID = C.UserUID
                                WHERE C.UserUID = ?
                                ORDER BY C.Del ASC, C.Slot ASC";
                                $cStmt = odbc_prepare($conn, $charSql);
                                odbc_execute($cStmt, [$userUID]);

                                $activeChars = [];
                                $deletedChars = [];
                                while ($c = odbc_fetch_array($cStmt)) {
                                    if ((int) $c['Del'] === 0) {
                                        $activeChars[] = $c;
                                    } else {
                                        $deletedChars[] = $c;
                                    }
                                }
                                ?>

                                <!-- Active Characters -->
                                <div style="margin-bottom: 50px;">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                        <h2 style="color: #fff; font-size: 18px; text-transform: uppercase; letter-spacing: 2px;">
                                            Active
                                            Characters</h2>
                                    </div>
                                    <div class="chars-table-container">
                                        <table class="chars-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 8%;">Slot</th>
                                                    <th style="width: 5%;"></th>
                                                    <th style="text-align: left; padding-left: 10px; width: 22%;">Name</th>
                                                    <th style="width: 10%;">Lv</th>
                                                    <th style="width: 15%;">Race</th>
                                                    <th style="width: 15%;">Create date</th>
                                                    <th style="width: 15%;">Last enter</th>
                                                    <th style="text-align:right; width: 10%;">Actions</th>
                                                </tr>
                                            </thead>
                                        <tbody>
                                            <?php if (empty($activeChars)): ?>
                                                <tr>
                                                    <td colspan="8" style="text-align:center; color:#666; padding: 40px;">No active
                                                        characters
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($activeChars as $c): ?>
                                                    <tr>
                                                        <td><?php echo $c['Slot']; ?></td>
                                                        <td><img src="assets/class/<?php echo (int) $c['Job']; ?>.webp"
                                                                style="width: 24px; vertical-align: middle;"></td>
                                                        <td><a href="admin.php?view=CharEdit&id=<?php echo $c['CharID']; ?>"
                                                                class="user-link"><?php echo htmlspecialchars($c['CharName']); ?></a></td>
                                                        <td><?php echo $c['Level']; ?></td>
                                                        <td><?php echo getRaceName((int) $c['Family']); ?></td>
                                                        <td style="color:#888;"><?php echo date('Y M d H:i', strtotime($c['RegDate'])); ?>
                                                        </td>
                                                        <td style="color: #e8c881;"><?php echo formatRelativeTime($c['LeaveDate']); ?></td>
                                                        <td style="text-align:right;">
                                                            <div style="display: flex; justify-content: flex-end; gap: 15px;">
                                                                <form action="admin_actions.php" method="POST"
                                                                    onsubmit="return confirm('Delete character <?php echo htmlspecialchars($c['CharName']); ?>?');">
                                                                    <input type="hidden" name="action" value="char_delete">
                                                                    <input type="hidden" name="char_id" value="<?php echo $c['CharID']; ?>">
                                                                    <input type="hidden" name="user_uid" value="<?php echo $userUID; ?>">
                                                                    <input type="hidden" name="return_view" value="UserEdit">
                                                                    <input type="hidden" name="tab" value="Characters">
                                                                    <button type="submit"
                                                                        style="background:none; border:none; color:#e53e3e; cursor:pointer; font-size: 18px;"
                                                                        title="Delete"><i class="fas fa-trash"></i></button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Deleted Characters -->
                                <div>
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                        <h2 style="color: #fff; font-size: 18px; text-transform: uppercase; letter-spacing: 2px;">
                                            Deleted
                                            Characters</h2>
                                    </div>
                                    <div class="chars-table-container">
                                        <table class="chars-table">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: left; padding-left: 10px; width: 20%;">Name</th>
                                                    <th style="width: 10%;">Lv</th>
                                                    <th style="width: 15%;">Race</th>
                                                    <th style="width: 15%;">Create date</th>
                                                    <th style="width: 15%;">Last enter</th>
                                                    <th style="width: 15%;">Delete date</th>
                                                    <th style="text-align:right; width: 10%;">Actions</th>
                                                </tr>
                                            </thead>
                                        <tbody>
                                            <?php if (empty($deletedChars)): ?>
                                                <tr>
                                                    <td colspan="7" style="text-align:center; color:#666; padding: 40px;">No deleted
                                                        characters
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($deletedChars as $c): ?>
                                                    <tr>
                                                        <td><a href="admin.php?view=CharEdit&id=<?php echo $c['CharID']; ?>"
                                                                class="user-link"><?php echo htmlspecialchars($c['CharName']); ?></a></td>
                                                        <td><?php echo $c['Level']; ?></td>
                                                        <td><?php echo getRaceName((int) $c['Family']); ?></td>
                                                        <td style="color:#888;"><?php echo date('Y M d H:i', strtotime($c['RegDate'])); ?>
                                                        </td>
                                                        <td style="color: #e8c881;"><?php echo formatRelativeTime($c['LeaveDate']); ?></td>
                                                        <td style="color:#888;">
                                                            <?php echo $c['DeleteDate'] ? date('Y M d H:i', strtotime($c['DeleteDate'])) : 'N/A'; ?>
                                                        </td>
                                                        <td style="text-align:right;">
                                                            <form action="admin_actions.php" method="POST"
                                                                onsubmit="return confirm('Restore character <?php echo htmlspecialchars($c['CharName']); ?>?');">
                                                                <input type="hidden" name="action" value="char_restore">
                                                                <input type="hidden" name="char_id" value="<?php echo $c['CharID']; ?>">
                                                                <input type="hidden" name="user_uid" value="<?php echo $userUID; ?>">
                                                                <input type="hidden" name="return_view" value="UserEdit">
                                                                <input type="hidden" name="tab" value="Characters">
                                                                <button type="submit"
                                                                    style="background:none; border:none; color:#48bb78; cursor:pointer;"
                                                                    title="Restore"><i class="fas fa-undo"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php elseif ($tab === 'AuthLog'): ?>
                                <?php
                                // Pagination for Auth Log
                                $alLimit = 30;
                                $alPage = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
                                $alFilterType = isset($_GET['at']) ? $_GET['at'] : '';
                                $alSearchIP = isset($_GET['sip']) ? trim($_GET['sip']) : '';
                                $alOffset = ($alPage - 1) * $alLimit;

                                // Build WHERE clause
                                $alWhere = ["UserID = ?"];
                                $alParams = [$userData['UserID']];
                                if ($alFilterType === '107') {
                                    $alWhere[] = "ActionType = 107";
                                } elseif ($alFilterType === '108') {
                                    $alWhere[] = "ActionType = 108";
                                } else {
                                    $alWhere[] = "ActionType IN (107, 108)";
                                }
                                if ($alSearchIP !== '') {
                                    $alWhere[] = "Text1 LIKE '%" . str_replace("'", "''", $alSearchIP) . "%'";
                                }
                                $alWhereSql = "WHERE " . implode(" AND ", $alWhere);

                                // Total Count
                                $alCountSql = "SELECT COUNT(*) as Total FROM PS_GameLog.dbo.ActionLog $alWhereSql";
                                $alCountStmt = odbc_prepare($conn, $alCountSql);
                                odbc_execute($alCountStmt, $alParams);
                                $alTotal = (int) (odbc_fetch_array($alCountStmt)['Total'] ?? 0);
                                $alTotalPages = max(1, ceil($alTotal / $alLimit));

                                $alSql = "SELECT ActionType, ActionTime, Text1
                                          FROM PS_GameLog.dbo.ActionLog
                                          $alWhereSql
                                          ORDER BY ActionTime DESC
                                          OFFSET $alOffset ROWS FETCH NEXT $alLimit ROWS ONLY";
                                $alStmt = odbc_prepare($conn, $alSql);
                                odbc_execute($alStmt, $alParams);
                                ?>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                                    <h4 style="color: #fff; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; border-left: 3px solid #da9f50; padding-left: 15px; display: flex; align-items: center; gap: 10px; margin: 0;">
                                        <i class="fas fa-sign-in-alt" style="color: #da9f50; font-size: 14px;"></i> Authentication Log
                                    </h4>
                                    <span style="color: #555; font-size: 12px;"><?php echo number_format($alTotal); ?> event<?php echo $alTotal !== 1 ? 's' : ''; ?></span>
                                </div>

                                <div class="chars-table-container">
                                    <form method="GET" id="authLogFilters">
                                        <input type="hidden" name="view" value="UserEdit">
                                        <input type="hidden" name="uid" value="<?php echo $userUID; ?>">
                                        <input type="hidden" name="tab" value="AuthLog">
                                    <table class="chars-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 120px;">Type
                                                    <select name="at" class="table-filter-select" onchange="this.form.submit()">
                                                        <option value="">All</option>
                                                        <option value="107" <?php echo $alFilterType === '107' ? 'selected' : ''; ?>>Login</option>
                                                        <option value="108" <?php echo $alFilterType === '108' ? 'selected' : ''; ?>>Logout</option>
                                                    </select>
                                                </th>
                                                <th>Date & Time</th>
                                                <th>IP Address
                                                    <input type="text" name="sip" class="table-filter-input" placeholder="IP" value="<?php echo htmlspecialchars($alSearchIP); ?>" onchange="this.form.submit()">
                                                </th>
                                                <th>Relative</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $alCount = 0;
                                            $prevTime = null;
                                            while ($al = odbc_fetch_array($alStmt)):
                                                $alCount++;
                                                $isLogin = (int) $al['ActionType'] === 107;
                                                $actionTime = strtotime($al['ActionTime']);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($isLogin): ?>
                                                            <span style="background: rgba(72, 187, 120, 0.1); color: #48bb78; border: 1px solid rgba(72, 187, 120, 0.2); font-size: 11px; padding: 3px 10px; border-radius: 4px; font-weight: 600;">
                                                                <i class="fas fa-sign-in-alt" style="margin-right: 4px;"></i> LOGIN
                                                            </span>
                                                        <?php else: ?>
                                                            <span style="background: rgba(229, 62, 62, 0.1); color: #e53e3e; border: 1px solid rgba(229, 62, 62, 0.2); font-size: 11px; padding: 3px 10px; border-radius: 4px; font-weight: 600;">
                                                                <i class="fas fa-sign-out-alt" style="margin-right: 4px;"></i> LOGOUT
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('Y M d', $actionTime); ?> <span style="color: #e8c881; font-weight: 500;"><?php echo date('H:i:s', $actionTime); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($isLogin && !empty($al['Text1'])): ?>
                                                            <span style="color: #6a8ec1; font-family: monospace; font-size: 12px;">
                                                                <i class="fas fa-globe" style="margin-right: 5px; opacity: 0.5; font-size: 10px;"></i><?php echo htmlspecialchars($al['Text1']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span style="color: #333;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="color: #888;">
                                                        <?php echo formatRelativeTime($al['ActionTime']); ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            <?php if ($alCount === 0): ?>
                                                <tr>
                                                    <td colspan="4" style="padding: 100px; color: #444; text-align: center; font-style: italic; letter-spacing: 1px;">
                                                        <i class="fas fa-shield-alt" style="font-size: 30px; opacity: 0.15; margin-bottom: 15px; display: block;"></i>
                                                        No authentication events found for this account.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>

                                <?php if ($alTotalPages > 1): ?>
                                    <div class="pagination"
                                        style="display: flex; justify-content: center; gap: 10px; margin-top: 30px; align-items: center;">
                                        <?php if ($alPage > 1): ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=AuthLog&p=<?php echo $alPage - 1; ?><?php echo $alFilterType !== '' ? '&at=' . $alFilterType : ''; ?><?php echo $alSearchIP !== '' ? '&sip=' . urlencode($alSearchIP) : ''; ?>"
                                                class="btn-search" style="text-decoration: none; padding: 8px 15px;">&laquo; Prev</a>
                                        <?php endif; ?>

                                        <?php
                                        $alStartPage = max(1, $alPage - 2);
                                        $alEndPage = min($alTotalPages, $alPage + 2);
                                        for ($i = $alStartPage; $i <= $alEndPage; $i++):
                                            ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=AuthLog&p=<?php echo $i; ?><?php echo $alFilterType !== '' ? '&at=' . $alFilterType : ''; ?><?php echo $alSearchIP !== '' ? '&sip=' . urlencode($alSearchIP) : ''; ?>"
                                                class="btn-search"
                                                style="text-decoration: none; padding: 8px 15px; <?php echo ($i === $alPage) ? 'background: #e8c881; color: #000;' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>

                                        <?php if ($alPage < $alTotalPages): ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=AuthLog&p=<?php echo $alPage + 1; ?><?php echo $alFilterType !== '' ? '&at=' . $alFilterType : ''; ?><?php echo $alSearchIP !== '' ? '&sip=' . urlencode($alSearchIP) : ''; ?>"
                                                class="btn-search" style="text-decoration: none; padding: 8px 15px;">Next &raquo;</a>
                                        <?php endif; ?>
                                    </div>
                                    <div style="text-align: center; color: #555; font-size: 11px; margin-top: 10px;">
                                        PAGE <?php echo $alPage; ?> OF <?php echo $alTotalPages; ?> &bull; TOTAL
                                        <?php echo number_format($alTotal); ?> EVENTS
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($tab === 'PointLog'): ?>
                                <?php
                                // Pagination for Point Log
                                $plLimit = 25;
                                $plPage = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
                                $plSearchReason = isset($_GET['sr']) ? trim($_GET['sr']) : '';
                                $plOffset = ($plPage - 1) * $plLimit;

                                // Build WHERE
                                $plWhereParts = ["UserID = ?"];
                                if ($plSearchReason !== '') {
                                    $plWhereParts[] = "Reason LIKE '%" . str_replace("'", "''", $plSearchReason) . "%'";
                                }
                                $plWhereSql = implode(" AND ", $plWhereParts);

                                // Total Count
                                $plCountSql = "SELECT COUNT(*) as Total FROM PS_UserData.dbo.Web_PointHistory WHERE $plWhereSql";
                                $plCountStmt = odbc_prepare($conn, $plCountSql);
                                odbc_execute($plCountStmt, [$userData['UserID']]);
                                $plTotal = (int) (odbc_fetch_array($plCountStmt)['Total'] ?? 0);
                                $plTotalPages = ceil($plTotal / $plLimit);

                                // Fetch Point History
                                $plSql = "SELECT RowID, UserID, Date, PointsAdded, Reason, GM_Account 
                                          FROM PS_UserData.dbo.Web_PointHistory 
                                          WHERE $plWhereSql
                                          ORDER BY RowID DESC 
                                          OFFSET $plOffset ROWS FETCH NEXT $plLimit ROWS ONLY";
                                $plStmt = odbc_prepare($conn, $plSql);
                                odbc_execute($plStmt, [$userData['UserID']]);
                                ?>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                                    <h4 style="color: #fff; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; border-left: 3px solid #da9f50; padding-left: 15px; display: flex; align-items: center; gap: 10px; margin: 0;">
                                        <i class="fas fa-history" style="color: #da9f50; font-size: 14px;"></i> Point Transaction History
                                    </h4>
                                    <span style="color: #555; font-size: 12px;"><?php echo number_format($plTotal); ?> record<?php echo $plTotal !== 1 ? 's' : ''; ?></span>
                                </div>

                                <div class="chars-table-container">
                                    <form method="GET" id="pointLogFilters">
                                        <input type="hidden" name="view" value="UserEdit">
                                        <input type="hidden" name="uid" value="<?php echo $userUID; ?>">
                                        <input type="hidden" name="tab" value="PointLog">
                                    <table class="chars-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;">#</th>
                                                <th>Date</th>
                                                <th>Points</th>
                                                <th style="text-align: left;">Reason
                                                    <input type="text" name="sr" class="table-filter-input" style="text-align: left;" placeholder="Reason" value="<?php echo htmlspecialchars($plSearchReason); ?>" onchange="this.form.submit()">
                                                </th>
                                                <th>Admin / GM</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $plCount = 0;
                                            while ($h = odbc_fetch_array($plStmt)):
                                                $plCount++;
                                                ?>
                                                <tr>
                                                    <td style="font-family: 'monospace'; color: #888;">#<?php echo $h['RowID']; ?></td>
                                                    <td>
                                                        <?php echo date('Y M d', strtotime($h['Date'])); ?> <span style="color: #888; font-size: 11px;"><?php echo date('H:i', strtotime($h['Date'])); ?></span>
                                                    </td>
                                                    <td style="color: #e8c881; font-weight: 700;">
                                                        +<?php echo number_format($h['PointsAdded']); ?>
                                                    </td>
                                                    <td style="text-align: left; color: #aaa; font-style: italic;">
                                                        <?php echo htmlspecialchars($h['Reason'] ?: '-'); ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge" style="background: rgba(106, 142, 193, 0.1); color: #6a8ec1; border: 1px solid rgba(106, 142, 193, 0.2); font-size: 11px; padding: 3px 10px;">
                                                            <i class="fas fa-user-shield" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($h['GM_Account']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            <?php if ($plCount === 0): ?>
                                                <tr>
                                                    <td colspan="5" style="padding: 100px; color: #444; text-align: center; font-style: italic; letter-spacing: 1px;">
                                                        <i class="fas fa-info-circle" style="margin-right: 10px;"></i> No point transactions found for this account.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>

                                <?php if ($plTotalPages > 1): ?>
                                    <div class="pagination"
                                        style="display: flex; justify-content: center; gap: 10px; margin-top: 30px; align-items: center;">
                                        <?php if ($plPage > 1): ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=PointLog&p=<?php echo $plPage - 1; ?><?php echo $plSearchReason !== '' ? '&sr=' . urlencode($plSearchReason) : ''; ?>"
                                                class="btn-search" style="text-decoration: none; padding: 8px 15px;">&laquo; Prev</a>
                                        <?php endif; ?>

                                        <?php
                                        $plStartPage = max(1, $plPage - 2);
                                        $plEndPage = min($plTotalPages, $plPage + 2);
                                        for ($i = $plStartPage; $i <= $plEndPage; $i++):
                                            ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=PointLog&p=<?php echo $i; ?><?php echo $plSearchReason !== '' ? '&sr=' . urlencode($plSearchReason) : ''; ?>"
                                                class="btn-search"
                                                style="text-decoration: none; padding: 8px 15px; <?php echo ($i === $plPage) ? 'background: #e8c881; color: #000;' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>

                                        <?php if ($plPage < $plTotalPages): ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=PointLog&p=<?php echo $plPage + 1; ?><?php echo $plSearchReason !== '' ? '&sr=' . urlencode($plSearchReason) : ''; ?>"
                                                class="btn-search" style="text-decoration: none; padding: 8px 15px;">Next &raquo;</a>
                                        <?php endif; ?>
                                    </div>
                                    <div style="text-align: center; color: #555; font-size: 11px; margin-top: 10px;">
                                        PAGE <?php echo $plPage; ?> OF <?php echo $plTotalPages; ?> &bull; TOTAL
                                        <?php echo number_format($plTotal); ?> TRANSACTIONS
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($tab === 'Purchases'): ?>
                                <?php
                                // Pagination for Purchases (ActionType 113 from ActionLog)
                                $puLimit = 25;
                                $puPage = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
                                $puSearchItem = isset($_GET['si']) ? trim($_GET['si']) : '';
                                $puOffset = ($puPage - 1) * $puLimit;

                                // Build WHERE (no alias for count, AL. prefix for join query)
                                $puSearchFilter = '';
                                if ($puSearchItem !== '') {
                                    $puSearchFilter = " AND Text1 LIKE '%" . str_replace("'", "''", $puSearchItem) . "%'";
                                }

                                // Total Count
                                $puCountSql = "SELECT COUNT(*) as Total FROM PS_GameLog.dbo.ActionLog WHERE UserUID = ? AND ActionType = 113" . $puSearchFilter;
                                $puCountStmt = odbc_prepare($conn, $puCountSql);
                                odbc_execute($puCountStmt, [$userUID]);
                                $puTotal = (int) (odbc_fetch_array($puCountStmt)['Total'] ?? 0);
                                $puTotalPages = max(1, ceil($puTotal / $puLimit));

                                // Fetch Purchases with Character Name
                                $puSql = "SELECT AL.ActionTime, AL.CharID, AL.Text1, AL.Value4, AL.Value5, C.CharName
                                          FROM PS_GameLog.dbo.ActionLog AL
                                          LEFT JOIN PS_GameData.dbo.Chars C ON C.CharID = AL.CharID
                                          WHERE AL.UserUID = ? AND AL.ActionType = 113" . $puSearchFilter . "
                                          ORDER BY AL.ActionTime DESC
                                          OFFSET $puOffset ROWS FETCH NEXT $puLimit ROWS ONLY";
                                $puStmt = odbc_prepare($conn, $puSql);
                                odbc_execute($puStmt, [$userUID]);
                                ?>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                                    <h4 style="color: #fff; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; border-left: 3px solid #da9f50; padding-left: 15px; display: flex; align-items: center; gap: 10px; margin: 0;">
                                        <i class="fas fa-shopping-cart" style="color: #da9f50; font-size: 14px;"></i> Purchase History
                                    </h4>
                                    <span style="color: #555; font-size: 12px;"><?php echo number_format($puTotal); ?> purchase<?php echo $puTotal !== 1 ? 's' : ''; ?></span>
                                </div>

                                <div class="chars-table-container">
                                    <form method="GET" id="purchaseFilters">
                                        <input type="hidden" name="view" value="UserEdit">
                                        <input type="hidden" name="uid" value="<?php echo $userUID; ?>">
                                        <input type="hidden" name="tab" value="Purchases">
                                    <table class="chars-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th style="text-align: left; padding-left: 20px;">Item
                                                    <input type="text" name="si" class="table-filter-input" style="text-align: left;" placeholder="Item" value="<?php echo htmlspecialchars($puSearchItem); ?>" onchange="this.form.submit()">
                                                </th>
                                                <th>Character</th>
                                                <th>Qty</th>
                                                <th>Gold</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $puCount = 0;
                                            while ($pu = odbc_fetch_array($puStmt)):
                                                $puCount++;
                                                $itemName = $pu['Text1'] ?: '-';
                                                $charName = $pu['CharName'] ?: '-';
                                                $qty = (int) ($pu['Value4'] ?? 0);
                                                $gold = number_format(abs((int) ($pu['Value5'] ?? 0)));
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php echo date('Y M d', strtotime($pu['ActionTime'])); ?> <span style="color: #888; font-size: 11px;"><?php echo date('H:i', strtotime($pu['ActionTime'])); ?></span>
                                                    </td>
                                                    <td style="text-align: left; padding-left: 20px;">
                                                        <span style="color: #e8c881; font-weight: 500;"><?php echo htmlspecialchars($itemName); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($pu['CharID'] && $charName !== '-'): ?>
                                                            <a href="admin.php?view=CharEdit&id=<?php echo $pu['CharID']; ?>" class="user-link"><?php echo htmlspecialchars($charName); ?></a>
                                                        <?php else: ?>
                                                            <span style="color: #444;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="color: #aaa;">
                                                        x<?php echo $qty; ?>
                                                    </td>
                                                    <td style="color: #e8c881; font-weight: 600;">
                                                        <?php echo $gold; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            <?php if ($puCount === 0): ?>
                                                <tr>
                                                    <td colspan="5" style="padding: 100px; color: #444; text-align: center; font-style: italic; letter-spacing: 1px;">
                                                        <i class="fas fa-shopping-cart" style="font-size: 30px; opacity: 0.15; margin-bottom: 15px; display: block;"></i>
                                                        No purchases found for this account.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>

                                <?php if ($puTotalPages > 1): ?>
                                    <div class="pagination"
                                        style="display: flex; justify-content: center; gap: 10px; margin-top: 30px; align-items: center;">
                                        <?php if ($puPage > 1): ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Purchases&p=<?php echo $puPage - 1; ?><?php echo $puSearchItem !== '' ? '&si=' . urlencode($puSearchItem) : ''; ?>"
                                                class="btn-search" style="text-decoration: none; padding: 8px 15px;">&laquo; Prev</a>
                                        <?php endif; ?>

                                        <?php
                                        $puStartPage = max(1, $puPage - 2);
                                        $puEndPage = min($puTotalPages, $puPage + 2);
                                        for ($i = $puStartPage; $i <= $puEndPage; $i++):
                                            ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Purchases&p=<?php echo $i; ?><?php echo $puSearchItem !== '' ? '&si=' . urlencode($puSearchItem) : ''; ?>"
                                                class="btn-search"
                                                style="text-decoration: none; padding: 8px 15px; <?php echo ($i === $puPage) ? 'background: #e8c881; color: #000;' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>

                                        <?php if ($puPage < $puTotalPages): ?>
                                            <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Purchases&p=<?php echo $puPage + 1; ?><?php echo $puSearchItem !== '' ? '&si=' . urlencode($puSearchItem) : ''; ?>"
                                                class="btn-search" style="text-decoration: none; padding: 8px 15px;">Next &raquo;</a>
                                        <?php endif; ?>
                                    </div>
                                    <div style="text-align: center; color: #555; font-size: 11px; margin-top: 10px;">
                                        PAGE <?php echo $puPage; ?> OF <?php echo $puTotalPages; ?> &bull; TOTAL
                                        <?php echo number_format($puTotal); ?> PURCHASES
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($tab === 'Chat'): ?>
                                <?php
                                // Pagination for Chat
                                $limit = 25;
                                $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
                                $chatFilterType = isset($_GET['ct']) ? $_GET['ct'] : '';
                                $offset = ($page - 1) * $limit;

                                // Build WHERE clause
                                $whereClauses = ["L.UserUID = ?"];
                                $queryParams = [$userUID];
                                if ($chatFilterType !== '') {
                                    $whereClauses[] = "L.ChatType = ?";
                                    $queryParams[] = (int) $chatFilterType;
                                }
                                $whereSql = "WHERE " . implode(" AND ", $whereClauses);

                                // Total Count
                                $countSql = "SELECT COUNT(*) as Total FROM PS_ChatLog.dbo.ChatLog L $whereSql";
                                $cStmt = odbc_prepare($conn, $countSql);
                                odbc_execute($cStmt, $queryParams);
                                $totalResults = (int) (odbc_fetch_array($cStmt)['Total'] ?? 0);
                                $totalPages = ceil($totalResults / $limit);

                                // Fetch Chat Logs - OFFSET and FETCH NEXT require SQL Server 2012+
                                $chatSql = "SELECT L.CharID, L.ChatType, L.ChatData, L.MapID, L.ChatTime, C.CharName 
                                FROM PS_ChatLog.dbo.ChatLog L
                                LEFT JOIN PS_GameData.dbo.Chars C ON C.CharID = L.CharID
                                $whereSql
                                ORDER BY L.ChatTime DESC
                                OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";

                                $chatQuery = odbc_prepare($conn, $chatSql);
                                odbc_execute($chatQuery, $queryParams);
                                ?>

                                <div style="margin-bottom: 25px; display: flex; align-items: flex-end;">
                                    <form method="GET" style="display: flex; flex-direction: column; gap: 8px;">
                                        <input type="hidden" name="view" value="UserEdit">
                                        <input type="hidden" name="uid" value="<?php echo $userUID; ?>">
                                        <input type="hidden" name="tab" value="Chat">

                                        <label
                                            style="color: #888; text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; padding-left: 2px;">Type</label>
                                        <select name="ct"
                                            style="background: #050505; border: 1px solid #222; color: #ccc; padding: 8px 15px; font-size: 13px; outline: none; border-radius: 4px; min-width: 150px; cursor: pointer; transition: border-color 0.3s;"
                                            onmouseover="this.style.borderColor='#444'" onmouseout="this.style.borderColor='#222'"
                                            onchange="this.form.submit()">
                                            <option value="">All</option>
                                            <?php
                                            $cTypesList = [1, 3, 4, 5, 7];
                                            foreach ($cTypesList as $ctId):
                                                ?>
                                                <option value="<?php echo $ctId; ?>" <?php echo ($chatFilterType !== '' && (int) $chatFilterType === $ctId) ? 'selected' : ''; ?>>
                                                    <?php echo getChatTypeName($ctId); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>

                                <div class="chars-table-container">
                                    <table class="chars-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 160px; text-align: left; padding-left: 15px;">Time</th>
                                                <th style="width: 140px; text-align: left;">Character</th>
                                                <th style="width: 110px;">Type</th>
                                                <th style="text-align: left;">Message</th>
                                                <th style="width: 120px;">Map</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $hasChat = false;
                                            while ($chat = odbc_fetch_array($chatQuery)):
                                                $hasChat = true; ?>
                                                <tr>
                                                    <td>
                                                        <?php $ct = strtotime($chat['ChatTime']); ?>
                                                        <span style="color: #aaa;"><?php echo date('d M Y', $ct); ?></span>
                                                        <span style="color: #555; font-size: 11px;"><?php echo date('H:i', $ct); ?></span>
                                                     </td>
                                                    <td>
                                                        <?php if ($chat['CharName']): ?>
                                                            <a href="admin.php?view=CharEdit&id=<?php echo $chat['CharID']; ?>" class="user-link"><?php echo htmlspecialchars($chat['CharName']); ?></a>
                                                        <?php else: ?>
                                                            <span><?php echo $chat['CharID']; ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $cType = (int) $chat['ChatType'];
                                                        $cColor = '#eee';
                                                        $cBg = 'rgba(255,255,255,0.04)';
                                                        if ($cType == 0) { $cColor = '#f6ad55'; $cBg = 'rgba(246,173,85,0.08)'; }      // Whisper
                                                        if ($cType == 1) { $cColor = '#aaa';    $cBg = 'rgba(255,255,255,0.04)'; }     // Normal
                                                        if ($cType == 3) { $cColor = '#da70d6'; $cBg = 'rgba(218,112,214,0.08)'; }     // Guild
                                                        if ($cType == 4) { $cColor = '#68d391'; $cBg = 'rgba(104,211,145,0.08)'; }     // Party
                                                        if ($cType == 5) { $cColor = '#e8c881'; $cBg = 'rgba(232,200,129,0.08)'; }     // Trade
                                                        if ($cType == 7) { $cColor = '#7b8cf7'; $cBg = 'rgba(123,140,247,0.08)'; }     // Area
                                                        ?>
                                                        <span style="display: inline-block; background: <?php echo $cBg; ?>; color: <?php echo $cColor; ?>; border: 1px solid <?php echo $cColor; ?>22; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 3px 8px; border-radius: 3px;">
                                                            <?php echo getChatTypeName($cType); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($chat['ChatData']); ?>
                                                    </td>
                                                    <td><?php echo getMapName($chat['MapID']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>

                                            <?php if (!$hasChat): ?>
                                                <tr>
                                                    <td colspan="5" style="text-align:center; color:#555; padding: 40px;">No chat history found for this account</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>

                                    <?php if ($totalPages > 1): ?>
                                        <div class="pagination"
                                            style="display: flex; justify-content: center; gap: 10px; margin-top: 30px; align-items: center;">
                                            <?php if ($page > 1): ?>
                                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Chat&p=<?php echo $page - 1; ?><?php echo $chatFilterType !== '' ? '&ct=' . $chatFilterType : ''; ?>"
                                                    class="btn-search" style="text-decoration: none; padding: 8px 15px;">&laquo; Prev</a>
                                            <?php endif; ?>

                                            <?php
                                            // Show page numbers
                                            $startPage = max(1, $page - 2);
                                            $endPage = min($totalPages, $page + 2);
                                            for ($i = $startPage; $i <= $endPage; $i++):
                                                ?>
                                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Chat&p=<?php echo $i; ?><?php echo $chatFilterType !== '' ? '&ct=' . $chatFilterType : ''; ?>"
                                                    class="btn-search"
                                                    style="text-decoration: none; padding: 8px 15px; <?php echo ($i === $page) ? 'background: #e8c881; color: #000;' : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            <?php endfor; ?>

                                            <?php if ($page < $totalPages): ?>
                                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Chat&p=<?php echo $page + 1; ?><?php echo $chatFilterType !== '' ? '&ct=' . $chatFilterType : ''; ?>"
                                                    class="btn-search" style="text-decoration: none; padding: 8px 15px;">Next &raquo;</a>
                                            <?php endif; ?>
                                        </div>
                                        <div style="text-align: center; color: #555; font-size: 11px; margin-top: 10px;">
                                            PAGE <?php echo $page; ?> OF <?php echo $totalPages; ?> &bull; TOTAL
                                            <?php echo number_format($totalResults); ?> MESSAGES
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($tab === 'Warehouse'): ?>
                                <?php
                                $p = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
                                $itemsPerPage = 15;
                                $offset = ($p - 1) * $itemsPerPage;

                                // Total Count Query
                                $countSql = "SELECT COUNT(*) as Total FROM PS_GameData.dbo.UserStoredItems ci WHERE ci.UserUID = ?";
                                $cStmt = odbc_prepare($conn, $countSql);
                                odbc_execute($cStmt, [$userUID]);
                                $totalItems = (int) (odbc_fetch_array($cStmt)['Total'] ?? 0);
                                $totalPages = ceil($totalItems / $itemsPerPage);

                                // Fetch items for the current page
                                $sql = "SELECT ci.ItemUID, ci.Type, ci.TypeID, i.ItemID, i.ItemName, 
                                       ci.Craftname, 0 as Quality, ci.Gem1, ci.Gem2, ci.Gem3, ci.Gem4, ci.Gem5, ci.Gem6,
                                       ci.Maketime, ci.[Count], ci.Slot, ci.Del,
                                       i.ReqLevel, i.ConstStr, i.ConstDex, i.ConstRec, i.ConstInt, i.ConstWis, i.ConstLuc,
                                       i.ConstHP, i.ConstSP, i.ConstMP, i.Defensefighter, i.Defensemage
                                FROM PS_GameData.dbo.UserStoredItems ci
                                JOIN PS_GameDefs.dbo.Items i ON ci.Type = i.Type AND ci.TypeID = i.TypeID
                                WHERE ci.UserUID = ?
                                ORDER BY ci.ItemUID DESC
                                OFFSET $offset ROWS FETCH NEXT $itemsPerPage ROWS ONLY";
                                $qRes = odbc_prepare($conn, $sql);
                                odbc_execute($qRes, [$userUID]);

                                $items_list = [];
                                $gem_ids = [];
                                if ($qRes) {
                                    while ($item = odbc_fetch_array($qRes)) {
                                        $items_list[] = $item;
                                        for ($g = 1; $g <= 6; $g++) {
                                            $gval = (int) $item["Gem$g"];
                                            if ($gval > 0)
                                                $gem_ids[] = 30000 + $gval;
                                        }
                                    }
                                }

                                $gem_names = [];
                                if (!empty($gem_ids)) {
                                    $id_list = implode(",", array_unique($gem_ids));
                                    $gem_res = odbc_exec($conn, "SELECT ItemID, ItemName FROM PS_GameDefs.dbo.Items WHERE ItemID IN ($id_list)");
                                    if ($gem_res) {
                                        while ($gem = odbc_fetch_array($gem_res)) {
                                            $gem_names[$gem['ItemID']] = $gem['ItemName'];
                                        }
                                    }
                                }
                                ?>

                                <div class="chars-table-container">
                                    <table class="chars-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 35%; text-align: left; padding-left: 20px;">ITEM</th>

                                                <th style="width: 10%;">SLOT</th>
                                                <th style="width: 10%;">QTY</th>
                                                <th style="width: 25%;">GEMS</th>
                                                <th style="width: 12%;">DATE</th>
                                                <th style="width: 8%;">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($items_list)): ?>
                                                <tr>
                                                    <td colspan="6" style="text-align:center; padding: 40px; color: #555;">Warehouse is empty</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($items_list as $item):
                                                    $gems = [];
                                                    for ($g = 1; $g <= 6; $g++) {
                                                        $gval = (int) $item["Gem$g"];
                                                        if ($gval > 0) {
                                                            $gid = 30000 + $gval;
                                                            $gems[] = [
                                                                'id' => $gid,
                                                                'name' => isset($gem_names[$gid]) ? $gem_names[$gid] : "Unknown Gem [$gid]"
                                                            ];
                                                        }
                                                    }

                                                    $craft = $item['Craftname'] ?? '00000000000000000000';
                                                    if (strlen($craft) < 20)
                                                        $craft = str_pad($craft, 20, '0', STR_PAD_LEFT);

                                                    $bonusStats = [
                                                        'STR' => (int) substr($craft, 0, 2),
                                                        'DEX' => (int) substr($craft, 2, 2),
                                                        'REC' => (int) substr($craft, 4, 2),
                                                        'INT' => (int) substr($craft, 6, 2),
                                                        'WIS' => (int) substr($craft, 8, 2),
                                                        'LUC' => (int) substr($craft, 10, 2),
                                                        'HP' => (int) substr($craft, 12, 2) * 100,
                                                        'SP' => (int) substr($craft, 14, 2) * 100,
                                                        'MP' => (int) substr($craft, 16, 2) * 100,
                                                        'Enchant' => (int) substr($craft, 18, 2)
                                                    ];

                                                    $baseStats = [
                                                        'STR' => (int) ($item['ConstStr'] ?? 0),
                                                        'DEX' => (int) ($item['ConstDex'] ?? 0),
                                                        'REC' => (int) ($item['ConstRec'] ?? 0),
                                                        'INT' => (int) ($item['ConstInt'] ?? 0),
                                                        'WIS' => (int) ($item['ConstWis'] ?? 0),
                                                        'LUC' => (int) ($item['ConstLuc'] ?? 0),
                                                        'HP' => (int) ($item['ConstHP'] ?? 0),
                                                        'SP' => (int) ($item['ConstSP'] ?? 0),
                                                        'MP' => (int) ($item['ConstMP'] ?? 0)
                                                    ];
                                                    ?>
                                                    <tr>
                                                        <td style="text-align: left; padding-left: 20px; overflow: visible;">
                                                            <div class="item-name-wrapper">
                                                                <div style="color: #eee; font-weight: 500; font-size: 13px;">
                                                                    <?php echo htmlspecialchars($item['ItemName'] ?? 'Unknown'); ?>
                                                                    <?php if ($bonusStats['Enchant'] > 0): ?>
                                                                        <span
                                                                            class="enchant-badge">[<?php echo $bonusStats['Enchant']; ?>]</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div style="color: #555; font-size: 10px; letter-spacing: 0.5px;">UID:
                                                                    <?php echo $item['ItemUID']; ?> | ID: <?php echo $item['ItemID']; ?>
                                                                </div>

                                                                <div class="item-stats-tooltip">
                                                                    <div class="tooltip-header">
                                                                        <span><?php echo htmlspecialchars($item['ItemName'] ?? 'Unknown Item'); ?></span>
                                                                        <?php if ($bonusStats['Enchant'] > 0): ?>
                                                                            <span
                                                                                class="enchant-badge">[<?php echo $bonusStats['Enchant']; ?>]</span>
                                                                        <?php endif; ?>
                                                                    </div>

                                                                    <?php if (($item['Defensefighter'] ?? 0) > 0): ?>
                                                                        <div class="stat-line" style="color: #fff;">Phys. defense
                                                                            <?php echo $item['Defensefighter']; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <?php if (($item['Defensemage'] ?? 0) > 0): ?>
                                                                        <div class="stat-line" style="color: #fff;">Mag. defense
                                                                            <?php echo $item['Defensemage']; ?>
                                                                        </div>
                                                                    <?php endif; ?>

                                                                    <?php
                                                                    $allStats = ['HP' => 'hp', 'SP' => 'sp', 'MP' => 'mp', 'STR' => 'str', 'DEX' => 'dex', 'REC' => 'rec', 'INT' => 'int', 'WIS' => 'wis', 'LUC' => 'luc'];
                                                                    foreach ($allStats as $label => $cssClass):
                                                                        $base = $baseStats[$label];
                                                                        $bonus = $bonusStats[$label];
                                                                        if ($base > 0 || $bonus > 0): ?>
                                                                            <div class="stat-line">
                                                                                <span
                                                                                    class="stat-label-<?php echo $cssClass; ?>"><?php echo $label; ?></span>
                                                                                <span class="stat-v-base">+<?php echo $base + $bonus; ?></span>
                                                                                <?php if ($bonus > 0): ?><span
                                                                                        class="stat-v-bonus">+<?php echo $bonus; ?></span><?php endif; ?>
                                                                            </div>
                                                                        <?php endif; endforeach; ?>

                                                                    <?php if (isset($item['ReqLevel']) && $item['ReqLevel'] > 0): ?>
                                                                        <div class="req-level" style="color:#eee;">Lv.
                                                                            <?php echo $item['ReqLevel']; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $item['Slot']; ?>
                                                        </td>
                                                        <td style="color: #e8c881; font-weight: 600;">
                                                            <?php echo number_format($item['Count']); ?>
                                                        </td>
                                                        <td>
                                                            <?php if (empty($gems)): ?><span style="color: #555;">-</span><?php else: ?>
                                                                <div
                                                                    style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                                                                    <?php foreach ($gems as $gem): ?>
                                                                        <div class="gem-badge"
                                                                            style="font-size: 9px; color: #e8c881; background: rgba(232, 200, 129, 0.05); padding: 2px 8px; border-radius: 10px; border: 1px solid rgba(232, 200, 129, 0.1); width: fit-content;">
                                                                            <i class="fas fa-gem"
                                                                                style="font-size: 7px; margin-right: 4px; opacity: 0.6;"></i>
                                                                            <?php echo htmlspecialchars($gem['name']); ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td style="color: #888; font-size: 13px;">
                                                            <?php echo date('Y M d', strtotime($item['Maketime'])); ?> <span style="font-size: 11px; color: #555;"><?php echo date('H:i', strtotime($item['Maketime'])); ?></span>
                                                        </td>
                                                        <td>
                                                            <form action="admin_actions.php" method="POST"
                                                                onsubmit="return confirm('PERMANENTLY delete this item from warehouse?');">
                                                                <input type="hidden" name="action" value="warehouse_item_delete">
                                                                <input type="hidden" name="user_uid" value="<?php echo $userUID; ?>">
                                                                <input type="hidden" name="item_uid"
                                                                    value="<?php echo $item['ItemUID']; ?>">
                                                                <input type="hidden" name="return_view" value="UserEdit">
                                                                <input type="hidden" name="tab" value="Warehouse">
                                                                <button type="submit" class="inventory-icon-btn btn-delete"
                                                                    title="Delete Item Permamently">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>

                                    <?php if ($totalPages > 1): ?>
                                        <div class="pagination"
                                            style="display: flex; justify-content: center; gap: 10px; margin-top: 30px; align-items: center;">
                                            <?php if ($p > 1): ?>
                                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Warehouse&p=<?php echo $p - 1; ?>"
                                                    class="btn-search" style="text-decoration: none; padding: 8px 15px;">&laquo; Prev</a>
                                            <?php endif; ?>
                                            <?php
                                            $startPage = max(1, $p - 2);
                                            $endPage = min($totalPages, $p + 2);
                                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Warehouse&p=<?php echo $i; ?>"
                                                    class="btn-search"
                                                    style="text-decoration: none; padding: 8px 15px; <?php echo ($i === $p) ? 'background: #e8c881; color: #000;' : ''; ?>"><?php echo $i; ?></a>
                                            <?php endfor; ?>
                                            <?php if ($p < $totalPages): ?>
                                                <a href="admin.php?view=UserEdit&uid=<?php echo $userUID; ?>&tab=Warehouse&p=<?php echo $p + 1; ?>"
                                                    class="btn-search" style="text-decoration: none; padding: 8px 15px;">Next &raquo;</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php elseif ($view === 'CHARS'): ?>
                        <?php
                        // --- Get Filter Parameters ---
                        $fOnlyOnline = isset($_GET['only_online']) && $_GET['only_online'] == '1';
                        $fOnlyActive = isset($_GET['only_active']) && $_GET['only_active'] == '1';

                        $fFaction = isset($_GET['f_faction']) ? $_GET['f_faction'] : '';
                        $fCharID = isset($_GET['f_charid']) ? trim($_GET['f_charid']) : '';
                        $fUserID = isset($_GET['f_userid']) ? trim($_GET['f_userid']) : '';
                        $fCharName = isset($_GET['f_charname']) ? trim($_GET['f_charname']) : '';
                        $fLevel = isset($_GET['f_level']) ? trim($_GET['f_level']) : '';
                        $fClass = isset($_GET['f_class']) ? $_GET['f_class'] : '';
                        $fKills = isset($_GET['f_kills']) ? trim($_GET['f_kills']) : '';
                        $fStatus = isset($_GET['f_status']) ? $_GET['f_status'] : '';
                        $fOnline = isset($_GET['f_online']) ? $_GET['f_online'] : '';
                        $fDateType = isset($_GET['f_date_type']) ? $_GET['f_date_type'] : '';
                        $fDateVal = isset($_GET['f_date_val']) ? $_GET['f_date_val'] : '';
                        $fDateVal2 = isset($_GET['f_date_val2']) ? $_GET['f_date_val2'] : '';

                        // --- Build Query ---
                        $whereClauses = [];
                        if ($fOnlyOnline)
                            $whereClauses[] = "C.LoginStatus = 1";
                        if ($fOnlyActive)
                            $whereClauses[] = "C.Del = 0";

                        if ($fFaction !== '')
                            $whereClauses[] = "MG.Country = " . (int) $fFaction;
                        if ($fCharID !== '')
                            $whereClauses[] = "C.CharID = " . (int) $fCharID;
                        if ($fUserID !== '')
                            $whereClauses[] = "C.UserID LIKE '%" . $fUserID . "%'";
                        if ($fCharName !== '')
                            $whereClauses[] = "C.CharName LIKE '%" . $fCharName . "%'";
                        if ($fLevel !== '')
                            $whereClauses[] = "C.Level = " . (int) $fLevel;
                        if ($fClass !== '')
                            $whereClauses[] = "C.Job = " . (int) $fClass;
                        if ($fKills !== '')
                            $whereClauses[] = "C.K1 >= " . (int) $fKills;
                        if ($fStatus !== '')
                            $whereClauses[] = "C.Del = " . ($fStatus === 'A' ? 0 : 1);
                        if ($fOnline !== '')
                            $whereClauses[] = "C.LoginStatus = " . (int) $fOnline;

                        function formatToSqlDate($d)
                        {
                            $d = trim($d);
                            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $d, $m)) {
                                return "{$m[3]}-{$m[2]}-{$m[1]}";
                            }
                            return $d;
                        }

                        if ($fDateType !== '' && $fDateVal !== '') {
                            $val1 = formatToSqlDate($fDateVal);
                            if ($fDateType === 'on') {
                                $whereClauses[] = "CAST(C.RegDate AS DATE) = '$val1'";
                            } elseif ($fDateType === 'after') {
                                $whereClauses[] = "CAST(C.RegDate AS DATE) >= '$val1'";
                            } elseif ($fDateType === 'before') {
                                $whereClauses[] = "CAST(C.RegDate AS DATE) <= '$val1'";
                            } elseif ($fDateType === 'range' && $fDateVal2 !== '') {
                                $val2 = formatToSqlDate($fDateVal2);
                                $whereClauses[] = "CAST(C.RegDate AS DATE) BETWEEN '$val1' AND '$val2'";
                            }
                        }

                        $whereSql = "";
                        if (count($whereClauses) > 0) {
                            $whereSql = " WHERE " . implode(" AND ", $whereClauses);
                        }

                        $charQuery = odbc_exec($conn, "SELECT TOP 100 
                        C.CharID, C.UserID, C.CharName, C.Level, C.K1 as Kills, C.Job,
                        C.RegDate, C.LeaveDate, C.LoginStatus, C.Del,
                        MG.Country as Faction
                    FROM PS_GameData.dbo.Chars C
                    LEFT JOIN PS_GameData.dbo.UserMaxGrow MG ON MG.UserUID = C.UserUID
                    $whereSql
                    ORDER BY C.CharID DESC");
                        ?>

                        

                        <div class="chars-page-header">
                            <h2 class="chars-title">CHARACTERS</h2>
                            <div class="chars-top-filters">
                                <form method="GET" style="display: flex; gap: 20px; align-items: center;" id="topFilters">
                                    <input type="hidden" name="view" value="CHARS">
                                    <!-- Maintain Header Filters -->
                                    <input type="hidden" name="f_faction"
                                        value="<?php echo htmlspecialchars($fFaction); ?>">
                                    <input type="hidden" name="f_charid" value="<?php echo htmlspecialchars($fCharID); ?>">
                                    <input type="hidden" name="f_userid" value="<?php echo htmlspecialchars($fUserID); ?>">
                                    <input type="hidden" name="f_charname"
                                        value="<?php echo htmlspecialchars($fCharName); ?>">
                                    <input type="hidden" name="f_level" value="<?php echo htmlspecialchars($fLevel); ?>">
                                    <input type="hidden" name="f_class" value="<?php echo htmlspecialchars($fClass); ?>">
                                    <input type="hidden" name="f_kills" value="<?php echo htmlspecialchars($fKills); ?>">
                                    <input type="hidden" name="f_status" value="<?php echo htmlspecialchars($fStatus); ?>">
                                    <input type="hidden" name="f_online" value="<?php echo htmlspecialchars($fOnline); ?>">
                                    <input type="hidden" name="f_date_type"
                                        value="<?php echo htmlspecialchars($fDateType); ?>">
                                    <input type="hidden" name="f_date_val"
                                        value="<?php echo htmlspecialchars($fDateVal); ?>">
                                    <input type="hidden" name="f_date_val2"
                                        value="<?php echo htmlspecialchars($fDateVal2); ?>">

                                    <label class="filter-checkbox-label">
                                        <input type="checkbox" name="only_online" value="1" <?php echo $fOnlyOnline ? 'checked' : ''; ?> onchange="this.form.submit()"> Only online
                                    </label>
                                    <label class="filter-checkbox-label">
                                        <input type="checkbox" name="only_active" value="1" <?php echo $fOnlyActive ? 'checked' : ''; ?> onchange="this.form.submit()"> Only active
                                    </label>
                                </form>
                                <button onclick="window.location.href='admin.php?view=CHARS'"
                                    class="btn-reset-filters">RESET
                                    FILTERS & SORT</button>
                            </div>
                        </div>

                        <div class="chars-table-container">
                            <form method="GET" id="headerFilters">
                                <input type="hidden" name="view" value="CHARS">
                                <!-- Preserve top filters -->
                                <?php if ($fOnlyOnline): ?><input type="hidden" name="only_online" value="1"><?php endif; ?>
                                <?php if ($fOnlyActive): ?><input type="hidden" name="only_active" value="1"><?php endif; ?>
                                <!-- Date filter inputs remain within the table headers -->

                                <table class="chars-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Faction
                                                <select name="f_faction" class="table-filter-select"
                                                    onchange="this.form.submit()">
                                                    <option value="">All</option>
                                                    <option value="0" <?php echo $fFaction === '0' ? 'selected' : ''; ?>>AOL
                                                    </option>
                                                    <option value="1" <?php echo $fFaction === '1' ? 'selected' : ''; ?>>UOF
                                                    </option>
                                                </select>
                                            </th>
                                            <th style="width: 100px;">CharID
                                                <input type="text" name="f_charid" class="table-filter-input"
                                                    placeholder="ID" value="<?php echo htmlspecialchars($fCharID); ?>"
                                                    onchange="this.form.submit()">
                                            </th>
                                            <th style="width: 140px;">UserID
                                                <input type="text" name="f_userid" class="table-filter-input"
                                                    placeholder="User" value="<?php echo htmlspecialchars($fUserID); ?>"
                                                    onchange="this.form.submit()">
                                            </th>
                                            <th style="width: 170px;">Char Name
                                                <input type="text" name="f_charname" class="table-filter-input"
                                                    placeholder="Name" value="<?php echo htmlspecialchars($fCharName); ?>"
                                                    onchange="this.form.submit()">
                                            </th>
                                            <th style="width: 80px;">Level
                                                <input type="text" name="f_level" class="table-filter-input"
                                                    placeholder="Level" value="<?php echo htmlspecialchars($fLevel); ?>"
                                                    onchange="this.form.submit()">
                                            </th>
                                            <th style="width: 130px;">Class
                                                <select name="f_class" class="table-filter-select"
                                                    onchange="this.form.submit()">
                                                    <option value="">All</option>
                                                    <?php for ($i = 0; $i <= 5; $i++): ?>
                                                        <option value="<?php echo $i; ?>" <?php echo ($fClass !== '' && (int) $fClass === $i) ? 'selected' : ''; ?>><?php echo getJobName($i); ?>
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </th>
                                            <th style="width: 110px;">Kills
                                                <input type="text" name="f_kills" class="table-filter-input"
                                                    placeholder="Kills" value="<?php echo htmlspecialchars($fKills); ?>"
                                                    onchange="this.form.submit()">
                                            </th>
                                            <th style="width: 40px;"></th>
                                            <th style="width: 200px; vertical-align: top; text-align: left;">
                                                <div style="font-size: 11px; margin-bottom: 8px; text-align: center;">
                                                    Created Date
                                                </div>
                                                <select name="f_date_type" class="table-filter-select"
                                                    style="width: 100%; margin-bottom: 5px;"
                                                    onchange="toggleDateInputs(this.value)">
                                                    <option value="">All dates</option>
                                                    <option value="on" <?php echo $fDateType === 'on' ? 'selected' : ''; ?>>On
                                                        date
                                                    </option>
                                                    <option value="after" <?php echo $fDateType === 'after' ? 'selected' : ''; ?>>
                                                        After
                                                        date</option>
                                                    <option value="before" <?php echo $fDateType === 'before' ? 'selected' : ''; ?>>
                                                        Before
                                                        date</option>
                                                    <option value="range" <?php echo $fDateType === 'range' ? 'selected' : ''; ?>>Date
                                                        range</option>
                                                </select>

                                                <div id="date_input_container"
                                                    style="<?php echo $fDateType === '' ? 'display:none;' : ''; ?>">
                                                    <div class="date-input-wrapper">
                                                        <input type="text" name="f_date_val" class="table-filter-input"
                                                            placeholder="DD.MM.YYYY"
                                                            value="<?php echo htmlspecialchars($fDateVal); ?>"
                                                            style="width: 100%; margin-bottom: 5px;"
                                                            oninput="maskDate(this)" onchange="this.form.submit()">
                                                        <i class="far fa-calendar-alt"></i>
                                                    </div>

                                                    <div id="date_range_second"
                                                        style="<?php echo $fDateType === 'range' ? '' : 'display:none;'; ?>">
                                                        <div
                                                            style="font-size: 9px; color: #444; text-align: center; margin-bottom: 2px;">
                                                            TO</div>
                                                        <div class="date-input-wrapper">
                                                            <input type="text" name="f_date_val2" class="table-filter-input"
                                                                placeholder="DD.MM.YYYY"
                                                                value="<?php echo htmlspecialchars($fDateVal2); ?>"
                                                                style="width: 100%;" oninput="maskDate(this)"
                                                                onchange="this.form.submit()">
                                                            <i class="far fa-calendar-alt"></i>
                                                        </div>
                                                    </div>
                                                </div>

                                                <script>
                                                    function maskDate(input) {
                                                        let v = input.value.replace(/\D/g, ''); // Remove all non-digits
                                                        let formatted = "";
                                                        if (v.length > 0) {
                                                            formatted += v.substring(0, 2);
                                                            if (v.length > 2) {
                                                                formatted += "." + v.substring(2, 4);
                                                                if (v.length > 4) {
                                                                    formatted += "." + v.substring(4, 8);
                                                                }
                                                            }
                                                        }
                                                        input.value = formatted;
                                                    }

                                                    function toggleDateInputs(type) {
                                                        const container = document.getElementById('date_input_container');
                                                        const rangeSecond = document.getElementById('date_range_second');

                                                        if (type === '') {
                                                            container.style.display = 'none';
                                                            document.getElementById('headerFilters').submit();
                                                        } else {
                                                            container.style.display = 'block';
                                                            if (type === 'range') {
                                                                rangeSecond.style.display = 'block';
                                                            } else {
                                                                rangeSecond.style.display = 'none';
                                                            }
                                                        }
                                                    }
                                                </script>
                                            </th>
                                            <th style="width: 120px;">Login</th>
                                            <th style="width: 95px;">Status
                                                <select name="f_status" class="table-filter-select"
                                                    onchange="this.form.submit()">
                                                    <option value="">All</option>
                                                    <option value="A" <?php echo $fStatus === 'A' ? 'selected' : ''; ?>>Active
                                                    </option>
                                                    <option value="X" <?php echo $fStatus === 'X' ? 'selected' : ''; ?>>
                                                        Deleted
                                                    </option>
                                                </select>
                                            </th>
                                            <th style="width: 95px;">Online
                                                <select name="f_online" class="table-filter-select"
                                                    onchange="this.form.submit()">
                                                    <option value="">All</option>
                                                    <option value="1" <?php echo $fOnline === '1' ? 'selected' : ''; ?>>On
                                                    </option>
                                                    <option value="0" <?php echo $fOnline === '0' ? 'selected' : ''; ?>>Off
                                                    </option>
                                                </select>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($c = odbc_fetch_array($charQuery)): ?>
                                            <tr>
                                                <td>
                                                    <img src="assets/<?php echo ($c['Faction'] == 1 ? 'uof.webp' : 'aol.webp'); ?>"
                                                        style="width: 20px;">
                                                </td>
                                                <td style="color: #666;"><?php echo $c['CharID']; ?></td>
                                                <td><a href="admin.php?view=USERS&search_user=<?php echo urlencode($c['UserID']); ?>"
                                                        class="user-link"><?php echo htmlspecialchars($c['UserID']); ?></a></td>
                                                <td><a href="admin.php?view=CharEdit&id=<?php echo $c['CharID']; ?>"
                                                        class="user-link"
                                                        style="color: #fff;"><?php echo htmlspecialchars($c['CharName']); ?></a>
                                                </td>
                                                <td><?php echo $c['Level']; ?></td>
                                                <td>
                                                    <img src="assets/class/<?php echo $c['Job']; ?>.webp"
                                                        style="width: 22px; display: block; margin: 0 auto;">
                                                </td>
                                                <td style="color: #fff; font-weight: 500;">
                                                    <?php echo number_format($c['Kills']); ?>
                                                </td>
                                                <td><?php echo getAdminRankIcon((int) $c['Kills']); ?></td>
                                                <td style="color: #888; font-size: 13px;">
                                                    <?php echo date('d.m.Y, H:i', strtotime($c['RegDate'])); ?>
                                                </td>
                                                <td style="color: #888; font-size: 13px;">
                                                    <?php echo formatRelativeTime($c['LeaveDate']); ?>
                                                </td>
                                                <td>
                                                    <span
                                                        class="status-badge <?php echo ($c['Del'] == 0 ? 'status-active' : 'status-deleted'); ?>">
                                                        <?php echo ($c['Del'] == 0 ? 'Active' : 'Deleted'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="online-badge <?php echo ($c['LoginStatus'] == 1 ? 'online-on' : 'online-off'); ?>">
                                                        <?php echo ($c['LoginStatus'] == 1 ? 'On' : 'Off'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    <?php elseif ($view === 'CHAREDIT'): ?>
                        <?php
                        $charID = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                        $charData = null;
                        if ($charID > 0) {
                            $qData = odbc_exec($conn, "SELECT * FROM PS_GameData.dbo.Chars WHERE CharID = $charID");
                            $charData = odbc_fetch_array($qData);
                        }

                        if (!$charData):
                            ?>
                            <div class="alert alert-error"
                                style="background: rgba(229, 62, 62, 0.1); color: #e53e3e; padding: 20px; border: 1px solid rgba(229, 62, 62, 0.2);">
                                Character not found.</div>
                        <?php else: ?>
                            <div class="char-edit-header">
                                <div class="char-title-section">
                                    <h1><?php echo htmlspecialchars($charData['CharName']); ?> |
                                        <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'Inventory') ? 'INVENTORY' : 'OVERVIEW'; ?>
                                    </h1>
                                    <p>Character Management</p>
                                </div>
                                <div class="char-actions-bar">
                                    <form action="admin_actions.php" method="POST" style="display: inline; margin: 0;"
                                        onsubmit="return confirm('Are you sure you want to kick UserUID <?php echo (int) $charData['UserUID']; ?>?');">
                                        <input type="hidden" name="action" value="char_kick">
                                        <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                        <input type="hidden" name="return_view" value="CharEdit">
                                        <button type="submit" class="admin-btn btn-kick">KICK</button>
                                    </form>

                                    <?php if ((int) $charData['Del'] === 0): ?>
                                        <form method="POST" style="display: inline; margin: 0;"
                                            onsubmit="return confirm('Are you sure you want to delete this character?');">
                                            <input type="hidden" name="admin_action" value="delete_char">
                                            <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                            <button type="submit" class="admin-btn btn-delete-char">DELETE CHAR</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline; margin: 0;"
                                            onsubmit="return confirm('Resurrect this character? System will automatically assign an available slot (0-4).');">
                                            <input type="hidden" name="admin_action" value="resurrect_char">
                                            <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                            <button type="submit" class="admin-btn btn-resurrect">RESURRECT CHAR</button>
                                        </form>
                                    <?php endif; ?>

                                    <div class="char-status-badge" style="<?php
                                    if ((int) $charData['Del'] === 1) {
                                        echo 'background: rgba(229, 62, 62, 0.2); color: #e53e3e; border-color: rgba(229, 62, 62, 0.3);';
                                    } elseif ((int) $charData['LoginStatus'] === 1) {
                                        echo '';
                                    } else {
                                        echo 'background: rgba(100, 116, 139, 0.2); color: #94a3b8; border-color: rgba(100, 116, 139, 0.3);';
                                    }
                                    ?>">
                                        <?php
                                        if ((int) $charData['Del'] === 1)
                                            echo 'DELETED';
                                        elseif ((int) $charData['LoginStatus'] === 1)
                                            echo 'ONLINE';
                                        else
                                            echo 'OFFLINE';
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="char-tabs">
                                <a href="admin.php?view=CharEdit&id=<?php echo $charID; ?>"
                                    class="char-tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'Overview') ? 'active' : ''; ?>"
                                    style="text-decoration: none;">Overview</a>
                                <a href="admin.php?view=CharEdit&id=<?php echo $charID; ?>&tab=Inventory"
                                    class="char-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'Inventory') ? 'active' : ''; ?>"
                                    style="text-decoration: none;">Inventory</a>
                                <a href="admin.php?view=CharEdit&id=<?php echo $charID; ?>&tab=DroppedItems"
                                    class="char-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'DroppedItems') ? 'active' : ''; ?>"
                                    style="text-decoration: none;">Dropped Items</a>
                            </div>

                            <?php if (isset($_GET['msg'])): ?>
                                <?php if ($_GET['msg'] === 'renamed'): ?>
                                    <div class="alert alert-success"
                                        style="background: rgba(72, 187, 120, 0.1); color: #48bb78; padding: 15px; border: 1px solid rgba(72, 187, 120, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-check-circle"></i> Character name has been successfully updated!
                                    </div>
                                <?php elseif ($_GET['msg'] === 'level_updated'): ?>
                                    <div class="alert alert-success"
                                        style="background: rgba(72, 187, 120, 0.4); color: #fff; padding: 15px; border: 1px solid rgba(72, 187, 120, 0.6); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-arrow-up"></i> Character level has been successfully updated!
                                    </div>
                                <?php elseif ($_GET['msg'] === 'map_changed'): ?>
                                    <div class="alert alert-success"
                                        style="background: rgba(72, 187, 120, 0.1); color: #48bb78; padding: 15px; border: 1px solid rgba(72, 187, 120, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-map-marker-alt"></i> Character has been teleported to the new map!
                                    </div>
                                <?php elseif ($_GET['msg'] === 'char_deleted'): ?>
                                    <div class="alert alert-success"
                                        style="background: rgba(229, 62, 62, 0.1); color: #e53e3e; padding: 15px; border: 1px solid rgba(229, 62, 62, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-trash-alt"></i> Character has been marked as deleted!
                                    </div>
                                <?php elseif ($_GET['msg'] === 'char_resurrected'): ?>
                                    <div class="alert alert-success"
                                        style="background: rgba(72, 187, 120, 0.1); color: #48bb78; padding: 15px; border: 1px solid rgba(72, 187, 120, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-heart"></i> Character has been successfully resurrected!
                                    </div>
                                <?php elseif ($_GET['msg'] === 'resurrect_no_slot'): ?>
                                    <div class="alert alert-error"
                                        style="background: rgba(229, 62, 62, 0.1); color: #e53e3e; padding: 15px; border: 1px solid rgba(229, 62, 62, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-exclamation-triangle"></i> Resurrection failed! This user already has 5 active
                                        characters (Slot 0-4 full).
                                    </div>
                                <?php elseif ($_GET['msg'] === 'gold_sent'): ?>
                                    <div class="alert alert-success"
                                        style="background: rgba(72, 187, 120, 0.1); color: #48bb78; padding: 15px; border: 1px solid rgba(72, 187, 120, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-coins"></i> Gold balance has been updated successfully!
                                    </div>
                                <?php elseif ($_GET['msg'] === 'gold_limit'): ?>
                                    <div class="alert alert-error"
                                        style="background: rgba(229, 62, 62, 0.1); color: #e53e3e; padding: 15px; border: 1px solid rgba(229, 62, 62, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-exclamation-triangle"></i> Maximum gold per transfer is 500,000,000!
                                    </div>
                                <?php elseif ($_GET['msg'] === 'kicked'): ?>
                                    <div class="alert alert-success"
                                        style="background: rgba(72, 187, 120, 0.1); color: #48bb78; padding: 15px; border: 1px solid rgba(72, 187, 120, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-bolt"></i> Kick command sent successfully for UserUID
                                        <?php echo isset($_GET['uid']) ? (int) $_GET['uid'] : ''; ?>!
                                    </div>
                                <?php elseif ($_GET['msg'] === 'kick_failed'): ?>
                                    <div class="alert alert-error"
                                        style="background: rgba(229, 62, 62, 0.1); color: #e53e3e; padding: 15px; border: 1px solid rgba(229, 62, 62, 0.2); margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                                        <i class="fas fa-exclamation-triangle"></i> Failed to send kick command. Could not find UserUID
                                        or
                                        database
                                        error.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'Overview'): ?>
                                <div class="char-edit-section">
                                    <h3>CHANGE NICKNAME</h3>
                                    <form class="char-edit-form" method="POST">
                                        <input type="hidden" name="admin_action" value="rename">
                                        <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                        <div class="char-input-group">
                                            <input type="text" name="new_name" class="char-input"
                                                value="<?php echo htmlspecialchars($charData['CharName']); ?>">
                                        </div>
                                        <button type="submit" class="btn-char-save">RENAME</button>
                                    </form>
                                </div>

                                <div class="char-edit-section">
                                    <h3>CHANGE CLASS (SOON)</h3>
                                    <form class="char-edit-form">
                                        <div class="char-input-group">
                                            <select class="char-input">
                                                <option value="0" <?php echo ($charData['Job'] == 0) ? 'selected' : ''; ?>>0 - Fighter
                                                    &
                                                    Warrior
                                                </option>
                                                <option value="1" <?php echo ($charData['Job'] == 1) ? 'selected' : ''; ?>>1 -
                                                    Defender &
                                                    Guardian
                                                </option>
                                                <option value="2" <?php echo ($charData['Job'] == 2) ? 'selected' : ''; ?>>2 - Ranger
                                                    &
                                                    Assassin
                                                </option>
                                                <option value="3" <?php echo ($charData['Job'] == 3) ? 'selected' : ''; ?>>3 - Archer
                                                    &
                                                    Hunter
                                                </option>
                                                <option value="4" <?php echo ($charData['Job'] == 4) ? 'selected' : ''; ?>>
                                                    <?php echo "4 - Mage & Pagan"; ?>
                                                </option>
                                                <option value="5" <?php echo ($charData['Job'] == 5) ? 'selected' : ''; ?>>5 - Priest
                                                    &
                                                    Oracle
                                                </option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn-char-save">CHANGE</button>
                                    </form>
                                </div>

                                <div class="char-edit-section">
                                    <h3>CHANGE MAP</h3>
                                    <form class="char-edit-form" method="POST">
                                        <input type="hidden" name="admin_action" value="change_map">
                                        <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                        <div class="char-input-group">
                                            <input type="number" name="new_map" class="char-input" min="0" max="109"
                                                value="<?php echo (int) $charData['Map']; ?>">
                                        </div>
                                        <button type="submit" class="btn-char-save" style="background: #4a5568;">TELEPORT</button>
                                    </form>
                                </div>

                                <div class="char-edit-section">
                                    <h3>UPDATE LEVEL</h3>
                                    <form class="char-edit-form" method="POST">
                                        <input type="hidden" name="admin_action" value="update_level">
                                        <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                        <div class="char-input-group">
                                            <input type="number" name="new_level" class="char-input" min="1" max="80"
                                                value="<?php echo (int) $charData['Level']; ?>">
                                        </div>
                                        <button type="submit" class="btn-char-save" style="background: #2d3748;">UPDATE LEVEL</button>
                                    </form>
                                </div>

                                <div class="char-edit-section">
                                    <h3>SEND GOLD</h3>
                                    <form class="char-edit-form" method="POST">
                                        <input type="hidden" name="admin_action" value="send_gold">
                                        <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                        <div class="char-input-group">
                                            <input type="number" name="gold_amount" class="char-input"
                                                placeholder="Current: <?php echo number_format($charData['Money']); ?>" min="0"
                                                max="500000000" required>
                                        </div>
                                        <button type="submit" class="btn-char-save" style="background: #dd6b20;">SEND GOLD</button>
                                    </form>
                                </div>

                                <table class="char-info-table" style="margin-top: 50px;">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="header-main">Main</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Username</td>
                                            <td style="color: #6a8ec1;"><?php echo htmlspecialchars($charData['UserID']); ?>
                                                (<?php echo $charData['UserUID']; ?>)</td>
                                        </tr>
                                        <tr>
                                            <td>Name</td>
                                            <td><?php echo htmlspecialchars($charData['CharName']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Level</td>
                                            <td><?php echo $charData['Level']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Kills</td>
                                            <td>
                                                <?php echo number_format($charData['K1']); ?>
                                                <?php echo getAdminRankIcon((int) $charData['K1']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Deaths</td>
                                            <td><?php echo isset($charData['K2']) ? number_format($charData['K2']) : '0'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Wins</td>
                                            <td><?php echo isset($charData['K3']) ? number_format($charData['K3']) : '0'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Defeats</td>
                                            <td><?php echo isset($charData['K4']) ? number_format($charData['K4']) : '0'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Money</td>
                                            <td style="color: #da9f50;"><?php echo number_format($charData['Money']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Create date</td>
                                            <td><?php echo date('l, F d, Y g:i A', strtotime($charData['RegDate'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Last enter date</td>
                                            <td style="color: #da9f50;">
                                                <?php echo formatRelativeTime($charData['LastEnterDate'] ?? $charData['RegDate'], 3); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Last leave date</td>
                                            <td><?php echo date('l, F d, Y g:i A', strtotime($charData['LeaveDate'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Deleted</td>
                                            <td><?php echo ((int) $charData['Del'] === 0) ? 'False' : 'True'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php elseif ($_GET['tab'] === 'Inventory'): ?>
                                <div class="inventory-table-container">
                                    <?php
                                    // --- Inventory & Editing Logic ---
                                    $p = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
                                    $itemsPerPage = 15;
                                    $offset = ($p - 1) * $itemsPerPage;

                                    // Total Count Query
                                    $countSql = "SELECT COUNT(*) as Total FROM PS_GameData.dbo.CharItems ci WHERE ci.CharID = $charID";
                                    $countRes = odbc_exec($conn, $countSql);
                                    $totalItems = 0;
                                    if ($countRes) {
                                        $countData = odbc_fetch_array($countRes);
                                        $totalItems = (int) ($countData['Total'] ?? 0);
                                    }
                                    $totalPages = ceil($totalItems / $itemsPerPage);

                                    // Fetch items for the current page
                                    $sql = "SELECT ci.Type, ci.TypeID, i.ItemName, ci.Bag, ci.Slot, ci.ItemUID, ci.Craftname, 
                                               ci.Gem1, ci.Gem2, ci.Gem3, ci.Gem4, ci.Gem5, ci.Gem6, ci.Quality, ci.Count, ci.Del,
                                               i.ReqLevel, i.ConstStr, i.ConstDex, i.ConstRec, i.ConstInt, i.ConstWis, i.ConstLuc,
                                               i.ConstHP, i.ConstSP, i.ConstMP, i.Defensefighter, i.Defensemage
                                        FROM PS_GameData.dbo.CharItems ci
                                        LEFT JOIN PS_GameDefs.dbo.Items i ON ci.Type=i.Type AND ci.TypeID = i.TypeID
                                        WHERE ci.CharID = $charID
                                        ORDER BY ci.Del ASC, ci.Bag ASC, ci.Slot ASC
                                        OFFSET $offset ROWS FETCH NEXT $itemsPerPage ROWS ONLY";
                                    $res = odbc_exec($conn, $sql);

                                    $items_list = [];
                                    $gem_ids = [];
                                    if ($res) {
                                        while ($item = odbc_fetch_array($res)) {
                                            $items_list[] = $item;
                                            for ($g = 1; $g <= 6; $g++) {
                                                $gval = $item["Gem$g"];
                                                if ($gval > 0)
                                                    $gem_ids[] = 30000 + $gval;
                                            }
                                        }
                                    }

                                    // Handle Item Editing View
                                    $editItemUID = isset($_GET['edit_item']) ? (int) $_GET['edit_item'] : 0;
                                    $editItem = null;
                                    $lapises = [];

                                    if ($editItemUID > 0) {
                                        $editSql = "SELECT ci.*, i.ItemName, i.Slot FROM PS_GameData.dbo.CharItems ci 
                                                LEFT JOIN PS_GameDefs.dbo.Items i ON ci.Type=i.Type AND ci.TypeID = i.TypeID 
                                                WHERE ci.ItemUID = $editItemUID AND ci.CharID = $charID";
                                        $editRes = odbc_exec($conn, $editSql);
                                        if ($editRes) {
                                            $editItem = odbc_fetch_array($editRes);
                                        }
                                        $lapisSql = "SELECT TypeID, ItemName FROM PS_GameDefs.dbo.Items WHERE [Type] = 30 ORDER BY ItemName ASC";
                                        $lapisRes = odbc_exec($conn, $lapisSql);
                                        if ($lapisRes) {
                                            while ($lapis = odbc_fetch_array($lapisRes)) {
                                                $lapises[] = $lapis;
                                            }
                                        }
                                    }

                                    $gem_names = [];
                                    if (!empty($gem_ids)) {
                                        $id_list = implode(",", array_unique($gem_ids));
                                        $gem_res = odbc_exec($conn, "SELECT ItemID, ItemName FROM PS_GameDefs.dbo.Items WHERE ItemID IN ($id_list)");
                                        while ($gem = odbc_fetch_array($gem_res)) {
                                            $gem_names[$gem['ItemID']] = $gem['ItemName'];
                                        }
                                    }

                                    // Render Item Edit Form (above table)
                                    if ($editItem):
                                        $c = str_pad($editItem['Craftname'] ?? '0', 20, '0', STR_PAD_LEFT);
                                        $eStats = [
                                            'str' => (int) substr($c, 0, 2),
                                            'dex' => (int) substr($c, 2, 2),
                                            'rec' => (int) substr($c, 4, 2),
                                            'int' => (int) substr($c, 6, 2),
                                            'wis' => (int) substr($c, 8, 2),
                                            'luc' => (int) substr($c, 10, 2),
                                            'hp' => (int) substr($c, 12, 2),
                                            'sp' => (int) substr($c, 14, 2),
                                            'mp' => (int) substr($c, 16, 2),
                                            'ench' => (int) substr($c, 18, 2)
                                        ];

                                        // Restrictions Logic
                                        $rawCN = trim($editItem['Craftname'] ?? '');
                                        $isCraftnameEmpty = ($rawCN === '');
                                        $maxSlots = (int) ($editItem['Slot'] ?? 0);
                                        ?>
                                        <div class="item-editor-card">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                <div class="item-editor-title">EDIT ITEM ATTRIBUTES</div>
                                                <a href="admin.php?view=CharEdit&id=<?php echo $charID; ?>&tab=Inventory&p=<?php echo $p; ?>"
                                                    class="btn-action btn-sm" style="width: auto; min-width: 40px;"><i
                                                        class="fas fa-times" style="margin:0;"></i></a>
                                            </div>
                                            <form method="POST">
                                                <input type="hidden" name="admin_action" value="update_item"><input type="hidden"
                                                    name="char_id" value="<?php echo $charID; ?>"><input type="hidden" name="item_uid"
                                                    value="<?php echo $editItem['ItemUID']; ?>">

                                                <div class="editor-grid" style="grid-template-columns: 1fr;">
                                                    <div class="editor-field">
                                                        <label class="editor-label">Item Information</label>
                                                        <div
                                                            style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.05); color: #fff; font-weight: 600;">
                                                            UID: <?php echo $editItem['ItemUID']; ?> &nbsp; | &nbsp;
                                                            <?php echo htmlspecialchars($editItem['ItemName']); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="editor-label" style="margin-bottom: 12px;">Gem Slots</div>
                                                <div class="editor-grid">
                                                    <?php
                                                    for ($g = 1; $g <= 6; $g++):
                                                        $curG = (int) $editItem["Gem$g"];
                                                        $isSlotDisabled = ($g > $maxSlots);
                                                        ?>
                                                        <div class="editor-field"
                                                            style="<?php echo $isSlotDisabled ? 'opacity: 0.3; pointer-events: none;' : ''; ?>">
                                                            <select name="gem<?php echo $g; ?>" class="editor-select" <?php echo $isSlotDisabled ? 'disabled' : ''; ?>>
                                                                <option value="0">Empty Slot</option>
                                                                <?php foreach ($lapises as $lp): ?>
                                                                    <option value="<?php echo $lp['TypeID']; ?>" <?php echo ($curG == $lp['TypeID']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($lp['ItemName']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>


                                                <div class="editor-label" style="margin-bottom: 12px;">Craftname Stats (Bonus)
                                                    <?php if ($isCraftnameEmpty): ?><span
                                                            style="color: #ff4d4d; font-size: 9px;">(Cannot be
                                                            rerolled - Item has no Craftname)</span><?php endif; ?>
                                                </div>
                                                <div class="stat-input-group"
                                                    style="<?php echo $isCraftnameEmpty ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">
                                                    <?php $lbls = ['STR' => 'str', 'DEX' => 'dex', 'REC' => 'rec', 'INT' => 'int', 'WIS' => 'wis', 'LUC' => 'luc', 'HP' => 'hp', 'SP' => 'sp', 'MP' => 'mp', 'ENCHANT' => 'ench'];
                                                    foreach ($lbls as $l => $k): ?>
                                                        <div class="editor-field">
                                                            <label class="editor-label" style="font-size: 9px;"><?php echo $l; ?></label>
                                                            <input type="number" name="stat_<?php echo $k; ?>"
                                                                value="<?php echo $eStats[$k]; ?>" class="editor-input" min="0"
                                                                max="<?php echo ($k === 'ench') ? '20' : '50'; ?>" <?php echo $isCraftnameEmpty ? 'disabled' : ''; ?>>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div style="margin-top: 30px; display: flex; justify-content: flex-end;"><button
                                                        type="submit" class="btn-action">UPDATE ITEM DATA</button></div>
                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <table class="char-info-table styled-inventory" style="table-layout: fixed; width: 100%;">
                                        <thead
                                            style="background: rgba(0,0,0,0.2); border-bottom: 2px solid rgba(218, 159, 80, 0.1);">
                                            <tr>
                                                <th
                                                    style="text-align: left; padding: 15px 20px; width: 40%; color: #da9f50; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1.5px;">
                                                    ITEM</th>
                                                <th
                                                    style="width: 15%; text-align: center; color: #da9f50; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1.5px; padding: 15px 0;">
                                                    BAG / SLOT</th>
                                                <th
                                                    style="width: 10%; text-align: center; color: #da9f50; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1.5px; padding: 15px 0;">
                                                    QUANTITY</th>
                                                <th
                                                    style="width: 20%; text-align: center; color: #da9f50; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1.5px; padding: 15px 0;">
                                                    GEMS</th>
                                                <th
                                                    style="width: 15%; text-align: center; color: #da9f50; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1.5px; padding: 15px 0;">
                                                    ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items_list as $item):
                                                // ... rest of the row rendering ...
                                
                                                $gems = [];
                                                for ($g = 1; $g <= 6; $g++) {
                                                    $gval = $item["Gem$g"];
                                                    if ($gval > 0) {
                                                        $gid = 30000 + $gval;
                                                        $gems[] = [
                                                            'id' => $gid,
                                                            'name' => isset($gem_names[$gid]) ? $gem_names[$gid] : "Unknown Gem [$gid]"
                                                        ];
                                                    }
                                                }

                                                // Parse Craftname (20 digits)
                                                $craft = $item['Craftname'] ?? '00000000000000000000';
                                                if (strlen($craft) < 20)
                                                    $craft = str_pad($craft, 20, '0', STR_PAD_LEFT);

                                                $bonusStats = [
                                                    'STR' => (int) substr($craft, 0, 2),
                                                    'DEX' => (int) substr($craft, 2, 2),
                                                    'REC' => (int) substr($craft, 4, 2),
                                                    'INT' => (int) substr($craft, 6, 2),
                                                    'WIS' => (int) substr($craft, 8, 2),
                                                    'LUC' => (int) substr($craft, 10, 2),
                                                    'HP' => (int) substr($craft, 12, 2) * 100,
                                                    'SP' => (int) substr($craft, 14, 2) * 100,
                                                    'MP' => (int) substr($craft, 16, 2) * 100,
                                                    'Enchant' => (int) substr($craft, 18, 2)
                                                ];

                                                $baseStats = [
                                                    'STR' => (int) ($item['ConstStr'] ?? 0),
                                                    'DEX' => (int) ($item['ConstDex'] ?? 0),
                                                    'REC' => (int) ($item['ConstRec'] ?? 0),
                                                    'INT' => (int) ($item['ConstInt'] ?? 0),
                                                    'WIS' => (int) ($item['ConstWis'] ?? 0),
                                                    'LUC' => (int) ($item['ConstLuc'] ?? 0),
                                                    'HP' => (int) ($item['ConstHP'] ?? 0),
                                                    'SP' => (int) ($item['ConstSP'] ?? 0),
                                                    'MP' => (int) ($item['ConstMP'] ?? 0)
                                                ];
                                                ?>
                                                <tr class="<?php echo ((int) $item['Del'] === 1) ? 'item-row-deleted' : ''; ?>">
                                                    <td style="text-align: left; padding-left: 20px; overflow: visible;">
                                                        <div class="item-name-wrapper">
                                                            <div style="color: #eee; font-weight: 500; font-size: 13px;">
                                                                <?php echo !empty($item['ItemName']) ? htmlspecialchars($item['ItemName']) : "Unknown [" . $item['Type'] . "/" . $item['TypeID'] . "]"; ?>
                                                                <?php if ($bonusStats['Enchant'] > 0): ?>
                                                                    <span
                                                                        class="enchant-badge">[<?php echo $bonusStats['Enchant']; ?>]</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div style="color: #555; font-size: 10px; letter-spacing: 0.5px;">TYPE:
                                                                <?php echo $item['Type']; ?> | ID: <?php echo $item['TypeID']; ?>
                                                            </div>

                                                            <!-- Tooltip -->
                                                            <div class="item-stats-tooltip">
                                                                <div class="tooltip-header">
                                                                    <span><?php echo htmlspecialchars($item['ItemName'] ?? 'Unknown Item'); ?></span>
                                                                    <?php if ($bonusStats['Enchant'] > 0): ?>
                                                                        <span
                                                                            class="enchant-badge">[<?php echo $bonusStats['Enchant']; ?>]</span>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <?php if (($item['Quality'] ?? 0) > 0): ?>
                                                                    <div class="stat-line" style="color: #eeee74;">damage Absorption +
                                                                        <?php echo $item['Quality']; ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if (($item['Defensefighter'] ?? 0) > 0): ?>
                                                                    <div class="stat-line" style="color: #fff;">Phys. defense
                                                                        <?php echo $item['Defensefighter']; ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if (($item['Defensemage'] ?? 0) > 0): ?>
                                                                    <div class="stat-line" style="color: #fff;">Mag. defense
                                                                        <?php echo $item['Defensemage']; ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php
                                                                $allStats = [
                                                                    'HP' => 'hp',
                                                                    'SP' => 'sp',
                                                                    'MP' => 'mp',
                                                                    'STR' => 'str',
                                                                    'DEX' => 'dex',
                                                                    'REC' => 'rec',
                                                                    'INT' => 'int',
                                                                    'WIS' => 'wis',
                                                                    'LUC' => 'luc'
                                                                ];
                                                                foreach ($allStats as $label => $cssClass):
                                                                    $base = $baseStats[$label];
                                                                    $bonus = $bonusStats[$label];
                                                                    if ($base > 0 || $bonus > 0):
                                                                        ?>
                                                                        <div class="stat-line">
                                                                            <span
                                                                                class="stat-label-<?php echo $cssClass; ?>"><?php echo $label; ?></span>
                                                                            <span class="stat-v-base">+<?php echo $base + $bonus; ?></span>
                                                                            <?php if ($bonus > 0): ?>
                                                                                <span class="stat-v-bonus">+<?php echo $bonus; ?></span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <?php
                                                                    endif;
                                                                endforeach;
                                                                ?>

                                                                <?php if (isset($item['ReqLevel']) && $item['ReqLevel'] > 0): ?>
                                                                    <div class="req-level" style="color:#eee;">Lv.
                                                                        <?php echo $item['ReqLevel']; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td style="text-align: center; font-family: 'Futura PT', sans-serif; color: #888;">
                                                        <?php echo ($item['Bag'] == 0) ? '<span style="color: #81b3b3;">Equipped Item</span>' : 'B' . $item['Bag'] . ' S' . $item['Slot']; ?>
                                                    </td>
                                                    <td style="text-align: center; color: #da9f50; font-weight: 600;">
                                                        <?php echo $item['Count']; ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <?php if (empty($gems)): ?>
                                                            <span style="color: #333;">-</span>
                                                        <?php else: ?>
                                                            <div
                                                                style="display: flex; flex-direction: column; gap: 4px; align-items: center; padding: 5px 0;">
                                                                <?php foreach ($gems as $gem): ?>
                                                                    <div class="gem-badge" title="ID: <?php echo $gem['id']; ?>"
                                                                        style="font-size: 9px; color: #e8c881; background: rgba(232, 200, 129, 0.05); padding: 2px 8px; border-radius: 10px; border: 1px solid rgba(232, 200, 129, 0.1); width: fit-content;">
                                                                        <i class="fas fa-gem"
                                                                            style="font-size: 7px; margin-right: 4px; opacity: 0.6;"></i>
                                                                        <?php echo htmlspecialchars($gem['name']); ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <?php if ((int) $item['Del'] === 0): ?>
                                                            <div style="display: flex; justify-content: center; gap: 10px;">
                                                                <form method="POST" style="display: inline;"
                                                                    onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                                    <input type="hidden" name="admin_action" value="delete_item">
                                                                    <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                                                    <input type="hidden" name="item_uid"
                                                                        value="<?php echo $item['ItemUID']; ?>">
                                                                    <button type="submit" class="inventory-icon-btn btn-delete"
                                                                        title="Delete Item">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </form>

                                                                <a href="admin.php?view=CharEdit&id=<?php echo $charID; ?>&tab=Inventory&p=<?php echo $p; ?>&edit_item=<?php echo $item['ItemUID']; ?>"
                                                                    class="inventory-icon-btn btn-edit" title="Edit Item Attributes">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            </div>
                                                        <?php else: ?>
                                                            <span
                                                                style="color: #666; font-size: 10px; font-weight: 700; letter-spacing: 1px;">DELETED</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <?php if ($totalPages > 1): ?>
                                        <div class="pagination-wrapper">
                                            <?php if ($p > 1): ?>
                                                <a href="admin.php?view=CharEdit&id=<?php echo $charID; ?>&tab=Inventory&p=<?php echo $p - 1; ?>"
                                                    class="pagination-link" title="Previous Page">
                                                    <i class="fas fa-chevron-left" style="font-size: 10px; margin: 0;"></i>
                                                </a>
                                            <?php endif; ?>

                                            <span class="pagination-info">
                                                Page <?php echo $p; ?> of <?php echo $totalPages; ?>
                                            </span>

                                            <?php if ($p < $totalPages): ?>
                                                <a href="admin.php?view=CharEdit&id=<?php echo $charID; ?>&tab=Inventory&p=<?php echo $p + 1; ?>"
                                                    class="pagination-link" title="Next Page">
                                                    <i class="fas fa-chevron-right" style="font-size: 10px; margin: 0;"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'DroppedItems'): ?>
                                <div class="char-edit-section">
                                    <h3>RESTORE DROPPED ITEMS</h3>
                                    <p
                                        style="color: #666; font-size: 11px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;">
                                        Showing items dropped (Action 112) that were not recovered.
                                    </p>

                                    <div class="chars-table-container">
                                        <form method="GET" action="admin.php" id="droppedItemsFilter">
                                            <input type="hidden" name="view" value="CharEdit">
                                            <input type="hidden" name="id" value="<?php echo $charID; ?>">
                                            <input type="hidden" name="tab" value="DroppedItems">
                                        <table class="chars-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 110px;">Date</th>
                                                    <th style="width: 140px;">Item Name
                                                        <input type="text" name="search_drop" class="table-filter-input" placeholder="Name" value="<?php echo isset($_GET['search_drop']) ? htmlspecialchars($_GET['search_drop']) : ''; ?>" onchange="this.form.submit()">
                                                    </th>
                                                    <th style="text-align: left;">Details (Gems/Craft)</th>
                                                    <th style="width: 80px; text-align: center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $searchDrop = isset($_GET['search_drop']) ? trim($_GET['search_drop']) : '';
                                                $searchDropFilter = '';
                                                if ($searchDrop !== '') {
                                                    $searchClean = str_replace("'", "''", $searchDrop); // Basic anti-injection
                                                    $searchDropFilter = " AND ALL_LOG.Text1 LIKE '%$searchClean%' ";
                                                }

                                                // Optimize by using standard SQL NOT EXISTS instead of N+1 PHP queries. Limit to 50 to ensure high speed.
                                                $droppedQuery = odbc_exec($conn, "SELECT TOP 50 ALL_LOG.* FROM PS_GameLog.dbo.ActionLog ALL_LOG 
                                                                    WHERE CharID = $charID AND ActionType = 112 
                                                                    AND Text2 NOT IN ('use_item', 'etin_return')
                                                                    $searchDropFilter
                                                                    AND NOT EXISTS (
                                                                        SELECT 1 FROM PS_GameLog.dbo.ActionLog picked
                                                                        WHERE picked.Value1 = ALL_LOG.Value1 AND picked.ActionType = 111 
                                                                        AND picked.ActionTime BETWEEN ALL_LOG.ActionTime AND DATEADD(MINUTE, 10, ALL_LOG.ActionTime)
                                                                    )
                                                                    AND NOT EXISTS (SELECT 1 FROM PS_GameData.dbo.CharItems WHERE ItemUID = ALL_LOG.Value1)
                                                                    AND NOT EXISTS (SELECT 1 FROM PS_GameData.dbo.UserStoredItems WHERE ItemUID = ALL_LOG.Value1)
                                                                    AND NOT EXISTS (SELECT 1 FROM PS_GameData.dbo.MarketItems WHERE ItemUID = ALL_LOG.Value1)
                                                                    ORDER BY ActionTime DESC");

                                                $foundItems = false;
                                                while ($item = odbc_fetch_array($droppedQuery)):
                                                    $itemUID = $item['Value1'];
                                                    $actionTime = $item['ActionTime'];

                                                    $foundItems = true;
                                                    ?>
                                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                                        <td
                                                            style="color: #444; font-size: 11px; padding: 12px 5px; white-space: nowrap;">
                                                            <?php echo date('d M, H:i', strtotime($actionTime)); ?>
                                                        </td>
                                                        <td
                                                            style="padding: 12px 5px; color: #e8c881; font-weight: 500; font-size: 12px;">
                                                            <div style="width: 130px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                                title="<?php echo htmlspecialchars($item['Text1']); ?>">
                                                                <?php echo htmlspecialchars($item['Text1']); ?>
                                                            </div>
                                                            <div style="font-size: 9px; color: #444; margin-top: 4px;">UID:
                                                                <?php echo $itemUID; ?>
                                                            </div>
                                                        </td>
                                                        <td style="text-align: left; font-size: 11px; color: #666; padding: 12px 5px;">
                                                            <div style="width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                                title="<?php echo htmlspecialchars($item['Text2']); ?>">
                                                                <?php echo htmlspecialchars($item['Text2']); ?>
                                                            </div>
                                                        </td>
                                                        <td style="padding: 12px 0; text-align: center;">
                                                            <?php if ((int) $charData['LoginStatus'] === 1): ?>
                                                                <span style="color: #e53e3e; font-size: 9px; font-weight: 700;">PLAYER
                                                                    ONLINE</span>
                                                            <?php else: ?>
                                                                <form action="admin_actions.php" method="POST"
                                                                    onsubmit="return confirm('Restore this item to Warehouse?');">
                                                                    <input type="hidden" name="action" value="restore_dropped_item">
                                                                    <input type="hidden" name="char_id" value="<?php echo $charID; ?>">
                                                                    <input type="hidden" name="log_row" value="<?php echo $item['row']; ?>">
                                                                    <input type="hidden" name="return_view" value="CharEdit">
                                                                    <input type="hidden" name="tab" value="DroppedItems">
                                                                    <button type="submit"
                                                                        style="padding: 4px 8px; font-size: 9px; cursor: pointer; transition: all 0.3s; border-radius: 4px; color: #48bb78; background: rgba(72, 187, 120, 0.1); border: 1px solid rgba(72, 187, 120, 0.2);">RESTORE</button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>

                                                <?php if (!$foundItems): ?>
                                                    <tr>
                                                        <td colspan="4"
                                                            style="padding: 40px; color: #444; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                                                            No restorable dropped items found for this character.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php elseif ($view === 'ACTIONLOG'): ?>
                        <?php
                        $userIdSearch = isset($_GET['username']) ? trim($_GET['username']) : "";
                        $charNameSearch = isset($_GET['charname']) ? trim($_GET['charname']) : "";
                        $actionTypesSearch = isset($_GET['action-type']) ? $_GET['action-type'] : [];
                        $rowsLimit = isset($_GET['rows']) && (int) $_GET['rows'] > 0 ? (int) $_GET['rows'] : 25;
                        $dateStartSearch = isset($_GET['date-start-enabled'], $_GET['date-start']) ? $_GET['date-start'] : "";
                        $dateEndSearch = isset($_GET['date-end-enabled'], $_GET['date-end']) ? $_GET['date-end'] : "";

                        $ActionTypes = [
                            10 => "Own Kill",
                            102 => "Unknown 102",
                            103 => "Kill/Win",
                            104 => "Death/Lose",
                            105 => "Res 105",
                            106 => "Res 106",
                            107 => "Game Login",
                            108 => "Game Logout",
                            111 => "Pick Item",
                            112 => "Lose Item",
                            113 => "Purchase",
                            114 => "Sell to NPC",
                            115 => "Trade In",
                            116 => "Trade Out",
                            118 => "Duel Trade",
                            119 => "Lapis Link",
                            121 => "WH Put",
                            122 => "WH Take",
                            123 => "Duel Lose",
                            124 => "Duel Win",
                            131 => "Take Quest",
                            133 => "Finish Quest",
                            141 => "Learn Skill",
                            146 => "Level Up",
                            151 => "Stat STR",
                            152 => "Stat DEX",
                            153 => "Stat INT",
                            154 => "Stat WIS",
                            155 => "Stat REC",
                            156 => "Stat LUC",
                            163 => "Money Transfer",
                            164 => "Teleport",
                            173 => "Boss Action",
                            180 => "Admin Action",
                            210 => "Party Join",
                            211 => "Party Leave",
                            212 => "Enchant",
                            213 => "Rec (Reroll)",
                            216 => "Auction Create",
                            217 => "Auction Remove",
                            218 => "Auction Buy",
                            219 => "Auction Bet",
                            220 => "Auction Take Item",
                            221 => "Auction Take Money",
                            222 => "Auction Sell",
                            223 => "Auction Finish",
                            224 => "Auction Get Bet",
                            226 => "Auction Bet Cancel"
                        ];

                        $whereClauses = ["1=1"];
                        if ($userIdSearch) {
                            $qUid = odbc_prepare($conn, "SELECT UserUID FROM PS_UserData.dbo.Users_Master WHERE UserID=?");
                            odbc_execute($qUid, [$userIdSearch]);
                            if ($rUid = odbc_fetch_array($qUid)) {
                                $uUid = $rUid['UserUID'];
                                $whereClauses[] = "AL.UserUID=$uUid";
                            } else {
                                $whereClauses[] = "1=0";
                            }
                        }
                        if ($charNameSearch) {
                            $qCid = odbc_prepare($conn, "SELECT CharID FROM PS_GameData.dbo.Chars WHERE CharName=?");
                            odbc_execute($qCid, [$charNameSearch]);
                            if ($rCid = odbc_fetch_array($qCid)) {
                                $cCid = $rCid['CharID'];
                                $whereClauses[] = "AL.CharID=$cCid";
                            } else {
                                $whereClauses[] = "1=0";
                            }
                        }
                        if (!empty($actionTypesSearch)) {
                            $typesList = implode(',', array_map('intval', $actionTypesSearch));
                            $whereClauses[] = "AL.ActionType IN ($typesList)";
                        }
                        if ($dateStartSearch) {
                            $dStart = date("Y-m-d H:i:s", strtotime($dateStartSearch));
                            $whereClauses[] = "AL.ActionTime >= '$dStart'";
                        }
                        if ($dateEndSearch) {
                            $dEnd = date("Y-m-d H:i:s", strtotime($dateEndSearch));
                            $whereClauses[] = "AL.ActionTime <= '$dEnd'";
                        }

                        $whereSql = "WHERE " . implode(" AND ", $whereClauses);
                        $logsQuery = odbc_exec($conn, "SELECT TOP $rowsLimit AL.*, UM.UserID, UM.AdminLevel, C.CharName 
                                            FROM PS_GameLog.dbo.ActionLog AL
                                            LEFT JOIN PS_UserData.dbo.Users_Master UM ON AL.UserUID = UM.UserUID
                                            LEFT JOIN PS_GameData.dbo.Chars C ON AL.CharID = C.CharID
                                            $whereSql ORDER BY AL.[row] DESC");
                        ?>

                        <div class="logs-filter-card"
                            style="background: rgba(255,255,255,0.02); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 40px;">
                            <form action="admin.php" method="GET" id="logsSearchForm"
                                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px;">
                                <input type="hidden" name="view" value="ActionLog">

                                <div class="editor-field">
                                    <label class="editor-label">UserID</label>
                                    <input type="text" name="username"
                                        value="<?php echo htmlspecialchars($userIdSearch); ?>" class="editor-input"
                                        placeholder="Search UserID...">
                                    <div style="font-size: 10px; color: #444; margin-top: 5px; text-align: center;">-- OR --
                                    </div>
                                    <label class="editor-label" style="margin-top: 5px;">Char Name</label>
                                    <input type="text" name="charname"
                                        value="<?php echo htmlspecialchars($charNameSearch); ?>" class="editor-input"
                                        placeholder="Search Char Name...">
                                </div>

                                <div class="editor-field">
                                    <label class="editor-label">Action Types</label>
                                    <select name="action-type[]" multiple class="editor-select"
                                        style="height: 180px; padding: 2px; font-size: 12px; line-height: 1.2;">
                                        <?php foreach ($ActionTypes as $atid => $atname): ?>
                                            <option value="<?php echo $atid; ?>" <?php echo in_array($atid, $actionTypesSearch) ? 'selected' : ''; ?>
                                                style="padding: 4px 8px; border-bottom: 1px solid rgba(255,255,255,0.02);">
                                                [<?php echo $atid; ?>] <?php echo $atname; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div style="font-size: 9px; color: #666; cursor: pointer; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;"
                                        onclick="document.querySelectorAll('#logsSearchForm select option').forEach(o => o.selected = false)">
                                        RESET SELECTION</div>
                                </div>

                                <div class="editor-field">
                                    <label class="editor-label">Date Range</label>
                                    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
                                        <input type="checkbox" name="date-start-enabled" id="ds_en" <?php echo isset($_GET['date-start-enabled']) ? 'checked' : ''; ?>>
                                        <input type="datetime-local" name="date-start" id="dateStart" class="editor-input"
                                            style="flex:1;"
                                            value="<?php echo $dateStartSearch ? date("Y-m-d\TH:i", strtotime($dateStartSearch)) : date("Y-m-d\TH:i"); ?>">
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <input type="checkbox" name="date-end-enabled" id="de_en" <?php echo isset($_GET['date-end-enabled']) ? 'checked' : ''; ?>>
                                        <input type="datetime-local" name="date-end" id="dateEnd" class="editor-input"
                                            style="flex:1;"
                                            value="<?php echo $dateEndSearch ? date("Y-m-d\TH:i", strtotime($dateEndSearch)) : date("Y-m-d\TH:i"); ?>">
                                    </div>
                                </div>

                                <div class="editor-field"
                                    style="display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <label class="editor-label">Rows</label>
                                        <input type="number" name="rows" value="<?php echo $rowsLimit; ?>" min="1"
                                            max="5000" class="editor-input" style="text-align: center;">
                                    </div>
                                    <button type="submit" class="btn-action" style="padding: 10px;">APPLY FILTERS</button>
                                </div>
                            </form>
                        </div>

                        <div class="chars-table-container">
                            <table class="chars-table">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Date</th>
                                        <th style="width: 130px;">User</th>
                                        <th style="width: 130px;">Char Name</th>
                                        <th style="width: 150px;">Action</th>
                                        <th style="text-align: left;">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($log = odbc_fetch_array($logsQuery)):
                                        $actId = $log['ActionType'];
                                        $actName = $ActionTypes[$actId] ?? "Action $actId";
                                        $time = strtotime($log['ActionTime']);

                                        // Detail Parsing
                                        $details = "";
                                        switch ($actId) {
                                            case 107:
                                                $details = "IP: <b>{$log['Text1']}</b> | {$log['Text2']}";
                                                break;
                                            case 111:
                                            case 112:
                                                $details = "<span style='color: #e8c881;'>{$log['Text1']}</span> (x{$log['Value3']}) | ID: {$log['Value2']}";
                                                $craftLabel = getCraftnameLabel($log['Text3']);
                                                if ($craftLabel)
                                                    $details .= " <span style='color: #666; font-size: 11px;'>[$craftLabel]</span>";
                                                break;
                                            case 113:
                                            case 114:
                                                $details = "<span style='color: #e8c881;'>{$log['Text1']}</span> (x{$log['Value4']}) | Gold: " . number_format(castToUInt($log['Value5']));
                                                break;
                                            case 163:
                                                $details = "To: {$log['Text1']} | Amount: " . number_format(castToUInt($log['Value1']));
                                                break;
                                            case 164:
                                                $details = "Map: " . getMapName($log['Value3']) . " | Target: {$log['Text1']}";
                                                break;
                                            default:
                                                $tStr = trim("{$log['Text1']} {$log['Text2']} {$log['Text3']} {$log['Text4']}");
                                                $vStr = trim("V1:{$log['Value1']} V2:{$log['Value2']}");
                                                $details = $tStr ? $tStr : $vStr;
                                        }
                                        ?>
                                        <tr>
                                            <td style="color: #666; font-size: 11px;">
                                                <?php echo date('d M, H:i', $time); ?>
                                            </td>
                                            <td>
                                                <a href="admin.php?view=USERS&f_userid=<?php echo urlencode($log['UserID'] ?? ''); ?>"
                                                    class="user-link"><?php echo htmlspecialchars($log['UserID'] ?? '-'); ?></a>
                                            </td>
                                            <td>
                                                <?php if (!empty($log['CharName'])): ?>
                                                    <a href="admin.php?view=CharEdit&id=<?php echo $log['CharID']; ?>"
                                                        class="user-link"
                                                        style="color: #fff;"><?php echo htmlspecialchars($log['CharName']); ?></a>
                                                <?php else: ?>
                                                    <span style="color: #444; font-size: 11px;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span
                                                    style="color: #6a8ec1; font-weight: 500; font-size: 11px; text-transform: uppercase;"
                                                    title="<?php echo "ID: $actId"; ?>"><?php echo $actName; ?></span>
                                            </td>
                                            <td
                                                style="text-align: left; font-size: 12px; color: #888; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo $details; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($view === 'ITEMS'): ?>
                        

                        <?php
                        $selectedItemID = isset($_GET['item_id']) ? (int) $_GET['item_id'] : 0;
                        $searchIn = isset($_GET['search_in']) ? $_GET['search_in'] : 'inventory';
                        $itemSearchTerm = isset($_GET['search_item_name']) ? trim($_GET['search_item_name']) : '';

                        // 1. Fetch search-on-demand results (Top 30 only)
                        $matchingItems = [];
                        if ($itemSearchTerm) {
                            $qItems = odbc_prepare(
                                $conn,
                                "SELECT TOP 30 ItemID, ItemName 
                     FROM PS_GameDefs.dbo.Items 
                     WHERE ItemName LIKE ? 
                     ORDER BY ItemName ASC"
                            );

                            if ($qItems) {
                                $searchParam = '%' . $itemSearchTerm . '%';
                                odbc_execute($qItems, [$searchParam]);

                                while ($ri = odbc_fetch_array($qItems)) {
                                    $matchingItems[] = $ri;
                                }
                            }
                        }

                        // 2. Fetch Owner Results (Inventory Search)
                        $results = [];
                        if ($selectedItemID > 0) {
                            $type = floor($selectedItemID / 1000);
                            $typeID = $selectedItemID % 1000;

                            if ($searchIn === 'inventory') {
                                $sql = "SELECT ci.ItemUID, ci.Type, ci.TypeID, i.ItemID, i.ItemName, 
                                   ci.Craftname, ci.Quality, ci.Gem1, ci.Gem2, ci.Gem3, ci.Gem4, ci.Gem5, ci.Gem6,
                                   ci.Maketime,
                                   U.UserID as AccountName, C.CharName, C.CharID,
                                   i.ReqLevel, i.ConstStr, i.ConstDex, i.ConstRec, i.ConstInt, i.ConstWis, i.ConstLuc,
                                   i.ConstHP, i.ConstSP, i.ConstMP, i.Defensefighter, i.Defensemage,
                                   1 as [Count] -- Inventory items are usually individual rows
                            FROM PS_GameData.dbo.CharItems ci
                            JOIN PS_GameDefs.dbo.Items i ON ci.Type = i.Type AND ci.TypeID = i.TypeID
                            JOIN PS_GameData.dbo.Chars C ON ci.CharID = C.CharID
                            JOIN PS_UserData.dbo.Users_Master U ON C.UserUID = U.UserUID
                            WHERE i.ItemID = ?
                            ORDER BY ci.ItemUID DESC";
                                $qRes = odbc_prepare($conn, $sql);
                                odbc_execute($qRes, [$selectedItemID]);
                                if ($qRes) {
                                    while ($rr = odbc_fetch_array($qRes)) {
                                        $results[] = $rr;
                                    }
                                }
                            } elseif ($searchIn === 'warehouse') {
                                $sql = "SELECT ci.ItemUID, ci.Type, ci.TypeID, i.ItemID, i.ItemName, 
                                   ci.Craftname, 0 as Quality, ci.Gem1, ci.Gem2, ci.Gem3, ci.Gem4, ci.Gem5, ci.Gem6,
                                   ci.Maketime,
                                   U.UserID as AccountName, C.CharName, C.CharID,
                                   i.ReqLevel, i.ConstStr, i.ConstDex, i.ConstRec, i.ConstInt, i.ConstWis, i.ConstLuc,
                                   i.ConstHP, i.ConstSP, i.ConstMP, i.Defensefighter, i.Defensemage,
                                   ci.[Count]
                            FROM PS_GameData.dbo.UserStoredItems ci
                            JOIN PS_GameDefs.dbo.Items i ON ci.Type = i.Type AND ci.TypeID = i.TypeID
                            JOIN PS_UserData.dbo.Users_Master U ON ci.UserUID = U.UserUID
                            LEFT JOIN PS_GameData.dbo.Chars C ON C.UserUID = U.UserUID AND C.Slot = 0 AND C.Del = 0
                            WHERE i.ItemID = ?
                            ORDER BY ci.ItemUID DESC";
                                $qRes = odbc_prepare($conn, $sql);
                                odbc_execute($qRes, [$selectedItemID]);
                                if ($qRes) {
                                    while ($rr = odbc_fetch_array($qRes)) {
                                        $results[] = $rr;
                                    }
                                }
                            }
                        }
                        ?>

                        <div class="items-search-card">
                            <div class="items-title">ITEMS LIST</div>

                            <!-- Combined Search Container -->
                            <div style="display: grid; grid-template-columns: 400px 1fr; gap: 40px; align-items: start;">

                                <!-- Search Helper Card (Left Side) -->
                                <div
                                    style="background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid rgba(255,255,255,0.03); padding: 25px;">
                                    <label class="search-label"
                                        style="display: flex; justify-content: space-between; align-items: center;">
                                        Find Item
                                    </label>
                                    <form action="admin.php" method="GET"
                                        style="display: flex; gap: 10px; margin-bottom: 20px;">
                                        <input type="hidden" name="view" value="ITEMS">
                                        <input type="text" name="search_item_name" class="char-input"
                                            value="<?php echo htmlspecialchars($itemSearchTerm); ?>"
                                            placeholder="Search ItemName..."
                                            style="background: rgba(255,255,255,0.03); height: 38px;">
                                        <button type="submit" class="btn-action btn-sm" style="height: 38px;"><i
                                                class="fas fa-search"></i></button>
                                    </form>

                                    <!-- Matches Section -->
                                    <div class="blacklist-scroll"
                                        style="max-height: 250px; overflow-y: auto; padding-right: 5px;">
                                        <?php if ($itemSearchTerm): ?>
                                            <?php if (empty($matchingItems)): ?>
                                                <div style="text-align: center; padding: 20px; color: #444; font-size: 11px;">No
                                                    items
                                                    found.
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($matchingItems as $m): ?>
                                                    <div onclick="selectItem('<?php echo $m['ItemID']; ?>', '<?php echo $m['ItemID'] . ' - ' . addslashes(htmlspecialchars($m['ItemName'])); ?>')"
                                                        style="background: rgba(255,255,255,0.02); padding: 10px 15px; border: 1px solid rgba(255,255,255,0.03); border-radius: 4px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; cursor: pointer; transition: all 0.2s;"
                                                        onmouseover="this.style.borderColor='#e8c881'"
                                                        onmouseout="this.style.borderColor='rgba(255,255,255,0.03)'"
                                                        class="item-match-row">
                                                        <span style="font-size: 13px;">
                                                            <span
                                                                style="color: #ccc;"><?php echo htmlspecialchars($m['ItemName']); ?></span>
                                                            <span
                                                                style="color: #444; font-size: 10px; margin-left: 10px;">#<?php echo $m['ItemID']; ?></span>
                                                        </span>
                                                        <i class="fas fa-plus-circle" style="color: #222; font-size: 12px;"></i>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div style="text-align: center; padding: 40px 0; color: #333;">
                                                <i class="fas fa-search"
                                                    style="font-size: 24px; opacity: 0.1; margin-bottom: 10px;"></i>
                                                <p style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">
                                                    Search for
                                                    items
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Selected Item & Action Form (Right Side) -->
                                <div style="padding-top: 5px;">
                                    <form action="admin.php" method="GET" id="itemsSearchForm">
                                        <input type="hidden" name="view" value="ITEMS">

                                        <div class="search-field" style="margin-bottom: 25px;">
                                            <label class="search-label">Selected Item</label>
                                            <div
                                                style="background: rgba(0,0,0,0.3); border: 1px solid #222; padding: 15px; border-radius: 6px; display: flex; align-items: center; gap: 20px;">
                                                <div style="flex: 1;">
                                                    <input type="text" name="item_id" id="selectedItemId"
                                                        value="<?php echo $selectedItemID; ?>" placeholder="ItemID..."
                                                        class="char-input" readonly
                                                        style="background: transparent; border: none; font-size: 16px; color: #e8c881; font-weight: 600; padding: 0; cursor: default;">
                                                    <div id="selectedItemDisplay"
                                                        style="color: #555; font-size: 11px; margin-top: 5px; text-transform: uppercase;">
                                                        <?php
                                                        if ($selectedItemID > 0) {
                                                            $checkItem = odbc_prepare($conn, "SELECT ItemName FROM PS_GameDefs.dbo.Items WHERE ItemID = ?");
                                                            odbc_execute($checkItem, [$selectedItemID]);
                                                            if ($checkItem && $r_name = odbc_fetch_array($checkItem)) {
                                                                echo htmlspecialchars($r_name['ItemName']);
                                                            } else {
                                                                echo "UNKNOWN ITEM ID";
                                                            }
                                                        } else {
                                                            echo "No Item Selected";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div style="height: 30px; width: 1px; background: rgba(255,255,255,0.05);">
                                                </div>
                                                <div style="flex: 1;">
                                                    <label class="search-label"
                                                        style="margin-bottom: 5px; font-size: 10px;">Search
                                                        in</label>
                                                    <select name="search_in" class="editor-select"
                                                        style="width: 100%; min-width: 150px;">
                                                        <option value="inventory" <?php echo $searchIn === 'inventory' ? 'selected' : ''; ?>>Inventory</option>
                                                        <option value="warehouse" <?php echo $searchIn === 'warehouse' ? 'selected' : ''; ?>>Warehouse</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: flex; justify-content: flex-end;">
                                            <button type="submit" class="btn-action"
                                                style="padding: 12px 50px;">ACCEPT</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <script>
                            function selectItem(id, text) {
                                document.getElementById('selectedItemId').value = id;
                                // Extract just the name for display
                                const parts = text.split(' - ');
                                const name = parts.length > 1 ? parts.slice(1).join(' - ') : text;
                                document.getElementById('selectedItemDisplay').innerText = name;

                                // Add a small animation to show selection
                                const card = document.getElementById('selectedItemId').parentElement.parentElement;
                                card.style.borderColor = '#e8c881';
                                setTimeout(() => {
                                    card.style.borderColor = '#222';
                                }, 500);
                            }
                        </script>

                        <?php if ($selectedItemID > 0): ?>
                            <div class="results-header">ITEMS</div>
                            <div class="users-table-container">
                                <table class="items-results-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 250px;">ItemUID</th>
                                            <th style="width: 100px;">ItemID</th>
                                            <th>Item Name</th>
                                            <th style="width: 250px;">Owner</th>
                                            <th style="width: 150px;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($results)): ?>
                                            <tr>
                                                <td colspan="5" style="text-align: center; padding: 50px; color: #444;">No items
                                                    found
                                                    in the selected <?php echo $searchIn; ?>.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($results as $res): ?>
                                                <?php
                                                // Tooltip Logic
                                                $craft = $res['Craftname'] ?? '00000000000000000000';
                                                if (strlen($craft) < 20)
                                                    $craft = str_pad($craft, 20, '0', STR_PAD_LEFT);

                                                $bonusStats = [
                                                    'STR' => (int) substr($craft, 0, 2),
                                                    'DEX' => (int) substr($craft, 2, 2),
                                                    'REC' => (int) substr($craft, 4, 2),
                                                    'INT' => (int) substr($craft, 6, 2),
                                                    'WIS' => (int) substr($craft, 8, 2),
                                                    'LUC' => (int) substr($craft, 10, 2),
                                                    'HP' => (int) substr($craft, 12, 2) * 100,
                                                    'SP' => (int) substr($craft, 14, 2) * 100,
                                                    'MP' => (int) substr($craft, 16, 2) * 100,
                                                    'Enchant' => (int) substr($craft, 18, 2)
                                                ];

                                                $baseStats = [
                                                    'STR' => (int) ($res['ConstStr'] ?? 0),
                                                    'DEX' => (int) ($res['ConstDex'] ?? 0),
                                                    'REC' => (int) ($res['ConstRec'] ?? 0),
                                                    'INT' => (int) ($res['ConstInt'] ?? 0),
                                                    'WIS' => (int) ($res['ConstWis'] ?? 0),
                                                    'LUC' => (int) ($res['ConstLuc'] ?? 0),
                                                    'HP' => (int) ($res['ConstHP'] ?? 0),
                                                    'SP' => (int) ($res['ConstSP'] ?? 0),
                                                    'MP' => (int) ($res['ConstMP'] ?? 0)
                                                ];
                                                ?>
                                                <tr>
                                                    <td class="uid-cell"><?php echo $res['ItemUID']; ?></td>
                                                    <td class="item-id-cell"><?php echo number_format($res['ItemID']); ?></td>
                                                    <td class="item-name-cell">
                                                        <div class="item-name-wrapper">
                                                            <?php echo htmlspecialchars($res['ItemName']); ?>
                                                            <?php if ($bonusStats['Enchant'] > 0): ?>
                                                                <span class="enchant-badge">[<?php echo $bonusStats['Enchant']; ?>]</span>
                                                            <?php endif; ?>

                                                            <!-- Tooltip -->
                                                            <div class="item-stats-tooltip">
                                                                <div class="tooltip-header">
                                                                    <span><?php echo htmlspecialchars($res['ItemName'] ?? 'Unknown Item'); ?></span>
                                                                    <?php if ($bonusStats['Enchant'] > 0): ?>
                                                                        <span
                                                                            class="enchant-badge">[<?php echo $bonusStats['Enchant']; ?>]</span>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <?php if (($res['Quality'] ?? 0) > 0): ?>
                                                                    <div class="stat-line" style="color: #eeee74;">damage Absorption +
                                                                        <?php echo $res['Quality']; ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if (($res['Defensefighter'] ?? 0) > 0): ?>
                                                                    <div class="stat-line" style="color: #fff;">Phys. defense
                                                                        <?php echo $res['Defensefighter']; ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if (($res['Defensemage'] ?? 0) > 0): ?>
                                                                    <div class="stat-line" style="color: #fff;">Mag. defense
                                                                        <?php echo $res['Defensemage']; ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php
                                                                $allStats = [
                                                                    'HP' => 'hp',
                                                                    'SP' => 'sp',
                                                                    'MP' => 'mp',
                                                                    'STR' => 'str',
                                                                    'DEX' => 'dex',
                                                                    'REC' => 'rec',
                                                                    'INT' => 'int',
                                                                    'WIS' => 'wis',
                                                                    'LUC' => 'luc'
                                                                ];
                                                                foreach ($allStats as $label => $cssClass):
                                                                    $base = $baseStats[$label];
                                                                    $bonus = $bonusStats[$label];
                                                                    if ($base > 0 || $bonus > 0):
                                                                        ?>
                                                                        <div class="stat-line">
                                                                            <span
                                                                                class="stat-label-<?php echo $cssClass; ?>"><?php echo $label; ?></span>
                                                                            <span class="stat-v-base">+<?php echo $base + $bonus; ?></span>
                                                                            <?php if ($bonus > 0): ?>
                                                                                <span class="stat-v-bonus">+<?php echo $bonus; ?></span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <?php
                                                                    endif;
                                                                endforeach;
                                                                ?>

                                                                <?php if (isset($res['ReqLevel']) && $res['ReqLevel'] > 0): ?>
                                                                    <div class="req-level" style="color:#eee;">Lv.
                                                                        <?php echo $res['ReqLevel']; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="owner-info">
                                                        <div>
                                                            <span class="label">Account:</span>
                                                            <a href="admin.php?view=UserEdit&uid=<?php echo $res['AccountName']; ?>"
                                                                class="value"><?php echo htmlspecialchars($res['AccountName']); ?></a>
                                                        </div>
                                                        <div style="margin-top: 4px;">
                                                            <span class="label">Main char:</span>
                                                            <?php if (!empty($res['CharName'])): ?>
                                                                <a href="admin.php?view=CharEdit&id=<?php echo $res['CharID']; ?>"
                                                                    class="value"
                                                                    style="color: #fff;"><?php echo htmlspecialchars($res['CharName']); ?></a>
                                                            <?php else: ?>
                                                                <span class="value" style="color: #444; font-style: italic;">No
                                                                    characters</span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if (($res['Count'] ?? 1) > 1): ?>
                                                            <div style="margin-top: 4px;">
                                                                <span class="label">Count:</span>
                                                                <span class="value"
                                                                    style="color: #e8c881; font-weight: bold;"><?php echo number_format($res['Count']); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="date-cell">
                                                        <?php
                                                        // Handle Date (fallback to unknown if Maketime doesn't exist)
                                                        echo isset($res['Maketime']) ? date('d M H:i', strtotime($res['Maketime'])) : "-";
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div
                            style="background: #0a0a0a; border: 1px dashed #333; padding: 60px; border-radius: 8px; text-align: center;">
                            <i class="fas fa-layer-group" style="font-size: 30px; color: #333; margin-bottom: 15px;"></i>
                            <h3 style="color: #666; font-weight: 300; margin: 0;">MODULE: <?php echo $view; ?></h3>
                            <p style="color: #444; font-size: 10px; margin-top: 10px; text-transform: uppercase;">This
                                administrative module is active but currently contains no data.</p>
                        </div>
                    <?php endif; ?>
            </section>
        </div>
    </div>


    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <?php include 'modules/footer.php'; ?>
</body></html>