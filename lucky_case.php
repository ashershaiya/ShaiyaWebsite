<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$active_page = 'account';

// Fetch Lucky Chest Setting
$luckyChestEnabled = '1';
$qLucky = odbc_exec($conn, "IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) SELECT SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey = 'LuckyChestEnabled'");
if ($qLucky && ($row = odbc_fetch_array($qLucky))) {
    $luckyChestEnabled = $row['SettingValue'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Chest | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .case-wrapper {
            background: linear-gradient(135deg, rgba(20, 20, 20, 0.95), rgba(0, 0, 0, 0.85));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.8), inset 0 0 30px rgba(255, 215, 0, 0.05);
        }

        .roulette-container {
            position: relative;
            width: 100%;
            height: 140px;
            background: linear-gradient(180deg, #0a0a0a, #1a1a1a, #0a0a0a);
            border: 2px solid #222;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.9), 0 5px 15px rgba(0, 0, 0, 0.5);
            /* Soft glow overlay */
            padding-left: 2px;
        }

        .roulette-center-line {
            position: absolute;
            top: 0;
            left: 50%;
            width: 3px;
            height: 100%;
            background: #fff;
            transform: translateX(-50%);
            z-index: 10;
            box-shadow: 0 0 15px #e74c3c, 0 0 25px #e74c3c, 0 0 35px #e74c3c;
            border-radius: 2px;
        }

        .roulette-center-line::after {
            content: '';
            position: absolute;
            top: -5px;
            bottom: -5px;
            left: -10px;
            right: -10px;
            background: linear-gradient(to bottom, rgba(231, 76, 60, 0.4), transparent, rgba(231, 76, 60, 0.4));
            pointer-events: none;
        }

        .roulette-track {
            display: flex;
            height: 100%;
            position: absolute;
            left: 50%;
            transition: left 5s cubic-bezier(0.15, 0.85, 0.2, 1);
            align-items: center;
        }

        .roulette-item {
            width: 100px;
            height: 110px;
            margin: 0 5px;
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 2px solid transparent;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
            color: inherit;
            /* will be overridden by JS dynamically */
            position: relative;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.7);
        }

        /* Ambient glow trick using currentColor */
        .roulette-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: inherit;
            box-shadow: 0 0 18px currentColor;
            opacity: 0.15;
            z-index: 0;
            transition: opacity 0.3s;
        }

        .roulette-item i,
        .roulette-item span {
            position: relative;
            z-index: 1;
        }

        .roulette-item i {
            font-size: 34px;
            margin-bottom: 12px;
        }

        .roulette-item span {
            font-size: 11px;
            font-weight: 800;
            text-align: center;
            padding: 0 5px;
            text-shadow: 1px 1px 2px #000, 0 0 5px rgba(0, 0, 0, 0.8);
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .btn-roll {
            background: linear-gradient(135deg, #f39c12, #d35400);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 16px 45px;
            font-family: 'Cinzel', serif;
            font-size: 19px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 35px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 10px 20px rgba(211, 84, 0, 0.3), inset 0 2px 0 rgba(255, 255, 255, 0.2);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            margin-left: 5px;
            margin-right: 5px;
        }

        .btn-roll:hover:not(:disabled) {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 25px rgba(211, 84, 0, 0.5), inset 0 2px 0 rgba(255, 255, 255, 0.4);
            background: linear-gradient(135deg, #f1c40f, #e67e22);
        }

        .btn-roll:disabled {
            background: #444;
            color: #888;
            border: 1px solid #333;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        #result-msg {
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            min-height: 24px;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header"
                style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                <h2 style="margin: 0;"><i class="fas fa-gift"></i> Lucky Chest</h2>
                <a href="account.php" class="admin-back-btn"><i class="fas fa-arrow-left"></i> Back to Account</a>
            </div>
            <div class="content-body" style="padding: 40px;">

                <div class="case-wrapper">
                    <?php if ($luckyChestEnabled == '1'): ?>
                        <h3 style="color: var(--text-gold); margin-bottom: 20px; font-family: 'Cinzel', serif;">Daily
                            Supplies Chest</h3>
                        <p style="color: #bbb; font-size: 13px; margin-bottom: 30px;">Open this chest once every 6 hours to
                            receive free supplies delivered straight to your Giftbox. Potential rewards include AP and more!
                        </p>

                        <div class="roulette-container">
                            <div class="roulette-center-line"></div>
                            <div class="roulette-track" id="track">
                                <!-- Items will be generated via JS -->
                            </div>
                        </div>

                        <div id="result-msg"></div>

                        <button id="btnOpen" class="btn-roll">Open Chest</button>
                    <?php else: ?>
                        <div style="padding: 40px; text-align: center;">
                            <i class="fas fa-clover"
                                style="font-size: 48px; color: var(--text-gold); opacity: 0.3; margin-bottom: 20px;"></i>
                            <h3 style="color: #fff; font-family: 'Cinzel', serif; margin-bottom: 15px;">Lucky chest is
                                currently disabled</h3>
                            <p style="color: #a3a3a3; line-height: 1.6;">We are currently working on updates for the rewards
                                and system. The lucky chest will be available again shortly. Thank you for your patience!
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>

    <script>
        // Item definitions (should roughly match backend probabilities to look authentic)
        const REWARDS = [
            { id: 100251, name: '10 AP', icon: 'gem', color: '#bdc3c7' },       // Common (Silver)
            { id: 100252, name: '100 AP', icon: 'gem', color: '#3498db' },      // Rare (Blue)
            { id: 100253, name: '1000 AP', icon: 'gem', color: '#9b59b6' },      // Epic (Purple)
            { id: 100254, name: '10.000 AP', icon: 'gem', color: '#ff3f34' },    // Legendary (Red/Orange)
            { id: 0, name: 'Nothing', icon: 'times-circle', color: '#7f8c8d' }   // Junk (Grey)
        ];

        const ITEM_WIDTH = 110; // 100px width + 10px margin

        function generateTrack(winningItem) {
            const track = document.getElementById('track');
            track.innerHTML = '';

            // We need about 50 filler items before the winning item
            for (let i = 0; i < 50; i++) {
                const randItem = REWARDS[Math.floor(Math.random() * REWARDS.length)];
                track.appendChild(createHtmlItem(randItem));
            }

            // The 51st item is the winner (index 50)
            track.appendChild(createHtmlItem(winningItem));

            // A few items after the winner
            for (let i = 0; i < 5; i++) {
                const randItem = REWARDS[Math.floor(Math.random() * REWARDS.length)];
                track.appendChild(createHtmlItem(randItem));
            }

            // Reset track position
            track.style.transition = 'none';
            // Start position: the very first item is exactly centered
            track.style.left = `calc(50% - ${ITEM_WIDTH / 2}px)`;
        }

        function createHtmlItem(itemData) {
            const div = document.createElement('div');
            div.className = 'roulette-item';
            div.style.borderColor = itemData.color;
            div.style.color = itemData.color; // Allows CSS currentColor to generate dynamic ambient shadows!
            div.innerHTML = `
                <i class="fas fa-${itemData.icon}" style="color: ${itemData.color}"></i>
                <span style="color: ${itemData.color}">${itemData.name}</span>
            `;
            return div;
        }

        document.getElementById('btnOpen').addEventListener('click', function () {
            const btn = this;
            const msgBox = document.getElementById('result-msg');
            const track = document.getElementById('track');

            btn.disabled = true;
            msgBox.innerHTML = '<span style="color:#aaa;">Connecting to server...</span>';

            fetch('lucky_case_api.php')
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        msgBox.innerHTML = `<span style="color:#e74c3c;">${data.message}</span>`;
                        btn.disabled = false;
                        return;
                    }

                    msgBox.innerHTML = '';
                    generateTrack(data.item);

                    // Force browser reflow to reset transition
                    void track.offsetWidth;

                    // Calculate destination
                    // We want index 50 (the winning item) to be centered.
                    // To do that, the track needs to slide LEFT by 50 * ITEM_WIDTH.
                    // We add a tiny bit of random offset so it doesn't always land dead center.
                    const randomCloseness = Math.floor(Math.random() * 40) - 20; // -20 to +20 px
                    const destination = -(50 * ITEM_WIDTH) + randomCloseness;

                    track.style.transition = 'left 5s cubic-bezier(0.15, 0.85, 0.2, 1)';
                    track.style.left = `calc(50% - ${ITEM_WIDTH / 2}px + ${destination}px)`;

                    setTimeout(() => {
                        if (data.item.id == 0) {
                            msgBox.innerHTML = `<span style="color:#e74c3c;">Tough luck! You won <b style="color:${data.item.color}">${data.item.name}</b> this time. Try again later!</span>`;
                        } else {
                            msgBox.innerHTML = `<span style="color:#2ecc71;">Congratulations! You won: <b style="color:${data.item.color}">${data.item.name}</b>. Check your Giftbox!</span>`;
                        }
                    }, 5000);
                })
                .catch(err => {
                    console.error(err);
                    msgBox.innerHTML = '<span style="color:#e74c3c;">Network error occurred.</span>';
                    btn.disabled = false;
                });
        });

        // Initialize with random track
        generateTrack(REWARDS[0]);
    </script>
</body>

</html>