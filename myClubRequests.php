<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || ($_SESSION['type'] !== 'student' && $_SESSION['type'] !== 'committee')) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// FIXED QUERY: Now explicitly asking for 'membershipStatus' instead of 'status'
$query = "SELECT c.clubName, cm.membershipJoinDate, cm.membershipStatus 
          FROM club_membership cm 
          JOIN club c ON cm.clubID = c.clubID 
          WHERE cm.userID = ? 
          ORDER BY cm.membershipJoinDate DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$requests = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>My Club Requests - FK-SCEMS</title>
    <link rel="stylesheet" href="studentStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; color: #444; font-weight: 600; }
        .badge-pending { background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; }
        .badge-approved { background: #d4edda; color: #155724; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; }
        .badge-rejected { background: #f8d7da; color: #721c24; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; }
    </style>
</head>
<body class="dashboard-body">

<header class="main-header">
    <div class="header-left">
        <img src="images/umpsaLogo.png" alt="Logo" class="mini-logo">
        <span class="system-title">FK-SCEMS</span>
    </div>
    <div class="header-right">
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?></span>
            <span class="user-role" style="text-transform: capitalize;"><?php echo htmlspecialchars($_SESSION['type']); ?></span>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="dashboard-wrapper">
    <nav class="sidebar">
        <ul>
            <li><a href="studentDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li class="active">
                <a href="clubDirectory.php"><i class="fas fa-users"></i> Club</a>
                <ul style="list-style-type: none; margin-left: 20px; font-size: 0.9rem; margin-top: 5px;">
                    <li><a href="clubDirectory.php" style="color: #666;">- Club Directory</a></li>
                    <li><a href="myClubRequests.php" style="color: #008b8b; font-weight: bold;">- My Club Requests</a></li>
                </ul>
            </li>
            <li><a href="browseEvents.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <h2 style="margin-bottom: 25px;">My Club Requests</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Club Name</th>
                        <th>Request Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($requests) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($requests)): 
                            // FIXED VARIABLE: Now explicitly pulling 'membershipStatus'
                            $currentStatus = $row['membershipStatus'];
                            
                            if ($currentStatus == 'Approved') $badge = 'badge-approved';
                            elseif ($currentStatus == 'Rejected') $badge = 'badge-rejected';
                            else $badge = 'badge-pending';
                        ?>
                            <tr>
                                <td style="font-weight: 500; color: #333;"><?php echo htmlspecialchars($row['clubName']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['membershipJoinDate']))); ?></td>
                                <td><span class="<?php echo $badge; ?>"><?php echo htmlspecialchars($currentStatus); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center; padding: 30px; color: #888;">You have not requested to join any clubs yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>