<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_club'])) {
    $clubName = trim($_POST['clubName']);
    $clubCategory = trim($_POST['clubCategory']);
    $clubStatus = trim($_POST['clubStatus']);
    $clubDescription = trim($_POST['clubDescription']);
    $createdDate = date('Y-m-d');

    $stmt = mysqli_prepare($conn, "INSERT INTO Club (clubName, clubCategory, clubDescription, clubStatus, clubCreatedDate) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $clubName, $clubCategory, $clubDescription, $clubStatus, $createdDate);
    
    if(mysqli_stmt_execute($stmt)){
        $message = "<div style='color: #155724; padding: 15px; background: #d4edda; margin-bottom: 20px; border-radius: 8px; border: 1px solid #c3e6cb;'>Club Created Successfully!</div>";
    } else {
        $message = "<div style='color: #721c24; padding: 15px; background: #f8d7da; margin-bottom: 20px; border-radius: 8px; border: 1px solid #f5c6cb;'>Error creating club.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Club - FK-SCEMS</title>
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
        <h2>Create / Update Club</h2>

        <div class="module-actions">
            <a href="manageFacultyClubs.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-arrow-left"></i> Back to Club List
            </a>
        </div>

        <?php echo $message; ?>

        <div class="table-container" style="max-width: 800px;">
            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #444; margin-bottom: 8px;">Club Name:</label>
                    <input type="text" name="clubName" class="filter-input" required style="width: 100%;">
                </div>
                
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: 600; color: #444; margin-bottom: 8px;">Category:</label>
                        <select name="clubCategory" class="filter-select" style="width: 100%;">
                            <option value="Academic">Academic</option>
                            <option value="Sports">Sports</option>
                            <option value="Culture">Culture</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: 600; color: #444; margin-bottom: 8px;">Status:</label>
                        <select name="clubStatus" class="filter-select" style="width: 100%;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; color: #444; margin-bottom: 8px;">Description:</label>
                    <textarea name="clubDescription" class="filter-input" style="width: 100%; height: 120px; resize: vertical;"></textarea>
                </div>
                
                <button type="submit" name="submit_club" class="btn btn-success"><i class="fas fa-save"></i> Save Club Details</button>
            </form>
        </div>
    </main>
</div>

</body>

</html>