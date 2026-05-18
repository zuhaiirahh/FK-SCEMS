<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || ($_SESSION['type'] !== 'student' && $_SESSION['type'] !== 'committee')) {
    header("Location: login.php");
    exit();
}

// Fetch active clubs
$query = "SELECT * FROM club WHERE clubStatus = 'Active' ORDER BY clubName ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Club Directory - FK-SCEMS</title>
    <link rel="stylesheet" href="studentStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .club-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .club-card { background: white; border: 1px solid #eee; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; }
        .club-card h3 { color: #008b8b; margin-bottom: 10px; font-size: 1.2rem; }
        .club-card p { color: #555; font-size: 0.9rem; margin-bottom: 8px; }
        .view-details-btn { display: inline-block; text-align: center; background: #f4f4f4; border: 1px solid #ccc; color: #333; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin-top: 15px; font-weight: bold; transition: 0.2s; }
        .view-details-btn:hover { background: #e0e0e0; }
        .filter-bar { display: flex; gap: 15px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; align-items: center; }
        .filter-bar input, .filter-bar select { padding: 10px; border: 1px solid #ccc; border-radius: 5px; flex: 1; }
        .filter-bar button { padding: 10px 20px; background: #008b8b; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
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
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?></span>
            <span class="user-role" style="text-transform: capitalize;"><?php echo htmlspecialchars($_SESSION['type']); ?></span>
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
        </ul>
    </nav>

    <main class="main-content">
        <h2>Club Directory</h2>

        <div class="filter-bar">
            <input type="text" placeholder="Search club name...">
            <select>
                <option value="All">All Categories</option>
                <option value="Academic">Academic</option>
                <option value="Sports">Sports</option>
                <option value="Culture">Culture</option>
            </select>
            <button><i class="fas fa-search"></i> Search</button>
        </div>

        <div class="club-grid">
            <?php while ($club = mysqli_fetch_assoc($result)): ?>
                <div class="club-card">
                    <div>
                        <h3><?php echo htmlspecialchars($club['clubName']); ?></h3>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($club['clubCategory']); ?></p>
                        <p><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">Active</span></p>
                        <p style="margin-top: 10px; line-height: 1.4; color: #666;">
                            <?php echo htmlspecialchars(substr($club['clubDescription'], 0, 90)) . '...'; ?>
                        </p>
                    </div>
                    <a href="clubDetails.php?id=<?php echo $club['clubID']; ?>" class="view-details-btn">View Details</a>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
</div>
</body>
</html>