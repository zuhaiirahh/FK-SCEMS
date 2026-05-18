<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manageEvent.php');
    exit();
}

$eventID = (int) ($_POST['eventID'] ?? 0);
$action = $_POST['action'] ?? 'draft';

function redirectEditError(int $eventID, string $message): void
{
    $_SESSION['editEvent_error'] = $message;
    header('Location: editEvent.php?id=' . $eventID);
    exit();
}

if ($eventID <= 0) {
    $_SESSION['manageEvent_error'] = 'Invalid event.';
    header('Location: manageEvent.php');
    exit();
}

$existing = fetchEventById($conn, $eventID);
if (!$existing) {
    $_SESSION['manageEvent_error'] = 'Event not found.';
    header('Location: manageEvent.php');
    exit();
}

$eventTitle = trim($_POST['eventTitle'] ?? '');
$eventCategory = trim($_POST['eventCategory'] ?? '');
$eventDate = trim($_POST['eventDate'] ?? '');
$startTime = trim($_POST['startTime'] ?? '');
$endTime = trim($_POST['endTime'] ?? '');
$venue = trim($_POST['venue'] ?? '');
$locationRoom = trim($_POST['locationRoom'] ?? '');
$eventDescription = trim($_POST['eventDescription'] ?? '');
$maxParticipants = trim($_POST['maxParticipants'] ?? '') !== '' ? (int) $_POST['maxParticipants'] : null;

if ($eventTitle === '' || $eventCategory === '' || $eventDate === '' || $startTime === '' || $endTime === '' || $venue === '' || $eventDescription === '') {
    redirectEditError($eventID, 'Please fill in all required fields.');
}

if (strlen($eventDescription) > 500) {
    redirectEditError($eventID, 'Event description must not exceed 500 characters.');
}

if ($startTime >= $endTime) {
    redirectEditError($eventID, 'End time must be after start time.');
}

ensureEventPosterColumn($conn);
$newPosterPath = uploadEventPosterFile(function (string $message) use ($eventID): void {
    redirectEditError($eventID, $message);
});
$posterPath = $newPosterPath ?? ($existing['eventPoster'] ?? null);
if ($newPosterPath !== null && !empty($existing['eventPoster']) && $existing['eventPoster'] !== $newPosterPath) {
    deleteEventPosterFile($existing['eventPoster']);
}

$legacy = eventUsesLegacySchema($conn);

if ($legacy) {
    ensureLegacyEventSchema($conn);
    try {
        $clubID = resolveClubIdForCategory($conn, $eventCategory);
    } catch (RuntimeException $e) {
        redirectEditError($eventID, $e->getMessage());
    }

    $eventStatus = legacyEventStatusFromAction($action);
    $eventVenue = $venue;
    if ($locationRoom !== '') {
        $eventVenue .= ', ' . $locationRoom;
    }
    $maxParticipations = $maxParticipants ?? 0;

    $stmt = mysqli_prepare($conn, '
        UPDATE event SET
            clubID = ?, eventTitle = ?, eventDescription = ?, eventDate = ?, eventTime = ?,
            eventVenue = ?, maxParticipations = ?, eventStatus = ?, eventPoster = ?
        WHERE eventID = ?
    ');
    if (!$stmt) {
        redirectEditError($eventID, 'Database error. Could not update event.');
    }
    mysqli_stmt_bind_param(
        $stmt,
        'isssssissi',
        $clubID,
        $eventTitle,
        $eventDescription,
        $eventDate,
        $startTime,
        $eventVenue,
        $maxParticipations,
        $eventStatus,
        $posterPath,
        $eventID
    );
} else {
    $status = extendedEventStatusFromAction($action);
    $stmt = mysqli_prepare($conn, '
        UPDATE event SET
            eventTitle = ?, eventCategory = ?, eventDate = ?, startTime = ?, endTime = ?,
            venue = ?, locationRoom = ?, eventDescription = ?, maxParticipants = ?, status = ?,
            eventPoster = ?
        WHERE eventID = ?
    ');
    if (!$stmt) {
        redirectEditError($eventID, 'Database error. Could not update event.');
    }
    mysqli_stmt_bind_param(
        $stmt,
        'ssssssssissi',
        $eventTitle,
        $eventCategory,
        $eventDate,
        $startTime,
        $endTime,
        $venue,
        $locationRoom,
        $eventDescription,
        $maxParticipants,
        $status,
        $posterPath,
        $eventID
    );
}

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    redirectEditError($eventID, 'Failed to update event. Please try again.');
}
mysqli_stmt_close($stmt);

if ($legacy && $action === 'submit') {
    syncLegacyEventsForStudentBrowse($conn);
}

$isDraft = $action !== 'submit';
$_SESSION['createEvent_success'] = $isDraft
    ? 'Event updated and saved as draft.'
    : 'Event updated and published. Students can browse and register.';

header('Location: manageEvent.php');
exit();
