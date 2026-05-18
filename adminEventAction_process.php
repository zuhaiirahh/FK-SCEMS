<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';
require_once __DIR__ . '/includes/admin_layout.php';

admin_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: adminEventManagement.php');
    exit();
}

$eventID = (int) ($_POST['eventID'] ?? 0);
$action = $_POST['action'] ?? '';

if ($eventID <= 0 || !in_array($action, ['approve', 'reject', 'cancel'], true)) {
    $_SESSION['admin_event_error'] = 'Invalid request.';
    header('Location: adminEventManagement.php');
    exit();
}

$event = fetchEventById($conn, $eventID);
if (!$event) {
    $_SESSION['admin_event_error'] = 'Event not found.';
    header('Location: adminEventManagement.php');
    exit();
}

$legacy = eventUsesLegacySchema($conn);

if ($action === 'approve') {
    $newStatus = $legacy ? 'Active' : 'approved';
} elseif ($action === 'reject') {
    $newStatus = $legacy ? 'Rejected' : 'rejected';
} else {
    $newStatus = $legacy ? 'Cancelled' : 'cancelled';
}

if ($legacy) {
    $stmt = mysqli_prepare($conn, 'UPDATE event SET eventStatus = ? WHERE eventID = ? LIMIT 1');
} else {
    $stmt = mysqli_prepare($conn, 'UPDATE event SET status = ? WHERE eventID = ? LIMIT 1');
}

if (!$stmt) {
    $_SESSION['admin_event_error'] = 'Could not update event status.';
    header('Location: adminEventManagement.php');
    exit();
}

mysqli_stmt_bind_param($stmt, 'si', $newStatus, $eventID);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
    $_SESSION['admin_event_error'] = 'Failed to update event. Please try again.';
    header('Location: adminEventManagement.php');
    exit();
}

if ($legacy && $action === 'approve') {
    syncLegacyEventsForStudentBrowse($conn);
}

$labels = [
    'approve' => 'approved',
    'reject' => 'rejected',
    'cancel' => 'cancelled',
];
$_SESSION['admin_event_success'] = 'Event "' . ($event['eventTitle'] ?? '') . '" has been ' . $labels[$action] . '.';

header('Location: adminEventManagement.php');
exit();
