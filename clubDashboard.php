<?php
session_start();
include 'fkscems.php';

// Sekatan keselamatan admin
if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch stats for cards
$totalClubs = $activeClubs = $inactiveClubs = $totalStudents = 0;

$query = "SELECT 
            COUNT(*) as total, 
            SUM(CASE WHEN clubStatus = 'Active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN clubStatus = 'Inactive' THEN 1 ELSE 0 END) as inactive 
          FROM Club";
$result = mysqli_query($conn, $query);
if ($row = mysqli_fetch_assoc($result)) {
    $totalClubs = $row['total'] ?? 0;
    $activeClubs = $row['active'] ?? 0;
    $inactiveClubs = $row['inactive'] ?? 0;
}

// Chart Data: Students per club (Diselaraskan sekiranya kolum bernama 'status')
$chartDataQuery = "SELECT c.clubName, COUNT(cm.userID) as total_members 
                   FROM Club c 
                   LEFT JOIN Club_Membership cm ON c.clubID = cm.clubID
                   GROUP BY c.clubID";
$chartResult = mysqli_query($conn, $chartDataQuery);
$labels = [];
$values = [];
while ($cRow = mysqli_fetch_assoc($chartResult)) {
    $labels[] = $cRow['clubName'];
    $values[] = $cRow['total_members'];
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
        /* Elemen CSS tambahan untuk mengendalikan reka bentuk sub-menu bercabang */
        .sidebar ul li.has-submenu {
            display: block;
        }

        .sidebar ul .submenu-list {
            list-style: none;
            padding-left: 15px;
            background-color: #fcfdfd;
        }

        .sidebar ul .submenu-list li a {
            padding: 10px 25px;
            font-size: 0.85rem;
            color: #555;
        }

        /* Pewarnaan aktif khusus untuk sub-menu yang sedang dibuka */
        .sidebar ul .submenu-list li.sub-active a {
            background-color: #d1eefc;
            font-weight: bold;
            border-left: 4px solid #008b8b;
            color: #000;
        }

        .cards-row {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            flex: 1;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            border-top: 4px solid #008b8b;
        }

        .card-title {
            display: block;
            color: #666;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }

        .charts-row {
            display: flex;
            gap: 20px;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            flex: 1;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            height: 380px;
            position: relative;
        }

        .chart-container h4 {
            margin-bottom: 15px;
            color: #444;
            font-size: 1rem;
        }

        .chart-container canvas {
            max-height: 300px !important;
            width: 100% !important;
        }
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
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin Name'); ?></span>
                <span class="user-role">Administrator</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li>
                    <a href="adminDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                </li>
                <li>
                    <a href="adminUserControl.php"><i class="fas fa-users-cog"></i> User Control</a>
                </li>

                <li class="has-submenu">
                    <a href="#" style="background: #f4f6f7; font-weight: 600;"><i class="fas fa-tasks"></i> Club Control</a>
                    <ul class="submenu-list">
                        <li class="sub-active">
                            <a href="clubDashboard.php"><i class="far fa-circle" style="font-size:0.7rem;"></i> Manage Faculty Clubs</a>
                        </li>
                        <li>
                            <a href="manageClubCommittees.php"><i class="far fa-circle" style="font-size:0.7rem;"></i> Manage Club Committees</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="#"><i class="fas fa-file-alt"></i> Reporting</a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Club Dashboard Management</h2>

            <div class="cards-row">
                <div class="card">
                    <span class="card-title">Total Clubs</span>
                    <span class="card-value"><?php echo $totalClubs; ?></span>
                </div>
                <div class="card">
                    <span class="card-title">Active Clubs</span>
                    <span class="card-value" style="color: #27ae60;"><?php echo $activeClubs; ?></span>
                </div>
                <div class="card">
                    <span class="card-title">Inactive Clubs</span>
                    <span class="card-value" style="color: #dc3545;"><?php echo $inactiveClubs; ?></span>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-container">
                    <h4>Student Distribution Across Clubs</h4>
                    <canvas id="barChart"></canvas>
                </div>
                <div class="chart-container">
                    <h4>Active vs Inactive Clubs</h4>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Students',
                    data: <?php echo json_encode($values); ?>,
                    backgroundColor: '#008b8b'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    data: [<?php echo $activeClubs; ?>, <?php echo $inactiveClubs; ?>],
                    backgroundColor: ['#27ae60', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>

</html>