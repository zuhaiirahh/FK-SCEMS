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
        $message = "<div style='color:green; padding:10px; background:#d4edda; margin-bottom:15px; border-radius:4px;'>Club Created Successfully!</div>";
    } else {
        $message = "<div style='color:red; padding:10px; background:#f8d7da; margin-bottom:15px; border-radius:4px;'>Error creating club.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Create Club - FK-SCEMS</title>
    <link rel="stylesheet" href="adminStyle.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <main class="main-content" style="padding: 40px; max-width: 800px; margin: auto;">
            <h2>Create / Update Club</h2>
            <a href="manageFacultyClubs.php" style="margin-bottom: 20px; display: block; color: #0056b3;">&larr; Back to Clubs</a>
            
            <?php echo $message; ?>

            <div class="table-container">
                <form method="POST">
                    <div style="margin-bottom: 15px;">
                        <label>Club Name:</label><br>
                        <input type="text" name="clubName" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Category:</label><br>
                        <select name="clubCategory" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                            <option value="Academic">Academic</option>
                            <option value="Sports">Sports</option>
                            <option value="Culture">Culture</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Status:</label><br>
                        <select name="clubStatus" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Description:</label><br>
                        <textarea name="clubDescription" style="width:100%; padding:8px; height:100px; border:1px solid #ccc; border-radius:4px;"></textarea>
                    </div>
                    <button type="submit" name="submit_club" style="padding:10px 20px; background:#28a745; color:white; border:none; border-radius:4px; cursor:pointer;">Save</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>