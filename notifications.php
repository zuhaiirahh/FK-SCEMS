<?php
session_start();
require_once __DIR__ . '/includes/student_layout.php';

student_require_auth();

$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
student_page_start('Notifications', $userName, 'notifications');
?>

<div class="page-header">
    <h1>Notification</h1>
    <div class="title-accent"></div>
    <p class="page-subtitle">Stay updated on your event registrations and club activities.</p>
</div>

<div class="table-container empty-state">
    <i class="fas fa-bell"></i>
    <p><strong>No notifications yet.</strong></p>
    <p style="margin-top:8px;font-size:0.9rem;">You will see alerts here when clubs approve registrations or send updates.</p>
</div>

<?php student_page_end(); ?>
