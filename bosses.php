<?php
session_start();
require_once 'db.php';

$active_page = 'bosses';


// Boss mappings: ID => Respawn Time (seconds)
$bosses = array('2472' => '54000', '835' => '21600', '1259' => '21600');

// Pre-fetch all drops for the tracked bosses
$bossIdsStr = implode(',', array_keys($bosses));
$dropData = [];
$queryDrops = "SELECT mi.MobID, i.ItemName, mi.DropRate, mi.ItemOrder 
              FROM PS_GameDefs.dbo.MobItems mi 
              JOIN PS_GameDefs.dbo.Items i ON mi.Grade = i.Grade 
              WHERE mi.MobID IN ($bossIdsStr) 
              AND mi.Grade > 0 
              AND LEFT(i.ItemName, 1) != '?' 
              ORDER BY mi.DropRate DESC";
$stmtDrops = odbc_prepare($conn, $queryDrops);
odbc_execute($stmtDrops);
while ($drow = odbc_fetch_array($stmtDrops)) {
    $dropData[$drow['MobID']][] = [
        'name' => mb_convert_encoding($drow['ItemName'], 'UTF-8', 'ISO-8859-1'),
        'rate' => (float)$drow['DropRate'],
        'order' => (int)$drow['ItemOrder']
    ];
}

// Pre-fetch latest death logs for all tracked bosses in a single query
$bossDeaths = [];
$queryDeaths = "SELECT MobID, CharName, ActionTime 
                FROM (
                    SELECT MobID, CharName, ActionTime,
                           ROW_NUMBER() OVER (PARTITION BY MobID ORDER BY ActionTime DESC) as rn
                    FROM PS_GameLog.dbo.Boss_Death_Log 
                    WHERE MobID IN ($bossIdsStr)
                ) t
                WHERE rn = 1";
$stmtDeaths = odbc_prepare($conn, $queryDeaths);
if ($stmtDeaths && odbc_execute($stmtDeaths)) {
    while ($rowD = odbc_fetch_array($stmtDeaths)) {
        $bossDeaths[$rowD['MobID']] = $rowD;
    }
}

$timeR = date("Y-m-d H:i:s.000");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bosses | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .boss-table-container {
            max-width: 1000px;
            margin: 10px auto;
        }
        .content-area-box {
            overflow: visible !important;
        }
        .boss-name {
            color: var(--text-gold);
            font-family: var(--heading-font);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 14px;
        }
        .map-text { color: #d1d5db; font-size: 13px; }
        .level-text { color: #fff; font-weight: 700; font-size: 14px; }
        .killer-text { color: #d1d5db; text-transform: uppercase; font-size: 13px; }
        .respawn-timer {
            font-weight: 700; font-size: 14px; color: #fff;
            font-family: 'Cinzel', serif; display: inline-block; text-align: right;
        }
        .respawn-col { min-width: 150px; text-align: right !important; }
        .status-now { color: #22c55e; font-weight: 900; text-transform: uppercase; }
        .boss-name-hover {
            position: relative; cursor: help; display: inline-flex;
            align-items: center; border-bottom: 1px dotted rgba(200, 160, 90, 0.5); padding-bottom: 2px;
        }
        .drop-tooltip {
            visibility: hidden; width: 320px; background-color: rgba(10, 10, 12, 0.98);
            backdrop-filter: blur(15px); color: #fff; text-align: left;
            border: 1px solid var(--border-gold); border-radius: var(--radius-md);
            padding: 15px; position: absolute; z-index: 1000;
            left: calc(100% + 15px); top: 50%; transform: translateY(-50%);
            opacity: 0; transition: opacity 0.2s, visibility 0.2s;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.9), 0 0 10px rgba(232, 200, 129, 0.1);
            pointer-events: auto;
        }
        .drop-tooltip::after {
            content: ''; position: absolute; top: 0; left: -20px; width: 20px; height: 100%; display: block; background: transparent;
        }
        .drop-tooltip::before {
            content: ""; position: absolute; top: 50%; left: -6px; transform: translateY(-50%) rotate(45deg);
            width: 10px; height: 10px; background: rgba(10, 10, 12, 1);
            border-left: 1px solid var(--border-gold); border-bottom: 1px solid var(--border-gold); z-index: -1;
        }
        .tooltip-top { top: 0 !important; transform: none !important; }
        .tooltip-top::before { top: 15px !important; transform: rotate(45deg) !important; }
        .tooltip-bottom { top: auto !important; bottom: 0 !important; transform: none !important; }
        .tooltip-bottom::before { top: auto !important; bottom: 15px !important; transform: rotate(45deg) !important; }
        .boss-name-hover:hover .drop-tooltip, .drop-tooltip:hover { visibility: visible; opacity: 1; }
        .drop-tooltip h4 {
            color: var(--text-gold); font-family: var(--heading-font); font-size: 14px;
            margin-bottom: 12px; border-bottom: 1px solid rgba(232, 200, 129, 0.3);
            padding-bottom: 8px; text-transform: uppercase; display: flex; justify-content: space-between;
        }
        .drop-tooltip table { width: 100%; border-collapse: collapse; }
        .drop-tooltip table th { font-size: 11px; color: rgba(255, 255, 255, 0.5); text-align: left; padding-bottom: 5px; text-transform: uppercase; }
        .drop-tooltip .drop-list-container { max-height: 250px; overflow-y: auto; padding-right: 5px; }
        .drop-tooltip table td { font-size: 12px; padding: 6px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05); color: #e5e7eb; }
        .drop-tooltip table tr:last-child td { border-bottom: none; }
        .drop-rate-val { color: #a8d08d; font-weight: bold; text-align: right; }
        .drop-list-container::-webkit-scrollbar { width: 4px; }
        .drop-list-container::-webkit-scrollbar-thumb { background: var(--text-gold); border-radius: 2px; }
        .spawn-note {
            background: rgba(0, 0, 0, 0.4); border-left: 4px solid var(--text-gold);
            padding: 15px 20px; margin: 0 auto 15px; max-width: 1000px; color: #ddd;
            font-size: 14px; line-height: 1.5; border-radius: 0 4px 4px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); text-align: left;
        }
    </style>
    <script>
        function startTimer(duration, displayId, completeText) {
            var timer = duration, hours, minutes, seconds;
            var display = document.getElementById(displayId);
            if (!display) return;
            var interval = setInterval(function () {
                hours = parseInt(timer / 3600, 10);
                minutes = parseInt((timer % 3600) / 60, 10);
                seconds = parseInt(timer % 60, 10);

                hours = hours < 10 ? "0" + hours : hours;
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = hours + "h " + minutes + "m " + seconds + "s";

                if (--timer < 0) {
                    clearInterval(interval);
                    display.innerHTML = completeText;
                }
            }, 1000);
        }
    </script>
</head>
<body>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header">
                <h2><i class="fas fa-dragon"></i> WORLD BOSSES</h2>
            </div>
            <div class="content-body" style="padding: 40px;">
                <div class="boss-table-container">
                    <div class="spawn-note">
                        <i class="fas fa-info-circle" style="color: var(--text-gold); margin-right: 8px;"></i>
                        <strong>Notice:</strong> World bosses may spawn up to one hour earlier or later than the scheduled time displayed below.
                    </div>
                    <div class="spawn-note">
                        <i class="fas fa-gift" style="color: var(--text-gold); margin-right: 8px;"></i>
                        <strong>Drops:</strong> To view the potential items and drop rates for a boss, simply hover your cursor over its name in the list below.
                    </div>
                    <table class="ranking-table">
                        <thead>
                            <tr>
                                <th>BOSS</th>
                                <th>MAP</th>
                                <th>BOSS LEVEL</th>
                                <th>KILLER</th>
                                <th class="respawn-col">RESPAWN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $idx = 0;
                            $totalBosses = count($bosses);
                            foreach ($bosses as $boss => $time) {
                                $idx++;
                                $tooltipAlignClass = '';
                                if ($idx === 1) $tooltipAlignClass = 'tooltip-top';
                                else if ($idx === $totalBosses) $tooltipAlignClass = 'tooltip-bottom';

                                $mapLoc = "Unknown";
                                $bossName = "Unknown Boss";
                                $bossLvl = "0";
                                switch ($boss) {
                                    case 2472:
                                        $bossName = "Dios Exiel"; $bossLvl = "64"; $mapLoc = "Caelum Sacra"; break;
                                    case 835:
                                        $bossName = "Kimuraku"; $bossLvl = "61"; $mapLoc = "D2 Floor 3"; break;
                                    case 1259:
                                        $bossName = "Greendieta Seraphim"; $bossLvl = "62"; $mapLoc = "Sky City Floor 3"; break;
                                }

                                $row1 = $bossDeaths[$boss] ?? null;

                                $killer = ($row1 && !empty($row1['CharName'])) ? htmlspecialchars(mb_convert_encoding($row1['CharName'], 'UTF-8', 'ISO-8859-1')) : "-";

                                if (!$row1 || empty($row1['ActionTime'])) {
                                    $showTime = "<span style='color:#9ca3af;'>No Data</span>";
                                } else {
                                    $actualTime = date("Y-m-d H:i");
                                    $nextTime = date("Y-m-d H:i", strtotime($row1['ActionTime'] . '+' . $time . ' seconds'));
                                    $countdown = strtotime($nextTime) - strtotime($timeR);

                                    if ($countdown < 0) {
                                        $showTime = "<span class='status-now'>NOW!</span>";
                                    } else {
                                        $hours = floor($countdown / 3600);
                                        $mins = floor(($countdown % 3600) / 60);
                                        $secs = $countdown % 60;
                                        $formattedTime = sprintf("%02dh %02dm %02ds", $hours, $mins, $secs);
                                        $showTime = "<span id='boss-timer-$boss' class='respawn-timer'>$formattedTime</span>";
                                        $showTime .= "<script>startTimer($countdown, 'boss-timer-$boss', '<span class=\"status-now\">NOW!</span>')</script>";
                                    }
                                }

                                $bossDropsHtml = '<tr><td colspan="2" style="text-align:center; padding: 20px 0; color: #9ca3af;">No unique drops recorded</td></tr>';
                                $seenItems = [];
                                if (isset($dropData[$boss]) && !empty($dropData[$boss])) {
                                    $bossDropsHtml = '';
                                    foreach ($dropData[$boss] as $dropItem) {
                                        if (in_array($dropItem['name'], $seenItems)) continue;
                                        $seenItems[] = $dropItem['name'];
                                        $rate = $dropItem['rate'];
                                        if ($dropItem['order'] > 4) $rate = ($rate / 100000);
                                        if ($rate > 100) $rate = 100;
                                        $formattedRate = rtrim(rtrim(number_format($rate, 4), '0'), '.') . '%';

                                        $bossDropsHtml .= '<tr><td>' . htmlspecialchars($dropItem['name']) . '</td><td class="drop-rate-val">' . $formattedRate . '</td></tr>';
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="boss-name">
                                        <div class="boss-name-hover"><?php echo htmlspecialchars($bossName); ?>
                                            <div class="drop-tooltip <?php echo $tooltipAlignClass; ?>">
                                                <h4>
                                                    <span><i class="fas fa-gift"></i> Potential Drops</span>
                                                    <span style="font-size: 10px; opacity: 0.6;"><?php echo count($seenItems); ?> Items</span>
                                                </h4>
                                                <div class="drop-list-container">
                                                    <table>
                                                        <thead><tr><th>Item Name</th><th style="text-align:right;">Chance</th></tr></thead>
                                                        <tbody><?php echo $bossDropsHtml; ?></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="map-text"><?php echo htmlspecialchars($mapLoc); ?></td>
                                    <td class="level-text"><?php echo $bossLvl; ?></td>
                                    <td class="killer-text"><?php echo $killer; ?></td>
                                    <td class="respawn-col"><?php echo $showTime; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>
</body>
</html>