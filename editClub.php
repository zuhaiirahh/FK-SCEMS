<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$clubID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle the update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_club'])) {
    $clubName = trim($_POST['clubName']);
    $clubCategory = trim($_POST['clubCategory']);
    $clubStatus = trim($_POST['clubStatus']);
    $clubDescription = trim($_POST['clubDescription']);

    $updateStmt = mysqli_prepare($conn, "UPDATE Club SET clubName=?, clubCategory=?, clubDescription=?, clubStatus=? WHERE clubID=?");
    mysqli_stmt_bind_param($updateStmt, "ssssi", $clubName, $clubCategory, $clubDescription, $clubStatus, $clubID);
    
    if(mysqli_stmt_execute($updateStmt)){
        $message = "<div style='color:green; padding:10px; background:#d4edda; margin-bottom:15px; border-radius:4px;'>Club Updated Successfully!</div>";
    } else {
        $message = "<div style='color:red; padding:10px; background:#f8d7da; margin-bottom:15px; border-radius:4px;'>Error updating club.</div>";
    }
}

// Fetch current club details to pre-fill the form
$query = "SELECT * FROM Club WHERE clubID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $clubID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$club = mysqli_fetch_assoc($result);

if (!$club) {
    echo "Club not found or invalid ID.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Edit Club - FK-SCEMS</title>
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
            </ul>
        </nav>

        <main class="main-content" style="padding: 40px; max-width: 800px; margin: auto;">
            <h2>Edit Club Information</h2>
            <a href="manageFacultyClubs.php" style="margin-bottom: 20px; display: block; color: #0056b3;">&larr; Back to Clubs</a>
            
            <?php echo $message; ?>

            <div class="table-container">
                <form method="POST">
                    <div style="margin-bottom: 15px;">
                        <label>Club Name:</label><br>
                        <input type="text" name="clubName" value="<?php echo htmlspecialchars($club['clubName']); ?>" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Category:</label><br>
                        <select name="clubCategory" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                            <option value="Academic" <?php if($club['clubCategory'] == 'Academic') echo 'selected'; ?>>Academic</option>
                            <option value="Sports" <?php if($club['clubCategory'] == 'Sports') echo 'selected'; ?>>Sports</option>
                            <option value="Culture" <?php if($club['clubCategory'] == 'Culture') echo 'selected'; ?>>Culture</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Status:</label><br>
                        <select name="clubStatus" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                            <option value="Active" <?php if($club['clubStatus'] == 'Active') echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if($club['clubStatus'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Description:</label><br>
                        <textarea name="clubDescription" style="width:100%; padding:8px; height:100px; border:1px solid #ccc; border-radius:4px;"><?php echo htmlspecialchars($club['clubDescription']); ?></textarea>
                    </div>
                    <button type="submit" name="update_club" style="padding:10px 20px; background:#0056b3; color:white; border:none; border-radius:4px; cursor:pointer;">Update Club</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>