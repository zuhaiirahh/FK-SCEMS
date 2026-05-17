<?php
session_start();
include 'fkscems.php';

// Proteksi halaman admin
if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* =========================================
   FETCH DATA USER UNTUK TABEL
   ========================================= */
$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $searchQuery = " WHERE userID LIKE '%$search%' OR name LIKE '%$search%' ";
}

$userQuery = "SELECT userID, name, userType, 'Active' as status FROM user" . $searchQuery . " ORDER BY userID DESC";
$userResult = mysqli_query($conn, $userQuery);
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Control - FK-SCEMS</title>

    <link rel="stylesheet" href="adminStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .user-control-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .btn-register {
            padding: 10px 20px;
            background: #008b8b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.9rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-register:hover {
            background: #007070;
        }

        .action-btns {
            display: flex;
            gap: 10px;
        }

        .btn-edit {
            color: #0056b3;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-delete {
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
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
                    <a href="adminDashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                </li>
                <li>
                    <a href="clubDashboard.php"><i class="fas fa-tasks"></i> Club Management</a>
                </li>
                <li>
                    <a href="adminEventManagement.php"><i class="fas fa-calendar-check"></i> Events</a>
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
            <h2>Manage Membership</h2>

            <div class="user-control-header">
                <form action="adminUserControl.php" method="GET" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Filter by name / ID" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </form>

                <a href="adminRegisterUser.php" class="btn-register">
                    <i class="fas fa-user-plus"></i> Register New User
                </a>
            </div>

            <section class="table-section">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($userResult && mysqli_num_rows($userResult) > 0) {
                                while ($row = mysqli_fetch_assoc($userResult)) {
                            ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['userID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>
                                            <span style="background: #e2e8f0; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">
                                                <?php echo htmlspecialchars($row['userType']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge complete"><?php echo htmlspecialchars($row['status']); ?></span>
                                        </td>
                                        <td align="center">
                                            <div class="action-btns">
                                                <a href="editUser.php?id=<?php echo $row['userID']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                                <a href="deleteUser.php?id=<?php echo $row['userID']; ?>" class="btn-delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: #666;">
                                        No users found matching the filter criteria.
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

</body>

</html>