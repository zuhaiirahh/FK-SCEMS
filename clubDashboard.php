<?php
session_start();
include 'fkscems.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Fetch stats for summary cards
$totalClubs = $activeClubs = $inactiveClubs = $totalStudents = 0;

$statQuery = "SELECT 
                COUNT(*) as total, 
                SUM(CASE WHEN clubStatus = 'Active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN clubStatus = 'Inactive' THEN 1 ELSE 0 END) as inactive 
              FROM Club";
$statResult = mysqli_query($conn, $statQuery);

if ($statResult && $row = mysqli_fetch_assoc($statResult)) {
    $totalClubs     = (int)$row['total'];
    $activeClubs    = (int)$row['active'];
    $inactiveClubs  = (int)$row['inactive'];
}

// Ambil jumlah pelajar (Sila tukar nama table 'ClubMembership' jika berbeza di database anda)
$studentQuery = "SELECT COUNT(DISTINCT studentID) as total_stud FROM ClubMembership";
$studentResult = mysqli_query($conn, $studentQuery);
if ($studentResult && $sRow = mysqli_fetch_assoc($studentResult)) {
    $totalStudents = (int)$sRow['total_stud'];
}

// 2. Fetch data for Bar Chart (Sila tukar nama table jika berbeza)
$labels = [];
$values = [];

$chartQuery = "SELECT c.clubName, COUNT(m.studentID) as student_count 
               FROM Club c 
               LEFT JOIN ClubMembership m ON c.clubID = m.clubID 
               GROUP BY c.clubID";
$chartResult = mysqli_query($conn, $chartQuery);

if ($chartResult) {
    while ($cRow = mysqli_fetch_assoc($chartResult)) {
        $labels[] = $cRow['clubName'];
        $values[] = (int)$cRow['student_count'];
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Dashboard - FK-SCEMS</title>
    <link rel="stylesheet" href="adminStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { flex: 1; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; }
        .stat-card p { margin: 0; font-size: 24px; font-weight: bold; color: #333; }
        .charts-wrapper { display: flex; gap: 20px; }
        .chart-box { flex: 1; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    </style>
</head>

<body class="dashboard-body">

<header class="main-header">
    <div class="header-left">
        <img src="images/umpsaLogo.png" alt="UMPSA Logo" class="mini-logo">
        <span class="system-title">FK-SCEMS</span>
    </div>

    <div class="header-right">
        <div class="user-info">
            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin Name'); ?>
            </span>
            <span class="user-role">Administrator</span>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="dashboard-wrapper">

    <nav class="sidebar">
        <ul>
            <li>
                <a href="adminDashboard.php">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li class="active">
                <a href="clubDashboard.php">
                    <i class="fas fa-tasks"></i> Club Management
                </a>
            </li>
            <li>
                <a href="adminEventManagement.php">
                    <i class="fas fa-calendar-check"></i> Events
                </a>
            </li>
            <li>
                <a href="adminUserControl.php">
                    <i class="fas fa-users-cog"></i> User Control
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-file-alt"></i> Reporting Module
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-user-shield"></i> My Profile
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content">

        <h2>Club Dashboard</h2>

        <div class="module-actions">
            <a href="clubDashboard.php" class="module-btn">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="manageFacultyClubs.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-building"></i> Manage Faculty Clubs
            </a>
            <a href="manageClubCommittees.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-user-tie"></i> Manage Club Committees
            </a>
            <a href="approveApplicants.php" class="module-btn" style="background: #f4f4f4; color: #333; border: 1px solid #ccc;">
                <i class="fas fa-user-check"></i> Approve Applicants
            </a>
        </div>

        <section class="stats-container">
            <div class="stat-card">
                <h3>Total Clubs</h3>
                <p><?php echo $totalClubs; ?></p>
            </div>
            <div class="stat-card" style="border-top: 4px solid #28a745;">
                <h3>Active Clubs</h3>
                <p style="color: #28a745;"><?php echo $activeClubs; ?></p>
            </div>
            <div class="stat-card" style="border-top: 4px solid #dc3545;">
                <h3>Inactive Clubs</h3>
                <p style="color: #dc3545;"><?php echo $inactiveClubs; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Students</h3>
                <p><?php echo $totalStudents; ?></p>
            </div>
        </section>

        <section class="charts-wrapper">
            <div class="chart-box">
                <h3>Student Analytics per Club</h3>
                <canvas id="barChart" height="200"></canvas>
            </div>
            <div class="chart-box" style="max-width: 400px;">
                <h3>Club Status Distribution</h3>
                <canvas id="pieChart"></canvas>
            </div>
        </section>

    </main>
</div>

    <script>
        // Graf Bar
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: { 
                labels: <?php echo json_encode($labels); ?>, 
                datasets: [{ 
                    label: 'Students', 
                    data: <?php echo json_encode($values); ?>, 
                    backgroundColor: '#0056b3' 
                }] 
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Graf Pie
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: { 
                labels: ['Active', 'Inactive'], 
                datasets: [{ 
                    data: [<?php echo $activeClubs; ?>, <?php echo $inactiveClubs; ?>], 
                    backgroundColor: ['#28a745', '#dc3545'] 
                }] 
            },
            options: { responsive: true }
        });
    </script>
</body>
</html>