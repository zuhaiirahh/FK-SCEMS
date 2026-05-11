<?php
session_start();
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
    <title>My Club Requests - FK-SCEMS</title>
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
            <a href="login.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li><a href="studentDashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="active">
                    <a href="#"><i class="fas fa-users"></i> Clubs</a>
                    <ul class="submenu">
                        <li><a href="clubDirectory.php">- Club Directory</a></li>
                        <li><a href="myClubRequests.php" class="active-sub">- My Club Requests</a></li>
                    </ul>
                </li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="#"><i class="fas fa-star"></i> My Merit</a></li>
                <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <section class="table-section">
                <div class="section-header">
                    <h3>My Club Requests</h3>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Club Name</th>
                                <th>Membership Join Date</th>
                                <th>Membership Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>FK Computer Science Club</td>
                                <td>10 May 2026</td>
                                <td><span class="status-badge approved">Approved</span></td>
                                <td><button class="view-btn"><i class="fas fa-eye"></i> View</button></td>
                            </tr>
                            <tr>
                                <td>Cybersecurity Society</td>
                                <td>11 May 2026</td>
                                <td><span class="status-badge pending">Pending</span></td>
                                <td><button class="view-btn"><i class="fas fa-eye"></i> View</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

</body>

</html>