<?php
/**
 * Student sidebar — set $studentNavActive:
 * dashboard | browse-events | event-registration | registration-history | profile | notifications
 */
$studentNavActive = $studentNavActive ?? '';

$studentNavItems = [
    ['key' => 'dashboard', 'href' => 'studentDashboard.php', 'icon' => 'fa-th-large', 'label' => 'Dashboard'],
    ['key' => 'browse-events', 'href' => 'browseEvents.php', 'icon' => 'fa-calendar-alt', 'label' => 'Browse Events'],
    ['key' => 'event-registration', 'href' => 'browseEvents.php', 'icon' => 'fa-clipboard-list', 'label' => 'Event Registration'],
    ['key' => 'registration-history', 'href' => 'studentRegistrationHistory.php', 'icon' => 'fa-history', 'label' => 'My Registration History'],
    ['key' => 'profile', 'href' => 'studentProfile.php', 'icon' => 'fa-user-circle', 'label' => 'Profile'],
    ['key' => 'notifications', 'href' => 'notifications.php', 'icon' => 'fa-bell', 'label' => 'Notifications'],
];
?>
<nav class="sidebar" aria-label="Student portal navigation">
    <p class="sidebar-portal-label">Student Portal</p>
    <ul class="sidebar-nav-list">
        <?php foreach ($studentNavItems as $item): ?>
            <li<?php echo $item['key'] === $studentNavActive ? ' class="active"' : ''; ?>>
                <a href="<?php echo htmlspecialchars($item['href']); ?>"<?php echo $item['key'] === $studentNavActive ? ' aria-current="page"' : ''; ?>>
                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                    <?php echo htmlspecialchars($item['label']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="sidebar-footer">&copy; <?php echo date('Y'); ?> FK-SCEMS UMPSA</p>
</nav>
