<?php

function admin_require_auth(): void
{
    if (!isset($_SESSION['userID']) || ($_SESSION['type'] ?? '') !== 'admin') {
        header('Location: login.php');
        exit();
    }
}

function admin_nav_class(string $key, string $active): string
{
    return $key === $active ? 'active' : '';
}

function admin_page_start(string $pageTitle, string $navActive): void
{
    $userName = htmlspecialchars($_SESSION['name'] ?? 'Administrator');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FK-SCEMS</title>
    <link rel="stylesheet" href="adminStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <header class="main-header">
        <div class="header-left">
            <img src="images/umpsaLogo.png" alt="UMPSA Logo" class="mini-logo">
            <span class="system-title">FK-SCEMS</span>
        </div>
        <div class="header-right">
            <div class="user-info">
                <span class="user-name"><?php echo $userName; ?></span>
                <span class="user-role">Administrator</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li class="<?php echo admin_nav_class('dashboard', $navActive); ?>">
                    <a href="adminDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                </li>
                <li class="<?php echo admin_nav_class('clubs', $navActive); ?>">
                    <a href="manageFacultyClubs.php"><i class="fas fa-tasks"></i> Club Management</a>
                </li>
                <li class="<?php echo admin_nav_class('events', $navActive); ?>">
                    <a href="adminEventManagement.php"><i class="fas fa-calendar-check"></i> Events</a>
                </li>
                <li class="<?php echo admin_nav_class('users', $navActive); ?>">
                    <a href="adminUserControl.php"><i class="fas fa-users-cog"></i> User Control</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-file-alt"></i> Reporting Module</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-user-shield"></i> My Profile</a>
                </li>
            </ul>
        </nav>
        <main class="main-content">
    <?php
}

function admin_page_end(): void
{
    ?>
        </main>
    </div>
</body>
</html>
    <?php
}
