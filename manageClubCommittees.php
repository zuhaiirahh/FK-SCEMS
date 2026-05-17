<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
// Handle assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_role'])) {
    $clubID = $_POST['clubID'];
    $userID = $_POST['userID'];
    $rolePosition = $_POST['rolePosition'];

    $stmt = mysqli_prepare($conn, "INSERT INTO Club_Committee (clubID, userID, rolePosition) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $clubID, $userID, $rolePosition);
    if(mysqli_stmt_execute($stmt)){
<<<<<<< HEAD
        $message = "<div style='color: #155724; padding: 15px; background: #d4edda; margin-bottom: 20px; border-radius: 8px; border: 1px solid #c3e6cb;'>Role Assigned Successfully!</div>";
=======
        $message = "<div style='color:green; padding:10px; background:#d4edda; margin-bottom:15px; border-radius:4px;'>Role Assigned Successfully!</div>";
>>>>>>> c442e2197c8554fdec32940e6363c6bed9342a79
    }
}

// Fetch all committee assignments to show in table
$committeeQuery = "SELECT cc.committeeID, c.clubName, u.name, cc.rolePosition 
                   FROM Club_Committee cc
                   JOIN Club c ON cc.clubID = c.clubID
                   JOIN User u ON cc.userID = u.userID";
$committees = mysqli_query($conn, $committeeQuery);

// Fetch dropdown data
$clubs = mysqli_query($conn, "SELECT clubID, clubName FROM Club WHERE clubStatus='Active'");
$students = mysqli_query($conn, "SELECT userID, name FROM User WHERE userType='student' OR userType='committee'");
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
=======
>>>>>>> c442e2197c8554fdec32940e6363c6bed9342a79
    <title>Manage Committees - FK-SCEMS</title>
    <link rel="stylesheet" href="adminStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
<<<<<<< HEAD

<header class="main-header">
    <div class="header-left">
        <img src="images/umpsaLogo.png" alt="UMPSA Logo" class="mini-logo">
        <span class="system-title">FK-SCEMS</span>
    </div>
    <div class="header-right">
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin Name'); ?></span>
            <span class="user-role">Administrator</span>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="dashboard-wrapper">
    <nav class="sidebar">
        <ul>
            <li><a href="adminDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li class="active"><a href="clubDashboard.php"><i class="fas fa-tasks"></i> Club Management</a></li>
            <li><a href="#"><i class="fas fa-calendar-check"></i> Events</a></li>
            <li><a href="#"><i class="fas fa-users-cog"></i> User Control</a></li>
            <li><a href="#"><i class="fas fa-file-alt"></i> Reporting Module</a></li>
            <li><a href="#"><i class="fas fa-user-shield"></i> My Profile</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <h2>Manage Club Committees</h2>

        <div class="module-actions">
            <a href="clubDashboard.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="manageFacultyClubs.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-building"></i> Manage Faculty Clubs
            </a>
            <a href="manageClubCommittees.php" class="module-btn">
                <i class="fas fa-user-tie"></i> Manage Club Committees
            </a>
            <a href="approveApplicants.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-user-check"></i> Approve Applicants
            </a>
        </div>

        <?php echo $message; ?>

        <div class="filter-bar" style="background: #e0f2f1; border-left: 5px solid #008b8b; margin-bottom: 30px;">
            <form method="POST" style="display: flex; gap: 15px; width: 100%; align-items: flex-end;">
                <div style="flex: 2;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #333; margin-bottom: 5px;">Select Club:</label>
                    <select name="clubID" class="filter-select" required style="width: 100%;">
                        <option value="" disabled selected>-- Choose a Club --</option>
                        <?php while($c = mysqli_fetch_assoc($clubs)): ?>
                            <option value="<?php echo $c['clubID']; ?>"><?php echo htmlspecialchars($c['clubName']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="flex: 2;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #333; margin-bottom: 5px;">Assign Student:</label>
                    <select name="userID" class="filter-select" required style="width: 100%;">
                        <option value="" disabled selected>-- Select a Student --</option>
                        <?php while($s = mysqli_fetch_assoc($students)): ?>
                            <option value="<?php echo $s['userID']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #333; margin-bottom: 5px;">Role:</label>
                    <select name="rolePosition" class="filter-select" required style="width: 100%;">
                        <option value="President">President</option>
                        <option value="Vice President">Vice President</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Treasurer">Treasurer</option>
                        <option value="Committee Member">Member</option>
                    </select>
                </div>
                <div>
                    <button type="submit" name="assign_role" class="btn btn-primary"><i class="fas fa-plus"></i> Assign</button>
                </div>
            </form>
        </div>

        <section class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Committee ID</th>
                        <th>Club Name</th>
                        <th>Student Name</th>
                        <th>Role Position</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($committees && mysqli_num_rows($committees) > 0) {
                        while($row = mysqli_fetch_assoc($committees)): 
                    ?>
                    <tr>
                        <td><?php echo $row['committeeID']; ?></td>
                        <td><?php echo htmlspecialchars($row['clubName']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><span class="status-badge status-active" style="background: #e2e8f0; color: #1e293b;"><?php echo htmlspecialchars($row['rolePosition']); ?></span></td>
                        <td>
                            <a href="#" class="action-link" style="color: #dc3545;"><i class="fas fa-trash"></i> Remove</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; color: #888;'>No committee members assigned yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

    </main>
</div>

=======
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
                        <li><a href="manageFacultyClubs.php" style="color: #666;">- Manage Faculty Clubs</a></li>
                        <li><a href="manageClubCommittees.php" style="color: #0056b3; font-weight:bold;">- Manage Committees</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Manage Club Committees</h2>
            <?php echo $message; ?>

            <div class="table-container" style="margin-bottom: 30px; background:#f9f9f9;">
                <form method="POST" style="display:flex; gap:15px; align-items:flex-end;">
                    <div style="flex:1;">
                        <label>Select Club:</label>
                        <select name="clubID" required style="width:100%; padding:8px;">
                            <?php while($c = mysqli_fetch_assoc($clubs)): ?>
                                <option value="<?php echo $c['clubID']; ?>"><?php echo htmlspecialchars($c['clubName']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Select Student:</label>
                        <select name="userID" required style="width:100%; padding:8px;">
                            <?php while($s = mysqli_fetch_assoc($students)): ?>
                                <option value="<?php echo $s['userID']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Role:</label>
                        <select name="rolePosition" required style="width:100%; padding:8px;">
                            <option value="President">President</option>
                            <option value="Vice President">Vice President</option>
                            <option value="Secretary">Secretary</option>
                        </select>
                    </div>
                    <button type="submit" name="assign_role" style="padding:9px 20px; background:#0056b3; color:white; border:none; border-radius:4px;">Assign</button>
                </form>
            </div>

            <section class="table-section">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Committee ID</th>
                                <th>Club Name</th>
                                <th>Student Name</th>
                                <th>Role Position</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($committees)): ?>
                            <tr>
                                <td><?php echo $row['committeeID']; ?></td>
                                <td><?php echo htmlspecialchars($row['clubName']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><span class="status-badge complete"><?php echo htmlspecialchars($row['rolePosition']); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
>>>>>>> c442e2197c8554fdec32940e6363c6bed9342a79
</body>
</html>