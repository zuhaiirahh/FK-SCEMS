<?php
session_start();

// Already logged in — send to the right page
if (isset($_SESSION['userID'], $_SESSION['type'])) {
    if ($_SESSION['type'] === 'admin') {
        header('Location: adminDashboard.php');
    } elseif ($_SESSION['type'] === 'committee') {
        header('Location: dashboardSwitcher.php');
    } else {
        header('Location: studentDashboard.php');
    }
    exit();
}

$loginError = $_SESSION['login_error'] ?? '';
$loginNotice = $_SESSION['login_notice'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_notice']);
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FK-SCEMS</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <div class="left-panel">
            <div class="info-content">
                <img src="images/umpsaLogo.png" alt="UMPSA Logo" class="logo">
                <h2>FK-SCEMS</h2>
                <p class="system-name">FK Student Club & Event Management System</p>
                <p class="university">Universiti Malaysia Pahang Al-Sultan Abdullah</p>
                <p class="location">Pekan | Gambang</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="login-box">
                <h1>Welcome Back</h1>
                <p class="subtitle">Please enter your credentials to access the portal</p>

                <?php if ($loginNotice): ?>
                    <p style="color:#2c5282;background:#e8f4fc;padding:10px 12px;border-radius:6px;font-size:0.9rem;margin-bottom:16px;">
                        <?php echo htmlspecialchars($loginNotice); ?>
                    </p>
                <?php endif; ?>

                <?php if ($loginError): ?>
                    <p style="color:#c0392b;background:#fdecea;padding:10px 12px;border-radius:6px;font-size:0.9rem;margin-bottom:16px;">
                        <?php echo htmlspecialchars($loginError); ?>
                    </p>
                <?php endif; ?>

                <form action="login_process.php" method="POST">
                    <div class="input-group">
                        <label>User ID</label>
                        <input type="text" name="userid" placeholder="Enter your User ID" required autofocus>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter password" required>
                    </div>
                    <button type="submit" name="login" class="sign-in-btn">Sign In</button>
                </form>

            </div>
        </div>
    </div>

</body>

</html>