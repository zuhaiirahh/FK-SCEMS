<?php
session_start();

// Jika belum login, tendang balik ke login.php
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// (Optional) Jika student cuba masuk page admin
if ($_SESSION['role'] !== 'admin' && basename($_SERVER['PHP_SELF']) == 'adminDashboard.php') {
    header("Location: dashboardSwitcher.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FK-SCEMS</title>
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
                <span class="user-name"><?php echo $_SESSION['name']; ?></span>
                <span class="user-role">Administrator</span>
            </div>
            <a href="login.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li class="active">
                    <a href="#"><i class="fas fa-chart-line"></i> Dashboard</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-tasks"></i> Club Management</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-calendar-check"></i> Events</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-users-cog"></i> User Control</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-file-alt"></i> Reporting Module</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-user-shield"></i> My Profile</a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Participation & Attendance Analytics</h2>

            <section class="filter-bar">
                <select class="filter-select">
                    <option disabled selected>Club</option>
                    <option>Cybersecurity Club</option>
                    <option>Robotics Club</option>
                </select>
                <select class="filter-select">
                    <option disabled selected>Semester</option>
                    <option>Sem 1 2025/2026</option>
                    <option>Sem 2 2025/2026</option>
                </select>
                <select class="filter-select">
                    <option disabled selected>Event Type</option>
                    <option>Academic</option>
                    <option>Sports</option>
                </select>
                <button class="apply-btn">Apply</button>
            </section>

            <div class="insight-cards">
                <div class="card">
                    <span class="card-title">Registered Student</span>
                    <span class="card-value">1,240</span>
                </div>
                <div class="card">
                    <span class="card-title">Active Club</span>
                    <span class="card-value">42</span>
                </div>
                <div class="card">
                    <span class="card-title">Upcoming Event</span>
                    <span class="card-value">12</span>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-box">
                    <h4>Student Participation</h4>
                    <div class="placeholder-chart">
                        <span>[ Bar Chart Visualization ]</span>
                    </div>
                </div>
                <div class="chart-box">
                    <h4>Points Distribution</h4>
                    <div class="placeholder-chart">
                        <span>[ Pie Chart Visualization ]</span>
                    </div>
                </div>
            </div>

            <section class="table-section">
                <h3>Top Participants & Recognition Status</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Club</th>
                                <th>Points</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Ahmad Firdaus</td>
                                <td>Software Engineering Club</td>
                                <td>520</td>
                                <td><span class="status-badge complete">Gold</span></td>
                            </tr>
                            <tr>
                                <td>Siti Nurhaliza</td>
                                <td>Data Science Club</td>
                                <td>480</td>
                                <td><span class="status-badge complete">Silver</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="table-section">
                <h3>Recent User Registration</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Date Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CB24001</td>
                                <td>Mohd Faizal</td>
                                <td>Student</td>
                                <td>10 May 2026</td>
                            </tr>
                            <tr>
                                <td>AD9012</td>
                                <td>Sarah Tan</td>
                                <td>Committee</td>
                                <td>09 May 2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

</body>

</html>