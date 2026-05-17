<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/browse_event_helpers.php';
require_once __DIR__ . '/includes/event_schema.php';

if (!isset($_SESSION['userID']) || !in_array($_SESSION['type'] ?? '', ['student', 'committee'], true)) {
    header('Location: login.php');
    exit();
}

$userID = (int) $_SESSION['userID'];
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
$roleLabel = ($_SESSION['type'] ?? '') === 'committee' ? 'Committee Club' : 'Student';

$search = trim($_GET['search'] ?? '');
$clubFilter = trim($_GET['club'] ?? 'all');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 3;
$today = date('Y-m-d');

$flashSuccess = $_SESSION['browse_success'] ?? '';
$flashError = $_SESSION['browse_error'] ?? '';
unset($_SESSION['browse_success'], $_SESSION['browse_error']);

$events = [];
$clubs = [];
$totalEvents = 0;
$hasRegTable = false;
$hasWaitTable = false;

$legacySchema = eventUsesLegacySchema($conn);

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $regCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
    $hasRegTable = $regCheck && mysqli_num_rows($regCheck) > 0;
    $waitCheck = mysqli_query($conn, "SHOW TABLES LIKE 'waiting_list'");
    $hasWaitTable = $waitCheck && mysqli_num_rows($waitCheck) > 0;

    $browseWhere = browseStudentEventsWhere($conn, $today, 'e');
    $clubJoin = $legacySchema ? ' INNER JOIN club c ON e.clubID = c.clubID ' : '';

    if ($legacySchema) {
        $clubResult = mysqli_query($conn, "
            SELECT DISTINCT c.clubID, c.clubName, c.clubCategory
            FROM event e
            INNER JOIN club c ON e.clubID = c.clubID
            WHERE {$browseWhere}
            ORDER BY c.clubName
        ");
        if ($clubResult) {
            while ($row = mysqli_fetch_assoc($clubResult)) {
                $clubs[] = [
                    'id' => (int) $row['clubID'],
                    'name' => $row['clubName'],
                    'category' => $row['clubCategory'],
                ];
            }
        }
    } else {
        $clubResult = mysqli_query($conn, "
            SELECT DISTINCT eventCategory FROM event e
            WHERE {$browseWhere}
            ORDER BY eventCategory
        ");
        if ($clubResult) {
            while ($row = mysqli_fetch_assoc($clubResult)) {
                if ($row['eventCategory'] !== '') {
                    $clubs[] = [
                        'id' => $row['eventCategory'],
                        'name' => clubDisplayName($row['eventCategory']),
                        'category' => $row['eventCategory'],
                    ];
                }
            }
        }
    }

    $where = "WHERE {$browseWhere}";
    $types = '';
    $params = [];

    if ($search !== '') {
        $where .= ' AND e.eventTitle LIKE ?';
        $types .= 's';
        $params[] = '%' . $search . '%';
    }

    if ($clubFilter !== 'all' && $clubFilter !== '') {
        if ($legacySchema) {
            $where .= ' AND e.clubID = ?';
            $types .= 'i';
            $params[] = (int) $clubFilter;
        } else {
            $where .= ' AND e.eventCategory = ?';
            $types .= 's';
            $params[] = $clubFilter;
        }
    }

    $countSql = "SELECT COUNT(*) AS total FROM event e {$clubJoin} {$where}";
    $countStmt = mysqli_prepare($conn, $countSql);
    if ($countStmt) {
        if ($types !== '') {
            mysqli_stmt_bind_param($countStmt, $types, ...$params);
        }
        mysqli_stmt_execute($countStmt);
        $totalEvents = (int) (mysqli_stmt_get_result($countStmt)->fetch_assoc()['total'] ?? 0);
        mysqli_stmt_close($countStmt);
    }

    $offset = ($page - 1) * $perPage;
    $regSub = $hasRegTable
        ? '(SELECT COUNT(*) FROM event_registration er WHERE er.eventID = e.eventID)'
        : '0';
    $userRegSub = $hasRegTable
        ? "(SELECT registrationID FROM event_registration ur WHERE ur.eventID = e.eventID AND ur.userID = {$userID} LIMIT 1)"
        : 'NULL';
    $userWaitSub = $hasWaitTable
        ? "(SELECT waitingID FROM waiting_list uw WHERE uw.eventID = e.eventID AND uw.userID = {$userID} LIMIT 1)"
        : 'NULL';

    $extraCols = $legacySchema ? ', c.clubName, c.clubCategory' : '';
    $orderBy = $legacySchema ? 'e.eventDate ASC, e.eventTime ASC' : 'e.eventDate ASC, e.startTime ASC';

    $listSql = "
        SELECT e.*, {$regSub} AS regCount, {$userRegSub} AS userRegistrationID, {$userWaitSub} AS userWaitingID{$extraCols}
        FROM event e
        {$clubJoin}
        {$where}
        ORDER BY {$orderBy}
        LIMIT ? OFFSET ?
    ";

    $listStmt = mysqli_prepare($conn, $listSql);
    if ($listStmt) {
        $listTypes = $types . 'ii';
        $listParams = array_merge($params, [$perPage, $offset]);
        mysqli_stmt_bind_param($listStmt, $listTypes, ...$listParams);
        mysqli_stmt_execute($listStmt);
        $result = mysqli_stmt_get_result($listStmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = normalizeEventRow($conn, $row);
        }
        mysqli_stmt_close($listStmt);
    }
}

$totalPages = max(1, (int) ceil($totalEvents / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$rangeStart = $totalEvents === 0 ? 0 : (($page - 1) * $perPage) + 1;
$rangeEnd = min($page * $perPage, $totalEvents);
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Browse Upcoming Events - FK-SCEMS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "tertiary-fixed-dim": "#ffb599",
                        "on-tertiary-fixed": "#370e00",
                        "on-primary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "surface": "#f8f9ff",
                        "on-secondary-fixed-variant": "#40484e",
                        "surface-dim": "#cbdbf5",
                        "surface-tint": "#0058cc",
                        "on-primary-fixed": "#001945",
                        "primary": "#004aaf",
                        "error": "#ba1a1a",
                        "inverse-on-surface": "#eaf1ff",
                        "primary-fixed-dim": "#b0c6ff",
                        "secondary-container": "#dce3eb",
                        "secondary-fixed-dim": "#c0c7cf",
                        "on-secondary-container": "#5e656c",
                        "outline": "#727786",
                        "surface-container": "#e5eeff",
                        "on-error": "#ffffff",
                        "inverse-surface": "#213145",
                        "on-tertiary-fixed-variant": "#7f2b00",
                        "on-tertiary": "#ffffff",
                        "on-primary-fixed-variant": "#00419c",
                        "tertiary-container": "#b74200",
                        "primary-container": "#0061e0",
                        "error-container": "#ffdad6",
                        "surface-container-highest": "#d3e4fe",
                        "tertiary-fixed": "#ffdbce",
                        "secondary-fixed": "#dce3eb",
                        "surface-bright": "#f8f9ff",
                        "on-surface": "#0b1c30",
                        "on-secondary": "#ffffff",
                        "on-tertiary-container": "#ffe3d9",
                        "primary-fixed": "#d9e2ff",
                        "outline-variant": "#c2c6d7",
                        "tertiary": "#8f3200",
                        "background": "#f8f9ff",
                        "on-background": "#0b1c30",
                        "surface-container-high": "#dce9ff",
                        "on-primary-container": "#e2e8ff",
                        "inverse-primary": "#b0c6ff",
                        "surface-container-low": "#eff4ff",
                        "on-surface-variant": "#424654",
                        "on-secondary-fixed": "#151c22",
                        "surface-variant": "#d3e4fe",
                        "secondary": "#585f66",
                        "on-error-container": "#93000a"
                    },
                    borderRadius: { DEFAULT: "0.125rem", lg: "0.25rem", xl: "0.5rem", full: "0.75rem" },
                    spacing: { lg: "32px", gutter: "24px", xl: "48px", xs: "8px", sm: "16px", md: "24px", margin: "24px", base: "4px" },
                    fontFamily: {
                        "label-sm": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "display-lg-mobile": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }],
                        "label-lg": ["14px", { lineHeight: "20px", fontWeight: "600" }],
                        "headline-md": ["18px", { lineHeight: "24px", fontWeight: "600" }],
                        "display-lg-mobile": ["20px", { lineHeight: "28px", fontWeight: "700" }],
                        "display-lg": ["24px", { lineHeight: "32px", fontWeight: "700" }],
                        "body-md": ["14px", { lineHeight: "20px", fontWeight: "400" }]
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-background text-on-background font-body-md text-body-md overflow-x-hidden">

    <header class="bg-surface docked full-width top-0 z-50 border-b border-outline-variant flex justify-between items-center w-full px-md h-16 fixed">
        <div class="flex items-center gap-xs">
            <img alt="UMPSA Logo" class="h-10 w-10 object-contain" src="images/umpsaLogo.png"
                onerror="this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuBdsmpzRrxLef1ebW_dHLGGXmAsNqQjTLWO5g649S2ZMU8KHmS96IOJFkW641Me8aPrwqwYImDCGsgQTkfTrowrB1GCYZtVcsTUD_0BS0DLa_TdCsxvBcG9qwLSPwxY2inQqTr8zQG8NPAqXR9360TP_m5ti6Ogv-9qTg0QDyiMIYvj_zyDBSC_zCC2k2i9nzC5YMrll5wicmgIZvwlfEzyHa7ytDrbhYDhV0Jcz9uApghTyCwHoCiAU9fzYpN-i7jE2LJLB71meMU'" />
            <span class="text-headline-md font-headline-md font-bold text-primary">FK-SCEMS</span>
        </div>
        <div class="flex items-center gap-md">
            <div class="hidden md:flex flex-col items-end">
                <span class="font-label-lg text-label-lg text-on-surface"><?php echo $userName; ?></span>
                <span class="text-[11px] text-on-surface-variant"><?php echo htmlspecialchars($roleLabel); ?></span>
            </div>
            <div class="flex items-center gap-sm">
                <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center overflow-hidden border border-outline-variant">
                    <span class="material-symbols-outlined text-on-surface-variant">account_circle</span>
                </div>
                <a href="logout.php"
                    class="flex items-center gap-xs px-sm py-xs bg-[#00796B] text-white rounded-lg hover:opacity-90 transition-all font-label-lg text-label-lg">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">logout</span>
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
                <a class="flex items-center gap-sm px-sm py-sm text-on-surface-variant hover:bg-surface-container-low rounded-lg transition-all active:scale-95 duration-150 font-label-lg text-label-lg"
                    href="studentDashboard.php">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
                <a class="flex items-center gap-sm px-sm py-sm bg-secondary-container text-on-secondary-container rounded-lg transition-all active:scale-95 duration-150 font-label-lg text-label-lg"
                    href="browseEvents.php">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">calendar_month</span>
                    Browse Events
                </a>
                <a class="flex items-center gap-sm px-sm py-sm text-on-surface-variant hover:bg-surface-container-low rounded-lg transition-all active:scale-95 duration-150 font-label-lg text-label-lg"
                    href="studentRegistrationHistory.php">
                    <span class="material-symbols-outlined">history</span>
                    My Registration History
                </a>
                <a class="flex items-center gap-sm px-sm py-sm text-on-surface-variant hover:bg-surface-container-low rounded-lg transition-all active:scale-95 duration-150 font-label-lg text-label-lg"
                    href="#">
                    <span class="material-symbols-outlined">person</span>
                    My Profile
                </a>
            </nav>
            <div class="mt-auto pt-md border-t border-outline-variant text-[11px] text-on-surface-variant text-center">
                © <?php echo date('Y'); ?> FK-SCEMS UMPSA
            </div>
        </aside>

        <main class="flex-1 ml-64 p-lg bg-background">
            <div class="max-w-6xl mx-auto">
                <?php if ($flashSuccess): ?>
                    <div class="mb-md flex items-start gap-2 p-md bg-surface-container border border-primary/30 rounded-xl text-on-surface" role="status">
                        <span class="material-symbols-outlined text-primary">check_circle</span>
                        <p class="text-body-md"><?php echo htmlspecialchars($flashSuccess); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <div class="mb-md flex items-start gap-2 p-md bg-error-container border border-error/20 rounded-xl text-on-error-container" role="alert">
                        <span class="material-symbols-outlined text-error">error</span>
                        <p class="text-body-md"><?php echo htmlspecialchars($flashError); ?></p>
                    </div>
                <?php endif; ?>

                <header class="mb-lg">
                    <h1 class="text-display-lg font-display-lg text-on-surface mb-sm">Browse Upcoming Events</h1>
                    <div class="w-20 h-1.5 bg-primary rounded-full"></div>
                </header>

                <form method="GET" action="browseEvents.php" id="browseFilterForm"
                    class="bg-white p-md rounded-xl border border-outline-variant mb-lg shadow-sm flex flex-col md:flex-row md:items-end gap-md">
                    <div class="flex-1">
                        <label class="block text-label-lg font-bold text-on-surface mb-xs" for="search">Search Event</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                            <input class="w-full pl-xl pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all"
                                id="search" name="search" placeholder="Search event title..." type="text"
                                value="<?php echo htmlspecialchars($search); ?>" />
                        </div>
                    </div>
                    <div class="w-full md:w-64">
                        <label class="block text-label-lg font-bold text-on-surface mb-xs" for="club">Filter Club</label>
                        <div class="relative">
                            <select class="w-full pl-sm pr-xl py-sm bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none appearance-none transition-all"
                                id="club" name="club">
                                <option value="all" <?php echo $clubFilter === 'all' ? 'selected' : ''; ?>>All Clubs</option>
                                <?php foreach ($clubs as $club): ?>
                                    <?php
                                    $clubValue = is_array($club) ? (string) $club['id'] : (string) $club;
                                    $clubLabel = is_array($club) ? $club['name'] : clubDisplayName($club);
                                    ?>
                                    <option value="<?php echo htmlspecialchars($clubValue); ?>" <?php echo (string) $clubFilter === $clubValue ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($clubLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="material-symbols-outlined absolute right-sm top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">expand_more</span>
                        </div>
                    </div>
                    <div class="w-full md:w-auto">
                        <a href="browseEvents.php"
                            class="block w-full md:w-auto px-md py-sm border border-outline-variant rounded-lg font-label-lg text-primary hover:bg-surface-container-low transition-colors text-center">Clear</a>
                    </div>
                </form>

                <?php if (!$hasRegTable): ?>
                    <p class="mb-md text-sm text-on-surface-variant bg-surface-container-low p-sm rounded-lg border border-outline-variant">
                        Registration tables are missing. Import <strong>setup.sql</strong> in phpMyAdmin to enable Register and Waiting List.
                    </p>
                <?php endif; ?>

                <div class="space-y-md">
                    <?php if (empty($events)): ?>
                        <div class="bg-white p-xl rounded-xl border border-outline-variant text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[48px] text-outline mb-sm">event_busy</span>
                            <p class="font-label-lg">No upcoming events found.</p>
                            <?php if ($search !== '' || $clubFilter !== 'all'): ?>
                                <a href="browseEvents.php" class="text-primary font-label-lg hover:underline mt-sm inline-block">Clear filters</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($events as $event):
                            $regCount = (int) $event['regCount'];
                            $maxSlots = $event['maxParticipants'] !== null ? (int) $event['maxParticipants'] : null;
                            $isFull = $maxSlots !== null && $regCount >= $maxSlots;
                            $available = $maxSlots !== null ? max(0, $maxSlots - $regCount) : null;
                            $userRegistered = !empty($event['userRegistrationID']);
                            $userWaiting = !empty($event['userWaitingID']);
                            $venue = htmlspecialchars($event['venue']);
                            if (!empty($event['locationRoom'])) {
                                $venue .= ', ' . htmlspecialchars($event['locationRoom']);
                            }
                            $clubName = clubDisplayName($event['eventCategory'], $event['clubName'] ?? null);
                            $clubIconName = clubIcon($event['eventCategory']);
                            $merit = meritValue($event['eventCategory']);
                            $slotClass = availableSlotClass($regCount, $maxSlots);
                            $poster = eventPosterUrl($event);
                            $eventId = (int) $event['eventID'];
                            ?>
                            <div class="bg-white p-md rounded-xl border border-outline-variant shadow-sm flex flex-col md:flex-row gap-md hover:border-primary transition-colors">
                                <div class="w-full md:w-48 h-48 rounded-lg overflow-hidden shrink-0 border border-outline-variant">
                                    <img class="w-full h-full object-cover" alt=""
                                        src="<?php echo htmlspecialchars($poster); ?>" />
                                </div>
                                <div class="flex-1 flex flex-col justify-between">
                                    <div>
                                        <h2 class="text-headline-md font-headline-md text-on-surface mb-xs"><?php echo htmlspecialchars($event['eventTitle']); ?></h2>
                                        <div class="flex items-center gap-xs text-primary font-bold mb-md">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;"><?php echo $clubIconName; ?></span>
                                            <span class="text-label-lg"><?php echo htmlspecialchars($clubName); ?></span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-sm gap-x-lg text-on-surface-variant">
                                            <div class="flex items-center gap-xs">
                                                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                                                <span><?php echo formatEventDate($event['eventDate']); ?></span>
                                            </div>
                                            <div class="flex items-center gap-xs">
                                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                                                <span><?php echo formatEventTime($event['startTime']); ?></span>
                                            </div>
                                            <div class="flex items-center gap-xs">
                                                <span class="material-symbols-outlined text-[18px]">location_on</span>
                                                <span><?php echo $venue; ?></span>
                                            </div>
                                            <div class="flex items-center gap-xs">
                                                <span class="material-symbols-outlined text-[18px]">local_activity</span>
                                                <span>Merit Value: <span class="font-bold text-on-surface"><?php echo $merit; ?></span>
                                                    <?php if ($maxSlots !== null): ?>
                                                        | Available Slot: <span class="<?php echo $slotClass; ?>"><?php echo $available; ?> / <?php echo $maxSlots; ?></span>
                                                    <?php else: ?>
                                                        | <span class="font-bold text-on-surface">Open slots</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-sm mt-md">
                                        <a href="viewEvent.php?id=<?php echo $eventId; ?>"
                                            class="px-lg py-sm border border-primary text-primary font-bold rounded-lg hover:bg-surface-container-low transition-all">View Detail</a>
                                        <?php if ($userRegistered): ?>
                                            <span class="px-lg py-sm bg-surface-container-low text-primary font-bold rounded-lg">Registered</span>
                                        <?php elseif ($userWaiting): ?>
                                            <span class="px-lg py-sm bg-surface-container-low text-primary font-bold rounded-lg">On Waiting List</span>
                                        <?php elseif ($isFull && $hasWaitTable): ?>
                                            <form method="POST" action="registerEvent_process.php">
                                                <input type="hidden" name="eventID" value="<?php echo $eventId; ?>" />
                                                <input type="hidden" name="action" value="waitlist" />
                                                <button type="submit" class="px-lg py-sm bg-[#00796B] text-white font-bold rounded-lg hover:opacity-90 transition-all">Join Waiting List</button>
                                            </form>
                                        <?php elseif (!$isFull && $hasRegTable): ?>
                                            <form method="POST" action="registerEvent_process.php">
                                                <input type="hidden" name="eventID" value="<?php echo $eventId; ?>" />
                                                <input type="hidden" name="action" value="register" />
                                                <button type="submit" class="px-lg py-sm bg-[#00796B] text-white font-bold rounded-lg hover:opacity-90 transition-all">Register</button>
                                            </form>
                                        <?php elseif ($isFull): ?>
                                            <span class="px-lg py-sm bg-error-container text-on-error-container font-bold rounded-lg">Full</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($totalEvents > 0): ?>
                    <div class="mt-xl flex flex-col sm:flex-row justify-center items-center gap-md">
                        <?php if ($totalPages > 1): ?>
                            <div class="flex justify-center items-center gap-xs">
                                <?php if ($page > 1): ?>
                                    <a href="<?php echo htmlspecialchars(browseQueryUrl(['page' => $page - 1])); ?>"
                                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-all">
                                        <span class="material-symbols-outlined">chevron_left</span>
                                    </a>
                                <?php else: ?>
                                    <span class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant opacity-40 cursor-not-allowed">
                                        <span class="material-symbols-outlined">chevron_left</span>
                                    </span>
                                <?php endif; ?>

                                <?php
                                $window = 3;
                                $startPage = max(1, $page - 1);
                                $endPage = min($totalPages, $startPage + $window - 1);
                                $startPage = max(1, $endPage - $window + 1);
                                for ($p = $startPage; $p <= $endPage; $p++):
                                    if ($p === $page): ?>
                                        <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-white font-bold"><?php echo $p; ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars(browseQueryUrl(['page' => $p])); ?>"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-all"><?php echo $p; ?></a>
                                    <?php endif;
                                endfor;
                                if ($endPage < $totalPages): ?>
                                    <span class="px-xs text-outline">...</span>
                                    <a href="<?php echo htmlspecialchars(browseQueryUrl(['page' => $totalPages])); ?>"
                                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-all"><?php echo $totalPages; ?></a>
                                <?php endif; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="<?php echo htmlspecialchars(browseQueryUrl(['page' => $page + 1])); ?>"
                                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-all">
                                        <span class="material-symbols-outlined">chevron_right</span>
                                    </a>
                                <?php else: ?>
                                    <span class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant opacity-40 cursor-not-allowed">
                                        <span class="material-symbols-outlined">chevron_right</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <p class="text-body-md text-on-surface-variant sm:ml-md">
                            Showing <?php echo $rangeStart; ?>-<?php echo $rangeEnd; ?> of <?php echo $totalEvents; ?> events
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        (function () {
            const form = document.getElementById('browseFilterForm');
            const club = document.getElementById('club');
            const search = document.getElementById('search');
            if (club && form) {
                club.addEventListener('change', function () { form.submit(); });
            }
            if (search && form) {
                search.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        form.submit();
                    }
                });
            }
        })();
    </script>

</body>

</html>
