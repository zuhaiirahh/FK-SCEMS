<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/student_layout.php';

student_require_auth();

$userID = (int) $_SESSION['userID'];
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');

$profile = [
    'name' => $_SESSION['name'] ?? '',
    'username' => $_SESSION['username'] ?? '',
    'email' => '',
    'type' => $_SESSION['type'] ?? 'student',
];

$stmt = mysqli_prepare($conn, 'SELECT name, username, email, type FROM user WHERE userID = ? LIMIT 1');
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $userID);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();
    if ($row) {
        $profile = array_merge($profile, $row);
    }
    mysqli_stmt_close($stmt);
}

student_page_start('Profile', $userName, 'profile');
?>

<div class="page-header">
    <h1>Profile</h1>
    <div class="title-accent"></div>
    <p class="page-subtitle">Your student account details.</p>
</div>

<div class="profile-card">
    <header>
        <i class="fas fa-user-circle"></i>
        <div>
            <p style="font-size:1.25rem;font-weight:bold;margin:0;"><?php echo htmlspecialchars($profile['name']); ?></p>
            <p style="margin:4px 0 0;color:#666;font-size:0.9rem;">Student</p>
        </div>
    </header>
    <dl>
        <dt>Username</dt>
        <dd><?php echo htmlspecialchars($profile['username']); ?></dd>
        <dt>Email</dt>
        <dd><?php echo htmlspecialchars($profile['email'] ?: '—'); ?></dd>
        <dt>Role</dt>
        <dd><?php echo htmlspecialchars(ucfirst($profile['type'])); ?></dd>
    </dl>
</div>

<?php student_page_end(); ?>
