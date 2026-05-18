<?php
/**
 * Shared committee layout chrome (header + sidebar + main wrapper).
 * Requires: $committeeNavActive, $userName, $pageTitle
 */
function committee_require_auth(): void
{
    if (!isset($_SESSION['userID']) || ($_SESSION['type'] ?? '') !== 'committee') {
        header('Location: login.php');
        exit();
    }
}

function committee_render_head(string $pageTitle): void
{
    ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pageTitle); ?> - FK-SCEMS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <?php require __DIR__ . '/committee_tailwind.php'; ?>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col">
    <?php
}

function committee_render_chrome_start(string $userName): void
{
    ?>
    <header class="flex justify-between items-center w-full px-gutter h-20 bg-surface-container-lowest border-b border-outline-variant fixed top-0 z-50">
        <div class="flex items-center gap-4">
            <img alt="UMPSA Logo" class="h-12 w-auto object-contain" src="images/umpsaLogo.png"
                onerror="this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuCseBg41XqB3iWYgoGKcIy61jZ8zaAtGGY-to9Q_S6-0lmkrXbgyhr1ucCBjjg3ri1km9XPNQO8SKhZK9BeJTMVmg2hxb4OLehmcg5pI0js7TaT3fCL4KwVYe3DQXA_-C1OvlthasIXP-89wjMFM_PKa4uG8b5TQUFa5zIc3NxRNOqORuIiUytHfxnudfj3f4jLsN_8RMgJvTB4Ztrwo3WT7XAUpbIpHxaKx_4ia0WyhFGbKf1oQ35phQNPHWS3Clk4wczPYMBrGuE'" />
            <span class="text-display-lg font-display-lg text-on-primary-fixed-variant">FK-SCEMS</span>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right hidden md:block">
                <p class="text-label-lg font-label-lg text-on-surface"><?php echo $userName; ?></p>
                <p class="text-label-sm font-label-sm text-on-surface-variant">Committee Club</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="committeeProfile.php" class="material-symbols-outlined text-primary text-[28px]">account_circle</a>
                <a href="logout.php"
                    class="bg-primary text-on-primary px-4 py-2 rounded-lg text-label-lg font-label-lg hover:bg-primary-container transition-colors">Logout</a>
            </div>
        </div>
    </header>
    <div class="flex flex-1 overflow-hidden pt-20">
    <?php
    require __DIR__ . '/committee_sidebar.php';
    ?>
        <main class="flex-1 overflow-y-auto p-gutter lg:p-lg space-y-md">
    <?php
}

function committee_render_chrome_end(): void
{
    ?>
        </main>
    </div>
</body>
</html>
    <?php
}
