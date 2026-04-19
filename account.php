<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['user'];

// Fetch user status for Admin check
$user_sql = "SELECT Status FROM PS_UserData.dbo.Users_Master WHERE UserID = '$username'";
$user_res = odbc_exec($conn, $user_sql);
$user_data = odbc_fetch_array($user_res);
$userStatus = (int) $user_data['Status'];

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .account-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        @media (max-width: 900px) {
            .account-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .account-grid {
                grid-template-columns: repeat(1, 1fr);
            }
        }

        .account-btn {
            position: relative;
            display: block;
            height: 110px;
            border-radius: 6px;
            overflow: hidden;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
            background-size: cover;
            background-position: center;
        }

        .account-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 215, 0, 0.5);
        }

        .account-btn-overlay {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            padding: 15px 0;
            text-align: center;
        }

        .account-btn span {
            color: #ffe57f;
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 15px;
            text-transform: uppercase;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
            display: inline-block;
        }

        .account-btn:hover span {
            color: #fff;
            text-shadow: 0 0 5px #ffe57f;
        }

        .bg-characters {
            background-image: url('assets/my_characters_bg.png');
        }

        .bg-gm {
            background-image: url('assets/gm_services_bg.png');
        }

        .bg-vote {
            background-image: url('assets/vote_for_us_bg.png');
        }

        .bg-gift {
            background-image: url('assets/gift_codes_bg.png');
        }

        .bg-password {
            background-image: url('assets/change_password_bg.png');
        }

        .bg-logout {
            background-image: url('assets/logout_bg.png');
        }

        .bg-admin {
            background-image: url('assets/admin_panel_bg.png');
        }
    </style>
</head>

<body>
    <?php $active_page = 'account'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header">
                <h2><i class="fas fa-user-cog"></i> My Account Panel</h2>
            </div>
            <div class="content-body" style="padding: 30px;">

                <div class="account-grid">
                    <a href="characters.php" class="account-btn bg-characters">
                        <div class="account-btn-overlay">
                            <span>My Characters</span>
                        </div>
                    </a>

                    <a href="lucky_case.php" class="account-btn"
                        style="background-image: url('assets/background.png'); border-color: rgba(255,215,0,0.3);">
                        <div class="account-btn-overlay">
                            <span style="color: var(--text-gold);"><i class="fas fa-gift"></i> Lucky Chest</span>
                        </div>
                    </a>

                    <a href="#" class="account-btn bg-vote">
                        <div class="account-btn-overlay">
                            <span>Vote For Us</span>
                        </div>
                    </a>

                    <?php if (in_array($userStatus, [16, 32, 48])): ?>
                        <a href="admin.php" class="account-btn bg-admin" style="border: 1px solid var(--text-gold);">
                            <div class="account-btn-overlay">
                                <span style="color: var(--text-aol);"><i class="fas fa-hammer"></i> STAFF PANEL</span>
                            </div>
                        </a>
                    <?php endif; ?>

                    <a href="change_password.php" class="account-btn bg-password">
                        <div class="account-btn-overlay">
                            <span>Change Password</span>
                        </div>
                    </a>

                    <a href="?logout=1" class="account-btn bg-logout">
                        <div class="account-btn-overlay">
                            <span>Logout</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>