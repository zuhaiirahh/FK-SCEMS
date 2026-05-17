<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header("Location: login.php");
    exit();
}

// Fetch pending memberships for clubs managed by this committee member
$committeeUserID = $_SESSION['userID'];
$query = "SELECT cm.membershipID, s.studentID, u.name, c.clubName, cm.membershipJoinDate, cm.membershipStatus 
          FROM Club_Membership cm
          JOIN Student s ON cm.userID = s.userID
          JOIN User u ON s.userID = u.userID
          JOIN Club c ON cm.clubID = c.clubID
          JOIN Club_Committee cc ON c.clubID = cc.clubID
          WHERE cc.userID = ? AND cm.membershipStatus = 'Pending'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $committeeUserID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pendingCount = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Applicants - FK-SCEMS</title>
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
                <span class="user-role">Committee Member</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li><a href="committeeDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li class="active">
                    <a href="#"><i class="fas fa-users"></i> Membership</a>
                    <ul style="list-style-type: none; margin-left: 20px; font-size: 0.9rem; margin-top: 5px;">
                        <li><a href="approveApplicants.php" style="color: #008b8b; font-weight: bold;">- Approve Applicants</a></li>
                    </ul>
                </li>
                <li><a href="manageEvent.php"><i class="fas fa-calendar-check"></i> Event Management</a></li>
                <li><a href="#"><i class="fas fa-clipboard-list"></i> Attendance</a></li>
                <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Approve Applicants</h2>

            <div style="background: #e9ecef; border-left: 4px solid #008b8b; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <strong>Notification:</strong> <?php echo $pendingCount; ?> Pending Membership Requests
            </div>

            <div class="filter-bar" style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label>Filter Status: </label>
                    <select style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
                        <option>All</option>
                        <option>Pending</option>
                    </select>
                </div>
                <div>
                    <label>Select Club to Manage: </label>
                    <select style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
                        <option>select club name</option>
                        </select>
                </div>
            </div>

            <div class="table-container" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ddd;">
                            <th style="padding: 10px;">Membership ID</th>
                            <th style="padding: 10px;">Student ID (Student Name)</th>
                            <th style="padding: 10px;">Membership Join Date</th>
                            <th style="padding: 10px;">Status</th>
                            <th style="padding: 10px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pendingCount > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;"><?php echo htmlspecialchars($row['membershipID']); ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($row['studentID'] . ' (' . $row['name'] . ')'); ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($row['membershipJoinDate']); ?></td>
                                <td style="padding: 10px;"><span style="color: #d97706; font-weight: bold; background: #fef3c7; padding: 4px 8px; border-radius: 4px;"><?php echo htmlspecialchars($row['membershipStatus']); ?></span></td>
                                <td style="padding: 10px;">
                                    <select style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
                                        <option>Action</option>
                                        <option value="approve">Approve</option>
                                        <option value="reject">Reject</option>
                                    </select>
                                    <button style="margin-left: 5px; padding: 5px 10px; border: 1px solid #ccc; background: #f9f9f9; cursor: pointer; border-radius: 4px;">View</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px;">No pending applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>