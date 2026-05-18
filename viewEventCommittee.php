<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/browse_event_helpers.php';
require_once __DIR__ . '/includes/event_schema.php';
require_once __DIR__ . '/includes/committee_layout.php';

committee_require_auth();

$eventID = (int) ($_GET['id'] ?? 0);
if ($eventID <= 0) {
    header('Location: manageEvent.php');
    exit();
}

$event = fetchEventById($conn, $eventID);
if (!$event) {
    $_SESSION['manageEvent_error'] = 'Event not found.';
    header('Location: manageEvent.php');
    exit();
}

$regCount = 0;
$regCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
if ($regCheck && mysqli_num_rows($regCheck) > 0) {
    $cStmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM event_registration WHERE eventID = ?');
    mysqli_stmt_bind_param($cStmt, 'i', $eventID);
    mysqli_stmt_execute($cStmt);
    $regCount = (int) (mysqli_stmt_get_result($cStmt)->fetch_assoc()['total'] ?? 0);
    mysqli_stmt_close($cStmt);
}

$badge = eventDisplayStatusLabel($event['status'], $event['eventDate']);
$statusClass = committee_status_class($badge['label']);
$venue = htmlspecialchars($event['venue'] ?? '');
if (!empty($event['locationRoom'])) {
    $venue .= ', ' . htmlspecialchars($event['locationRoom']);
}
$maxSlots = $event['maxParticipants'] !== null && $event['maxParticipants'] !== ''
    ? (int) $event['maxParticipants']
    : null;
$clubName = clubDisplayName($event['eventCategory'] ?? '', $event['clubName'] ?? null);
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');

committee_page_start($event['eventTitle'], $userName, 'event-management');
?>

<a href="manageEvent.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Manage Events</a>

<article class="event-detail-card">
    <header>
        <div>
            <p style="color:#666;font-size:0.9rem;margin:0 0 4px;">EVT<?php echo str_pad((string) $eventID, 4, '0', STR_PAD_LEFT); ?></p>
            <h1 style="margin:0;font-size:1.5rem;"><?php echo htmlspecialchars($event['eventTitle']); ?></h1>
            <p style="color:#008b8b;font-weight:600;margin:8px 0 0;"><?php echo htmlspecialchars($clubName); ?></p>
        </div>
        <span class="status-badge <?php echo htmlspecialchars($statusClass); ?>">
            <?php echo htmlspecialchars($badge['label']); ?>
        </span>
    </header>
    <section>
        <p style="white-space:pre-line;margin-bottom:1rem;"><?php echo htmlspecialchars($event['eventDescription'] ?? ''); ?></p>
        <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin:0;">
            <div><dt style="font-weight:600;display:inline;">Date: </dt><dd style="display:inline;margin:0;"><?php echo formatEventDate($event['eventDate']); ?></dd></div>
            <div><dt style="font-weight:600;display:inline;">Time: </dt><dd style="display:inline;margin:0;"><?php echo formatEventTime($event['startTime'] ?? ''); ?></dd></div>
            <div><dt style="font-weight:600;display:inline;">Venue: </dt><dd style="display:inline;margin:0;"><?php echo $venue; ?></dd></div>
            <div><dt style="font-weight:600;display:inline;">Registrations: </dt><dd style="display:inline;margin:0;"><?php echo $regCount; ?><?php echo $maxSlots ? ' / ' . $maxSlots : ''; ?></dd></div>
        </dl>
    </section>
    <footer>
        <a href="eventPartipantList.php?id=<?php echo $eventID; ?>" class="btn btn-outline">
            <i class="fas fa-users"></i> Recruit / Participants
        </a>
        <a href="editEvent.php?id=<?php echo $eventID; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
    </footer>
</article>

<?php committee_page_end(); ?>
