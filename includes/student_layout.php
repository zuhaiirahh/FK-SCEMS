<?php

function student_require_auth(): void
{
    if (!isset($_SESSION['userID']) || ($_SESSION['type'] ?? '') !== 'student') {
        header('Location: login.php');
        exit();
    }
}

function student_page_start(string $pageTitle, string $userName, string $navActive, string $mainClass = ''): void
{
    $studentNavActive = $navActive;
    $mainClassAttr = $mainClass !== '' ? ' ' . htmlspecialchars($mainClass) : '';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FK-SCEMS</title>
    <link rel="stylesheet" href="studentStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <header class="main-header">
        <div class="header-left">
            <img src="images/umpsaLogo.png" alt="UMPSA Logo" class="mini-logo">
            <span class="system-title">FK-SCEMS</span>
        </div>
        <div class="header-right">
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $userName; ?></span>
                    <span class="user-role">Student</span>
                </div>
                <span class="header-profile-icon" aria-hidden="true"><i class="fas fa-user-circle"></i></span>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    <div class="dashboard-wrapper">
    <?php require __DIR__ . '/student_sidebar.php'; ?>
    <main class="main-content<?php echo $mainClassAttr; ?>">
    <?php
}

function student_page_end(): void
{
    ?>
    </main>
    </div>
</body>
</html>
    <?php
}
