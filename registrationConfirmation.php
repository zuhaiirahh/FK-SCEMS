<?php
/**
 * Event registration confirmation (pre-register) and view existing registration.
 * ?id=5 — confirm before register/waitlist
 * ?registrationID=123 — view after registering (from history or browse)
 * ?id=5&action=waitlist — join waiting list confirmation
 */
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/browse_event_helpers.php';
require_once __DIR__ . '/includes/event_schema.php';
require_once __DIR__ . '/includes/student_reg_layout.php';

student_require_auth();

$userID = (int) $_SESSION['userID'];
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
$today = date('Y-m-d');

$registrationID = (int) ($_GET['registrationID'] ?? 0);
$eventID = (int) ($_GET['id'] ?? 0);
$action = ($_GET['action'] ?? 'register') === 'waitlist' ? 'waitlist' : 'register';

$flashError = $_SESSION['reg_confirm_error'] ?? '';
$flashSuccess = $_SESSION['reg_confirm_success'] ?? $_SESSION['browse_success'] ?? '';
unset($_SESSION['reg_confirm_error'], $_SESSION['reg_confirm_success'], $_SESSION['browse_success']);

$viewOnly = false;
$registration = null;
$waitingEntry = null;
$userRegistered = false;
$userWaiting = false;

if ($registrationID > 0) {
    $regCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
    if (!$regCheck || mysqli_num_rows($regCheck) === 0) {
        $_SESSION['browse_error'] = 'Registration system is not available.';
        header('Location: browseEvents.php');
        exit();
    }

    $rStmt = mysqli_prepare($conn, '
        SELECT registrationID, eventID, registrationStatus, confirmationStatus, registrationDate
        FROM event_registration
        WHERE registrationID = ? AND userID = ?
        LIMIT 1
    ');
    mysqli_stmt_bind_param($rStmt, 'ii', $registrationID, $userID);
    mysqli_stmt_execute($rStmt);
    $registration = mysqli_stmt_get_result($rStmt)->fetch_assoc();
    mysqli_stmt_close($rStmt);

    if (!$registration) {
        $_SESSION['browse_error'] = 'Registration not found.';
        header('Location: studentRegistrationHistory.php');
        exit();
    }

    $eventID = (int) $registration['eventID'];
    $viewOnly = true;
    $userRegistered = true;
} elseif ($eventID > 0) {
    $viewOnly = false;
} else {
    header('Location: browseEvents.php');
    exit();
}

$event = $viewOnly
    ? fetchEventById($conn, $eventID)
    : fetchBrowsableEvent($conn, $eventID, $today);

if (!$event) {
    $_SESSION['browse_error'] = 'Event not found or no longer available for registration.';
    header('Location: browseEvents.php');
    exit();
}

$regCount = 0;
$hasRegTable = false;
$hasWaitTable = false;

$regTableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
$hasRegTable = $regTableCheck && mysqli_num_rows($regTableCheck) > 0;
$waitTableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'waiting_list'");
$hasWaitTable = $waitTableCheck && mysqli_num_rows($waitTableCheck) > 0;

if ($hasRegTable) {
    $cStmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM event_registration WHERE eventID = ?');
    mysqli_stmt_bind_param($cStmt, 'i', $eventID);
    mysqli_stmt_execute($cStmt);
    $regCount = (int) (mysqli_stmt_get_result($cStmt)->fetch_assoc()['total'] ?? 0);
    mysqli_stmt_close($cStmt);

    if (!$viewOnly) {
        $uStmt = mysqli_prepare($conn, 'SELECT registrationID FROM event_registration WHERE eventID = ? AND userID = ? LIMIT 1');
        mysqli_stmt_bind_param($uStmt, 'ii', $eventID, $userID);
        mysqli_stmt_execute($uStmt);
        $existing = mysqli_stmt_get_result($uStmt)->fetch_assoc();
        mysqli_stmt_close($uStmt);
        if ($existing) {
            header('Location: registrationConfirmation.php?registrationID=' . (int) $existing['registrationID']);
            exit();
        }
    }
}

if ($hasWaitTable && !$viewOnly) {
    $wStmt = mysqli_prepare($conn, 'SELECT waitingID, waitingStatus, requestDate FROM waiting_list WHERE eventID = ? AND userID = ? LIMIT 1');
    mysqli_stmt_bind_param($wStmt, 'ii', $eventID, $userID);
    mysqli_stmt_execute($wStmt);
    $waitingEntry = mysqli_stmt_get_result($wStmt)->fetch_assoc();
    mysqli_stmt_close($wStmt);
    if ($waitingEntry) {
        $userWaiting = true;
        $viewOnly = true;
    }
} elseif ($hasWaitTable && $viewOnly && !$userRegistered) {
    $wStmt = mysqli_prepare($conn, 'SELECT waitingID, waitingStatus, requestDate FROM waiting_list WHERE eventID = ? AND userID = ? LIMIT 1');
    mysqli_stmt_bind_param($wStmt, 'ii', $eventID, $userID);
    mysqli_stmt_execute($wStmt);
    $waitingEntry = mysqli_stmt_get_result($wStmt)->fetch_assoc();
    mysqli_stmt_close($wStmt);
    $userWaiting = (bool) $waitingEntry;
}

$maxSlots = $event['maxParticipants'] !== null && $event['maxParticipants'] !== ''
    ? (int) $event['maxParticipants']
    : null;
$available = $maxSlots !== null ? max(0, $maxSlots - $regCount) : null;
$isFull = $maxSlots !== null && $regCount >= $maxSlots;

if (!$viewOnly && $action === 'register' && $isFull) {
    header('Location: registrationConfirmation.php?id=' . $eventID . '&action=waitlist');
    exit();
}

$venue = htmlspecialchars($event['venue'] ?? '');
if (!empty($event['locationRoom'])) {
    $venue .= ($venue !== '' ? ', ' : '') . htmlspecialchars($event['locationRoom']);
}
$clubName = clubDisplayName($event['eventCategory'] ?? '', $event['clubName'] ?? null);
$merit = meritValue($event['eventCategory'] ?? '');
$poster = eventPosterUrl($event);
$imageCaption = $venue !== '' ? 'Experience the event at ' . strip_tags($venue) : htmlspecialchars($event['eventTitle']);

$slotDisplay = $maxSlots !== null
    ? $available . ' / ' . $maxSlots
    : 'Open slots';

$student = [
    'name' => $_SESSION['name'] ?? 'Student',
    'matric' => '',
    'phone' => '',
    'email' => '',
    'totalMerit' => 0,
];

$uStmt = mysqli_prepare($conn, 'SELECT username, name, email, phoneNumber FROM user WHERE userID = ? LIMIT 1');
if ($uStmt) {
    mysqli_stmt_bind_param($uStmt, 'i', $userID);
    mysqli_stmt_execute($uStmt);
    if ($row = mysqli_stmt_get_result($uStmt)->fetch_assoc()) {
        $student['name'] = $row['name'] ?? $student['name'];
        $student['matric'] = $row['username'] ?? '';
        $student['phone'] = $row['phoneNumber'] ?? '';
        $student['email'] = $row['email'] ?? '';
    }
    mysqli_stmt_close($uStmt);
}

if ($hasRegTable) {
    if (eventUsesLegacySchema($conn)) {
        $meritSql = "
            SELECT c.clubCategory
            FROM event_registration er
            INNER JOIN event e ON er.eventID = e.eventID
            INNER JOIN club c ON e.clubID = c.clubID
            WHERE er.userID = ? AND LOWER(er.confirmationStatus) IN ('attended', 'confirmed')
        ";
    } else {
        $meritSql = "
            SELECT e.eventCategory
            FROM event_registration er
            INNER JOIN event e ON er.eventID = e.eventID
            WHERE er.userID = ? AND LOWER(er.confirmationStatus) IN ('attended', 'confirmed')
        ";
    }
    $mStmt = mysqli_prepare($conn, $meritSql);
    if ($mStmt) {
        mysqli_stmt_bind_param($mStmt, 'i', $userID);
        mysqli_stmt_execute($mStmt);
        $mRes = mysqli_stmt_get_result($mStmt);
        while ($mRow = mysqli_fetch_assoc($mRes)) {
            $cat = $mRow['clubCategory'] ?? $mRow['eventCategory'] ?? '';
            $student['totalMerit'] += meritValue($cat);
        }
        mysqli_stmt_close($mStmt);
    }
}

$infoMessage = 'Please review the event details below before confirming your registration. Ensure your student information is up to date.';
$confirmLabel = $action === 'waitlist'
    ? 'I confirm that the above information is correct. I would like to join the waiting list for this event.'
    : 'I confirm that the above information is correct. I would like to register for this event.';
$submitLabel = $action === 'waitlist' ? 'Confirm Waiting List' : 'Confirm Registration';
$pageTitle = 'Event Registration Confirmation';
$navActive = 'event-registration';

student_reg_page_start($pageTitle, $userName, $navActive);
require __DIR__ . '/includes/registration_confirm_tailwind_view.php';
student_reg_page_end();
