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
        $message = "<div style='color:green; padding:10px; background:#d4edda; margin-bottom:15px; border-radius:4px;'>Role Assigned Successfully!</div>";
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
    <title>Manage Committees - FK-SCEMS</title>
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
</body>
</html>