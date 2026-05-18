<?php

require_once __DIR__ . '/student_layout.php';

function student_reg_nav_class(string $key, string $activeKey): string
{
    $base = 'px-4 py-3 flex items-center gap-3 transition-all rounded-lg';
    if ($key === $activeKey) {
        return $base . ' bg-surface-container-high text-primary border-l-4 border-primary font-bold';
    }
    return $base . ' text-on-surface-variant hover:bg-surface-container group';
}

function student_reg_page_start(string $pageTitle, string $userName, string $navActive): void
{
    $navItems = [
        ['key' => 'dashboard', 'href' => 'studentDashboard.php', 'icon' => 'dashboard', 'label' => 'Dashboard'],
        ['key' => 'browse-events', 'href' => 'browseEvents.php', 'icon' => 'explore', 'label' => 'Browse Events'],
        ['key' => 'event-registration', 'href' => 'browseEvents.php', 'icon' => 'app_registration', 'label' => 'Event Registration'],
        ['key' => 'registration-history', 'href' => 'studentRegistrationHistory.php', 'icon' => 'history', 'label' => 'My Registration History'],
        ['key' => 'profile', 'href' => 'studentProfile.php', 'icon' => 'person', 'label' => 'Profile'],
        ['key' => 'notifications', 'href' => 'notifications.php', 'icon' => 'notifications', 'label' => 'Notifications'],
    ];
    ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FK-SCEMS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <?php require __DIR__ . '/student_tailwind_reg_config.php'; ?>
</head>
<body class="bg-background text-on-surface">
<header class="flex justify-between items-center w-full px-gutter h-20 bg-surface-container-lowest border-b border-outline-variant fixed top-0 z-50">
    <div class="flex items-center gap-4">
        <a href="studentDashboard.php" class="flex items-center gap-4">
            <img alt="UMPSA Logo" class="h-12 w-auto object-contain" src="images/umpsaLogo.png"
                onerror="this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuCseBg41XqB3iWYgoGKcIy61jZ8zaAtGGY-to9Q_S6-0lmkrXbgyhr1ucCBjjg3ri1km9XPNQO8SKhZK9BeJTMVmg2hxb4OLehmcg5pI0js7TaT3fCL4KwVYe3DQXA_-C1OvlthasIXP-89wjMFM_PKa4uG8b5TQUFa5zIc3NxRNOqORuIiUytHfxnudfj3f4jLsN_8RMgJvTB4Ztrwo3WT7XAUpbIpHxaKx_4ia0WyhFGbKf1oQ35phQNPHWS3Clk4wczPYMBrGuE'">
            <span class="text-display-lg font-display-lg text-primary">FK-SCEMS</span>
        </a>
    </div>
    <div class="flex items-center gap-6">
        <div class="text-right hidden md:block">
            <p class="text-label-lg font-label-lg text-on-surface"><?php echo $userName; ?></p>
            <p class="text-label-sm font-label-sm text-on-surface-variant">Student</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="material-symbols-outlined text-primary text-[28px]">account_circle</span>
            <a href="logout.php" class="bg-primary text-on-primary px-4 py-2 rounded-lg text-label-lg font-label-lg hover:bg-primary-container transition-colors">Logout</a>
        </div>
    </div>
</header>
<aside class="flex flex-col h-screen fixed left-0 top-0 pt-20 bg-surface-container-lowest border-r border-outline-variant w-64 hidden md:flex z-40">
    <nav class="flex-1 mt-6 px-2 space-y-1">
        <?php foreach ($navItems as $item): ?>
            <a class="<?php echo student_reg_nav_class($item['key'], $navActive); ?>"
                href="<?php echo htmlspecialchars($item['href']); ?>"
                <?php echo $item['key'] === $navActive ? ' aria-current="page"' : ''; ?>>
                <span class="material-symbols-outlined"><?php echo htmlspecialchars($item['icon']); ?></span>
                <span class="text-label-lg font-label-lg"><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
<main class="md:ml-64 pt-20 min-h-screen">
<div class="p-gutter max-w-6xl mx-auto space-y-md">
    <?php
}

function student_reg_page_end(): void
{
    ?>
</div>
<div class="h-20 md:hidden"></div>
</main>
<nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-surface-container-lowest border-t border-outline-variant flex justify-around items-center z-50" aria-label="Mobile navigation">
    <a href="studentDashboard.php" class="flex flex-col items-center gap-1 text-on-surface-variant">
        <span class="material-symbols-outlined">dashboard</span>
        <span class="text-[10px] font-label-sm">Dashboard</span>
    </a>
    <a href="browseEvents.php" class="flex flex-col items-center gap-1 text-on-surface-variant">
        <span class="material-symbols-outlined">calendar_today</span>
        <span class="text-[10px] font-label-sm">Events</span>
    </a>
    <a href="studentRegistrationHistory.php" class="flex flex-col items-center gap-1 text-primary">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">assignment_ind</span>
        <span class="text-[10px] font-label-sm">History</span>
    </a>
    <a href="studentProfile.php" class="flex flex-col items-center gap-1 text-on-surface-variant">
        <span class="material-symbols-outlined">person</span>
        <span class="text-[10px] font-label-sm">Profile</span>
    </a>
</nav>
</body>
</html>
    <?php
}
