<?php

function committee_require_auth(): void
{
    if (!isset($_SESSION['userID']) || ($_SESSION['type'] ?? '') !== 'committee') {
        header('Location: login.php');
        exit();
    }
}

function committee_status_class(string $label): string
{
    return match ($label) {
        'Active' => 'complete',
        'Draft' => 'pending',
        'Closed', 'Cancelled' => 'cancelled',
        default => 'pending',
    };
}

function committee_page_start(string $pageTitle, string $userName, string $navActive, string $mainClass = ''): void
{
    $committeeNavActive = $navActive;
    $mainClassAttr = $mainClass !== '' ? ' ' . htmlspecialchars($mainClass) : '';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FK-SCEMS</title>
    <link rel="stylesheet" href="committeeStyle.css">
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
                <span class="user-role">Committee Club</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    <div class="dashboard-wrapper">
    <?php require __DIR__ . '/committee_sidebar.php'; ?>
    <main class="main-content<?php echo $mainClassAttr; ?>">
    <?php
}

function committee_page_end(): void
{
    ?>
    </main>
    </div>
</body>
</html>
    <?php
}
