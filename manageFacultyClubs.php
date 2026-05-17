<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all clubs from the database
$query = "SELECT clubID, clubName, clubCategory, clubStatus, clubCreatedDate FROM Club ORDER BY clubID DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin Name'); ?>
            </span>
            <span class="user-role">Administrator</span>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="dashboard-wrapper">

    <nav class="sidebar">
        <ul>
            <li>
                <a href="adminDashboard.php">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li class="active">
                <a href="clubDashboard.php">
                    <i class="fas fa-tasks"></i> Club Management
                </a>
            </li>
            <li>
                <a href="adminEventManagement.php">
                    <i class="fas fa-calendar-check"></i> Events
                </a>
            </li>
            <li>
                <a href="adminUserControl.php">
                    <i class="fas fa-users-cog"></i> User Control
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-file-alt"></i> Reporting Module
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-user-shield"></i> My Profile
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content">

        <h2>Manage Faculty Clubs</h2>

        <div class="module-actions">
            <a href="clubDashboard.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="manageFacultyClubs.php" class="module-btn">
                <i class="fas fa-building"></i> Manage Faculty Clubs
            </a>
            <a href="manageClubCommittees.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-user-tie"></i> Manage Club Committees
            </a>
            <a href="approveApplicants.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-user-check"></i> Approve Applicants
            </a>
        </div>

        <section class="filter-bar">
            <input type="text" class="filter-input" placeholder="Search Club (ID or Name)...">
            
            <select class="filter-select">
                <option value="All" selected>All Categories</option>
                <option value="Academic">Academic</option>
                <option value="Sports">Sports</option>
                <option value="Culture">Culture</option>
            </select>
            
            <select class="filter-select">
                <option value="All" selected>All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
            
            <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <a href="createClub.php" class="btn btn-success" style="margin-left: auto;"><i class="fas fa-plus"></i> Create New Club</a>
        </section>

        <section class="table-container">
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
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { 
                            $statusClass = ($row['clubStatus'] === 'Active') ? 'status-active' : 'status-inactive';
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['clubID']); ?></td>
                            <td><?php echo htmlspecialchars($row['clubName']); ?></td>
                            <td><?php echo htmlspecialchars($row['clubCategory']); ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['clubStatus']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['clubCreatedDate']); ?></td>
                            <td>
                                <a href="editClub.php?id=<?php echo $row['clubID']; ?>" class="action-link"><i class="fas fa-edit"></i> View / Edit</a>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; color: #888;'>No clubs found in the database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

    </main>
</div>

</body>
</html>