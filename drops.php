<?php
require_once 'db.php';

$active_page = 'drops';

// Dynamic Drop Finder Configurations
$config_minimap_size = 350; // Change this value to adjust the minimap tooltip size (in pixels)
$config_max_grade = 3072;
$config_max_level = 80;
$config_hide_zero_drops = true;
$config_hide_empty_items = true;
$item_blacklist_str = '38147,38149,38151,38150,38148,72037,87037,87017,87057,72017,72057,38170,38170,44032,44039,44033,44034,44035,44036,44037,44038,44040,44041,41164,44138,44141';

$qDrops = @odbc_exec($conn, "IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) SELECT SettingKey, CAST(SettingValue AS VARCHAR(MAX)) as SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey IN ('DropsMaxGrade', 'DropsMaxLevel', 'DropsHideZero', 'DropsHideEmpty', 'DropsBlacklist')");
if ($qDrops) {
    while ($row = @odbc_fetch_array($qDrops)) {
        if ($row['SettingKey'] == 'DropsMaxGrade') $config_max_grade = (int)$row['SettingValue'];
        if ($row['SettingKey'] == 'DropsMaxLevel') $config_max_level = (int)$row['SettingValue'];
        if ($row['SettingKey'] == 'DropsHideZero') $config_hide_zero_drops = ($row['SettingValue'] == '1');
        if ($row['SettingKey'] == 'DropsHideEmpty') $config_hide_empty_items = ($row['SettingValue'] == '1');
        if ($row['SettingKey'] == 'DropsBlacklist') $item_blacklist_str = $row['SettingValue'];
    }
}
$item_blacklist = empty(trim($item_blacklist_str)) ? "('-1')" : "(" . $item_blacklist_str . ")";

$search_results = null;
$mob_search_results = null;
$item_drops = null;
$mob_drops = null;
$selected_item_name = null;
$selected_mob_name = null;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $zero_drop_filter = $config_hide_zero_drops ? " AND mi.DropRate > 0" : "";
    $empty_items_filter = $config_hide_empty_items ? " AND EXISTS (SELECT 1 FROM PS_GameDefs.dbo.MobItems mi WHERE mi.Grade = i.Grade" . $zero_drop_filter . ")" : "";
    
    // Search for items matching the string (limit to 50 results to prevent massive rendering)
    $query = "SELECT TOP 50 ItemID, ItemName, ReqLevel, Slot FROM PS_GameDefs.dbo.Items i WHERE ItemName LIKE ? AND ReqLevel <= " . (int)$config_max_level . " AND ItemID NOT IN $item_blacklist AND ItemName NOT LIKE '?%'" . $empty_items_filter . " ORDER BY ItemName ASC";
    $stmt = odbc_prepare($conn, $query);
    $search_param = "%" . $search_term . "%";
    odbc_execute($stmt, array($search_param));
    
    $search_results = array();
    while ($row = odbc_fetch_array($stmt)) {
        $search_results[] = $row;
    }

    // Search for monsters matching the string
    $mob_query = "SELECT TOP 50 m.MobID, m.MapID, m.MobName, m.Level, mn.MapName FROM PS_GameDefs.dbo.Mobs AS m LEFT JOIN PS_GameDefs.dbo.MapNames AS mn ON m.MapID = mn.MapID WHERE m.MobName LIKE ? AND m.MobID < 1000000 ORDER BY m.MobName ASC";
    $stmt_mob = odbc_prepare($conn, $mob_query);
    odbc_execute($stmt_mob, array($search_param));
    
    $mob_search_results = array();
    while ($row = odbc_fetch_array($stmt_mob)) {
        $mob_search_results[] = $row;
    }
}

if (isset($_GET['mobid']) && !empty($_GET['mobid'])) {
    $mobid = (int)$_GET['mobid'];
    
    // Get Mob Name
    $name_query = odbc_prepare($conn, "SELECT TOP 1 MobName FROM PS_GameDefs.dbo.Mobs WHERE MobID = ?");
    odbc_execute($name_query, array($mobid));
    if ($row = odbc_fetch_array($name_query)) {
        $selected_mob_name = $row['MobName'];
        
        $per_page = 25;
        
        // Count total drops for pagination
        $count_query = "SELECT COUNT(*) as Total FROM PS_GameDefs.dbo.MobItems AS mi JOIN PS_GameDefs.dbo.Items AS i ON i.Grade = mi.Grade WHERE mi.MobID = ? AND i.ItemID NOT IN $item_blacklist AND LEFT(i.ItemName, 1) != '?'";
        $stmt_count = odbc_prepare($conn, $count_query);
        odbc_execute($stmt_count, array($mobid));
        $count_row = odbc_fetch_array($stmt_count);
        $total_drops = (int)($count_row['Total'] ?? 0);
        $total_pages = ceil($total_drops / $per_page);
        
        $page = 1;
        if (isset($_GET['page']) && is_numeric($_GET['page'])) {
            $page = max(1, min($total_pages, (int)$_GET['page']));
        }
        $offset = ($page - 1) * $per_page;
        
        // Final paginated query
        $drop_query = "SELECT i.ItemID, i.ItemName, i.ReqLevel, i.Slot, mi.DropRate, mi.ItemOrder, mi.Grade 
                      FROM PS_GameDefs.dbo.MobItems AS mi 
                      JOIN PS_GameDefs.dbo.Items AS i ON i.Grade = mi.Grade 
                      WHERE mi.MobID = ? AND i.ItemID NOT IN $item_blacklist AND LEFT(i.ItemName, 1) != '?' 
                      ORDER BY mi.DropRate DESC 
                      OFFSET $offset ROWS FETCH NEXT $per_page ROWS ONLY";
        $stmt = odbc_prepare($conn, $drop_query);
        if ($stmt) {
            odbc_execute($stmt, array($mobid));
            while ($drop_row = odbc_fetch_array($stmt)) {
                $mob_drops[] = $drop_row;
            }
        }
    }
}

if (isset($_GET['itemid']) && !empty($_GET['itemid'])) {
    $itemid = (int)$_GET['itemid'];
    
    // Get Item Name and Grade
    $name_query = odbc_prepare($conn, "SELECT TOP 1 ItemName, Grade FROM PS_GameDefs.dbo.Items WHERE ItemID = ?");
    odbc_execute($name_query, array($itemid));
    if ($row = odbc_fetch_array($name_query)) {
        $selected_item_name = $row['ItemName'];
        $item_grade = $row['Grade'];
        
        $per_page = 25;
        
        if ($item_grade <= $config_max_grade) {
            // Apply zero drop filter if enabled
            $zero_drop_condition = $config_hide_zero_drops ? " AND mi.DropRate > 0" : "";
            
            // Count total drops for pagination
            $count_query = "SELECT COUNT(*) as Total FROM PS_GameDefs.dbo.MobItems AS mi WHERE mi.Grade = ?" . $zero_drop_condition;
            $stmt_count = odbc_prepare($conn, $count_query);
            odbc_execute($stmt_count, array($item_grade));
            $count_row = odbc_fetch_array($stmt_count);
            $total_drops = (int)($count_row['Total'] ?? 0);
            $total_pages = ceil($total_drops / $per_page);
            
            $page = 1;
            if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                $page = max(1, ($total_pages > 0) ? min($total_pages, (int)$_GET['page']) : 1);
            }
            $offset = ($page - 1) * $per_page;
            
            // Final paginated query
            $drop_query = "SELECT m.MobID, m.MapID, m.MobName, m.HP, m.Level, m.Attrib, mi.DropRate, mi.ItemOrder, mn.MapName 
                          FROM PS_GameDefs.dbo.MobItems AS mi 
                          INNER JOIN PS_GameDefs.dbo.Mobs AS m ON m.MobID = mi.MobID 
                          JOIN PS_GameDefs.dbo.MapNames AS mn ON m.MapID = mn.MapID 
                          WHERE mi.Grade = ?" . $zero_drop_condition . " 
                          ORDER BY mi.DropRate DESC 
                          OFFSET $offset ROWS FETCH NEXT $per_page ROWS ONLY";
            $stmt = odbc_prepare($conn, $drop_query);
            odbc_execute($stmt, array($item_grade));
            
            while ($drop_row = odbc_fetch_array($stmt)) {
                $item_drops[] = $drop_row;
            }
        }
    }
}

$elements = array(0 => 'None', 1 => 'Fire', 2 => 'Water', 3 => 'Earth', 4 => 'Wind');
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drops | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-header {
            text-align: center;
            color: var(--text-gold);
            margin-bottom: 40px;
            letter-spacing: 2px;
            font-size: 28px;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0,0,0,0.8);
        }
        .map-hover {
            position: relative;
            cursor: help;
            border-bottom: 1px dotted rgba(200, 160, 90, 0.5);
            display: inline-block;
        }
        .map-hover:hover {
            z-index: 99999;
        }
        .map-hover .map-tooltip {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            bottom: 120%;
            left: 0;
            background: #0a0a0a;
            border: 1px solid var(--border-color);
            padding: 5px;
            border-radius: 4px;
            box-shadow: 0 5px 25px rgba(0,0,0,1);
            z-index: 99999;
            transition: opacity 0.2s;
            pointer-events: none;
            width: max-content;
        }
        .map-hover:hover .map-tooltip {
            visibility: visible;
            opacity: 1;
        }
        .map-hover .map-tooltip img {
            display: block;
            width: <?php echo $config_minimap_size; ?>px;
            height: <?php echo $config_minimap_size; ?>px;
            object-fit: cover;
            border-radius: 2px;
        }
        .search-container {
            text-align: center;
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .search-input {
            width: 100%;
            height: 50px;
            padding: 0 20px;
            box-sizing: border-box;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-color);
            color: var(--text-gold);
            font-size: 16px;
            border-radius: 4px;
            outline: none;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            border-color: var(--text-gold);
        }
        .search-btn {
            height: 50px;
            padding: 0 30px;
            box-sizing: border-box;
            background: linear-gradient(to bottom, #2a2a2a, #1a1a1a);
            border: 1px solid var(--border-color);
            color: var(--text-gold);
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .search-btn:hover {
            border-color: var(--text-gold);
            background: linear-gradient(to bottom, #3a3a3a, #2a2a2a);
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            margin-bottom: 20px;
        }
        .results-table th {
            background: linear-gradient(to bottom, #1a1a1a, #0a0a0a);
            padding: 15px;
            text-align: left;
            color: var(--text-gold);
            border-bottom: 1px solid var(--border-color);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .results-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(200, 160, 90, 0.1);
            color: #ddd;
        }
        .results-table tr:hover td {
            background: rgba(200, 160, 90, 0.05);
        }
        .view-btn {
            background: rgba(200, 160, 90, 0.1);
            color: var(--text-gold);
            border: 1px solid var(--border-color);
            padding: 8px 20px;
            cursor: pointer;
            border-radius: 3px;
            transition: all 0.2s;
            font-family: 'Cinzel', serif;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
        }
        .view-btn:hover {
            background: rgba(200, 160, 90, 0.2);
            border-color: var(--text-gold);
        }
        .ele-icon {
            vertical-align: middle;
            margin-left: 5px;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--text-gold);
            text-decoration: none;
            font-size: 14px;
            padding: 8px 15px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(200, 160, 90, 0.3);
            border-radius: 4px;
            transition: all 0.2s;
        }
        .back-btn:hover {
            text-decoration: none;
            background: rgba(200, 160, 90, 0.1);
            border-color: var(--text-gold);
        }
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: rgba(255, 255, 255, 0.5);
            background: rgba(0, 0, 0, 0.2);
            border: 1px dashed rgba(200, 160, 90, 0.3);
            border-radius: 4px;
        }
        .empty-state i {
            color: var(--text-gold);
            opacity: 0.5;
        }
        .search-note {
            background: rgba(0, 0, 0, 0.4);
            border-left: 4px solid var(--text-gold);
            padding: 15px 20px;
            margin-bottom: 25px;
            color: #ddd;
            font-size: 14px;
            line-height: 1.5;
            border-radius: 0 4px 4px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 25px;
            gap: 5px;
            flex-wrap: wrap;
        }
        .page-link, .page-active {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
            color: var(--text-gold);
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.2s;
            cursor: pointer;
            font-family: 'Cinzel', serif;
            font-weight: bold;
        }
        .page-link:hover {
            background: rgba(200, 160, 90, 0.2);
            border-color: var(--text-gold);
        }
        .page-active {
            background: linear-gradient(to bottom, #3a2a1a, #1a0a00);
            border-color: var(--text-gold);
            cursor: default;
        }
        .page-ellipsis {
            color: rgba(200, 160, 90, 0.5);
            padding: 0 5px;
        }
        .page-jump-input {
            width: 100px;
            height: 36px;
            padding: 0 10px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-color);
            color: var(--text-gold);
            text-align: center;
            border-radius: 4px;
            outline: none;
            font-family: inherit;
        }
        .page-jump-input:focus {
            border-color: var(--text-gold);
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box" style="margin-top: 20px;">
            <div class="content-header">
                <h2><i class="fas fa-search-location" style="margin-right: 10px;"></i> Drop Finder</h2>
            </div>
            
            <div class="content-body" style="padding: 40px;">
                <div class="search-note">
                    <i class="fas fa-info-circle" style="color: var(--text-gold); margin-right: 8px;"></i>
                    <strong>Search Tip:</strong> For precise results, enter the exact name of the item. Searching with only a few letters will bring up all items containing that text. If the page takes time to load, that means many monsters are dropping this item and this might take 2 to 5 seconds.
                </div>
                
                <div class="search-container">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="width: 100%; display: flex; justify-content: center; align-items: center; margin: 0;">
                        <input type="text" name="search" class="search-input" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search for an item or monster name..." required>
                        <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>

                <?php if ($search_results !== null || $mob_search_results !== null): ?>
                    <div class="search-tabs" style="margin-bottom: 20px;">
                        <?php if ($search_results !== null && count($search_results) > 0): ?>
                        <h3 style="color: var(--text-gold); margin-bottom: 15px; font-size: 18px; border-bottom: 1px solid rgba(200, 160, 90, 0.2); padding-bottom: 10px;">
                            <i class="fas fa-box" style="margin-right: 8px;"></i> Items Found (<?php echo count($search_results); ?>)
                        </h3>
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Item Level</th>
                                    <th>Linkable Slots</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $item): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars(mb_convert_encoding($item['ItemName'], 'UTF-8', 'ISO-8859-1')); ?></strong></td>
                                        <td><?php echo $item['ReqLevel']; ?></td>
                                        <td>
                                            <?php 
                                            if ($item['Slot'] == 0) {
                                                echo '<span style="color: rgba(255,255,255,0.4); font-size: 13px;">Not linkable</span>';
                                            } else {
                                                echo '<span style="color: var(--text-gold);">' . $item['Slot'] . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="margin: 0;">
                                                <input type="hidden" name="itemid" value="<?php echo $item['ItemID']; ?>">
                                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                                                <button type="submit" class="view-btn">View Drops <i class="fas fa-chevron-right" style="font-size: 11px; margin-left: 5px;"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>

                        <?php if ($mob_search_results !== null && count($mob_search_results) > 0): ?>
                        <h3 style="color: var(--text-gold); margin-top: 30px; margin-bottom: 15px; font-size: 18px; border-bottom: 1px solid rgba(200, 160, 90, 0.2); padding-bottom: 10px;">
                            <i class="fas fa-ghost" style="margin-right: 8px;"></i> Monsters Found (<?php echo count($mob_search_results); ?>)
                        </h3>
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Map</th>
                                    <th>Monster Name</th>
                                    <th>Monster Level</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mob_search_results as $mob): ?>
                                    <tr>
                                        <td>
                                            <div class="map-hover">
                                                <i class="fas fa-map-marker-alt" style="color: var(--text-gold); font-size: 12px; margin-right: 5px;"></i> <?php echo htmlspecialchars($mob['MapName'] ?? 'Unknown'); ?>
                                                <div class="map-tooltip">
                                                    <img src="assets/minimaps/<?php echo $mob['MapID']; ?>.webp" alt="Map" onerror="this.style.display='none';">
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars(mb_convert_encoding($mob['MobName'], 'UTF-8', 'ISO-8859-1')); ?></strong></td>
                                        <td><?php echo $mob['Level']; ?></td>
                                        <td style="text-align: right;">
                                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="margin: 0;">
                                                <input type="hidden" name="mobid" value="<?php echo $mob['MobID']; ?>">
                                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                                                <button type="submit" class="view-btn">View Loot Table <i class="fas fa-chevron-right" style="font-size: 11px; margin-left: 5px;"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>

                        <?php if ((count($search_results ?? []) === 0) && (count($mob_search_results ?? []) === 0)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open fa-3x" style="margin-bottom: 20px;"></i>
                            <p style="font-size: 18px; color: var(--text-gold);">No items or monsters found</p>
                            <p style="font-size: 14px; margin-top: 10px;">Please try a different search term.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($item_drops !== null): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(200, 160, 90, 0.2); padding-bottom: 10px;">
                        <h3 style="color: var(--text-gold); margin: 0; font-size: 18px;">
                            <i class="fas fa-crosshairs" style="margin-right: 8px;"></i>
                            Monsters dropping: <span style="color: #fff;"><?php echo htmlspecialchars(mb_convert_encoding($selected_item_name, 'UTF-8', 'ISO-8859-1')); ?></span>
                        </h3>
                        <?php if (isset($_GET['search'])): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="margin: 0;">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <button type="submit" class="back-btn" style="margin: 0;"><i class="fas fa-arrow-left"></i> Back to Results</button>
                        </form>
                        <?php else: ?>
                        <a href="drops.php" class="back-btn" style="margin: 0;"><i class="fas fa-arrow-left"></i> New Search</a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($item_drops) > 0): ?>
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Map</th>
                                    <th>Monster Name</th>
                                    <th>HP</th>
                                    <th>Level</th>
                                    <th>Element</th>
                                    <th>Drop Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($item_drops as $drop): ?>
                                    <tr>
                                        <td>
                                            <div class="map-hover">
                                                <i class="fas fa-map-marker-alt" style="color: var(--text-gold); font-size: 12px; margin-right: 5px;"></i> <?php echo htmlspecialchars($drop['MapName']); ?>
                                                <div class="map-tooltip">
                                                    <img src="assets/minimaps/<?php echo $drop['MapID']; ?>.webp" alt="Map" onerror="this.style.display='none';">
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars(mb_convert_encoding($drop['MobName'], 'UTF-8', 'ISO-8859-1')); ?></strong></td>
                                        <td><?php echo number_format($drop['HP'], 0, '.', ','); ?></td>
                                        <td><?php echo $drop['Level']; ?></td>
                                        <td>
                                            <?php 
                                            echo $elements[$drop['Attrib']];
                                            if (file_exists('assets/ele_' . $drop['Attrib'] . '.png')) {
                                                echo ' <img src="assets/ele_' . $drop['Attrib'] . '.png" class="ele-icon" alt="element">';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $DropRate = $drop['DropRate'];
                                            if ($drop['ItemOrder'] > 4) {
                                                $DropRate = ($DropRate / 100000);
                                            }
                                            if ($DropRate > 100) {
                                                $DropRate = 100;
                                            }
                                            echo '<span style="color: #a8d08d;">' . rtrim(rtrim(number_format($DropRate, 4), '0'), '.') . '%</span>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if ($total_pages > 1): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="margin: 0; margin-bottom: 20px;">
                            <input type="hidden" name="itemid" value="<?php echo (int)$_GET['itemid']; ?>">
                            <?php if (isset($_GET['search'])): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            
                            <div class="pagination-container">
                                <?php if ($page > 1): ?>
                                    <button type="submit" name="page" value="<?php echo $page - 1; ?>" class="page-link" title="Previous"><i class="fas fa-chevron-left" style="margin-right: 5px;"></i> Prev</button>
                                <?php endif; ?>
                                
                                <?php 
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<button type="submit" name="page" value="1" class="page-link">1</button>';
                                    if ($start_page > 2) {
                                        echo '<span class="page-ellipsis">...</span>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    if ($i == $page) {
                                        echo '<span class="page-active">' . $i . '</span>';
                                    } else {
                                        echo '<button type="submit" name="page" value="' . $i . '" class="page-link">' . $i . '</button>';
                                    }
                                }
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<span class="page-ellipsis">...</span>';
                                    }
                                    echo '<button type="submit" name="page" value="' . $total_pages . '" class="page-link">' . $total_pages . '</button>';
                                }
                                ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <button type="submit" name="page" value="<?php echo $page + 1; ?>" class="page-link" title="Next">Next <i class="fas fa-chevron-right" style="margin-left: 5px;"></i></button>
                                <?php endif; ?>
                            </div>
                        </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-ghost fa-3x" style="margin-bottom: 20px;"></i>
                            <p style="font-size: 18px; color: var(--text-gold);">No Dropping Monsters</p>
                            <p style="font-size: 14px; margin-top: 10px;">This item currently doesn't drop from any monster, or is only available from quests/mall.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($mob_drops !== null): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(200, 160, 90, 0.2); padding-bottom: 10px;">
                        <h3 style="color: var(--text-gold); margin: 0; font-size: 18px;">
                            <i class="fas fa-gift" style="margin-right: 8px;"></i>
                            Loot Table: <span style="color: #fff;"><?php echo htmlspecialchars(mb_convert_encoding($selected_mob_name, 'UTF-8', 'ISO-8859-1')); ?></span>
                        </h3>
                        <?php if (isset($_GET['search'])): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="margin: 0;">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <button type="submit" class="back-btn" style="margin: 0;"><i class="fas fa-arrow-left"></i> Back to Results</button>
                        </form>
                        <?php else: ?>
                        <a href="drops.php" class="back-btn" style="margin: 0;"><i class="fas fa-arrow-left"></i> New Search</a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($mob_drops) > 0): ?>
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Required Level</th>
                                    <th>Linkable Slots</th>
                                    <th style="text-align: right;">Drop Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mob_drops as $drop): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars(mb_convert_encoding($drop['ItemName'], 'UTF-8', 'ISO-8859-1')); ?></strong></td>
                                        <td><?php echo $drop['ReqLevel']; ?></td>
                                        <td>
                                            <?php 
                                            if ($drop['Slot'] == 0) {
                                                echo '<span style="color: rgba(255,255,255,0.4); font-size: 13px;">Not linkable</span>';
                                            } else {
                                                echo '<span style="color: var(--text-gold);">' . $drop['Slot'] . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php
                                            $DropRate = $drop['DropRate'];
                                            if ($drop['ItemOrder'] > 4) {
                                                $DropRate = ($DropRate / 100000);
                                            }
                                            if ($DropRate > 100) {
                                                $DropRate = 100;
                                            }
                                            echo '<span style="color: #a8d08d;">' . rtrim(rtrim(number_format($DropRate, 4), '0'), '.') . '%</span>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if ($total_pages > 1): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="margin: 0; margin-bottom: 20px;">
                            <input type="hidden" name="mobid" value="<?php echo (int)$_GET['mobid']; ?>">
                            <?php if (isset($_GET['search'])): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            
                            <div class="pagination-container">
                                <?php if ($page > 1): ?>
                                    <button type="submit" name="page" value="<?php echo $page - 1; ?>" class="page-link" title="Previous"><i class="fas fa-chevron-left" style="margin-right: 5px;"></i> Prev</button>
                                <?php endif; ?>
                                
                                <?php 
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<button type="submit" name="page" value="1" class="page-link">1</button>';
                                    if ($start_page > 2) {
                                        echo '<span class="page-ellipsis">...</span>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    if ($i == $page) {
                                        echo '<span class="page-active">' . $i . '</span>';
                                    } else {
                                        echo '<button type="submit" name="page" value="' . $i . '" class="page-link">' . $i . '</button>';
                                    }
                                }
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<span class="page-ellipsis">...</span>';
                                    }
                                    echo '<button type="submit" name="page" value="' . $total_pages . '" class="page-link">' . $total_pages . '</button>';
                                }
                                ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <button type="submit" name="page" value="<?php echo $page + 1; ?>" class="page-link" title="Next">Next <i class="fas fa-chevron-right" style="margin-left: 5px;"></i></button>
                                <?php endif; ?>
                            </div>
                        </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-ghost fa-3x" style="margin-bottom: 20px;"></i>
                            <p style="font-size: 18px; color: var(--text-gold);">No Drops Found</p>
                            <p style="font-size: 14px; margin-top: 10px;">This monster currently doesn't drop any items.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($search_results === null && $mob_search_results === null && $item_drops === null && $mob_drops === null): ?>
                    <div class="empty-state">
                        <i class="fas fa-search-dollar fa-3x" style="margin-bottom: 20px;"></i>
                        <p style="font-size: 18px; color: var(--text-gold);">Discover the loot</p>
                        <p style="font-size: 14px; margin-top: 10px;">Enter an item or monster name in the search box above to find where it drops!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'modules/footer.php'; ?>
</body>
</html>
