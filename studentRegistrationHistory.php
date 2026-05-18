<?php
session_start();
include 'fkscems.php';

// --- Access control: students only ---
if (!isset($_SESSION['userID']) || ($_SESSION['type'] ?? '') !== 'student') {
    header('Location: login.php');
    exit();
}

$userID   = (int) $_SESSION['userID'];
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');

// --- Filter / pagination params ---
$search  = trim($_GET['search'] ?? '');
$perPage = 10;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// --- Build WHERE clause ---
$where  = 'WHERE er.userID = ?';
$types  = 'i';
$params = [$userID];

if ($search !== '') {
    $where  .= ' AND e.eventTitle LIKE ?';
    $types  .= 's';
    $params[] = '%' . $search . '%';
}

// --- Count total ---
$total     = 0;
$countSql  = "SELECT COUNT(*) AS total
              FROM event_registration er
              INNER JOIN event e ON er.eventID = e.eventID
              $where";
$countStmt = mysqli_prepare($conn, $countSql);
if ($countStmt) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
    mysqli_stmt_execute($countStmt);
    $total = (int)(mysqli_stmt_get_result($countStmt)->fetch_assoc()['total'] ?? 0);
    mysqli_stmt_close($countStmt);
}

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

// --- Fetch registrations ---
$registrations = [];
$listSql = "SELECT er.registrationID, er.registrationStatus AS regStatus, er.confirmationStatus,
                   e.eventTitle, e.eventDate
            FROM event_registration er
            INNER JOIN event e ON er.eventID = e.eventID
            $where
            ORDER BY er.registrationID DESC
            LIMIT ? OFFSET ?";
$listStmt = mysqli_prepare($conn, $listSql);
if ($listStmt) {
    $lTypes  = $types . 'ii';
    $lParams = array_merge($params, [$perPage, $offset]);
    mysqli_stmt_bind_param($listStmt, $lTypes, ...$lParams);
    mysqli_stmt_execute($listStmt);
    $result = mysqli_stmt_get_result($listStmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $registrations[] = $row;
    }
    mysqli_stmt_close($listStmt);
}

// --- Statistics ---
$stats = ['active' => 0, 'attended' => 0, 'cancelled' => 0];
$statStmt = mysqli_prepare($conn,
    "SELECT
        SUM(registrationStatus IN ('registered','waiting')) AS active,
        SUM(registrationStatus = 'attended')                AS attended,
        SUM(registrationStatus IN ('cancelled','rejected')) AS cancelled
     FROM event_registration WHERE userID = ?");
if ($statStmt) {
    mysqli_stmt_bind_param($statStmt, 'i', $userID);
    mysqli_stmt_execute($statStmt);
    $sRow = mysqli_stmt_get_result($statStmt)->fetch_assoc();
    $stats = [
        'active'    => (int)($sRow['active']    ?? 0),
        'attended'  => (int)($sRow['attended']  ?? 0),
        'cancelled' => (int)($sRow['cancelled'] ?? 0),
    ];
    mysqli_stmt_close($statStmt);
}

// --- Helpers ---
function regStatusBadge(string $s): string {
    return match(strtolower($s)) {
        'registered'   => '<span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Registered</span>',
        'waiting'      => '<span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">Waiting List</span>',
        'attended'     => '<span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">Attended</span>',
        'cancelled'    => '<span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Cancelled</span>',
        'rejected'     => '<span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Rejected</span>',
        default        => '<span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">' . htmlspecialchars($s) . '</span>',
    };
}

function confirmBadge(?string $s): string {
    if (!$s || $s === '') return '<span class="text-outline">—</span>';
    return match(strtolower($s)) {
        'confirmed' => '<span class="inline-flex items-center px-3 py-1 bg-green-50 text-green-600 rounded-full text-xs font-semibold border border-green-200">Confirmed</span>',
        'pending'   => '<span class="inline-flex items-center px-3 py-1 bg-orange-50 text-orange-600 rounded-full text-xs font-semibold border border-orange-200">Pending</span>',
        default     => '<span class="text-outline">—</span>',
    };
}

function pageUrl(array $override = []): string {
    return '?' . http_build_query(array_merge($_GET, $override));
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>FK-SCEMS | My Registration History</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-container": "#dce3eb", "surface-container": "#e5eeff",
                        "surface-container-low": "#eff4ff", "on-secondary-fixed-variant": "#40484e",
                        "surface-dim": "#cbdbf5", "on-tertiary-fixed-variant": "#7f2b00",
                        "primary": "#004aaf", "tertiary-fixed": "#ffdbce",
                        "surface-container-high": "#dce9ff", "background": "#f8f9ff",
                        "error-container": "#ffdad6", "primary-fixed": "#d9e2ff",
                        "on-tertiary-container": "#ffe3d9", "on-secondary-fixed": "#151c22",
                        "on-surface": "#0b1c30", "inverse-surface": "#213145",
                        "error": "#ba1a1a", "on-primary-fixed-variant": "#00419c",
                        "outline": "#727786", "surface-tint": "#0058cc",
                        "on-secondary-container": "#5e656c", "on-primary-container": "#e2e8ff",
                        "on-tertiary": "#ffffff", "on-surface-variant": "#424654",
                        "on-error": "#ffffff", "primary-container": "#0061e0",
                        "tertiary-fixed-dim": "#ffb599", "secondary-fixed-dim": "#c0c7cf",
                        "secondary": "#585f66", "primary-fixed-dim": "#b0c6ff",
                        "tertiary-container": "#b74200", "on-secondary": "#ffffff",
                        "on-primary": "#ffffff", "surface-container-highest": "#d3e4fe",
                        "surface-bright": "#f8f9ff", "outline-variant": "#c2c6d7",
                        "surface": "#f8f9ff", "on-error-container": "#93000a",
                        "on-tertiary-fixed": "#370e00", "tertiary": "#8f3200",
                        "on-background": "#0b1c30", "on-primary-fixed": "#001945",
                        "secondary-fixed": "#dce3eb", "inverse-primary": "#b0c6ff",
                        "surface-container-lowest": "#ffffff", "inverse-on-surface": "#eaf1ff",
                        "surface-variant": "#d3e4fe"
                    },
                    borderRadius: { DEFAULT: "0.125rem", lg: "0.25rem", xl: "0.5rem", full: "0.75rem" },
                    spacing: { base: "4px", margin: "24px", xs: "8px", sm: "16px", md: "24px", lg: "32px", xl: "48px", gutter: "24px" },
                    fontFamily: {
                        "label-sm": ["Plus Jakarta Sans"], "label-lg": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"], "display-lg": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"], "display-lg-mobile": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }],
                        "label-lg": ["14px", { lineHeight: "20px", fontWeight: "600" }],
                        "body-md": ["14px", { lineHeight: "20px", fontWeight: "400" }],
                        "display-lg": ["24px", { lineHeight: "32px", fontWeight: "700" }],
                        "headline-md": ["18px", { lineHeight: "24px", fontWeight: "600" }],
                        "display-lg-mobile": ["20px", { lineHeight: "28px", fontWeight: "700" }]
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen">

<!-- ===== Top App Bar ===== -->
<header class="flex justify-between items-center w-full px-gutter h-20 bg-surface-container-lowest border-b border-outline-variant fixed top-0 z-50">
    <div class="flex items-center gap-4">
        <img alt="FK-SCEMS Logo" class="h-12 w-auto object-contain"
             src="images/umpsaLogo.png"
             onerror="this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuCNgljwJIa6Bo7DdW_h_Qocwr0XQo8hF1m70r3F1gVz0yQC1pnLwXS4CILBPxUDDiN0ADqX9_GQMSZdjMHJhw8zSSbudAB6VeuGhNFmzMkwQEI7cwPjAWNIgiNXNgnjcwbTInyjWGfyoLkOXy8wUCc0QoNp0_Kw_7VXv8c_XQCeuH5Avl1I0dwqlWnuB0KWO1TSS4ABmD1NsDwCvx2NyP04z3KHYqDhjMjwtP1OGLQdrqASnOvFj4s26KoPd8uM97D26IDgplATMkU'">
        <span class="text-display-lg font-display-lg text-on-primary-fixed-variant">FK-SCEMS</span>
    </div>
    <div class="flex items-center gap-6">
        <div class="text-right hidden md:block">
            <p class="text-label-lg font-label-lg text-on-surface"><?= $userName ?></p>
            <p class="text-label-sm font-label-sm text-on-surface-variant">Student</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="material-symbols-outlined text-primary cursor-pointer text-[28px]">account_circle</span>
            <a href="logout.php"
               class="bg-primary text-on-primary px-4 py-2 rounded-lg text-label-lg font-label-lg hover:bg-primary-container transition-colors">Logout</a>
        </div>
    </div>
</header>

<!-- ===== Side Nav ===== -->
<aside class="flex flex-col h-screen fixed left-0 top-0 pt-20 w-64 bg-surface-container-lowest border-r border-outline-variant hidden md:flex">
    <nav class="flex-1 px-2 space-y-1 mt-2">
        <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg" href="studentDashboard.php">
            <span class="material-symbols-outlined">dashboard</span><span class="text-label-lg font-label-lg">Dashboard</span>
        </a>
        <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg" href="browseEvents.php">
            <span class="material-symbols-outlined">calendar_today</span><span class="text-label-lg font-label-lg">Browse Events</span>
        </a>
        <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg" href="eventRegistrationConfirmation.php">
            <span class="material-symbols-outlined">app_registration</span><span class="text-label-lg font-label-lg">Event Registration</span>
        </a>
        <a class="bg-surface-container-high text-primary border-l-4 border-primary px-4 py-3 flex items-center gap-3 font-bold" href="studentRegistrationHistory.php">
            <span class="material-symbols-outlined">history</span><span class="text-label-lg font-label-lg">My Registration History</span>
        </a>
        <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg" href="studentProfile.php">
            <span class="material-symbols-outlined">person</span><span class="text-label-lg font-label-lg">Profile</span>
        </a>
        <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg" href="notifications.php">
            <span class="material-symbols-outlined">notifications</span><span class="text-label-lg font-label-lg">Notifications</span>
        </a>
    </nav>
    <div class="mt-auto px-2 pb-6 border-t border-outline-variant pt-4">
        <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg" href="settings.php">
            <span class="material-symbols-outlined">settings</span><span class="text-label-lg font-label-lg">Settings</span>
        </a>
        <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg" href="help.php">
            <span class="material-symbols-outlined">help</span><span class="text-label-lg font-label-lg">Help</span>
        </a>
    </div>
</aside>

<!-- ===== Main ===== -->
<main class="md:ml-64 pt-20 px-gutter pb-xl min-h-screen">
    <div class="max-w-6xl mx-auto py-xl">

        <!-- Header -->
        <div class="mb-lg">
            <h1 class="text-display-lg font-display-lg text-on-surface">My Registration History</h1>
        </div>

        <!-- Search -->
        <form method="GET" action="" class="mb-lg flex flex-col md:flex-row items-center gap-md">
            <div class="relative w-full max-w-md">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                <input class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-primary text-body-md placeholder:text-outline outline-none transition-all"
                       name="search" placeholder="Search event title..."
                       type="text" value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit"
                    class="px-md py-3 bg-primary text-on-primary rounded-xl font-label-lg hover:bg-primary-container transition-colors">
                Search
            </button>
            <?php if ($search !== ''): ?>
            <a href="studentRegistrationHistory.php"
               class="px-md py-3 border border-outline-variant text-on-surface-variant rounded-xl font-label-lg hover:bg-surface-container-low transition-colors">
                Clear
            </a>
            <?php endif; ?>
        </form>

        <!-- Table Card -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant bg-surface-container-low/50">
                            <th class="px-gutter py-4 text-label-lg font-label-lg text-on-surface-variant">Reg ID</th>
                            <th class="px-gutter py-4 text-label-lg font-label-lg text-on-surface-variant">Event Name</th>
                            <th class="px-gutter py-4 text-label-lg font-label-lg text-on-surface-variant">Date</th>
                            <th class="px-gutter py-4 text-label-lg font-label-lg text-on-surface-variant">Status</th>
                            <th class="px-gutter py-4 text-label-lg font-label-lg text-on-surface-variant">Confirmation</th>
                            <th class="px-gutter py-4 text-label-lg font-label-lg text-on-surface-variant text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        <?php if (empty($registrations)): ?>
                            <tr>
                                <td colspan="6" class="px-gutter py-xl text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[48px] text-outline block mx-auto mb-2">event_busy</span>
                                    <p class="font-label-lg">No registrations found<?= $search !== '' ? ' matching your search.' : '.' ?></p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registrations as $r): ?>
                            <tr class="hover:bg-surface-container-low/30 transition-colors">
                                <td class="px-gutter py-5 text-body-md font-body-md text-on-surface">
                                    REG<?= str_pad($r['registrationID'], 5, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td class="px-gutter py-5 text-body-md font-body-md font-semibold text-primary">
                                    <?= htmlspecialchars($r['eventTitle']) ?>
                                </td>
                                <td class="px-gutter py-5 text-body-md font-body-md text-on-surface-variant">
                                    <?= $r['eventDate'] ? date('d M Y', strtotime($r['eventDate'])) : '—' ?>
                                </td>
                                <td class="px-gutter py-5">
                                    <?= regStatusBadge($r['regStatus'] ?? '') ?>
                                </td>
                                <td class="px-gutter py-5">
                                    <?= confirmBadge($r['confirmationStatus'] ?? null) ?>
                                </td>
                                <td class="px-gutter py-5 text-right">
                                    <a href="viewRegistration.php?registrationID=<?= (int)$r['registrationID'] ?>"
                                       class="inline-flex items-center gap-1 px-4 py-2 border border-primary text-primary rounded-lg font-label-lg hover:bg-primary-container/10 transition-all">
                                        View <span class="material-symbols-outlined text-sm">keyboard_arrow_right</span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="px-gutter py-4 border-t border-outline-variant flex items-center justify-center gap-1">
                <!-- First -->
                <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars(pageUrl(['page' => 1])) ?>"
                       class="w-10 h-10 flex items-center justify-center bg-surface-container-lowest border border-outline-variant text-on-surface rounded-lg hover:bg-surface-container-low transition-all">
                        <span class="material-symbols-outlined text-md">keyboard_double_arrow_left</span>
                    </a>
                    <a href="<?= htmlspecialchars(pageUrl(['page' => $page - 1])) ?>"
                       class="w-10 h-10 flex items-center justify-center bg-surface-container-lowest border border-outline-variant text-on-surface rounded-lg hover:bg-surface-container-low transition-all">
                        <span class="material-symbols-outlined text-md">chevron_left</span>
                    </a>
                <?php endif; ?>

                <?php
                $startP = max(1, $page - 2);
                $endP   = min($totalPages, $startP + 4);
                $startP = max(1, $endP - 4);
                for ($p = $startP; $p <= $endP; $p++):
                ?>
                    <?php if ($p === $page): ?>
                        <span class="w-10 h-10 flex items-center justify-center bg-primary text-on-primary rounded-lg font-label-lg shadow-sm"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(pageUrl(['page' => $p])) ?>"
                           class="w-10 h-10 flex items-center justify-center bg-surface-container-lowest border border-outline-variant text-on-surface rounded-lg font-label-lg hover:bg-surface-container-low transition-all"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($endP < $totalPages): ?>
                    <span class="w-10 h-10 flex items-center justify-center text-outline">...</span>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= htmlspecialchars(pageUrl(['page' => $page + 1])) ?>"
                       class="w-10 h-10 flex items-center justify-center bg-surface-container-lowest border border-outline-variant text-on-surface rounded-lg font-label-lg hover:bg-surface-container-low transition-all">
                        <span class="material-symbols-outlined text-md">chevron_right</span>
                    </a>
                    <a href="<?= htmlspecialchars(pageUrl(['page' => $totalPages])) ?>"
                       class="w-10 h-10 flex items-center justify-center bg-surface-container-lowest border border-outline-variant text-on-surface rounded-lg font-label-lg hover:bg-surface-container-low transition-all">
                        <span class="material-symbols-outlined text-md">keyboard_double_arrow_right</span>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Bento Grid -->
        <div class="mt-xl grid grid-cols-1 md:grid-cols-3 gap-lg">
            <div class="bg-primary p-lg rounded-xl text-on-primary shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <span class="material-symbols-outlined">event_available</span>
                    </div>
                </div>
                <div class="text-display-lg font-display-lg"><?= str_pad($stats['active'], 2, '0', STR_PAD_LEFT) ?></div>
                <div class="text-label-lg font-label-lg opacity-80 uppercase tracking-wider">Active Registrations</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-lg rounded-xl shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-primary-fixed rounded-lg text-primary">
                        <span class="material-symbols-outlined">history</span>
                    </div>
                </div>
                <div class="text-display-lg font-display-lg text-on-surface"><?= str_pad($stats['attended'], 2, '0', STR_PAD_LEFT) ?></div>
                <div class="text-label-lg font-label-lg text-on-surface-variant uppercase tracking-wider">Total Events Attended</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-lg rounded-xl shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-error-container rounded-lg text-error">
                        <span class="material-symbols-outlined">cancel</span>
                    </div>
                </div>
                <div class="text-display-lg font-display-lg text-on-surface"><?= str_pad($stats['cancelled'], 2, '0', STR_PAD_LEFT) ?></div>
                <div class="text-label-lg font-label-lg text-on-surface-variant uppercase tracking-wider">Cancelled Requests</div>
            </div>
        </div>

    </div>
</main>

<!-- ===== Mobile Bottom Nav ===== -->
<nav class="md:hidden fixed bottom-0 left-0 w-full bg-surface-container-lowest border-t border-outline-variant flex justify-around py-3 px-gutter z-50">
    <a href="studentDashboard.php" class="flex flex-col items-center gap-1 text-on-surface-variant">
        <span class="material-symbols-outlined">dashboard</span>
        <span class="text-label-sm font-label-sm">Home</span>
    </a>
    <a href="studentRegistrationHistory.php" class="flex flex-col items-center gap-1 text-primary">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">assignment_ind</span>
        <span class="text-label-sm font-label-sm">History</span>
    </a>
    <a href="studentProfile.php" class="flex flex-col items-center gap-1 text-on-surface-variant">
        <span class="material-symbols-outlined">person</span>
        <span class="text-label-sm font-label-sm">Profile</span>
    </a>
</nav>

</body>
</html>