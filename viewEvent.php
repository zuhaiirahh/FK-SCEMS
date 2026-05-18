<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || !in_array($_SESSION['type'] ?? '', ['student', 'committee'], true)) {
    header('Location: login.php');
    exit();
}

$eventID = (int) ($_GET['id'] ?? 0);
if ($eventID <= 0) {
    header('Location: browseEvents.php');
    exit();
}

$userID = (int) $_SESSION['userID'];
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
$roleLabel = ($_SESSION['type'] ?? '') === 'committee' ? 'Committee Club' : 'Student';
$today = date('Y-m-d');

require_once __DIR__ . '/includes/browse_event_helpers.php';
require_once __DIR__ . '/includes/event_schema.php';

$regCount = 0;
$userRegistered = false;
$userWaiting = false;
$hasRegTable = false;

$event = fetchBrowsableEvent($conn, $eventID, $today);

if (!$event) {
    $_SESSION['browse_error'] = 'Event not found or no longer available.';
    header('Location: browseEvents.php');
    exit();
}

$regCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_registration'");
$hasRegTable = $regCheck && mysqli_num_rows($regCheck) > 0;
if ($hasRegTable) {
    $cStmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM event_registration WHERE eventID = ?');
    mysqli_stmt_bind_param($cStmt, 'i', $eventID);
    mysqli_stmt_execute($cStmt);
    $regCount = (int) (mysqli_stmt_get_result($cStmt)->fetch_assoc()['total'] ?? 0);
    mysqli_stmt_close($cStmt);

    $uStmt = mysqli_prepare($conn, 'SELECT registrationID FROM event_registration WHERE eventID = ? AND userID = ? LIMIT 1');
    mysqli_stmt_bind_param($uStmt, 'ii', $eventID, $userID);
    mysqli_stmt_execute($uStmt);
    $userRegistered = (bool) mysqli_stmt_get_result($uStmt)->fetch_assoc();
    mysqli_stmt_close($uStmt);

    $wCheck = mysqli_query($conn, "SHOW TABLES LIKE 'waiting_list'");
    if ($wCheck && mysqli_num_rows($wCheck) > 0) {
        $wStmt = mysqli_prepare($conn, 'SELECT waitingID FROM waiting_list WHERE eventID = ? AND userID = ? LIMIT 1');
        mysqli_stmt_bind_param($wStmt, 'ii', $eventID, $userID);
        mysqli_stmt_execute($wStmt);
        $userWaiting = (bool) mysqli_stmt_get_result($wStmt)->fetch_assoc();
        mysqli_stmt_close($wStmt);
    }
}

require_once __DIR__ . '/includes/browse_event_helpers.php';

$maxSlots = $event['maxParticipants'] !== null ? (int) $event['maxParticipants'] : null;
$isFull = $maxSlots !== null && $regCount >= $maxSlots;
$available = $maxSlots !== null ? max(0, $maxSlots - $regCount) : null;
$venue = htmlspecialchars($event['venue']);
if (!empty($event['locationRoom'])) {
    $venue .= ', ' . htmlspecialchars($event['locationRoom']);
}
$poster = eventPosterUrl($event);
$clubName = clubDisplayName($event['eventCategory'], $event['clubName'] ?? null);
$clubIcon = clubIcon($event['eventCategory']);
$merit = meritValue($event['eventCategory']);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($event['eventTitle']); ?> - FK-SCEMS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-[#f8f9ff] text-[#0b1c30] min-h-screen p-6">
    <div class="max-w-3xl mx-auto">
        <a href="browseEvents.php" class="inline-flex items-center gap-1 text-[#004aaf] font-semibold text-sm mb-6 hover:underline">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Browse Events
        </a>
        <div class="bg-white rounded-xl border border-[#c2c6d7] shadow-sm overflow-hidden">
            <img src="<?php echo htmlspecialchars($poster); ?>" alt="" class="w-full h-56 object-cover" />
            <div class="p-6 space-y-4">
                <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($event['eventTitle']); ?></h1>
                <p class="flex items-center gap-2 text-[#004aaf] font-semibold">
                    <span class="material-symbols-outlined"><?php echo $clubIcon; ?></span>
                    <?php echo htmlspecialchars($clubName); ?>
                </p>
                <p class="text-sm text-[#424654] whitespace-pre-line"><?php echo htmlspecialchars($event['eventDescription']); ?></p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-[#424654]">
                    <p><strong>Date:</strong> <?php echo formatEventDate($event['eventDate']); ?></p>
                    <p><strong>Time:</strong> <?php echo formatEventTime($event['startTime']); ?> – <?php echo formatEventTime($event['endTime']); ?></p>
                    <p><strong>Venue:</strong> <?php echo $venue; ?></p>
                    <p><strong>Merit:</strong> <?php echo $merit; ?></p>
                    <p><strong>Slots:</strong>
                        <?php if ($maxSlots !== null): ?>
                            <?php echo $available; ?> / <?php echo $maxSlots; ?> available
                        <?php else: ?>
                            Open registration
                        <?php endif; ?>
                    </p>
                    <p><strong>Organizer:</strong> <?php echo htmlspecialchars($event['organizerName']); ?></p>
                </div>
                <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-[#c2c6d7]">
                    <?php if ($userRegistered): ?>
                        <span class="px-6 py-2 rounded-lg bg-[#eff4ff] text-[#004aaf] font-bold">Registered</span>
                    <?php elseif ($userWaiting): ?>
                        <span class="px-6 py-2 rounded-lg bg-[#eff4ff] text-[#004aaf] font-bold">On Waiting List</span>
                    <?php elseif ($isFull): ?>
                        <form method="POST" action="registerEvent_process.php">
                            <input type="hidden" name="eventID" value="<?php echo $eventID; ?>" />
                            <input type="hidden" name="action" value="waitlist" />
                            <button type="submit" class="px-6 py-2 bg-[#00796B] text-white font-bold rounded-lg hover:opacity-90">Join Waiting List</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="registerEvent_process.php">
                            <input type="hidden" name="eventID" value="<?php echo $eventID; ?>" />
                            <input type="hidden" name="action" value="register" />
                            <button type="submit" class="px-6 py-2 bg-[#00796B] text-white font-bold rounded-lg hover:opacity-90">Register</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
