<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';

if (!isset($_SESSION['userID']) || !in_array($_SESSION['type'] ?? '', ['student', 'committee'], true)) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browseEvents.php');
    exit();
}

function redirectAfterRegister(string $message, bool $isError = false, ?int $eventID = null, ?int $registrationID = null): void
{
    if ($isError) {
        if ($eventID) {
            $_SESSION['reg_confirm_error'] = $message;
            $action = ($_POST['action'] ?? '') === 'waitlist' ? '&action=waitlist' : '';
            header('Location: registrationConfirmation.php?id=' . $eventID . $action);
        } else {
            $_SESSION['browse_error'] = $message;
            header('Location: browseEvents.php');
        }
    } elseif ($registrationID) {
        $_SESSION['reg_confirm_success'] = $message;
        header('Location: registrationConfirmation.php?registrationID=' . $registrationID);
    } elseif ($eventID) {
        $_SESSION['reg_confirm_success'] = $message;
        header('Location: registrationConfirmation.php?id=' . $eventID);
    } else {
        $_SESSION['browse_success'] = $message;
        header('Location: browseEvents.php');
    }
    exit();
}

$action = $_POST['action'] ?? '';
$eventID = (int) ($_POST['eventID'] ?? 0);
$userID = (int) $_SESSION['userID'];

if ($eventID <= 0 || !in_array($action, ['register', 'waitlist'], true)) {
    redirectAfterRegister('Invalid request.', true, $eventID > 0 ? $eventID : null);
}

$regTable = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
$waitTable = mysqli_query($conn, "SHOW TABLES LIKE 'waiting_list'");
if (!$regTable || mysqli_num_rows($regTable) === 0) {
    redirectAfterRegister('Registration is not set up yet. Please import student_registration_tables.sql in phpMyAdmin.', true, $eventID);
}

$today = date('Y-m-d');
$event = fetchBrowsableEvent($conn, $eventID, $today);

if (!$event) {
    redirectAfterRegister('This event is not available for registration.', true, $eventID);
}

if (!eventUsesLegacySchema($conn) && (int) ($event['registrationRequired'] ?? 1) === 1) {
    $regStart = $event['registrationStartDate'] ?: '1970-01-01';
    $regEnd = $event['registrationEndDate'] ?: '2099-12-31';
    if ($today < $regStart || $today > $regEnd) {
        redirectAfterRegister('Registration for this event is not open.', true, $eventID);
    }
}

$countStmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM event_registration WHERE eventID = ?');
mysqli_stmt_bind_param($countStmt, 'i', $eventID);
mysqli_stmt_execute($countStmt);
$countRow = mysqli_stmt_get_result($countStmt)->fetch_assoc();
mysqli_stmt_close($countStmt);
$regCount = (int) ($countRow['total'] ?? 0);
$maxSlots = $event['maxParticipants'] !== null && $event['maxParticipants'] !== ''
    ? (int) $event['maxParticipants']
    : null;

$existingReg = mysqli_prepare($conn, 'SELECT registrationID FROM event_registration WHERE eventID = ? AND userID = ? LIMIT 1');
mysqli_stmt_bind_param($existingReg, 'ii', $eventID, $userID);
mysqli_stmt_execute($existingReg);
$alreadyRegistered = (bool) mysqli_stmt_get_result($existingReg)->fetch_assoc();
mysqli_stmt_close($existingReg);

if ($alreadyRegistered) {
    redirectAfterRegister('You are already registered for this event.', true, $eventID);
}

$userWaiting = false;
if ($waitTable && mysqli_num_rows($waitTable) > 0) {
    $waitCheck = mysqli_prepare($conn, 'SELECT waitingID FROM waiting_list WHERE eventID = ? AND userID = ? LIMIT 1');
    mysqli_stmt_bind_param($waitCheck, 'ii', $eventID, $userID);
    mysqli_stmt_execute($waitCheck);
    $userWaiting = (bool) mysqli_stmt_get_result($waitCheck)->fetch_assoc();
    mysqli_stmt_close($waitCheck);
}

$eventTitle = $event['eventTitle'] ?? 'Event';

if ($action === 'register') {
    if ($maxSlots !== null && $regCount >= $maxSlots) {
        redirectAfterRegister('This event is full. You can join the waiting list instead.', true, $eventID);
    }

    $insert = mysqli_prepare($conn, "
        INSERT INTO event_registration (eventID, userID, registrationStatus, confirmationStatus)
        VALUES (?, ?, 'registered', 'pending')
    ");
    mysqli_stmt_bind_param($insert, 'ii', $eventID, $userID);

    if (!$insert || !mysqli_stmt_execute($insert)) {
        if ($insert) {
            mysqli_stmt_close($insert);
        }
        redirectAfterRegister('Failed to register. Please try again.', true, $eventID);
    }
    $newRegistrationID = (int) mysqli_insert_id($conn);
    mysqli_stmt_close($insert);

    redirectAfterRegister('Successfully registered for "' . $eventTitle . '".', false, null, $newRegistrationID);
}

if ($action === 'waitlist') {
    if (!$waitTable || mysqli_num_rows($waitTable) === 0) {
        redirectAfterRegister('Waiting list is not available.', true, $eventID);
    }

    if ($maxSlots === null || $regCount < $maxSlots) {
        redirectAfterRegister('This event still has available slots. Please register instead.', true, $eventID);
    }

    if ($userWaiting) {
        redirectAfterRegister('You are already on the waiting list for this event.', true, $eventID);
    }

    $insertWait = mysqli_prepare($conn, "
        INSERT INTO waiting_list (eventID, userID, waitingStatus)
        VALUES (?, ?, 'waiting')
    ");
    mysqli_stmt_bind_param($insertWait, 'ii', $eventID, $userID);

    if (!$insertWait || !mysqli_stmt_execute($insertWait)) {
        if ($insertWait) {
            mysqli_stmt_close($insertWait);
        }
        redirectAfterRegister('Failed to join the waiting list. Please try again.', true, $eventID);
    }
    mysqli_stmt_close($insertWait);

    redirectAfterRegister('You have joined the waiting list for "' . $eventTitle . '".', false, $eventID);
}

redirectAfterRegister('Invalid action.', true, $eventID);
