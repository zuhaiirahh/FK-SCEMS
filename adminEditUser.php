<?php
session_start();
include 'fkscems.php';

// Sekatan keselamatan admin
if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Semak jika ID pengguna dihantar melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $userIDToEdit = mysqli_real_escape_string($conn, $_GET['id']);

    // Ambil data asal pengguna tersebut dari database
    $query = "SELECT * FROM user WHERE userID = '$userIDToEdit'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $userData = mysqli_fetch_assoc($result);
    } else {
        echo "<script>alert('Pengguna tidak ditemui!'); window.location.href='adminUserControl.php';</script>";
        exit();
    }
} else {
    header("Location: adminUserControl.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - FK-SCEMS</title>
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
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin Name'); ?></span>
                <span class="user-role">Administrator</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li><a href="adminDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="clubDashboard.php"><i class="fas fa-tasks"></i> Club Management</a></li>
                <li><a href="adminEventManagement.php"><i class="fas fa-calendar-check"></i> Events</a></li>
                <li class="active"><a href="adminUserControl.php"><i class="fas fa-users-cog"></i> User Control</a></li>
                <li><a href="#"><i class="fas fa-file-alt"></i> Reporting Module</a></li>
                <li><a href="#"><i class="fas fa-user-shield"></i> My Profile</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Edit User Membership</h2>

            <div class="form-container">
                <form action="processEditUser.php" method="POST">

                    <input type="hidden" name="old_userID" value="<?php echo htmlspecialchars($userData['userID']); ?>">

                    <div class="form-group">
                        <label>User ID (Matric No / Staff ID)</label>
                        <input type="text" name="userID" class="form-control" value="<?php echo htmlspecialchars($userData['userID']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($userData['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>User Role / Type</label>
                        <select name="userType" class="form-select" required>
                            <option value="student" <?php echo ($userData['userType'] == 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="committee" <?php echo ($userData['userType'] == 'committee') ? 'selected' : ''; ?>>Committee</option>
                            <option value="admin" <?php echo ($userData['userType'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-buttons">
                        <a href="adminUserControl.php" class="btn-cancel" style="text-decoration: none; display: inline-block; text-align: center; line-height: 20px;">Cancel</a>
                        <button type="submit" name="update_user" class="btn-create">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>

</html>