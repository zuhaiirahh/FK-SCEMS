<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || ($_SESSION['type'] !== 'student' && $_SESSION['type'] !== 'committee')) {
    header("Location: login.php");
    exit();
}

$message = '';
$clubID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userID = $_SESSION['userID'];

// Handle Join Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_club'])) {
    if (isset($_POST['confirm_join'])) {
        // Check if already applied using the fixed 'status' column name
        $checkStmt = mysqli_prepare($conn, "SELECT membershipID FROM club_membership WHERE clubID = ? AND userID = ?");
        mysqli_stmt_bind_param($checkStmt, "ii", $clubID, $userID);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $message = "<div style='padding: 15px; background: #fff3cd; color: #856404; border-radius: 8px; margin-bottom: 20px;'>You have already applied or are a member of this club. Check 'My Club Requests'.</div>";
        } else {
            $joinDate = date('Y-m-d');
            $insertStmt = mysqli_prepare($conn, "INSERT INTO club_membership (clubID, userID, membershipJoinDate, status) VALUES (?, ?, ?, 'Pending')");
            mysqli_stmt_bind_param($insertStmt, "iis", $clubID, $userID, $joinDate);
            if (mysqli_stmt_execute($insertStmt)) {
                $message = "<div style='padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px;'>Your request to join has been submitted successfully!</div>";
            }
        }
    } else {
        $message = "<div style='padding: 15px; background: #f8d7da; color: #721c24; border-radius: 8px; margin-bottom: 20px;'>Please check the confirmation box to proceed.</div>";
    }
}

// Fetch Club Info
$clubStmt = mysqli_prepare($conn, "SELECT * FROM club WHERE clubID = ?");
mysqli_stmt_bind_param($clubStmt, "i", $clubID);
mysqli_stmt_execute($clubStmt);
$club = mysqli_fetch_assoc(mysqli_stmt_get_result($clubStmt));

// Fetch Committee Members
$commStmt = mysqli_prepare($conn, "SELECT u.name, cc.rolePosition FROM club_committee cc JOIN user u ON cc.userID = u.userID WHERE cc.clubID = ?");
mysqli_stmt_bind_param($commStmt, "i", $clubID);
mysqli_stmt_execute($commStmt);
$committees = mysqli_stmt_get_result($commStmt);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Club Details - FK-SCEMS</title>
    <link rel="stylesheet" href="studentStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .details-box { background: white; padding: 25px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .details-box h3 { border-bottom: 2px solid #f4f4f4; padding-bottom: 10px; margin-bottom: 15px; color: #333; }
        .info-row { margin-bottom: 12px; font-size: 1rem; color: #444; }
        .info-row strong { display: inline-block; width: 150px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #f9f9f9; color: #444; }
    </style>
</head>
<body class="dashboard-body">

<header class="main-header">
    <div class="header-left">
        <img src="images/umpsaLogo.png" alt="Logo" class="mini-logo">
        <span class="system-title">FK-SCEMS</span>
    </div>
    <div class="header-right">
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
                    <li><a href="clubDirectory.php" style="color: #008b8b; font-weight: bold;">- Club Directory</a></li>
                    <li><a href="myClubRequests.php" style="color: #666;">- My Club Requests</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <main class="main-content">
        <a href="clubDirectory.php" style="display: inline-block; margin-bottom: 20px; color: #008b8b; text-decoration: none; font-weight: bold;"><i class="fas fa-arrow-left"></i> Back to Directory</a>
        
        <h2>Club Details & Join Request</h2>
        <?php echo $message; ?>

        <div style="max-width: 800px;">
            <div class="details-box">
                <h3>Club Information</h3>
                <div class="info-row"><strong>Club Name:</strong> <?php echo htmlspecialchars($club['clubName'] ?? 'Unknown'); ?></div>
                <div class="info-row"><strong>Category:</strong> <?php echo htmlspecialchars($club['clubCategory'] ?? ''); ?></div>
                <div class="info-row"><strong>Description:</strong> <?php echo htmlspecialchars($club['clubDescription'] ?? ''); ?></div>
            </div>

            <div class="details-box">
                <h3>Committee Members</h3>
                <table>
                    <thead>
                        <tr><th>Student Name</th><th>Role Position</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($comm = mysqli_fetch_assoc($committees)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($comm['name']); ?></td>
                                <td><span style="background: #e2e8f0; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: bold;"><?php echo htmlspecialchars($comm['rolePosition']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="details-box" style="background: #f8fbfa; border: 1px solid #008b8b;">
                <h3>Join Request Confirmation</h3>
                <form method="POST">
                    <p style="margin-bottom: 10px; color: #555;"><strong>Optional Remarks:</strong> Why do you want to join?</p>
                    <textarea name="remarks" style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 15px;"></textarea>
                    
                    <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-weight: bold; color: #333; cursor: pointer;">
                        <input type="checkbox" name="confirm_join" value="1" style="width: 18px; height: 18px;">
                        I confirm that I want to join this club.
                    </label>
                    
                    <button type="submit" name="join_club" style="background: #008b8b; color: white; padding: 10px 25px; border: none; border-radius: 5px; font-weight: bold; font-size: 1rem; cursor: pointer;"><i class="fas fa-paper-plane"></i> Submit Request</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>