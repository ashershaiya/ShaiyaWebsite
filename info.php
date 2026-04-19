<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Info | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tab-nav {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 20px;
            flex-wrap: wrap;
        }

        .tab-content .ranking-table th,
        .tab-content .ranking-table td {
            text-align: center !important;
        }

        .enchant-grid {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }

        .enchant-grid .drop-header-box {
            flex: 1;
            margin-bottom: 0 !important;
        }

        @media (max-width: 992px) {
            .enchant-grid {
                flex-direction: column;
                gap: 50px;
            }
        }

        .tab-btn {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #9ca3af;
            padding: 12px 30px;
            cursor: pointer;
            font-family: var(--heading-font);
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tab-btn:hover {
            background: rgba(232, 200, 129, 0.1);
            color: #fff;
            border-color: rgba(232, 200, 129, 0.4);
            transform: translateY(-2px);
        }

        .tab-btn.active {
            background: linear-gradient(135deg, var(--text-gold), #b39352);
            color: #000;
            border-color: var(--text-gold);
            box-shadow: 0 4px 15px rgba(232, 200, 129, 0.3);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</head>

<body>
    <?php $active_page = 'info'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header">
                <h2><i class="fas fa-info-circle"></i> Server Information</h2>
            </div>
            <div class="content-body" style="padding: 40px;">
                <!-- Tab Navigation -->
                <div class="tab-nav">
                    <button class="tab-btn active" onclick="openTab(event, 'tab-info')">
                        <i class="fas fa-info-circle"></i> Information
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'tab-enchant')">
                        <i class="fas fa-magic"></i> Enchant Rates
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'tab-example')">
                        <i class="fas fa-dungeon"></i> Instances
                    </button>
                </div>

                <!-- TAB: INFORMATION -->
                <div id="tab-info" class="tab-content active">
                    <!-- Top Summary Boxes -->
                    <div class="info-summary-grid">
                        <div class="summary-box">
                            <span class="label">Release Date</span>
                            <span class="value">December 17, 2022</span>
                            <span class="sub-value">3+ Years Online</span>
                        </div>
                        <div class="summary-box">
                            <span class="label">Episode</span>
                            <span class="value">Classic 4.5</span>
                            <span class="sub-value">Modern Systems & QoL</span>
                        </div>
                        <div class="summary-box">
                            <span class="label">Level & Lapis</span>
                            <span class="value">Instant Lv60 • Lapis Lv5</span>
                            <span class="sub-value">No Debuff Lapis</span>
                        </div>

                    </div>

                    <!-- Feature Grid -->
                    <div class="feature-grid">
                        <!-- Core Gameplay -->
                        <div class="feature-card">
                            <h3><i class="fas fa-swords"></i> Core Gameplay</h3>
                            <ul class="feature-list">
                                <li>Episode 4.5</li>
                                <li>Instant Level 60 & Max Lapis Lv5 (No Debuff Lapises)</li>
                                <li>DEX / LUC / WIS Fixed</li>
                                <li>Fixed HP (Both Factions Equal)</li>
                            </ul>
                        </div>

                        <!-- PvP & Ranking -->
                        <div class="feature-card">
                            <h3><i class="fas fa-trophy"></i> PvP & Ranking</h3>
                            <ul class="feature-list">
                                <li>Maximum Kills up to 1,000,000 (2 Rank Colors before Star)</li>
                                <li>PvP Rank Rewards redeemable from 1,000 kills</li>
                                <li>Easy Ascension Points via PvP, Quests, Events, PvP Rewards</li>
                                <li>Shared Kill System</li>
                            </ul>
                        </div>

                        <!-- Custom Features -->
                        <div class="feature-card">
                            <h3><i class="fas fa-sparkles"></i> Custom Features</h3>
                            <ul class="feature-list">
                                <li>Perfect Recreation Rune (Max stat reroll)</li>
                                <li>Tiered Spender rewards for Ascension Points spent</li>
                                <li>Auto Loot</li>
                                <li>Cross-Faction Trade & Whisper</li>
                                <li>Guild Creation with only 2 players (and no penalty)</li>
                            </ul>
                        </div>

                        <!-- Quality of Life -->
                        <div class="feature-card">
                            <h3><i class="fas fa-rocket"></i> Quality of Life</h3>
                            <ul class="feature-list">
                                <li>Endless Pots</li>
                                <li>5s leader resurrection; 4s UT resurrection to prevent spawn kills.</li>
                                <li>Fast loading transitions</li>
                                <li>Ultra-Wide & 4K Resolution Support added</li>
                            </ul>
                        </div>
                    </div> <!-- Closing feature-grid -->
                </div> <!-- Closing tab-info -->

                <!-- TAB: ENCHANT RATES -->
                <div id="tab-enchant" class="tab-content">
                    <div class="enchant-grid">
                        <!-- Weapon Table -->
                        <div class="drop-header-box">
                            <h3 style="color: var(--text-gold); margin-bottom: 20px;"><i class="fas fa-hammer"></i>
                                Weapon Enchantment</h3>
                            <table class="ranking-table" style="width: 100%; text-align: center;">
                                <tr style="background: rgba(0,0,0,0.5);">
                                    <th style="padding: 12px;">Enchantment</th>
                                    <th>Chance</th>
                                    <th>Bonus</th>
                                </tr>
                                <tr>
                                    <td>0-1</td>
                                    <td style="color:#6ee7b7;">90%</td>
                                    <td>7</td>
                                </tr>
                                <tr>
                                    <td>1-2</td>
                                    <td style="color:#6ee7b7;">90%</td>
                                    <td>14</td>
                                </tr>
                                <tr>
                                    <td>2-3</td>
                                    <td style="color:#6ee7b7;">90%</td>
                                    <td>21</td>
                                </tr>
                                <tr>
                                    <td>3-4</td>
                                    <td style="color:#fbbf24;">80%</td>
                                    <td>31</td>
                                </tr>
                                <tr>
                                    <td>4-5</td>
                                    <td style="color:#f59e0b;">50%</td>
                                    <td>41</td>
                                </tr>
                                <tr>
                                    <td>5-6</td>
                                    <td style="color:#f59e0b;">20%</td>
                                    <td>51</td>
                                </tr>
                                <tr>
                                    <td>6-7</td>
                                    <td style="color:#ef4444;">10%</td>
                                    <td>64</td>
                                </tr>
                                <tr>
                                    <td>7-8</td>
                                    <td style="color:#ef4444;">5%</td>
                                    <td>77</td>
                                </tr>
                                <tr>
                                    <td>8-9</td>
                                    <td style="color:#ef4444;">3%</td>
                                    <td>90</td>
                                </tr>
                                <tr>
                                    <td>9-10</td>
                                    <td style="color:#ef4444;">1%</td>
                                    <td>106</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Armor Table -->
                        <div class="drop-header-box">
                            <h3 style="color: var(--text-gold); margin-bottom: 20px;"><i class="fas fa-shield-alt"></i>
                                Armor Enchantment</h3>
                            <table class="ranking-table" style="width: 100%; text-align: center;">
                                <tr style="background: rgba(0,0,0,0.5);">
                                    <th style="padding: 12px;">Enchantment</th>
                                    <th>Chance</th>
                                    <th>Bonus</th>
                                </tr>
                                <tr>
                                    <td>0-1</td>
                                    <td style="color:#6ee7b7;">90%</td>
                                    <td>5</td>
                                </tr>
                                <tr>
                                    <td>1-2</td>
                                    <td style="color:#6ee7b7;">90%</td>
                                    <td>10</td>
                                </tr>
                                <tr>
                                    <td>2-3</td>
                                    <td style="color:#6ee7b7;">90%</td>
                                    <td>15</td>
                                </tr>
                                <tr>
                                    <td>3-4</td>
                                    <td style="color:#fbbf24;">80%</td>
                                    <td>20</td>
                                </tr>
                                <tr>
                                    <td>4-5</td>
                                    <td style="color:#f59e0b;">50%</td>
                                    <td>25</td>
                                </tr>
                                <tr>
                                    <td>5-6</td>
                                    <td style="color:#f59e0b;">20%</td>
                                    <td>30</td>
                                </tr>
                                <tr>
                                    <td>6-7</td>
                                    <td style="color:#ef4444;">10%</td>
                                    <td>35</td>
                                </tr>
                                <tr>
                                    <td>7-8</td>
                                    <td style="color:#ef4444;">5%</td>
                                    <td>40</td>
                                </tr>
                                <tr>
                                    <td>8-9</td>
                                    <td style="color:#ef4444;">3%</td>
                                    <td>45</td>
                                </tr>
                                <tr>
                                    <td>9-10</td>
                                    <td style="color:#ef4444;">1%</td>
                                    <td>50</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB: INSTANCES -->
                <div id="tab-example" class="tab-content">
                    <div
                        style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 30px; border-radius: var(--radius-md);">
                        <ul
                            style="color: #d1d5db; line-height: 1.8; margin: 0; list-style-type: none; padding-left: 0; font-size: 16px;">
                            <li style="margin-bottom: 25px;">
                                <strong style="color: var(--text-gold); font-size: 18px;">Oblivion Insula (OI):</strong>
                                Party instance<br>
                                <span style="font-size: 14px; color:#9ca3af; display: inline-block; margin-top: 5px;"><i
                                        class="far fa-clock"></i> 24/7 Non-Stop (Min: 2 • Max: 30 Players)</span>
                            </li>
                            <li style="margin-bottom: 0;">
                                <strong style="color: var(--text-gold); font-size: 18px;">Cave of Stigma / Aurizen
                                    Ruin:</strong> Party instance<br>
                                <span style="font-size: 14px; color:#9ca3af; display: inline-block; margin-top: 5px;"><i
                                        class="far fa-clock"></i> 24/7 Non-Stop (Min: 2 • Max: 7 Players)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>