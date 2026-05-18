<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manageEvent.php');
    exit();
}

$eventID = (int) ($_POST['eventID'] ?? 0);
if ($eventID <= 0) {
    $_SESSION['manageEvent_error'] = 'Invalid event.';
    header('Location: manageEvent.php');
    exit();
}

$event = null;
$stmt = mysqli_prepare($conn, 'SELECT eventID, eventTitle FROM event WHERE eventID = ? LIMIT 1');
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $eventID);
    mysqli_stmt_execute($stmt);
    $event = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);
}

if (!$event) {
    $_SESSION['manageEvent_error'] = 'Event not found.';
    header('Location: manageEvent.php');
    exit();
}

foreach (['event_registration', 'waiting_list'] as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '{$table}'");
    if ($check && mysqli_num_rows($check) > 0) {
        $del = mysqli_prepare($conn, "DELETE FROM `{$table}` WHERE eventID = ?");
        if ($del) {
            mysqli_stmt_bind_param($del, 'i', $eventID);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);
        }
    }
}

$delEvent = mysqli_prepare($conn, 'DELETE FROM event WHERE eventID = ? LIMIT 1');
if (!$delEvent) {
    $_SESSION['manageEvent_error'] = 'Could not delete event.';
    header('Location: manageEvent.php');
    exit();
}
mysqli_stmt_bind_param($delEvent, 'i', $eventID);
$ok = mysqli_stmt_execute($delEvent);
mysqli_stmt_close($delEvent);

if (!$ok) {
    $_SESSION['manageEvent_error'] = 'Could not delete event.';
} else {
    $_SESSION['createEvent_success'] = 'Event "' . $event['eventTitle'] . '" deleted successfully.';
}

header('Location: manageEvent.php');
exit();
