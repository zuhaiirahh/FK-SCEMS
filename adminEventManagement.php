<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';
require_once __DIR__ . '/includes/admin_layout.php';

admin_require_auth();

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$legacy = eventUsesLegacySchema($conn);

$flashSuccess = $_SESSION['admin_event_success'] ?? '';
$flashError = $_SESSION['admin_event_error'] ?? '';
unset($_SESSION['admin_event_success'], $_SESSION['admin_event_error']);

function adminEventFilterSql(mysqli $conn, string $filter): string
{
    $today = date('Y-m-d');
    $legacy = eventUsesLegacySchema($conn);
    $col = $legacy ? 'e.eventStatus' : 'e.status';

    switch ($filter) {
        case 'pending':
            if ($legacy) {
                return " AND LOWER({$col}) IN ('pending', 'pending_approval')";
            }
            return " AND {$col} = 'pending_approval'";
        case 'active':
            if ($legacy) {
                return " AND LOWER({$col}) IN ('active', 'approved', 'open') AND e.eventDate >= '{$today}'";
            }
            return " AND {$col} = 'approved' AND e.eventDate >= '{$today}'";
        case 'draft':
            if ($legacy) {
                return " AND LOWER({$col}) = 'draft'";
            }
            return " AND {$col} = 'draft'";
        case 'closed':
            if ($legacy) {
                return " AND (LOWER({$col}) IN ('rejected', 'closed') OR (LOWER({$col}) IN ('active', 'approved', 'open') AND e.eventDate < '{$today}'))";
            }
            return " AND ({$col} = 'rejected' OR ({$col} = 'approved' AND e.eventDate < '{$today}'))";
        case 'cancelled':
            return " AND LOWER({$col}) IN ('cancelled', 'canceled')";
        default:
            return '';
    }
}

function adminEventRowNeedsReview(string $status): bool
{
    $key = strtolower(trim($status));
    return in_array($key, ['pending', 'pending_approval'], true);
}

$events = [];
$totalEvents = 0;
$pendingCount = 0;

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $statusCol = $legacy ? 'eventStatus' : 'status';
    $pendingWhere = $legacy
        ? "LOWER({$statusCol}) IN ('pending', 'pending_approval')"
        : "{$statusCol} = 'pending_approval'";
    $pendingRes = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event WHERE {$pendingWhere}");
    if ($pendingRes && ($pr = mysqli_fetch_assoc($pendingRes))) {
        $pendingCount = (int) $pr['c'];
    }

    $where = 'WHERE 1=1';
    $types = '';
    $params = [];

    if ($search !== '') {
        $where .= ' AND e.eventTitle LIKE ?';
        $types .= 's';
        $params[] = '%' . $search . '%';
    }
    $where .= adminEventFilterSql($conn, $statusFilter);

    $countSql = "SELECT COUNT(*) AS total FROM event e {$where}";
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
    $regCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
    $hasReg = $regCheck && mysqli_num_rows($regCheck) > 0;
    $regSub = $hasReg
        ? '(SELECT COUNT(*) FROM event_registration er WHERE er.eventID = e.eventID)'
        : '0';

    if ($legacy) {
        $clubTable = clubTableName($conn);
        $listSql = "
            SELECT e.eventID, e.eventTitle, e.eventDate, e.eventTime, e.eventVenue,
                   e.maxParticipations, e.eventStatus, c.clubName, {$regSub} AS regCount
            FROM event e
            LEFT JOIN `{$clubTable}` c ON e.clubID = c.clubID
            {$where}
            ORDER BY e.eventID DESC
            LIMIT ? OFFSET ?
        ";
    } else {
        $listSql = "
            SELECT e.eventID, e.eventTitle, e.eventDate, e.startTime, e.venue,
                   e.maxParticipants, e.status, e.eventCategory, {$regSub} AS regCount
            FROM event e
            {$where}
            ORDER BY e.eventID DESC
            LIMIT ? OFFSET ?
        ";
    }

    $listStmt = mysqli_prepare($conn, $listSql);
    if ($listStmt) {
        $listTypes = $types . 'ii';
        $listParams = array_merge($params, [$perPage, $offset]);
        mysqli_stmt_bind_param($listStmt, $listTypes, ...$listParams);
        mysqli_stmt_execute($listStmt);
        $result = mysqli_stmt_get_result($listStmt);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                if (!$legacy) {
                    $row = normalizeCommitteeEventListRow($conn, $row);
                    $row['clubName'] = $row['eventCategory'] ?? '—';
                }
                $events[] = $row;
            }
            mysqli_free_result($result);
        }
        mysqli_stmt_close($listStmt);
    }
}

$totalPages = max(1, (int) ceil($totalEvents / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}

function adminEventQueryUrl(array $overrides = []): string
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
    $q = http_build_query($params);
    return 'adminEventManagement.php' . ($q ? '?' . $q : '');
}

admin_page_start('Event Management', 'events');
?>
<style>
    .admin-page-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
    .admin-page-header p { color: #666; margin-top: 6px; }
    .admin-toolbar { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; margin-bottom: 20px; }
    .admin-search { position: relative; flex: 1; min-width: 220px; max-width: 360px; }
    .admin-search input { width: 100%; padding: 10px 12px 10px 38px; border: 1px solid #ddd; border-radius: 6px; }
    .admin-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }
    .admin-filter select { padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; min-width: 160px; }
    .btn-admin { padding: 10px 16px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; font-size: 0.9rem; }
    .btn-admin-primary { background: #008b8b; color: #fff; }
    .btn-admin-primary:hover { background: #007070; }
    .btn-admin-outline { background: #fff; color: #008b8b; border: 1px solid #008b8b; }
    .btn-admin-sm { padding: 6px 12px; font-size: 0.8rem; }
    .btn-approve { background: #28a745; color: #fff; }
    .btn-reject { background: #dc3545; color: #fff; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .pending-pill { background: #fff3cd; color: #856404; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
    .action-forms { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
</style>

<div class="admin-page-header">
    <div>
        <h2>Event Management</h2>
        <p>Review committee events, approve submissions, and manage visibility for students.</p>
        <?php if ($pendingCount > 0): ?>
            <p class="pending-pill" style="display:inline-block;margin-top:10px;">
                <i class="fas fa-clock"></i> <?php echo $pendingCount; ?> pending approval
            </p>
        <?php endif; ?>
    </div>
    <a href="adminDashboard.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flashSuccess); ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert-error" role="alert"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?></div>
<?php endif; ?>

<form method="GET" action="adminEventManagement.php" class="admin-toolbar">
    <div class="admin-search">
        <i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Search event title..." value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <div class="admin-filter">
        <label for="status" style="display:block;font-size:0.8rem;color:#666;margin-bottom:4px;">Status</label>
        <select id="status" name="status" onchange="this.form.submit()">
            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All</option>
            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending Approval</option>
            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
            <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Closed / Rejected</option>
            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
    </div>
    <?php if ($search !== '' || $statusFilter !== 'all'): ?>
        <a href="adminEventManagement.php" class="btn-admin btn-admin-outline">Clear</a>
    <?php endif; ?>
</form>

<section class="table-section">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Event ID</th>
                    <th>Title</th>
                    <th>Club / Category</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Slots</th>
                    <th>Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:30px;color:#666;">
                            No events found.
                            <a href="adminDashboard.php">Return to dashboard</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event):
                        $status = $event['eventStatus'] ?? '';
                        $badge = committeeEventDisplayStatus($status, $event['eventDate']);
                        $venue = htmlspecialchars($event['eventVenue'] ?? '—');
                        $max = $event['maxParticipations'] ?? null;
                        $regCount = (int) ($event['regCount'] ?? 0);
                        $slots = $max ? $regCount . '/' . (int) $max : $regCount . '/—';
                        $eventCode = 'EVT' . str_pad((string) $event['eventID'], 4, '0', STR_PAD_LEFT);
                        $needsReview = adminEventRowNeedsReview($status);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($eventCode); ?></td>
                            <td><strong><?php echo htmlspecialchars($event['eventTitle']); ?></strong></td>
                            <td><?php echo htmlspecialchars($event['clubName'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($event['eventDate']))); ?></td>
                            <td><?php echo $venue; ?></td>
                            <td><?php echo htmlspecialchars($slots); ?></td>
                            <td>
                                <span style="font-size:0.85rem;font-weight:bold;padding:4px 10px;border-radius:4px;" class="<?php echo htmlspecialchars($badge['class']); ?>">
                                    <?php echo htmlspecialchars($badge['label']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-forms">
                                    <a href="viewEventCommittee.php?id=<?php echo (int) $event['eventID']; ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($needsReview): ?>
                                        <form method="POST" action="adminEventAction_process.php" style="display:inline;">
                                            <input type="hidden" name="eventID" value="<?php echo (int) $event['eventID']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn-admin btn-approve btn-admin-sm" onclick="return confirm('Approve this event for students?');">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="adminEventAction_process.php" style="display:inline;">
                                            <input type="hidden" name="eventID" value="<?php echo (int) $event['eventID']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn-admin btn-reject btn-admin-sm" onclick="return confirm('Reject this event?');">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($totalEvents > $perPage): ?>
    <p style="margin-top:16px;color:#666;">
        Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $totalEvents; ?> events)
        <?php if ($page > 1): ?>
            — <a href="<?php echo htmlspecialchars(adminEventQueryUrl(['page' => $page - 1])); ?>">Previous</a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
            — <a href="<?php echo htmlspecialchars(adminEventQueryUrl(['page' => $page + 1])); ?>">Next</a>
        <?php endif; ?>
    </p>
<?php endif; ?>

<?php admin_page_end(); ?>
