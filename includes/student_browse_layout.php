<?php

require_once __DIR__ . '/student_layout.php';

function student_browse_nav_class(string $key, string $activeKey): string
{
    $base = 'flex items-center gap-sm px-sm py-sm rounded-lg transition-all active:scale-95 duration-150 font-label-lg text-label-lg';
    if ($key === $activeKey) {
        return $base . ' bg-secondary-container text-on-secondary-container';
    }
    return $base . ' text-on-surface-variant hover:bg-surface-container-low';
}

function student_browse_page_start(string $pageTitle, string $userName, string $navActive): void
{
    $studentNavActive = $navActive;
    $navItems = [
        ['key' => 'dashboard', 'href' => 'studentDashboard.php', 'icon' => 'dashboard', 'label' => 'Dashboard', 'fill' => false],
        ['key' => 'browse-events', 'href' => 'browseEvents.php', 'icon' => 'explore', 'label' => 'Browse Events', 'fill' => true],
        ['key' => 'event-registration', 'href' => 'browseEvents.php', 'icon' => 'app_registration', 'label' => 'Event Registration', 'fill' => false],
        ['key' => 'registration-history', 'href' => 'studentRegistrationHistory.php', 'icon' => 'history', 'label' => 'My Registration History', 'fill' => false],
        ['key' => 'profile', 'href' => 'studentProfile.php', 'icon' => 'person', 'label' => 'Profile', 'fill' => false],
        ['key' => 'notifications', 'href' => 'notifications.php', 'icon' => 'notifications', 'label' => 'Notifications', 'fill' => false],
    ];
    ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FK-SCEMS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <?php require __DIR__ . '/student_tailwind_browse_config.php'; ?>
</head>
<body class="bg-background text-on-background font-body-md text-body-md overflow-x-hidden">
<header class="bg-surface fixed top-0 z-50 border-b border-outline-variant flex justify-between items-center w-full px-md h-16">
    <div class="flex items-center gap-xs">
        <a href="studentDashboard.php" class="flex items-center gap-xs">
            <img alt="UMPSA Logo" class="h-10 w-10 object-contain" src="images/umpsaLogo.png"
                onerror="this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuBdsmpzRrxLef1ebW_dHLGGXmAsNqQjTLWO5g649S2ZMU8KHmS96IOJFkW641Me8aPrwqwYImDCGsgQTkfTrowrB1GCYZtVcsTUD_0BS0DLa_TdCsxvBcG9qwLSPwxY2inQqTr8zQG8NPAqXR9360TP_m5ti6Ogv-9qTg0QDyiMIYvj_zyDBSC_zCC2k2i9nzC5YMrll5wicmgIZvwlfEzyHa7ytDrbhYDhV0Jcz9uApghTyCwHoCiAU9fzYpN-i7jE2LJLB71meMU'">
            <span class="text-headline-md font-headline-md font-bold text-primary">FK-SCEMS</span>
        </a>
    </div>
    <div class="flex items-center gap-md">
        <div class="hidden md:flex flex-col items-end">
            <span class="font-label-lg text-label-lg text-on-surface"><?php echo $userName; ?></span>
            <span class="text-[11px] text-on-surface-variant">Student</span>
        </div>
        <div class="flex items-center gap-sm">
            <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center overflow-hidden border border-outline-variant">
                <span class="material-symbols-outlined text-on-surface-variant">account_circle</span>
            </div>
            <a href="logout.php" class="flex items-center gap-xs px-sm py-xs bg-brand-teal text-white rounded-lg hover:opacity-90 transition-all font-label-lg text-label-lg">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                Logout
            </a>
        </div>
    </div>
</header>
<div class="flex pt-16 min-h-screen">
<aside class="fixed left-0 top-16 h-[calc(100vh-64px)] w-64 bg-surface border-r border-outline-variant flex flex-col p-md space-y-xs overflow-y-auto scrollbar-hide z-40">
    <div class="mb-lg px-xs py-sm">
        <h3 class="text-label-lg font-bold text-on-surface-variant uppercase tracking-wider">Student Portal</h3>
    </div>
    <nav class="space-y-xs flex-1">
        <?php foreach ($navItems as $item): ?>
            <?php
            $isActive = $item['key'] === $studentNavActive;
            $iconClass = 'material-symbols-outlined';
            if ($isActive && $item['fill']) {
                $iconClass .= ' material-symbols-filled';
            }
            ?>
            <a class="<?php echo student_browse_nav_class($item['key'], $studentNavActive); ?>"
                href="<?php echo htmlspecialchars($item['href']); ?>"
                <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                <span class="<?php echo $iconClass; ?>"><?php echo htmlspecialchars($item['icon']); ?></span>
                <?php echo htmlspecialchars($item['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="mt-auto pt-md border-t border-outline-variant text-[11px] text-on-surface-variant text-center">
        &copy; <?php echo date('Y'); ?> FK-SCEMS UMPSA
    </div>
</aside>
<main class="flex-1 ml-64 p-lg bg-background min-h-[calc(100vh-64px)]">
<div class="max-w-6xl mx-auto">
    <?php
}

function student_browse_page_end(): void
{
    ?>
</div>
</main>
</div>
</body>
</html>
    <?php
}
