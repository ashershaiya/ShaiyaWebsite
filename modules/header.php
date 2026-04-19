<header class="header">
    <div class="logo-area">
        <div class="logo">Shaiya <span>Ascension</span></div>
        <div class="slogan">The Ultimate Classic Experience</div>
    </div>

    <!-- Header Stats Bar -->
    <?php include_once 'stats_logic.php'; ?>
    <div class="header-stats">
        <div class="h-stats-item aol">
            <img src="assets/aol.webp" alt="AOL">
            <span><?php echo $aol_pct; ?>%</span>
        </div>
        <div class="h-stats-divider"></div>
        <div class="h-stats-item uof">
            <img src="assets/uof.webp" alt="UOF">
            <span><?php echo $uof_pct; ?>%</span>
        </div>
        <div class="h-stats-divider"></div>
        <div class="h-stats-online">
            <span><?php echo number_format((int) $online_count); ?></span> <?php echo ($online_count == 1) ? 'Player' : 'Players'; ?> Online
        </div>

    </div>

    <style>
        .header-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 220px;
            height: 42px;
            background: rgba(232, 200, 129, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid var(--text-gold);
            color: #fff;
            font-family: var(--heading-font);
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 700;
            border-radius: var(--radius-md);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            text-decoration: none;
        }

        .header-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: skewX(-20deg);
            transition: left 0.5s ease;
        }

        .header-btn:hover::before {
            left: 150%;
        }

        .header-btn i {
            margin-right: 15px;
            font-size: 18px;
            color: var(--text-gold);
            transition: transform 0.3s;
        }

        .header-btn:hover {
            background: rgba(232, 200, 129, 0.2);
            box-shadow: 0 5px 20px rgba(232, 200, 129, 0.2);
            transform: translateY(-2px);
            color: #fff;
        }

        .header-btn:hover i {
            transform: scale(1.2);
        }
    </style>
    <div class="action-buttons">
        <a href="download.php" class="header-btn btn-download"><i class="fas fa-download"></i> Game Download</a>
        <a href="register.php" class="header-btn btn-register"><i class="fas fa-user-plus"></i> Free Registration</a>
    </div>
</header>