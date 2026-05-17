<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$query = "SELECT * FROM Club ORDER BY clubID DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Manage Faculty Clubs - FK-SCEMS</title>
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
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></span>
                <span class="user-role">Administrator</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li><a href="adminDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li class="active"><a href="manageFacultyClubs.php"><i class="fas fa-tasks"></i> Club Management</a>
                    <ul style="list-style:none; padding-left:20px; font-size:0.9rem; margin-top:5px;">
                        <li><a href="clubDashboard.php" style="color: #666;">- Overview</a></li>
                        <li><a href="manageFacultyClubs.php" style="color: #0056b3; font-weight:bold;">- Manage Faculty Clubs</a></li>
                        <li><a href="manageClubCommittees.php" style="color: #666;">- Manage Committees</a></li>
                    </ul>
                </li>
                <li><a href="#"><i class="fas fa-calendar-check"></i> Events</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Manage Faculty Clubs</h2>
            
            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                <input type="text" placeholder="Search Club..." style="padding:8px; border:1px solid #ccc; border-radius:4px; flex:1;">
                <button style="padding:8px 15px; background:#0056b3; color:white; border:none; border-radius:4px;">Search</button>
                <a href="createClub.php" style="padding:8px 15px; background:#28a745; color:white; border:none; border-radius:4px; text-decoration:none;">+ Create New Club</a>
            </div>

            <section class="table-section">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Club ID</th>
                                <th>Club Name</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['clubID']; ?></td>
                                <td><?php echo htmlspecialchars($row['clubName']); ?></td>
                                <td><?php echo htmlspecialchars($row['clubCategory']); ?></td>
                                <td><span class="status-badge <?php echo $row['clubStatus'] == 'Active' ? 'complete' : 'pending'; ?>"><?php echo $row['clubStatus']; ?></span></td>
                                <td><?php echo $row['clubCreatedDate']; ?></td>
                                <td><a href="#" style="color:#0056b3;">View/Edit</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>