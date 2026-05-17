<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header('Location: login.php');
    exit();
}

$userID = (int) $_SESSION['userID'];
$events = [];

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT eventID, eventTitle, eventCategory, eventDate, startTime, venue, status, createdAt
        FROM event
        WHERE createdBy = ?
        ORDER BY createdAt DESC
    ");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

$flashSuccess = $_SESSION['createEvent_success'] ?? '';
unset($_SESSION['createEvent_success']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - FK-SCEMS</title>
    <link rel="stylesheet" href="committeeStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="dashboard-body">

    <header class="main-header">
        <div class="header-left">
            <img src="images/umpsaLogo.png" alt="UMPSA Logo" class="mini-logo">
            <span class="system-title">FK-SCEMS</span>
        </div>
        <div class="header-right">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <span class="user-role">Club Member</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li><a href="committeeDashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li class="active has-submenu open">
                    <a href="#"><i class="fas fa-calendar-alt"></i> Event</a>
                    <ul class="submenu">
                        <li><a href="manageEvent.php" class="active-sub">My Events</a></li>
                        <li><a href="createEvent.php">Create Event</a></li>
                        <li><a href="#">Registered Events</a></li>
                        <li><a href="#">Upcoming Events</a></li>
                        <li><a href="#">Past Events</a></li>
                    </ul>
                </li>
                <li><a href="#"><i class="fas fa-clipboard-list"></i> My Registrations</a></li>
                <li><a href="#"><i class="fas fa-user-circle"></i> Profile</a></li>
                <li><a href="#"><i class="fas fa-bell"></i> Notifications</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1>My Events</h1>
                    <p class="page-subtitle">Manage events you have created.</p>
                </div>
                <a href="createEvent.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Event</a>
            </div>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($flashSuccess); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Venue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;color:#888;padding:24px;">
                                    No events yet. <a href="createEvent.php">Create your first event</a>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['eventTitle']); ?></td>
                                    <td><?php echo htmlspecialchars($event['eventCategory']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($event['eventDate']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('g:i A', strtotime($event['startTime']))); ?></td>
                                    <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = $event['status'] === 'approved' ? 'complete' : 'pending';
                                        $statusLabel = ucwords(str_replace('_', ' ', $event['status']));
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>

</html>
