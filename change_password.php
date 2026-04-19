<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['user'];
$message = '';

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = '<div style="color: #00ff00; text-align: center; margin-bottom: 15px; font-weight: bold;">Password changed successfully!</div>';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $message = '<div style="color: #ff4d4d; text-align: center; margin-bottom: 15px; font-weight: bold;">New passwords do not match.</div>';
    } elseif ($new_password === $current_password) {
        $message = '<div style="color: #ff4d4d; text-align: center; margin-bottom: 15px; font-weight: bold;">Your new password cannot be the same as your current password.</div>';
    } elseif (strlen($new_password) > 12) {
        $message = '<div style="color: #ff4d4d; text-align: center; margin-bottom: 15px; font-weight: bold;">New password cannot exceed 12 characters.</div>';
    } elseif (strlen($new_password) < 4) {
        $message = '<div style="color: #ff4d4d; text-align: center; margin-bottom: 15px; font-weight: bold;">New password must be at least 4 characters long.</div>';
    } else {
        // Verify current password first
        $verify_query = "SELECT UserID FROM Users_Master WHERE UserID = ? AND Pw = ?";
        $verify_stmt = odbc_prepare($conn, $verify_query);
        odbc_execute($verify_stmt, [$username, $current_password]);

        if (odbc_fetch_row($verify_stmt)) {
            // Password verified, update to new password
            $update_query = "UPDATE Users_Master SET Pw = ? WHERE UserID = ?";
            $update_stmt = odbc_prepare($conn, $update_query);
            $execute_success = odbc_execute($update_stmt, [$new_password, $username]);

            if ($execute_success) {
                header("Location: change_password.php?success=1");
                exit;
            } else {
                $message = '<div style="color: #ff4d4d; text-align: center; margin-bottom: 15px; font-weight: bold;">Database error: ' . odbc_errormsg($conn) . '</div>';
            }
        } else {
            $message = '<div style="color: #ff4d4d; text-align: center; margin-bottom: 15px; font-weight: bold;">Incorrect current password.</div>';
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
    <title>Change Password | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php $active_page = 'change_password'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header"
                style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                <h2 style="margin: 0;"><i class="fas fa-key"></i> Change Password</h2>
                <a href="account.php" class="admin-back-btn"><i class="fas fa-arrow-left"></i> Back to Account</a>
            </div>
            <div class="content-body" style="padding: 40px;">
                <form class="generic-form" style="max-width: 500px; margin: 0 auto;" method="POST"
                    action="change_password.php">
                    <p style="text-align:center; margin-bottom: 25px; color: #a3a3a3;">Secure your account by changing
                        your login password.</p>

                    <?php if (!empty($message))
                        echo $message; ?>

                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter your current password"
                            required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password (Max 12 chars)"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat new password" required>
                    </div>

                    <button type="submit" class="btn-submit" style="margin-top: 20px;">Update Password</button>


                </form>
            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>