<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

require_once 'db.php';
require_once 'db_pdo.php';

// Check Lucky Chest Setting
$luckyChestEnabled = '1';
$qLucky = odbc_exec($conn, "IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) SELECT SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'LuckyChestEnabled'");
if ($qLucky && ($row = odbc_fetch_array($qLucky))) {
    $luckyChestEnabled = $row['SettingValue'];
}

if ($luckyChestEnabled != '1') {
    echo json_encode(['success' => false, 'message' => 'Lucky chest is currently disabled for maintenance.']);
    exit;
}

// Prepare necessary table just in case it is missing (runs silently)
$createTableSQL = "
IF NOT EXISTS (SELECT * FROM PS_UserData.dbo.sysobjects WHERE name='Web_LuckyCase' AND xtype='U')
BEGIN
    CREATE TABLE PS_UserData.dbo.Web_LuckyCase (
        UserUID int PRIMARY KEY,
        LastRollTime datetime NOT NULL
    )
END
ELSE
BEGIN
    -- Check if LastClaim exists and rename it to LastRollTime
    IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('PS_UserData.dbo.Web_LuckyCase') AND name = 'LastClaim')
    BEGIN
        EXEC sp_rename 'PS_UserData.dbo.Web_LuckyCase.LastClaim', 'LastRollTime', 'COLUMN';
    END
END
";
@odbc_exec($conn, $createTableSQL);

// Get UserUID
$username = $_SESSION['user'];
$userQuery = odbc_prepare($conn, "SELECT UserUID FROM PS_UserData.dbo.Users_Master WHERE UserID = ?");
odbc_execute($userQuery, [$username]);
$userRow = odbc_fetch_array($userQuery);

if (!$userRow) {
    echo json_encode(['success' => false, 'message' => 'User account not found in database.']);
    exit;
}
$userUID = (int) $userRow['UserUID'];

// Check Cooldown
$timeQuery = odbc_exec($conn, "SELECT LastRollTime, DATEDIFF(second, LastRollTime, GETDATE()) AS SecondsPassed FROM PS_UserData.dbo.Web_LuckyCase WHERE UserUID = $userUID");
$timeRow = odbc_fetch_array($timeQuery);

if ($timeRow) {
    $secondsPassed = (int) $timeRow['SecondsPassed'];
    $cooldownSeconds = 6 * 3600; // 6 hours

    if ($secondsPassed < $cooldownSeconds) {
        $timeLeft = $cooldownSeconds - $secondsPassed;
        $hours = floor($timeLeft / 3600);
        $minutes = floor(($timeLeft % 3600) / 60);
        echo json_encode(['success' => false, 'message' => "You must wait $hours hours and $minutes minutes before opening another chest."]);
        exit;
    }
}

// Roll Rewards
$rewards = [
    ['id' => 100251, 'name' => '10 AP', 'icon' => 'gem', 'color' => '#bdc3c7', 'probability' => 20],        // Common
    ['id' => 100252, 'name' => '100 AP', 'icon' => 'gem', 'color' => '#3498db', 'probability' => 15],       // Rare
    ['id' => 100253, 'name' => '1000 AP', 'icon' => 'gem', 'color' => '#9b59b6', 'probability' => 10],       // Epic
    ['id' => 100254, 'name' => '10.000 AP', 'icon' => 'gem', 'color' => '#ff3f34', 'probability' => 5],     // Legendary
    ['id' => 0, 'name' => 'Nothing', 'icon' => 'times-circle', 'color' => '#7f8c8d', 'probability' => 2]    // Junk
];

// Determine Winning Item based on weighted probability
$totalProb = array_sum(array_column($rewards, 'probability'));
$rand = mt_rand(1, $totalProb);
$sum = 0;
$winningItem = null;

foreach ($rewards as $reward) {
    $sum += $reward['probability'];
    if ($rand <= $sum) {
        $winningItem = $reward;
        break;
    }
}

// Failsafe
if (!$winningItem)
    $winningItem = $rewards[0];

// Give item to Giftbox ONLY if they won something
if ($winningItem['id'] != 0) {
    // Shaiya max giftbox slots is 240 (0 to 239)
    $slotQuery = odbc_exec($conn, "SELECT Slot FROM PS_GameData.dbo.UserStoredPointItems WHERE UserUID = $userUID ORDER BY Slot ASC");
    $slot = 0;
    while ($sRow = odbc_fetch_array($slotQuery)) {
        if ($slot != (int) $sRow['Slot'])
            break;
        $slot++;
    }

    if ($slot >= 240) {
        echo json_encode(['success' => false, 'message' => 'Your Giftbox is full! Please clear it out first.']);
        exit;
    }

    // Insert into Bank
    $insertGift = odbc_exec($conn, "INSERT INTO PS_GameData.dbo.UserStoredPointItems (UserUID, Slot, ItemID, ItemCount, BuyDate) VALUES ($userUID, $slot, " . $winningItem['id'] . ", 1, GETDATE())");

    if (!$insertGift) {
        echo json_encode(['success' => false, 'message' => 'Database error while delivering item.']);
        exit;
    }
}

// Update Tracker
if ($timeRow) {
    odbc_exec($conn, "UPDATE PS_UserData.dbo.Web_LuckyCase SET LastRollTime = GETDATE() WHERE UserUID = $userUID");
} else {
    odbc_exec($conn, "INSERT INTO PS_UserData.dbo.Web_LuckyCase (UserUID, LastRollTime) VALUES ($userUID, GETDATE())");
}

echo json_encode([
    'success' => true,
    'item' => $winningItem
]);
exit;
