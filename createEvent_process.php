<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: createEvent.php');
    exit();
}

$action = $_POST['action'] ?? 'draft';
$status = ($action === 'submit') ? 'pending_approval' : 'draft';

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

$posterPath = null;
if (!empty($_FILES['eventPoster']['name'])) {
    $file = $_FILES['eventPoster'];
    $allowed = ['image/jpeg', 'image/jpg', 'image/png'];
    $maxSize = 5 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        redirectWithError('Failed to upload event poster.');
    }

    if ($file['size'] > $maxSize) {
        redirectWithError('Event poster must be 5MB or smaller.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        redirectWithError('Event poster must be PNG or JPEG.');
    }

    $uploadDir = __DIR__ . '/uploads/events/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        redirectWithError('Failed to save event poster.');
    }

    $posterPath = 'uploads/events/' . $filename;
}

$createdBy = (int) $_SESSION['userID'];

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
    redirectWithError('Database error. Please ensure the event table exists (run fkscems.sql).');
}

mysqli_stmt_bind_param(
    $stmt,
    'issssssssissssidissss',
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
    $posterPath,
    $estimatedCost,
    $maxParticipants,
    $contactPerson,
    $contactEmail,
    $contactPhone,
    $status
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    redirectWithError('Failed to save event. Please try again.');
}

mysqli_stmt_close($stmt);

$_SESSION['createEvent_success'] = $status === 'draft'
    ? 'Event saved as draft successfully.'
    : 'Event submitted for approval successfully.';

header('Location: manageEvent.php');
exit();
