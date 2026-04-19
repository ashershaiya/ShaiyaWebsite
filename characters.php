<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['user'];
$message = '';

// Get UserUID and Points
$user_info = [];
$user_query = "SELECT UserUID, Point FROM PS_UserData.dbo.Users_Master WHERE UserID = ?";
$user_stmt = odbc_prepare($conn, $user_query);
if ($user_stmt && odbc_execute($user_stmt, [$username])) {
    $user_info = odbc_fetch_array($user_stmt);
}

if (!$user_info) {
    // If user info not found for some reason
    header("Location: index.php?logout=1");
    exit;
}

$userUID = $user_info['UserUID'];
$userPoints = (int) $user_info['Point'];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $charName = $_POST['char_name'] ?? '';

    if ($_POST['action'] === 'delete' && !empty($charName)) {
        // Check if character is online
        $check_login_query = "SELECT LoginStatus FROM PS_GameData.dbo.Chars WHERE CharName = ? AND UserUID = ?";
        $check_login_stmt = odbc_prepare($conn, $check_login_query);
        $is_online = false;

        if ($check_login_stmt && odbc_execute($check_login_stmt, [$charName, $userUID])) {
            $row = odbc_fetch_array($check_login_stmt);
            if ($row && isset($row['LoginStatus']) && (int) $row['LoginStatus'] === 1) {
                $is_online = true;
            }
        }

        if ($is_online) {
            header("Location: characters.php?error=online");
            exit;
        } else {
            $del_query = "UPDATE PS_GameData.dbo.Chars SET Del = 1 WHERE CharName = ? AND UserUID = ?";
            $del_stmt = odbc_prepare($conn, $del_query);
            if ($del_stmt && odbc_execute($del_stmt, [$charName, $userUID])) {
                header("Location: characters.php?msg=deleted");
                exit;
            } else {
                header("Location: characters.php?error=del_fail");
                exit;
            }
        }
    }

    if ($_POST['action'] === 'resurrect' && !empty($charName)) {
        if ($userPoints >= 50) {
            // Check for available slot (0-4)
            $occupied = [];
            $sRes = odbc_prepare($conn, "SELECT [Slot] FROM PS_GameData.dbo.Chars WHERE UserUID = ? AND Del = 0");
            odbc_execute($sRes, [$userUID]);
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

            if ($newSlot === -1) {
                header("Location: characters.php?error=no_slot");
                exit;
            }

            // Deduct Points
            $upd_point_query = "UPDATE PS_UserData.dbo.Users_Master SET Point = Point - 50 WHERE UserID = ?";
            $upd_point_stmt = odbc_prepare($conn, $upd_point_query);

            if ($upd_point_stmt && odbc_execute($upd_point_stmt, [$username])) {
                // Restore Character
                $res_query = "UPDATE PS_GameData.dbo.Chars SET Del = 0, [Slot] = ? WHERE CharName = ? AND UserUID = ?";
                $res_stmt = odbc_prepare($conn, $res_query);
                if ($res_stmt && odbc_execute($res_stmt, [$newSlot, $charName, $userUID])) {
                    header("Location: characters.php?msg=resurrected");
                    exit;
                }
            }
            header("Location: characters.php?error=db_fail");
            exit;
        } else {
            header("Location: characters.php?error=points");
            exit;
        }
    }
}

// Handle GET messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $message = '<div class="alert alert-success">Character was successfully moved to Deleted Characters.</div>';
    } elseif ($_GET['msg'] === 'resurrected') {
        $message = '<div class="alert alert-success">Character was successfully resurrected! 50 Points deducted.</div>';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'online') {
        $message = '<div class="alert alert-error">You must be logged out of the game to delete a character.</div>';
    } elseif ($_GET['error'] === 'del_fail') {
        $message = '<div class="alert alert-error">Failed to delete character.</div>';
    } elseif ($_GET['error'] === 'db_fail') {
        $message = '<div class="alert alert-error">Database error while resurrecting.</div>';
    } elseif ($_GET['error'] === 'no_slot') {
        $message = '<div class="alert alert-error">Resurrection failed! You already have 5 active characters.</div>';
    } elseif ($_GET['error'] === 'points') {
        $message = '<div class="alert alert-error">Not enough Points! Resurrecting requires 50 Points.</div>';
    }
}

// Map helpers
$families = [
    0 => 'Human',
    1 => 'Elf',
    2 => 'Vail',
    3 => 'Deatheater'
];

$classes = [
    0 => [0 => 'Fighter', 1 => 'Defender', 5 => 'Priest'],
    1 => [2 => 'Ranger', 3 => 'Archer', 4 => 'Mage'],
    2 => [2 => 'Assassin', 4 => 'Pagan', 5 => 'Oracle'],
    3 => [0 => 'Warrior', 1 => 'Guardian', 3 => 'Hunter']
];

$map_names = [
    0 => 'Raigo/Karis',
    1 => 'Light map1',
    2 => 'Dark map1',
    3 => 'D1 light portal',
    4 => 'D1 Boss room',
    5 => 'Cornwell Ruins',
    6 => 'Light Asmo room',
    7 => 'Argilla Ruins',
    8 => 'Knight room',
    9 => 'D2',
    10 => 'D2 floor 2',
    11 => 'Kimu room',
    12 => 'Cloron',
    13 => 'Cloron floor 2',
    14 => 'Cloron floor 3',
    15 => 'Fantasma',
    16 => 'Fantasma floor 2',
    17 => 'Fantasma floor 3',
    18 => 'Proelium',
    19 => 'Light map2',
    20 => 'Dark map2',
    21 => 'Maitreyan',
    22 => 'Maitreyan boss room',
    23 => 'AidionNekria',
    24 => 'AidionNekria floor2',
    25 => 'Elemental Cave',
    26 => 'RuberChaos',
    27 => 'RuberChaos floor2',
    28 => 'Light map3',
    29 => 'dark map3',
    30 => 'CANTA',
    31 => '20-30 dungeon light',
    32 => '20-30 dungeon dark',
    33 => 'Fedion Temple',
    34 => 'Kalamus House',
    35 => 'Apulune',
    36 => 'Iris',
    37 => 'Stigma cave',
    38 => 'Aurizen ruins',
    39 => 'SECRET battle arena',
    40 => 'Arena',
    41 => 'SECRET Prison',
    42 => 'Blackmarket',
    43 => 'Pando',
    44 => 'Lanhaar',
    45 => 'DD1',
    46 => 'DD2',
    47 => 'Jungle',
    48 => 'Cryptic Throne (Light)',
    49 => 'Cryptic Throne (Fury)',
    50 => 'GRB Map',
    51 => 'Light Guildhouse',
    52 => 'Dark Guildhouse',
    53 => 'Light Managment Office',
    54 => 'Dark Managment Office',
    55 => 'SkyCity floor 1 (Light)',
    56 => 'SkyCity floor 1 (Fury)',
    57 => 'SkyCity floor 2',
    58 => 'SKyCity floor 3',
    59 => 'Etain Garden (Light)',
    60 => 'Stadium',
    61 => 'Etain Garden (Fury)',
    62 => 'Variant Kalamus House (Unused)',
    63 => 'Variant Aurizen Ruins (Unused)',
    64 => 'Oblivion Island',
    65 => 'Caelum Sacra Floor 1 LIGHT',
    66 => 'Caelum Sacra Floor 1 FURY',
    67 => 'Caelum Sacra Floor 2 (Aka exiel room)',
    68 => 'Valdemar Regnum',
    69 => 'Palaion Regnum',
    70 => 'Kanos Illium',
    71 => 'Queen Vanus',
    72 => 'Queen Servus',
    73 => 'Zehar Mine',
    74 => 'Dimension Crack',
    75 => 'Pantanassa',
    76 => 'Theodores',
    77 => 'Dungeon Arcanus Ruins',
    78 => 'Dungeon Arcanus Ruins 2F',
    79 => 'Dungeon Hypnosis Ruins',
    81 => 'Canyon of greed'
];

function getMapDisplay($mapId, $map_names)
{
    if (isset($map_names[$mapId])) {
        return $map_names[$mapId] . ' (' . $mapId . ')';
    } elseif ($mapId >= 100 && $mapId <= 109) {
        return 'Battle Zone (' . $mapId . ')';
    }
    return $mapId;
}

// Fetch Characters Logic
$active_characters = [];
$deleted_characters = [];

$query = "
    SELECT c.CharName, c.Del, c.Family, c.Job, c.Level, c.Map, c.K1, c.RegDate, umg.Country
    FROM PS_GameData.dbo.Chars c
    JOIN PS_GameData.dbo.UserMaxGrow umg ON c.UserUID = umg.UserUID
    WHERE c.UserUID = ? 
    ORDER BY c.RegDate DESC
";

$stmt = odbc_prepare($conn, $query);
if ($stmt) {
    odbc_execute($stmt, [$userUID]);
    while ($row = odbc_fetch_array($stmt)) {
        if ((int) $row['Del'] === 1) {
            $deleted_characters[] = $row;
        } else {
            $active_characters[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Characters | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .char-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .char-table th,
        .char-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .char-table th {
            background-color: rgba(0, 0, 0, 0.3);
            font-weight: bold;
            color: var(--text-gold);
        }

        .char-table tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .btn-action-small {
            background: linear-gradient(135deg, #111, #222);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            font-weight: bold;
        }

        .btn-action-small:hover {
            opacity: 0.8;
        }

        .btn-delete {
            border-color: #ff4d4d;
            color: #ff4d4d;
        }

        .btn-delete:hover {
            background: rgba(255, 77, 77, 0.1);
        }

        .btn-resurrect {
            border-color: #00ff00;
            color: #00ff00;
        }

        .btn-resurrect:hover {
            background: rgba(0, 255, 0, 0.1);
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .alert-success {
            background: rgba(0, 255, 0, 0.1);
            color: #00ff00;
            border: 1px solid rgba(0, 255, 0, 0.3);
        }

        .alert-error {
            background: rgba(255, 77, 77, 0.1);
            color: #ff4d4d;
            border: 1px solid rgba(255, 77, 77, 0.3);
        }

        .section-title {
            color: var(--text-gold);
            font-size: 18px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
            margin-top: 40px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>

<body>
    <?php $active_page = 'characters'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header"
                style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                <h2 style="margin: 0;"><i class="fas fa-users"></i> My Characters</h2>
                <a href="account.php" class="admin-back-btn"><i class="fas fa-arrow-left"></i> Back to Account</a>
            </div>
            <div class="content-body" style="padding: 30px;">
                <div
                    style="background-color: rgba(255, 184, 77, 0.1); border-left: 3px solid #ffb84d; color: #e0e0e0; padding: 12px 15px; margin-bottom: 15px; font-size: 14px; border-radius: 0 4px 4px 0;">
                    Your currently Shaiya Points balance is: <strong
                        style="color: #ffb84d;"><?php echo number_format($userPoints); ?></strong>
                </div>

                <?php if (!empty($message))
                    echo $message; ?>

                <!-- Active Characters Section -->
                <div class="section-title"><i class="fas fa-user-check"></i> Active Characters</div>
                <?php if (empty($active_characters)): ?>
                    <div
                        style="text-align: center; padding: 30px; background: rgba(0,0,0,0.2); border-radius: 5px; margin-bottom: 30px;">
                        <i class="fas fa-info-circle" style="font-size: 24px; color: #a3a3a3; margin-bottom: 10px;"></i>
                        <p style="color: #a3a3a3;">You do not have any active characters.</p>
                    </div>
                <?php else: ?>
                    <table class="char-table" style="margin-bottom: 30px;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Level</th>
                                <th style="text-align:center;">Faction</th>
                                <th style="text-align:center;">Class</th>
                                <th>Kills</th>
                                <th>Map</th>
                                <th>Creation Date</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_characters as $char): ?>
                                <tr>
                                    <td style="font-weight: bold;"><?php echo htmlspecialchars($char['CharName']); ?></td>
                                    <td><?php echo (int) $char['Level']; ?></td>
                                    <td style="text-align:center;">
                                        <?php $fac = ($char['Country'] == 0) ? 'aol' : 'uof'; ?>
                                        <img src="assets/<?php echo $fac; ?>.webp" alt="<?php echo strtoupper($fac); ?>"
                                            title="<?php echo $fac === 'aol' ? 'Alliance of Light' : 'Union of Fury'; ?>"
                                            style="width: 22px; vertical-align: middle;">
                                    </td>
                                    <td style="text-align:center;">
                                        <?php $cname = isset($classes[$char['Family']][$char['Job']]) ? $classes[$char['Family']][$char['Job']] : 'Unknown'; ?>
                                        <img src="assets/class/<?php echo (int) $char['Job']; ?>.webp"
                                            alt="<?php echo $cname; ?>" title="<?php echo $cname; ?>"
                                            style="width: 24px; vertical-align: middle;">
                                    </td>
                                    <td><?php echo number_format((int) $char['K1']); ?></td>
                                    <td><?php echo htmlspecialchars(getMapDisplay((int) $char['Map'], $map_names)); ?></td>
                                    <td style="font-size: 13px; color: #999;">
                                        <?php echo htmlspecialchars(date('M d, Y', strtotime($char['RegDate']))); ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <form method="POST" action="characters.php"
                                            onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($char['CharName']); ?>?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="char_name"
                                                value="<?php echo htmlspecialchars($char['CharName']); ?>">
                                            <button type="submit" class="btn-action-small btn-delete"><i
                                                    class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Deleted Characters Section -->
                <?php if (!empty($deleted_characters)): ?>
                    <div class="section-title" style="color:#ff4d4d;"><i class="fas fa-user-times"></i> Deleted Characters
                    </div>
                    <p style="font-size: 13px; color: #a3a3a3; margin-bottom: 15px;"><i class="fas fa-exclamation-triangle"
                            style="color:#ffb84d;"></i> Resurrecting a deleted character costs <strong>50 Points</strong>.
                    </p>

                    <table class="char-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Level</th>
                                <th style="text-align:center;">Faction</th>
                                <th style="text-align:center;">Class</th>
                                <th style="opacity:0.5;">Kills</th>
                                <th>Creation Date</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deleted_characters as $char): ?>
                                <tr>
                                    <td style="font-weight: bold; color: #ff4d4d;">
                                        <del><?php echo htmlspecialchars($char['CharName']); ?></del>
                                    </td>
                                    <td style="color: #999;"><?php echo (int) $char['Level']; ?></td>
                                    <td style="text-align:center;">
                                        <?php $fac = ($char['Country'] == 0) ? 'aol' : 'uof'; ?>
                                        <img src="assets/<?php echo $fac; ?>.webp" alt="<?php echo strtoupper($fac); ?>"
                                            title="<?php echo $fac === 'aol' ? 'Alliance of Light' : 'Union of Fury'; ?>"
                                            style="width: 22px; vertical-align: middle; filter: grayscale(100%); opacity: 0.6;">
                                    </td>
                                    <td style="text-align:center;">
                                        <?php $cname = isset($classes[$char['Family']][$char['Job']]) ? $classes[$char['Family']][$char['Job']] : 'Unknown'; ?>
                                        <img src="assets/class/<?php echo (int) $char['Job']; ?>.webp"
                                            alt="<?php echo $cname; ?>" title="<?php echo $cname; ?>"
                                            style="width: 24px; vertical-align: middle; filter: grayscale(100%); opacity: 0.6;">
                                    </td>
                                    <td style="color: #999; opacity:0.5;"><?php echo number_format((int) $char['K1']); ?></td>
                                    <td style="font-size: 13px; color: #777;">
                                        <?php echo htmlspecialchars(date('M d, Y', strtotime($char['RegDate']))); ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <form method="POST" action="characters.php"
                                            onsubmit="return confirm('Resurrecting <?php echo htmlspecialchars($char['CharName']); ?> will cost 50 Points. Proceed?');">
                                            <input type="hidden" name="action" value="resurrect">
                                            <input type="hidden" name="char_name"
                                                value="<?php echo htmlspecialchars($char['CharName']); ?>">
                                            <button type="submit" class="btn-action-small btn-resurrect"><i
                                                    class="fas fa-heartbeat"></i> Resurrect (50 DP)</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>