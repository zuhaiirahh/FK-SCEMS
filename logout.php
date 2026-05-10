<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - FK-SCEMS</title>
    <link rel="stylesheet" href="logoutStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <div class="logout-container">
        <div class="logout-box">
            <div class="icon-circle">
                <i class="fas fa-check"></i>
            </div>

            <h1>Successfully Logged Out</h1>
            <p>Thank you for using <strong>FK-SCEMS</strong>. Your session has been terminated successfully.</p>

            <hr class="divider">

            <div class="action-section">
                <p>Would you like to log in again?</p>
                <a href="login.php" class="login-link-btn">
                    <i class="fas fa-sign-in-alt"></i> Go to Login Page
                </a>
            </div>

            <div class="footer-logo">
                <img src="images/umpsaLogo.png" alt="UMPSA Logo">
                <span>FK-SCEMS System</span>
            </div>
        </div>
    </div>

</body>

</html>