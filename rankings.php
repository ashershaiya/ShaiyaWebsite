<?php
session_start();
require_once 'db.php';

// Configuration: Status values to exclude from rankings (Staff accounts)
$excludedStatuses = [16, 32, 48, 64];

function getRankIconHTML($kills)
{
    if ($kills < 1)
        return '-';
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
        9 => 40000,
        8 => 30000,
        7 => 20000,
        6 => 10000,
        5 => 5000,
        4 => 1000,
        3 => 300,
        2 => 50,
        1 => 1
    ];
    foreach ($ranks as $rankNum => $minKills) {
        if ($kills >= $minKills) {
            $yPos = -($rankNum - 1) * 32;
            return '<span title="Rank ' . $rankNum . '" style="background: url(\'assets/ranks.png\') no-repeat 0px ' . $yPos . 'px; display: inline-block; width: 32px; height: 16px; vertical-align: middle;"></span>';
        }
    }
    return '';
}

$classes = [
    0 => [0 => 'Fighter', 1 => 'Defender', 5 => 'Priest'],
    1 => [2 => 'Ranger', 3 => 'Archer', 4 => 'Mage'],
    2 => [2 => 'Assassin', 4 => 'Pagan', 5 => 'Oracle'],
    3 => [0 => 'Warrior', 1 => 'Guardian', 3 => 'Hunter']
];

$conditions = ["c.Del = 0", "c.K1 >= 0", "umg.Country IN (0,1)"];

// Exclude staff from rankings
if (!empty($excludedStatuses)) {
    $statusIn = implode(',', array_map('intval', $excludedStatuses));
    $conditions[] = "u.Status NOT IN ($statusIn)";
}

if (!empty($_GET['faction'])) {
    $f = $_GET['faction'];
    if ($f === 'aol') {
        $conditions[] = "umg.Country = 0";
    } elseif ($f === 'uof') {
        $conditions[] = "umg.Country = 1";
    } elseif ($f === 'all_online') {
        $conditions[] = "c.LoginStatus = 1";
    }
}



$whereString = implode(" AND ", $conditions);

$countQuery = "SELECT TOP 1000 1 as val FROM PS_GameData.dbo.Chars c JOIN PS_GameData.dbo.UserMaxGrow umg ON c.UserUID = umg.UserUID JOIN PS_UserData.dbo.Users_Master u ON c.UserUID = u.UserUID WHERE " . $whereString;
$resCount = @odbc_exec($conn, $countQuery);
$totalRecords = 0;
if ($resCount) {
    while (odbc_fetch_row($resCount)) {
        $totalRecords++;
    }
}
// Note: We cap rankings at 1,000 for server performance.
$totalRecords = min(1000, $totalRecords);

$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$totalPages = ceil($totalRecords / $limit);
$offsetRow = (int) (($page - 1) * $limit);
$limitRow = (int) ($page * $limit);

$query = "
    SELECT * FROM (
        SELECT ROW_NUMBER() OVER(ORDER BY c.K1 DESC, c.Level DESC) AS row_num,
               c.CharName, c.Family, c.Job, c.Level, c.K1, g.GuildName, umg.Country, c.LoginStatus
        FROM PS_GameData.dbo.Chars c
        JOIN PS_GameData.dbo.UserMaxGrow umg ON c.UserUID = umg.UserUID
        JOIN PS_UserData.dbo.Users_Master u ON c.UserUID = u.UserUID
        LEFT JOIN PS_GameData.dbo.GuildChars gc ON c.CharID = gc.CharID
        LEFT JOIN PS_GameData.dbo.Guilds g ON gc.GuildID = g.GuildID AND g.Del = 0
        WHERE $whereString
    ) AS tmp
    WHERE row_num > $offsetRow AND row_num <= $limitRow
    ORDER BY row_num ASC
";

$rankings = [];
$res = @odbc_exec($conn, $query);
if ($res) {
    while ($row = odbc_fetch_array($res)) {
        $rankings[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rankings | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* BANNER BUTTON STYLE */
        .banner-button {
            display: block;
            width: 100%;
            max-width: 1050px;
            margin: 0 auto 25px auto;
            height: 70px;
            background: url('assets/guild_battle.png') no-repeat center center;
            background-size: cover;
            border: 1px solid var(--border-gold);
            border-radius: var(--radius-md);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.4);
        }

        .banner-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            transition: background 0.3s ease;
        }

        .banner-button:hover::before {
            background: rgba(0, 0, 0, 0.2);
        }

        .banner-button:hover {
            transform: translateY(-2px);
            border-color: rgba(232, 200, 129, 0.8);
        }

        .banner-button-text {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.8);
            padding: 8px 0;
            text-align: center;
            color: var(--text-gold);
            font-family: var(--heading-font);
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 4px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .banner-button:hover .banner-button-text {
            color: #fff;
            letter-spacing: 6px;
            background: rgba(0, 0, 0, 0.9);
        }
    </style>
</head>

<body>
    <?php $active_page = 'rankings'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header">
                <h2><i class="fas fa-khanda"></i> Top Player Rankings</h2>
            </div>
            <div class="content-body" style="padding: 40px;">
                <!-- GUILDS RANKING BANNER -->
                <a href="guilds.php" class="banner-button" style="margin-bottom: 20px;">
                    <div class="banner-button-text">GUILDS RANKING</div>
                </a>
                <div
                    style="background: rgba(0,0,0,0.3); padding: 20px; border-radius: var(--radius-sm); border: 1px solid rgba(255,255,255,0.05); max-width: 1050px; margin: 0 auto;">

                    <form method="GET" action="rankings.php"
                        style="display: flex; gap: 10px; margin-bottom: 20px; justify-content: center; align-items: center;">

                        <select name="faction"
                            style="padding: 10px 40px 10px 15px; border-radius: var(--radius-sm); border: 1px solid rgba(255, 184, 77, 0.4); background-color: rgba(10,10,10,0.7); background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 width=%2224%22 height=%2224%22 fill=%22%23ffb84d%22%3E%3Cpath d=%22M7 10l5 5 5-5z%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 20px; color: #fff; outline:none; font-family:var(--primary-font); font-size:14px; cursor:pointer; font-weight: 500; appearance: none; -webkit-appearance: none; -moz-appearance: none; box-shadow: 0 0 10px rgba(0,0,0,0.5);">
                            <option value="" style="background: #111; color: #fff;">All Factions</option>
                            <option value="all_online" style="background: #111; color: #fff;" <?php echo (isset($_GET['faction']) && $_GET['faction'] === 'all_online') ? 'selected' : ''; ?>>All
                                Online</option>
                            <option value="aol" style="background: #111; color: #fff;" <?php echo (isset($_GET['faction']) && $_GET['faction'] === 'aol') ? 'selected' : ''; ?>>Alliance of
                                Light</option>
                            <option value="uof" style="background: #111; color: #fff;" <?php echo (isset($_GET['faction']) && $_GET['faction'] === 'uof') ? 'selected' : ''; ?>>Union of
                                Fury</option>
                        </select>

                        <button type="submit" class="btn-small" style="padding: 10px 20px;">Filter</button>
                    </form>

                    <table class="ranking-table">
                        <tr style="background: rgba(0,0,0,0.5);">
                            <th style="padding: 15px; width: 60px;">No.</th>
                            <th style="text-align:center;">Faction</th>
                            <th>Player Name</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;">Class</th>
                            <th>Level</th>
                            <th>Guild</th>
                            <th>Kills</th>
                            <th style="width: 80px; text-align:center;">Rank</th>
                        </tr>
                        <?php
                        foreach ($rankings as $player):
                            $rankPlacement = $player['row_num'];
                            $faction = ($player['Country'] == 0) ? 'AOL' : 'UOF';
                            $faction_class = ($faction == 'AOL') ? 'aol-text' : 'uof-text';
                            $class_name = isset($classes[$player['Family']][$player['Job']]) ? $classes[$player['Family']][$player['Job']] : 'Unknown';

                            $rank_class = '';
                            if ($rankPlacement == 1) {
                                $rank_class = 'rank-1';
                            } elseif ($rankPlacement == 2) {
                                $rank_class = 'rank-2';
                            } elseif ($rankPlacement == 3) {
                                $rank_class = 'rank-3';
                            } else {
                                $rank_class = 'color:#e0e0e0;';
                            }
                            ?>
                            <tr>
                                <td class="<?php echo ($rankPlacement <= 3) ? $rank_class : ''; ?>" style="padding: 15px; <?php if ($rankPlacement > 3)
                                             echo $rank_class; ?> font-weight: bold; text-align:center;">
                                    <?php echo $rankPlacement; ?>
                                </td>
                                <td style="text-align:center;"><img src="assets/<?php echo strtolower($faction); ?>.webp"
                                        alt="<?php echo $faction; ?>"
                                        title="<?php echo $faction === 'AOL' ? 'Alliance of Light' : 'Union of Fury'; ?>"
                                        style="width: 22px; vertical-align: middle;"></td>
                                <td style="font-weight: bold;"><?php echo htmlspecialchars($player['CharName']); ?></td>
                                <td style="text-align:center;">
                                    <?php if ((int) $player['LoginStatus'] === 1): ?>
                                        <span style="color: #10b981; font-weight: 600; font-size: 12px;"> Online</span>
                                    <?php else: ?>
                                        <span style="color: #9ca3af; font-weight: 500; font-size: 12px;"> Offline</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;"><img
                                        src="assets/class/<?php echo (int) $player['Job']; ?>.webp"
                                        alt="<?php echo $class_name; ?>" title="<?php echo $class_name; ?>"
                                        style="width: 24px; vertical-align: middle;"></td>
                                <td><?php echo (int) $player['Level']; ?></td>
                                <td><span
                                        style="color: #9ca3af; font-size: 13px;"><?php echo !empty($player['GuildName']) ? htmlspecialchars($player['GuildName']) : 'None'; ?></span>
                                </td>
                                <td style="color: #6ee7b7; font-weight: 700;">
                                    <?php echo number_format((int) $player['K1']); ?>
                                </td>
                                <td style="text-align:center;"><?php echo getRankIconHTML((int) $player['K1']); ?></td>
                            </tr>
                            <?php
                        endforeach;

                        if (empty($rankings)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 20px;">No ranked players found.</td>
                            </tr>
                        <?php endif; ?>
                    </table>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination"
                            style="margin-top: 25px; display: flex; justify-content: center; align-items: center; gap: 15px;">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo ($page - 1); ?>&faction=<?php echo isset($_GET['faction']) ? urlencode($_GET['faction']) : ''; ?>"
                                    class="btn-small"><i class="fas fa-chevron-left"></i> Previous</a>
                            <?php endif; ?>

                            <span style="color: #a3a3a3; font-size: 13px;">Page <strong
                                    style="color:var(--text-gold);"><?php echo $page; ?></strong> of
                                <strong><?php echo max(1, $totalPages); ?></strong></span>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo ($page + 1); ?>&faction=<?php echo isset($_GET['faction']) ? urlencode($_GET['faction']) : ''; ?>"
                                    class="btn-small">Next <i class="fas fa-chevron-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>