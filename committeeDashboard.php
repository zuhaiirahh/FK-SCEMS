<?php
session_start();
include 'fkscems.php';

// Sekatan akses: Hanya committee sahaja
if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header("Location: login.php");
    exit();
}

// Data dummy (Nanti anda boleh tukar dengan query SQL)
$totalMember = 150;
$activeEvents = 3;
$pendingApps = 12;
$waitlist = 5;
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Dashboard - FK-SCEMS</title>
    <link rel="stylesheet" href="adminStyle.css">
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
                <span class="user-name"><?php echo $_SESSION['name']; ?></span>
                <span class="user-role">Club Member</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li class="active">
                    <a href="committeeDashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                </li>
                <li>
                    <a href="membership.php"><i class="fas fa-users"></i> Membership</a>
                </li>
                <li>
                    <a href="manageEvent.php"><i class="fas fa-calendar-alt"></i> Event Management</a>
                </li>
                <li>
                    <a href="attendance.php"><i class="fas fa-clipboard-check"></i> Attendance</a>
                </li>
                <li>
                    <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <h2 class="section-label">Quick Insights</h2>

            <div class="insight-cards committee-grid">
                <div class="card">
                    <span class="card-title">Total Member</span>
                    <span class="card-value"><?php echo $totalMember; ?></span>
                </div>
                <div class="card">
                    <span class="card-title">Active Events</span>
                    <span class="card-value"><?php echo $activeEvents; ?></span>
                </div>
                <div class="card">
                    <span class="card-title">Pending Apps</span>
                    <span class="card-value"><?php echo $pendingApps; ?></span>
                </div>
                <div class="card">
                    <span class="card-title">Waitlist</span>
                    <span class="card-value"><?php echo $waitlist; ?></span>
                </div>
            </div>

            <h3>Recent Activities</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Activity Name</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Membership Renewal</td>
                            <td>System</td>
                            <td>10 May 2026</td>
                            <td><span class="status-badge complete">Completed</span></td>
                        </tr>
                        <tr>
                            <td>Tech Talk 2026</td>
                            <td>Event</td>
                            <td>12 May 2026</td>
                            <td><span class="status-badge pending">Pending</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>

</html>