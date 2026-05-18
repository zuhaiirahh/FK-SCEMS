<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';
require_once __DIR__ . '/includes/admin_layout.php';

admin_require_auth();

/* ===============================
    FETCH MAIN DASHBOARD DATA
================================ */

// 1. Total Registered Students
$studentQuery = "SELECT COUNT(*) as totalStudents FROM user WHERE userType = 'student'";
$studentResult = mysqli_query($conn, $studentQuery);
$totalStudents = ($studentResult && $row = mysqli_fetch_assoc($studentResult)) ? $row['totalStudents'] : 0;

// 2. Total Active Clubs
$clubQuery = "SELECT COUNT(*) as totalClubs FROM club WHERE clubStatus = 'Active'";
$clubResult = mysqli_query($conn, $clubQuery);
$activeClubs = ($clubResult && $row = mysqli_fetch_assoc($clubResult)) ? $row['totalClubs'] : 0;

// 3. Total Upcoming Events & pending approval
$eventCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event'");
$upcomingEvents = 0;
$pendingEvents = 0;
if ($eventCheck && mysqli_num_rows($eventCheck) > 0) {
    $eventQuery = "SELECT COUNT(*) as upcoming FROM event WHERE eventDate >= CURDATE()";
    $eventResult = mysqli_query($conn, $eventQuery);
    $upcomingEvents = ($eventResult && $row = mysqli_fetch_assoc($eventResult)) ? (int) $row['upcoming'] : 0;

    $legacy = eventUsesLegacySchema($conn);
    $statusCol = $legacy ? 'eventStatus' : 'status';
    $pendingWhere = $legacy
        ? "LOWER({$statusCol}) IN ('pending', 'pending_approval')"
        : "{$statusCol} = 'pending_approval'";
    $pendingRes = mysqli_query($conn, "SELECT COUNT(*) AS c FROM event WHERE {$pendingWhere}");
    if ($pendingRes && ($pr = mysqli_fetch_assoc($pendingRes))) {
        $pendingEvents = (int) $pr['c'];
    }
}

// 4. Recent User Registrations (Last 5)
$recentUsersQuery = "SELECT userID, name, userType FROM user ORDER BY userID DESC LIMIT 5";
$recentUsersResult = mysqli_query($conn, $recentUsersQuery);
admin_page_start('Administrator Dashboard', 'dashboard');
?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .insight-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
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
            font-weight: 600;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
        }

        .charts-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            flex: 1;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        a.card-link { text-decoration: none; color: inherit; display: block; flex: 1; }
        a.card-link:hover .card { box-shadow: 0 8px 16px rgba(0,0,0,0.08); }
    </style>

            <h2>System Overview & Analytics</h2>

            <div class="insight-cards">
                <div class="card">
                    <span class="card-title">Registered Students</span>
                    <span class="card-value" style="color: #0056b3;"><?php echo $totalStudents; ?></span>
                </div>

                <div class="card" style="border-top-color: #28a745;">
                    <span class="card-title">Active Clubs</span>
                    <span class="card-value" style="color: #28a745;"><?php echo $activeClubs; ?></span>
                </div>

                <a href="adminEventManagement.php" class="card-link">
                    <div class="card" style="border-top-color: #f39c12;">
                        <span class="card-title">Upcoming Events</span>
                        <span class="card-value" style="color: #f39c12;"><?php echo $upcomingEvents; ?></span>
                        <?php if ($pendingEvents > 0): ?>
                            <span style="display:block;margin-top:8px;font-size:0.85rem;color:#856404;font-weight:600;">
                                <?php echo $pendingEvents; ?> pending approval — click to review
                            </span>
                        <?php else: ?>
                            <span style="display:block;margin-top:8px;font-size:0.85rem;color:#666;">Manage all events</span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>

            <div class="charts-grid">
                <div class="chart-box">
                    <h4 style="margin-bottom: 15px; color: #444; border-bottom: 1px solid #eee; padding-bottom: 10px;">User Role Distribution</h4>
                    <canvas id="userRoleChart" height="250"></canvas>
                </div>

                <div class="chart-box">
                    <h4 style="margin-bottom: 15px; color: #444; border-bottom: 1px solid #eee; padding-bottom: 10px;">System Activity (Weekly)</h4>
                    <canvas id="activityChart" height="250"></canvas>
                </div>
            </div>

            <section class="table-section">
                <h3>Recent User Registrations</h3>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($recentUsersResult && mysqli_num_rows($recentUsersResult) > 0) {
                                while ($row = mysqli_fetch_assoc($recentUsersResult)) {
                            ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['userID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>
                                            <span style="background: #e2e8f0; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">
                                                <?php echo htmlspecialchars($row['userType']); ?>
                                            </span>
                                        </td>
                                        <td><span style="color: green; font-weight: bold;">Active</span></td>
                                    </tr>
                                <?php
                                } // Penutup gelung while
                            } else {
                                ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">No recent registrations found.</td>
                                </tr>
                            <?php
                            } // Penutup syarat if
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

    <script>
        // Sediakan carta anda di sini pada masa hadapan jika perlu
    </script>
</body>

</html>
