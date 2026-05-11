<?php
session_start();
// Pastikan hanya student atau committee boleh akses
if ($_SESSION['type'] !== 'student' && $_SESSION['type'] !== 'committee') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events - FK-SCEMS</title>
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
                <span class="user-name"><?php echo $_SESSION['name']; ?></span>
                <span class="user-role">Student</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li><a href="studentDashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Clubs</a></li>
                <li class="active">
                    <a href="browseEvents.php"><i class="fas fa-calendar-alt"></i> Events</a>
                    <ul class="submenu">
                        <li><a href="browseEvents.php" class="active-sub">- Browse Event</a></li>
                        <li><a href="eventRegistration.php">- Event Registration</a></li>
                        <li><a href="registrationHistory.php">- My Registration History</a></li>
                    </ul>
                </li>
                <li><a href="#"><i class="fas fa-star"></i> My Merit</a></li>
                <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2 class="section-label">Browse Upcoming Events</h2>

            <div class="search-filter-container">
                <div class="search-box">
                    <label>Search Event :</label>
                    <input type="text" placeholder="search event title">
                </div>
                <div class="filter-box">
                    <label>Filter Club :</label>
                    <select>
                        <option value="All">All</option>
                        <option value="FK-CSC">FK Computer Science Club</option>
                        <option value="PETAK">PETAK</option>
                    </select>
                </div>
            </div>

            <div class="event-list">
                <div class="event-card">
                    <div class="event-details">
                        <h3>Tech Talk: AI Future</h3>
                        <p><strong>Club:</strong> FK Computer Science Club</p>
                        <p><strong>Date & Time:</strong> 20 May 2026, 09:00 AM</p>
                        <p><strong>Venue:</strong> DK 1, FK Building</p>
                        <p><strong>Available Slot:</strong> 45</p>
                    </div>
                    <div class="event-actions">
                        <button class="view-btn">View Detail</button>
                        <button class="register-btn">Register</button>
                    </div>
                </div>

                <div class="event-card">
                    <div class="event-details">
                        <h3>Futsal Tournament</h3>
                        <p><strong>Club:</strong> PETAK Society</p>
                        <p><strong>Date & Time:</strong> 25 May 2026, 05:00 PM</p>
                        <p><strong>Venue:</strong> UMPSA Sports Complex</p>
                        <p><strong>Available Slot:</strong> <span class="full-tag">Full</span></p>
                    </div>
                    <div class="event-actions">
                        <button class="view-btn">View Detail</button>
                        <button class="waiting-btn">Join Waiting List</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>