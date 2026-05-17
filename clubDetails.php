<?php
session_start();
include 'fkscems.php';

// Ensure only student can access
if ($_SESSION['type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$message = '';
$clubID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$studentUserID = $_SESSION['userID'];

// Handle Join Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_club'])) {
    if (isset($_POST['confirm_join'])) {
        $remarks = trim($_POST['remarks']);
        $joinDate = date('Y-m-d');
        
        // Check if already applied
        $checkQuery = "SELECT membershipID FROM Club_Membership WHERE clubID = ? AND userID = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "ii", $clubID, $studentUserID);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $message = "<div style='padding: 10px; background: #fff3cd; color: #856404; margin-bottom: 15px;'>You have already applied or are a member of this club.</div>";
        } else {
            // Insert Pending Request
            $insertQuery = "INSERT INTO Club_Membership (clubID, userID, membershipJoinDate, membershipStatus) VALUES (?, ?, ?, 'Pending')";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "iis", $clubID, $studentUserID, $joinDate);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $message = "<div style='padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px;'>Your request to join has been submitted successfully and is pending approval!</div>";
            }
            mysqli_stmt_close($insertStmt);
        }
        mysqli_stmt_close($checkStmt);
    } else {
        $message = "<div style='padding: 10px; background: #f8d7da; color: #721c24; margin-bottom: 15px;'>Please check the confirmation box to proceed.</div>";
    }
}

// Fetch Club Information
$clubQuery = "SELECT * FROM Club WHERE clubID = ?";
$clubStmt = mysqli_prepare($conn, $clubQuery);
mysqli_stmt_bind_param($clubStmt, "i", $clubID);
mysqli_stmt_execute($clubStmt);
$clubResult = mysqli_stmt_get_result($clubStmt);
$club = mysqli_fetch_assoc($clubResult);

if (!$club) {
    echo "Club not found!";
    exit();
}

// Fetch Committee Members for this club
$committeeQuery = "SELECT u.name, cc.rolePosition 
                   FROM Club_Committee cc
                   JOIN Student s ON cc.userID = s.userID
                   JOIN User u ON s.userID = u.userID
                   WHERE cc.clubID = ?";
$commStmt = mysqli_prepare($conn, $committeeQuery);
mysqli_stmt_bind_param($commStmt, "i", $clubID);
mysqli_stmt_execute($commStmt);
$commResult = mysqli_stmt_get_result($commStmt);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Details - FK-SCEMS</title>
    <link rel="stylesheet" href="studentStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .details-box { background: white; padding: 20px; border-radius: 8px; border: 1px solid #ccc; margin-bottom: 20px; }
        .details-box h3 { border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 15px; text-align: center; color: #333;}
        .info-row { margin-bottom: 10px; font-size: 0.95rem; }
        .info-row strong { display: inline-block; width: 150px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #f9f9f9; }
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
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <span class="user-role">Student</span>
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
                        <li><a href="clubDirectory.php" style="color: #008b8b; font-weight: bold;">- Club Directory</a></li>
                        <li><a href="myClubRequests.php" style="color: #666;">- My Club Requests</a></li>
                    </ul>
                </li>
                <li><a href="browseEvents.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="#"><i class="fas fa-award"></i> My Merit</a></li>
                <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div style="margin-bottom: 20px;">
                <a href="clubDirectory.php" style="color: #008b8b; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Directory</a>
            </div>
            
            <h2>Club Details & Join Request</h2>
            <?php echo $message; ?>

            <div style="max-width: 800px;">
                <div class="details-box">
                    <h3>Club Information</h3>
                    <div class="info-row"><strong>Club ID:</strong> <?php echo htmlspecialchars($club['clubID']); ?></div>
                    <div class="info-row"><strong>Club Name:</strong> <?php echo htmlspecialchars($club['clubName']); ?></div>
                    <div class="info-row"><strong>Club Category:</strong> <?php echo htmlspecialchars($club['clubCategory']); ?></div>
                    <div class="info-row"><strong>Club Status:</strong> <?php echo htmlspecialchars($club['clubStatus']); ?></div>
                    <div class="info-row"><strong>Description:</strong> <?php echo htmlspecialchars($club['clubDescription']); ?></div>
                </div>

                <div class="details-box">
                    <h3 style="text-align: left; border: none;">Committee Members</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Role Position</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($comm = mysqli_fetch_assoc($commResult)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($comm['name']); ?></td>
                                <td><?php echo htmlspecialchars($comm['rolePosition']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($commResult) == 0) echo "<tr><td colspan='2'>No committee members assigned.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>

                <div class="details-box" style="background: transparent; border: none; padding: 0;">
                    <form method="POST" action="">
                        <p style="margin-bottom: 10px;"><strong>Join Request Confirmation</strong><br>Optional Remarks:</p>
                        <textarea name="remarks" style="width: 100%; height: 100px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px;"></textarea>
                        
                        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 0.9rem;">
                            <input type="checkbox" name="confirm_join" value="1">
                            [] I confirm that I want to join this club
                        </label>
                        
                        <div style="display: flex; gap: 15px;">
                            <button type="submit" name="join_club" style="background: #e2e8f0; color: #333; border: 1px solid #ccc; padding: 8px 20px; border-radius: 4px; cursor: pointer;">Submit Request</button>
                            <a href="clubDirectory.php" style="background: white; color: #333; border: 1px solid #ccc; padding: 8px 20px; border-radius: 4px; text-decoration: none;">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>