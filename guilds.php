<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guild Ranking | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .banner-button {
            display: block;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto 25px auto;
            height: 70px;
            background: url('assets/guild_banner.png') no-repeat center center;
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
    <?php $active_page = 'guilds'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header">
                <h2><i class="fas fa-shield-alt"></i> Top Guilds Ranking</h2>
            </div>
            <div class="content-body" style="padding: 40px;">
                <!-- ALL-TIME PVP RANKING BANNER -->
                <a href="rankings.php" class="banner-button">
                    <div class="banner-button-text">All-Time PvP Ranking</div>
                </a>

                <div
                    style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; max-width: 1200px; margin: 0 auto;">
                    <?php
                    $factions = [
                        ['id' => 0, 'name' => 'Alliance of Light', 'icon' => 'fa-sun', 'class' => 'aol-text'],
                        ['id' => 1, 'name' => 'Union of Fury', 'icon' => 'fa-moon', 'class' => 'uof-text']
                    ];

                    foreach ($factions as $f):
                        ?>
                        <div
                            style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: var(--radius-sm); border: 1px solid rgba(255,255,255,0.05);">
                            <h3 class="<?php echo $f['class']; ?>"
                                style="text-align: center; margin-bottom: 15px; font-family: var(--heading-font);"><i
                                    class="fas <?php echo $f['icon']; ?>"></i> <?php echo $f['name']; ?></h3>
                            <table class="ranking-table" style="width: 100%; text-align: center; font-size: 14px;">
                                <tr style="background: rgba(0,0,0,0.5);">
                                    <th style="padding: 10px; text-align: center; width: 40px;">Rank</th>
                                    <th style="text-align: left;">Guild Name</th>
                                    <th style="text-align: center;">Leader</th>
                                    <th style="text-align: center;">Members</th>
                                    <th style="text-align: center;">Total Points</th>
                                </tr>
                                <?php
                                require_once 'db.php';
                                $g_sql = "
                                SELECT TOP 20 GuildName, MasterName, TotalCount, GuildPoint, CreateDate
                                FROM PS_GameData.dbo.Guilds
                                WHERE Country = " . $f['id'] . " AND Del = 0
                                ORDER BY GuildPoint DESC, CreateDate ASC
                            ";
                                $g_res = @odbc_exec($conn, $g_sql);
                                $rank = 1;
                                if ($g_res) {
                                    while ($row = odbc_fetch_array($g_res)) {
                                        $rank_class = ($rank <= 3) ? "rank-$rank" : "";
                                        echo '<tr>';
                                        echo '<td class="' . $rank_class . '" style="padding: 10px;">' . $rank . '</td>';
                                        echo '<td class="' . $f['class']; ?> " style="text-align: left;"><i
                                            class="fas fa-shield-alt"></i> <?php echo htmlspecialchars($row['GuildName']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['MasterName']) . '</td>';
                                            echo '<td>' . (int) $row['TotalCount'] . '</td>';
                                            echo '<td style="font-weight: bold; color: var(--text-gold);">' . number_format((int) $row['GuildPoint']) . '</td>';
                                            echo '</tr>';
                                            $rank++;
                                    }
                                }
                                if ($rank === 1) {
                                    echo '<tr><td colspan="4" style="padding:20px;">No guilds found for this faction.</td></tr>';
                                }
                                ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>