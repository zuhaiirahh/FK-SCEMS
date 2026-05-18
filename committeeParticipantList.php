<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';
require_once __DIR__ . '/includes/committee_layout.php';

committee_require_auth();

$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
$search = trim($_GET['search'] ?? '');
$events = [];

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $regCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
    $hasReg = $regCheck && mysqli_num_rows($regCheck) > 0;

    $sql = 'SELECT e.eventID, e.eventTitle, e.eventDate, e.eventVenue, e.eventStatus';
    if ($hasReg) {
        $sql .= ', (SELECT COUNT(*) FROM event_registration er WHERE er.eventID = e.eventID) AS regCount';
    } else {
        $sql .= ', 0 AS regCount';
    }
    $sql .= ' FROM event e WHERE 1=1';
    $types = '';
    $params = [];
    if ($search !== '') {
        $sql .= ' AND e.eventTitle LIKE ?';
        $types = 's';
        $params[] = '%' . $search . '%';
    }
    $sql .= ' ORDER BY e.eventDate DESC';

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        if ($types !== '') {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

committee_page_start('Event Participant List', $userName, 'participant-list');
?>

<div class="page-header">
    <h1>Event Participant List</h1>
    <p class="page-subtitle">Select an event to view registered participants and waiting list.</p>
</div>

<form method="get" class="filter-bar">
    <div class="filter-field" style="flex:2;">
        <label for="search">Search event</label>
        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
            placeholder="Search event title...">
    </div>
    <div class="filter-actions">
        <button type="submit" class="apply-btn">Search</button>
    </div>
</form>

<?php if (empty($events)): ?>
    <p>No events found. <a href="manageEvent.php" class="text-link">Manage events</a></p>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ev['eventTitle']); ?></td>
                        <td><?php echo $ev['eventDate'] ? htmlspecialchars(date('d M Y', strtotime($ev['eventDate']))) : '—'; ?></td>
                        <td><?php echo htmlspecialchars($ev['eventVenue'] ?? '—'); ?></td>
                        <td><?php echo (int) ($ev['regCount'] ?? 0); ?></td>
                        <td>
                            <a href="eventPartipantList.php?id=<?php echo (int) $ev['eventID']; ?>" class="text-link">
                                <i class="fas fa-users"></i> View participants
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php committee_page_end(); ?>
