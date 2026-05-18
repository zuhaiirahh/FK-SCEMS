<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || ($_SESSION['type'] ?? '') !== 'student') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: studentRegistrationHistory.php');
    exit();
}

$registrationID = (int) ($_POST['registrationID'] ?? 0);
$userID = (int) $_SESSION['userID'];

if ($registrationID <= 0) {
    $_SESSION['reg_history_error'] = 'Invalid registration.';
    header('Location: studentRegistrationHistory.php');
    exit();
}

$regCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
if (!$regCheck || mysqli_num_rows($regCheck) === 0) {
    $_SESSION['reg_history_error'] = 'Registration system is not available.';
    header('Location: studentRegistrationHistory.php');
    exit();
}

$stmt = mysqli_prepare($conn, '
    SELECT er.registrationID, er.eventID, e.eventTitle
    FROM event_registration er
    INNER JOIN event e ON er.eventID = e.eventID
    WHERE er.registrationID = ? AND er.userID = ?
    LIMIT 1
');
if (!$stmt) {
    $_SESSION['reg_history_error'] = 'Could not cancel registration.';
    header('Location: studentRegistrationHistory.php');
    exit();
}

mysqli_stmt_bind_param($stmt, 'ii', $registrationID, $userID);
mysqli_stmt_execute($stmt);
$row = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

if (!$row) {
    $_SESSION['reg_history_error'] = 'Registration not found or you do not have permission to remove it.';
    header('Location: studentRegistrationHistory.php');
    exit();
}

$eventID = (int) $row['eventID'];
$eventTitle = $row['eventTitle'] ?? 'Event';

$del = mysqli_prepare($conn, 'DELETE FROM event_registration WHERE registrationID = ? AND userID = ? LIMIT 1');
if (!$del) {
    $_SESSION['reg_history_error'] = 'Could not cancel registration.';
    header('Location: studentRegistrationHistory.php');
    exit();
}

mysqli_stmt_bind_param($del, 'ii', $registrationID, $userID);
$ok = mysqli_stmt_execute($del);
mysqli_stmt_close($del);

if (!$ok) {
    $_SESSION['reg_history_error'] = 'Could not cancel registration. Please try again.';
    header('Location: studentRegistrationHistory.php');
    exit();
}

$returnTo = trim($_POST['returnTo'] ?? '');
if ($returnTo === 'confirmation') {
    $_SESSION['reg_history_success'] = 'Registration for "' . $eventTitle . '" has been cancelled.';
    header('Location: studentRegistrationHistory.php');
} else {
    $_SESSION['reg_history_success'] = 'Registration for "' . $eventTitle . '" has been deleted successfully.';
    header('Location: studentRegistrationHistory.php');
}
exit();
