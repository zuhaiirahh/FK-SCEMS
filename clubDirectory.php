<?php
session_start();
include 'fkscems.php';

if ($_SESSION['type'] !== 'student' && $_SESSION['type'] !== 'committee') {
    header("Location: login.php");
    exit();
}

// Fetch active clubs from the database
$query = "SELECT clubID, clubName, clubCategory, clubStatus, clubDescription FROM Club WHERE clubStatus = 'Active'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Directory - FK-SCEMS</title>
    <link rel="stylesheet" href="studentStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Club Directory</h2>
            </div>

            <div class="filter-bar" style="display: flex; gap: 15px; margin-bottom: 30px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="flex: 2; display: flex; align-items: center; gap: 10px;">
                    <label>Search club :</label>
                    <input type="text" placeholder="................................................................" style="flex: 1; padding: 8px; border: none; border-bottom: 1px dashed #ccc; outline: none;">
                </div>
                <div style="flex: 1; display: flex; align-items: center; gap: 10px;">
                    <label>Filter Category :</label>
                    <select style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; flex: 1;">
                        <option value="All">All Categories</option>
                        <option value="Academic">Academic</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                <button class="apply-btn" style="background: #008b8b; color: white; padding: 8px 20px; border: none; border-radius: 5px; cursor: pointer;">Search</button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php while ($club = mysqli_fetch_assoc($result)): ?>
                    <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #eee; box-shadow: 0 4px 6px rgba(0,0,0,0.05); position: relative;">
                        <h3 style="color: #008b8b; margin-bottom: 10px; font-size: 1.2rem;"><?php echo htmlspecialchars($club['clubName']); ?></h3>
                        <p style="margin-bottom: 5px;"><strong>Category:</strong> <?php echo htmlspecialchars($club['clubCategory']); ?></p>
                        <p style="margin-bottom: 5px;"><strong>Status:</strong> <span style="color: #28a745;"><?php echo htmlspecialchars($club['clubStatus']); ?></span></p>
                        <p style="color: #666; font-size: 0.9rem; margin-bottom: 40px;"><?php echo htmlspecialchars(substr($club['clubDescription'], 0, 80)) . '...'; ?></p>
                        
                        <a href="clubDetails.php?id=<?php echo $club['clubID']; ?>" style="position: absolute; bottom: 20px; right: 20px; background: #f4f4f4; border: 1px solid #ccc; padding: 5px 15px; border-radius: 5px; text-decoration: none; color: #333; font-size: 0.9rem;">View Details</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>

</body>
</html>