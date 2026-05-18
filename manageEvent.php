<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header('Location: login.php');
    exit();
}

$userID = (int) $_SESSION['userID'];
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 5;
$legacySchema = eventUsesLegacySchema($conn);

$events = [];
$totalEvents = 0;
$hasRegTable = false;

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $regCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
    $hasRegTable = $regCheck && mysqli_num_rows($regCheck) > 0;

    $where = 'WHERE 1=1';
    $types = '';
    $params = [];

    if ($search !== '') {
        $where .= ' AND e.eventTitle LIKE ?';
        $types .= 's';
        $params[] = '%' . $search . '%';
    }

    $where .= committeeStatusFilterSql($conn, $statusFilter);

    $countSql = "SELECT COUNT(*) AS total FROM event e {$where}";
    $countStmt = mysqli_prepare($conn, $countSql);

    if ($countStmt) {
        if ($types !== '') {
            mysqli_stmt_bind_param($countStmt, $types, ...$params);
        }
        mysqli_stmt_execute($countStmt);
        $countRow = mysqli_stmt_get_result($countStmt)->fetch_assoc();
        $totalEvents = (int) ($countRow['total'] ?? 0);
        mysqli_stmt_close($countStmt);
    }

    $offset = ($page - 1) * $perPage;
    $regSub = $hasRegTable
        ? '(SELECT COUNT(*) FROM event_registration er WHERE er.eventID = e.eventID)'
        : '0';

    if ($legacySchema) {
        $listSql = "
            SELECT
                e.eventID,
                e.eventTitle,
                e.eventDate,
                e.eventTime,
                e.eventVenue,
                e.maxParticipations,
                e.eventStatus,
                {$regSub} AS regCount
            FROM event e
            {$where}
            ORDER BY e.eventID DESC
            LIMIT ? OFFSET ?
        ";
    } else {
        $listSql = "
            SELECT
                e.eventID,
                e.eventTitle,
                e.eventDate,
                e.startTime,
                e.venue,
                e.maxParticipants,
                e.status,
                {$regSub} AS regCount
            FROM event e
            {$where}
            ORDER BY e.eventID DESC
            LIMIT ? OFFSET ?
        ";
    }

    $listStmt = mysqli_prepare($conn, $listSql);

    if ($listStmt) {
        $listTypes = $types . 'ii';
        $listParams = array_merge($params, [(int) $perPage, (int) $offset]);

        mysqli_stmt_bind_param($listStmt, $listTypes, ...$listParams);

        if (mysqli_stmt_execute($listStmt)) {
            $result = mysqli_stmt_get_result($listStmt);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $events[] = normalizeCommitteeEventListRow($conn, $row);
                }
                mysqli_free_result($result);
            }
        }
        mysqli_stmt_close($listStmt);
    }
}

$totalPages = max(1, (int) ceil($totalEvents / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}

$flashSuccess = $_SESSION['createEvent_success'] ?? '';
unset($_SESSION['createEvent_success']);

function buildQueryUrl(array $overrides = []): string
{
    $params = array_merge([
        'search' => $_GET['search'] ?? '',
        'status' => $_GET['status'] ?? 'all',
        'page' => $_GET['page'] ?? 1,
    ], $overrides);
    if (($params['search'] ?? '') === '') {
        unset($params['search']);
    }
    if (($params['status'] ?? 'all') === 'all') {
        unset($params['status']);
    }
    if (($params['page'] ?? 1) == 1) {
        unset($params['page']);
    }
    $query = http_build_query($params);
    return 'manageEvent.php' . ($query ? '?' . $query : '');
}

$rangeStart = $totalEvents === 0 ? 0 : (($page - 1) * $perPage) + 1;
$rangeEnd = min($page * $perPage, $totalEvents);
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Committee Event Management - FK-SCEMS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary-fixed-dim": "#b0c6ff",
                        "surface-container-high": "#dce9ff",
                        "primary-container": "#0061e0",
                        "tertiary": "#8f3200",
                        "surface-container-low": "#eff4ff",
                        "on-tertiary-container": "#ffe3d9",
                        "secondary-fixed-dim": "#c0c7cf",
                        "on-secondary-fixed-variant": "#40484e",
                        "surface-dim": "#cbdbf5",
                        "inverse-surface": "#213145",
                        "surface-variant": "#d3e4fe",
                        "inverse-primary": "#b0c6ff",
                        "error": "#ba1a1a",
                        "secondary": "#585f66",
                        "on-tertiary-fixed": "#370e00",
                        "on-surface-variant": "#424654",
                        "secondary-container": "#dce3eb",
                        "outline-variant": "#c2c6d7",
                        "on-secondary-container": "#5e656c",
                        "primary": "#004aaf",
                        "on-primary": "#ffffff",
                        "tertiary-container": "#b74200",
                        "on-primary-container": "#e2e8ff",
                        "on-error": "#ffffff",
                        "on-secondary": "#ffffff",
                        "surface-container": "#e5eeff",
                        "on-tertiary-fixed-variant": "#7f2b00",
                        "on-primary-fixed": "#001945",
                        "on-secondary-fixed": "#151c22",
                        "tertiary-fixed": "#ffdbce",
                        "secondary-fixed": "#dce3eb",
                        "error-container": "#ffdad6",
                        "on-surface": "#0b1c30",
                        "background": "#f8f9ff",
                        "on-background": "#0b1c30",
                        "surface": "#f8f9ff",
                        "inverse-on-surface": "#eaf1ff",
                        "on-primary-fixed-variant": "#00419c",
                        "outline": "#727786",
                        "on-error-container": "#93000a",
                        "surface-tint": "#0058cc",
                        "tertiary-fixed-dim": "#ffb599",
                        "surface-container-highest": "#d3e4fe",
                        "primary-fixed": "#d9e2ff",
                        "surface-bright": "#f8f9ff",
                        "surface-container-lowest": "#ffffff"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    spacing: {
                        base: "4px",
                        lg: "32px",
                        gutter: "24px",
                        xs: "8px",
                        sm: "16px",
                        xl: "48px",
                        md: "24px",
                        margin: "24px"
                    },
                    fontFamily: {
                        "display-lg": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "display-lg": ["24px", { lineHeight: "32px", fontWeight: "700" }],
                        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }],
                        "label-lg": ["14px", { lineHeight: "20px", fontWeight: "600" }],
                        "headline-md": ["18px", { lineHeight: "24px", fontWeight: "600" }],
                        "body-md": ["14px", { lineHeight: "20px", fontWeight: "400" }]
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-background text-on-background min-h-screen flex flex-col">

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
                <span class="material-symbols-outlined text-primary cursor-pointer text-[28px]">account_circle</span>
                <a href="logout.php"
                    class="bg-primary text-on-primary px-4 py-2 rounded-lg text-label-lg font-label-lg hover:bg-primary-container transition-colors">Logout</a>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden pt-20">
        <aside class="flex flex-col h-[calc(100vh-80px)] w-64 bg-surface-container-lowest border-r border-outline-variant hidden md:flex sticky top-20 shrink-0">
            <nav class="flex-1 px-2 py-4 space-y-1">
                <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group"
                    href="committeeDashboard.php">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="text-label-lg font-label-lg">Dashboard</span>
                </a>
                <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group"
                    href="manageEvent.php">
                    <span class="material-symbols-outlined">calendar_today</span>
                    <span class="text-label-lg font-label-lg">Event</span>
                </a>
                <a class="px-4 py-3 flex items-center gap-3 text-on-surface-variant hover:bg-surface-container transition-all rounded-lg group"
                    href="#">
                    <span class="material-symbols-outlined">assignment_ind</span>
                    <span class="text-label-lg font-label-lg">My Registrations</span>
                </a>
                <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group"
                    href="#">
                    <span class="material-symbols-outlined">person</span>
                    <span class="text-label-lg font-label-lg">Profile</span>
                </a>
                <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group"
                    href="#">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="text-label-lg font-label-lg">Notifications</span>
                </a>
            </nav>
            <div class="mt-auto px-2 pb-6 border-t border-outline-variant pt-4">
                <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg"
                    href="#">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="text-label-lg font-label-lg">Settings</span>
                </a>
                <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg"
                    href="#">
                    <span class="material-symbols-outlined">help</span>
                    <span class="text-label-lg font-label-lg">Help</span>
                </a>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto p-gutter lg:p-lg space-y-md">
            <?php if ($flashSuccess): ?>
                <div class="flex items-start gap-3 p-4 bg-surface-container border border-primary/20 rounded-xl text-on-surface" role="status">
                    <span class="material-symbols-outlined text-primary">check_circle</span>
                    <p class="text-body-md font-body-md"><?php echo htmlspecialchars($flashSuccess); ?></p>
                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-md mb-gutter">
                <h1 class="font-display-lg text-display-lg text-on-surface">Committee Event Management</h1>
                <button type="button" onclick="window.location.href='createEvent.php'"
                    class="bg-primary text-on-primary px-lg py-sm rounded-lg flex items-center gap-xs font-label-lg hover:brightness-110 shadow-sm transition-all active:scale-95">
                    <span class="material-symbols-outlined">add</span>
                    Add New Event
                </button>
            </div>

            <form method="GET" action="manageEvent.php" id="eventFilterForm" class="bg-surface-container-lowest border border-outline-variant p-gutter rounded-xl shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-md items-end">
                    <div class="md:col-span-5 flex flex-col gap-xs">
                        <label class="font-label-lg text-on-surface-variant" for="search">Search Event</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-outline">search</span>
                            <input class="w-full pl-xl pr-md py-sm bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary font-body-md"
                                id="search" name="search" placeholder="Search event title..." type="text"
                                value="<?php echo htmlspecialchars($search); ?>" />
                        </div>
                    </div>
                    <div class="md:col-span-4 flex flex-col gap-xs">
                        <label class="font-label-lg text-on-surface-variant" for="status">Filter Status</label>
                        <select class="w-full px-md py-sm bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary font-body-md appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center] bg-no-repeat"
                            id="status" name="status">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <a href="manageEvent.php"
                            class="block w-full px-md py-sm border border-outline-variant rounded-lg font-label-lg text-primary hover:bg-surface-container-low transition-colors text-center">Clear Filters</a>
                    </div>
                </div>
            </form>

            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr>
                                <th class="px-md py-sm font-label-lg text-on-surface-variant">Event ID</th>
                                <th class="px-md py-sm font-label-lg text-on-surface-variant">Title</th>
                                <th class="px-md py-sm font-label-lg text-on-surface-variant">Date</th>
                                <th class="px-md py-sm font-label-lg text-on-surface-variant">Venue</th>
                                <th class="px-md py-sm font-label-lg text-on-surface-variant text-center">Slot</th>
                                <th class="px-md py-sm font-label-lg text-on-surface-variant">Status</th>
                                <th class="px-md py-sm font-label-lg text-on-surface-variant text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="7" class="px-md py-xl text-center font-body-md text-on-surface-variant">
                                        No events found.
                                        <?php if ($search === '' && $statusFilter === 'all'): ?>
                                            <a href="createEvent.php" class="text-primary font-label-lg hover:underline">Create your first event</a>.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <?php
                                    $badge = committeeEventDisplayStatus($event['eventStatus'], $event['eventDate']);
                                    $venueDisplay = htmlspecialchars($event['eventVenue']);
                                    
                                    $max = $event['maxParticipations'];
                                    $regCount = (int) $event['regCount'];
                                    $slotDisplay = $max
                                        ? $regCount . '/' . (int) $max
                                        : $regCount . '/—';
                                    $eventCode = 'EVT' . str_pad((string) $event['eventID'], 4, '0', STR_PAD_LEFT);
                                    ?>
                                    <tr class="hover:bg-surface-container-low transition-colors">
                                        <td class="px-md py-md font-body-md text-on-surface"><?php echo htmlspecialchars($eventCode); ?></td>
                                        <td class="px-md py-md font-label-lg text-primary"><?php echo htmlspecialchars($event['eventTitle']); ?></td>
                                        <td class="px-md py-md font-body-md text-on-surface-variant"><?php echo htmlspecialchars(date('d M Y', strtotime($event['eventDate']))); ?></td>
                                        <td class="px-md py-md font-body-md text-on-surface-variant"><?php echo $venueDisplay; ?></td>
                                        <td class="px-md py-md font-body-md text-on-surface-variant text-center"><?php echo htmlspecialchars($slotDisplay); ?></td>
                                        <td class="px-md py-md">
                                            <span class="inline-flex items-center px-xs py-0.5 rounded-full text-[12px] font-bold <?php echo $badge['class']; ?>">
                                                <?php echo htmlspecialchars($badge['label']); ?>
                                            </span>
                                        </td>
                                        <td class="px-md py-md text-right">
                                            <div class="flex items-center justify-end gap-xs">
                                                <button type="button"
                                                    class="p-xs hover:bg-surface-variant rounded-lg text-primary transition-colors"
                                                    title="View event"
                                                    onclick="window.location.href='viewEvent.php?id=<?php echo (int) $event['eventID']; ?>'">
                                                    <span class="material-symbols-outlined">visibility</span>
                                                </button>
                                                <button type="button"
                                                    class="p-xs hover:bg-surface-variant rounded-lg text-outline transition-colors"
                                                    title="Edit event"
                                                    onclick="window.location.href='editEvent.php?id=<?php echo (int) $event['eventID']; ?>'">
                                                    <span class="material-symbols-outlined">edit</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-md bg-surface-container-low border-t border-outline-variant flex items-center justify-between">
                        <span class="font-body-md text-on-surface-variant">
                            <?php if ($totalEvents > 0): ?>
                                Showing <?php echo $rangeStart; ?>-<?php echo $rangeEnd; ?> of <?php echo $totalEvents; ?> event<?php echo $totalEvents === 1 ? '' : 's'; ?>
                            <?php else: ?>
                                Showing 0 events
                            <?php endif; ?>
                        </span>
                        <?php if ($totalEvents > 0 && $totalPages > 1): ?>
                            <div class="flex items-center gap-xs">
                                <?php if ($page > 1): ?>
                                    <a href="<?php echo htmlspecialchars(buildQueryUrl(['page' => $page - 1])); ?>"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-bright transition-colors text-outline">
                                        <span class="material-symbols-outlined">chevron_left</span>
                                    </a>
                                <?php else: ?>
                                    <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-outline opacity-40 cursor-not-allowed">
                                        <span class="material-symbols-outlined">chevron_left</span>
                                    </span>
                                <?php endif; ?>

                                <?php
                                $window = 3;
                                $startPage = max(1, $page - 1);
                                $endPage = min($totalPages, $startPage + $window - 1);
                                $startPage = max(1, $endPage - $window + 1);

                                if ($startPage > 1): ?>
                                    <a href="<?php echo htmlspecialchars(buildQueryUrl(['page' => 1])); ?>"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-bright transition-colors font-label-lg">1</a>
                                    <?php if ($startPage > 2): ?>
                                        <span class="px-xs text-outline">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                                    <?php if ($p === $page): ?>
                                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary text-on-primary font-label-lg"><?php echo $p; ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars(buildQueryUrl(['page' => $p])); ?>"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-bright transition-colors font-label-lg"><?php echo $p; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <span class="px-xs text-outline">...</span>
                                    <?php endif; ?>
                                    <a href="<?php echo htmlspecialchars(buildQueryUrl(['page' => $totalPages])); ?>"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-bright transition-colors font-label-lg"><?php echo $totalPages; ?></a>
                                <?php endif; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="<?php echo htmlspecialchars(buildQueryUrl(['page' => $page + 1])); ?>"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-bright transition-colors text-outline">
                                        <span class="material-symbols-outlined">chevron_right</span>
                                    </a>
                                <?php else: ?>
                                    <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-outline opacity-40 cursor-not-allowed">
                                        <span class="material-symbols-outlined">chevron_right</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($totalEvents > 0): ?>
                            <div class="flex items-center gap-xs">
                                <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-outline opacity-40 cursor-not-allowed">
                                    <span class="material-symbols-outlined">chevron_left</span>
                                </button>
                                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary text-on-primary font-label-lg">1</span>
                                <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-outline opacity-40 cursor-not-allowed">
                                    <span class="material-symbols-outlined">chevron_right</span>
                                </button>
                            </div>
                        <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        (function () {
            const form = document.getElementById('eventFilterForm');
            const status = document.getElementById('status');
            const search = document.getElementById('search');

            if (status && form) {
                status.addEventListener('change', function () {
                    form.submit();
                });
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