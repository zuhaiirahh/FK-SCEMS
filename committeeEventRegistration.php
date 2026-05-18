<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/committee_layout.php';

committee_require_auth();

$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
$search = trim($_GET['search'] ?? '');
$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$registrations = [];
$total = 0;
$hasTable = false;

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
$hasTable = $tableCheck && mysqli_num_rows($tableCheck) > 0;

if ($hasTable) {
    $where = 'WHERE 1=1';
    $types = '';
    $params = [];
    if ($search !== '') {
        $where .= ' AND (e.eventTitle LIKE ? OR u.name LIKE ? OR u.username LIKE ?)';
        $types .= 'sss';
        $like = '%' . $search . '%';
        $params = [$like, $like, $like];
    }

    $countSql = "SELECT COUNT(*) AS total FROM event_registration er
        INNER JOIN event e ON er.eventID = e.eventID
        INNER JOIN user u ON er.userID = u.userID $where";
    $countStmt = mysqli_prepare($conn, $countSql);
    if ($countStmt) {
        if ($types !== '') {
            mysqli_stmt_bind_param($countStmt, $types, ...$params);
        }
        mysqli_stmt_execute($countStmt);
        $total = (int) (mysqli_stmt_get_result($countStmt)->fetch_assoc()['total'] ?? 0);
        mysqli_stmt_close($countStmt);
    }

    $totalPages = max(1, (int) ceil($total / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    $listSql = "SELECT er.registrationID, er.registrationDate, er.registrationStatus, er.confirmationStatus,
            e.eventTitle, e.eventDate, u.name, u.username
        FROM event_registration er
        INNER JOIN event e ON er.eventID = e.eventID
        INNER JOIN user u ON er.userID = u.userID
        $where
        ORDER BY er.registrationDate DESC
        LIMIT ? OFFSET ?";
    $listStmt = mysqli_prepare($conn, $listSql);
    if ($listStmt) {
        $lTypes = $types . 'ii';
        $lParams = array_merge($params, [$perPage, $offset]);
        mysqli_stmt_bind_param($listStmt, $lTypes, ...$lParams);
        mysqli_stmt_execute($listStmt);
        $result = mysqli_stmt_get_result($listStmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $registrations[] = $row;
        }
        mysqli_stmt_close($listStmt);
    }
} else {
    $totalPages = 1;
}

$totalPages = max(1, (int) ceil(max($total, 1) / $perPage));

committee_page_start('Event Registration', $userName, 'event-registration');
?>

<div class="page-header">
    <h1>Event Registration</h1>
    <p class="page-subtitle">All student registrations across events.</p>
</div>

<form method="get" class="filter-bar">
    <div class="filter-field" style="flex:2;">
        <label for="search">Search</label>
        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
            placeholder="Event, student name, or username...">
    </div>
    <div class="filter-actions">
        <button type="submit" class="apply-btn">Search</button>
        <?php if ($search !== ''): ?>
            <a href="committeeEventRegistration.php" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if (!$hasTable): ?>
    <div class="notice-box warning">
        Registration tables are not set up. Run <code>student_registration_tables.sql</code> in phpMyAdmin.
    </div>
<?php elseif (empty($registrations)): ?>
    <p>No registrations found.</p>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Reg ID</th>
                    <th>Event</th>
                    <th>Student</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $row): ?>
                    <tr>
                        <td>REG<?php echo str_pad((string) $row['registrationID'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($row['eventTitle']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?> (<?php echo htmlspecialchars($row['username']); ?>)</td>
                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['registrationDate']))); ?></td>
                        <td><?php echo htmlspecialchars($row['confirmationStatus'] ?: $row['registrationStatus']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="table-footer">
            <span>Showing <?php echo count($registrations); ?> of <?php echo $total; ?> registrations</span>
        </div>
    </div>
<?php endif; ?>

<?php committee_page_end(); ?>
