<?php
session_start();
if ($_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User - FK-SCEMS</title>
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
                <li>
                    <a href="adminDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-tasks"></i> Club Management</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-calendar-check"></i> Events</a>
                </li>
                <li class="active">
                    <a href="adminUserControl.php"><i class="fas fa-users-cog"></i> User Control</a>
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
            <h2>Manage User</h2>

            <div class="form-container">
                <form action="processCreateUser.php" method="POST">

                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" class="form-control" placeholder="e.g. CB24001" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Enter full name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. student@ump.edu.my" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Assign Role</label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="" disabled selected>Select a role...</option>
                            <option value="student">Student</option>
                            <option value="committee">Club Committee</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <div class="form-buttons">
                        <button type="button" class="btn-cancel" onclick="window.location.href='adminUserControl.php'">Cancel</button>
                        <button type="submit" class="btn-create">Create Account</button>
                    </div>

                </form>
            </div>
        </main>
    </div>

</body>

</html>