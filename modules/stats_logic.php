<?php
require_once __DIR__ . '/../db.php';

// --- Server Stats Logic ---
$online_count = 0;
$aol_pct = 50;
$uof_pct = 50;

// Get Online Count based on LoginStatus
$online_query = "SELECT COUNT(*) AS online_count FROM PS_GameData.dbo.Chars WHERE Del = 0 AND LoginStatus = 1";
$online_stmt = odbc_exec($conn, $online_query);
if ($online_stmt && odbc_fetch_row($online_stmt)) {
    $online_count = odbc_result($online_stmt, "online_count");
}

// Get Faction Ratios
$faction_query = "
    SELECT 
        SUM(CASE WHEN umg.Country = 0 THEN 1 ELSE 0 END) AS aol_count,
        SUM(CASE WHEN umg.Country = 1 THEN 1 ELSE 0 END) AS uof_count
    FROM PS_GameData.dbo.Chars c
    INNER JOIN PS_GameData.dbo.UserMaxGrow umg ON c.UserUID = umg.UserUID
    WHERE c.Del = 0
";
$faction_stmt = odbc_exec($conn, $faction_query);
if ($faction_stmt && odbc_fetch_row($faction_stmt)) {
    $aol_count = (int) odbc_result($faction_stmt, "aol_count");
    $uof_count = (int) odbc_result($faction_stmt, "uof_count");
    $total_factions = $aol_count + $uof_count;

    if ($total_factions > 0) {
        $aol_pct = round(($aol_count / $total_factions) * 100);
        $uof_pct = round(($uof_count / $total_factions) * 100);
    }
}
// --------------------------
?>
