<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: createEvent.php');
    exit();
}

$action = $_POST['action'] ?? 'draft';

function redirectWithError(string $message): void
{
    $_SESSION['createEvent_error'] = $message;
    header('Location: createEvent.php');
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
$registrationRequired = ($_POST['registrationRequired'] ?? 'yes') === 'yes' ? 1 : 0;
$registrationStartDate = trim($_POST['registrationStartDate'] ?? '') ?: null;
$registrationEndDate = trim($_POST['registrationEndDate'] ?? '') ?: null;
$approvalRequired = ($_POST['approvalRequired'] ?? 'yes') === 'yes' ? 1 : 0;
$eventVisibility = trim($_POST['eventVisibility'] ?? 'club_members');
$estimatedCost = trim($_POST['estimatedCost'] ?? '') !== '' ? (float) $_POST['estimatedCost'] : 0.0;
$maxParticipants = trim($_POST['maxParticipants'] ?? '') !== '' ? (int) $_POST['maxParticipants'] : null;
$contactPerson = trim($_POST['contactPerson'] ?? '');
$contactEmail = trim($_POST['contactEmail'] ?? '');
$contactPhone = trim($_POST['contactPhone'] ?? '');

if ($eventTitle === '' || $eventCategory === '' || $eventDate === '' || $startTime === '' || $endTime === '' || $venue === '' || $eventDescription === '') {
    redirectWithError('Please fill in all required fields.');
}

if (strlen($eventDescription) > 500) {
    redirectWithError('Event description must not exceed 500 characters.');
}

if ($startTime >= $endTime) {
    redirectWithError('End time must be after start time.');
}

if ($registrationRequired && (!$registrationStartDate || !$registrationEndDate)) {
    redirectWithError('Please provide registration start and end dates.');
}

if ($registrationRequired && $registrationStartDate > $registrationEndDate) {
    redirectWithError('Registration end date must be on or after the start date.');
}

if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Please enter a valid contact email.');
}

try {
    $posterPath = uploadEventPosterFile(function (string $message): void {
        redirectWithError($message);
    });
} catch (RuntimeException $e) {
    redirectWithError($e->getMessage());
}

$legacy = eventUsesLegacySchema($conn);

if ($legacy) {
    ensureLegacyEventSchema($conn);

    try {
        $clubID = resolveClubIdForCategory($conn, $eventCategory);
    } catch (RuntimeException $e) {
        redirectWithError($e->getMessage());
    }

    $eventStatus = legacyEventStatusFromAction($action);
    $eventVenue = $venue;
    if ($locationRoom !== '') {
        $eventVenue .= ', ' . $locationRoom;
    }
    $maxParticipations = $maxParticipants ?? 0;
    $posterValue = $posterPath ?? '';

    $stmt = mysqli_prepare($conn, '
        INSERT INTO event (
            clubID, eventTitle, eventDescription, eventDate, eventTime,
            eventVenue, maxParticipations, eventStatus, eventPoster
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    if (!$stmt) {
        redirectWithError('Database error. Could not save event.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        'issssisis',
        $clubID,
        $eventTitle,
        $eventDescription,
        $eventDate,
        $startTime,
        $eventVenue,
        $maxParticipations,
        $eventStatus,
        $posterValue
    );
} else {
    $status = extendedEventStatusFromAction($action);
    $createdBy = (int) $_SESSION['userID'];
    $posterValue = $posterPath;
    $maxParticipantsValue = $maxParticipants;

    $stmt = mysqli_prepare($conn, "
        INSERT INTO event (
            createdBy, eventTitle, eventCategory, eventDate, startTime, endTime,
            venue, locationRoom, eventDescription, registrationRequired,
            registrationStartDate, registrationEndDate, approvalRequired,
            eventVisibility, eventPoster, estimatedCost, maxParticipants,
            contactPerson, contactEmail, contactPhone, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        redirectWithError('Database error. Please ensure the event table exists (run setup.sql).');
    }

    mysqli_stmt_bind_param(
        $stmt,
        'issssssssissssidisss',
        $createdBy,
        $eventTitle,
        $eventCategory,
        $eventDate,
        $startTime,
        $endTime,
        $venue,
        $locationRoom,
        $eventDescription,
        $registrationRequired,
        $registrationStartDate,
        $registrationEndDate,
        $approvalRequired,
        $eventVisibility,
        $posterValue,
        $estimatedCost,
        $maxParticipantsValue,
        $contactPerson,
        $contactEmail,
        $contactPhone,
        $status
    );
}

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    redirectWithError('Failed to save event. Please try again.');
}

mysqli_stmt_close($stmt);

if ($legacy && $action === 'submit') {
    syncLegacyEventsForStudentBrowse($conn);
}

$isDraft = $action !== 'submit';
$_SESSION['createEvent_success'] = $isDraft
    ? 'Event saved as draft successfully.'
    : 'Event published successfully. Students can browse and register when the event is active.';

header('Location: manageEvent.php');
exit();
