<?php
/**
 * Committee sidebar — requires $committeeNavActive:
 * dashboard | event-management | event-registration | participant-list | waiting-list | profile
 */
$committeeNavActive = $committeeNavActive ?? '';

$committeeNavItems = [
    ['key' => 'dashboard', 'href' => 'committeeDashboard.php', 'icon' => 'fa-th-large', 'label' => 'Dashboard'],
    ['key' => 'event-management', 'href' => 'manageEvent.php', 'icon' => 'fa-calendar-alt', 'label' => 'Event Management'],
    ['key' => 'event-registration', 'href' => 'committeeEventRegistration.php', 'icon' => 'fa-clipboard-list', 'label' => 'Event Registration'],
    ['key' => 'participant-list', 'href' => 'committeeParticipantList.php', 'icon' => 'fa-users', 'label' => 'Event Participant List'],
    ['key' => 'waiting-list', 'href' => 'waitingListManagement.php', 'icon' => 'fa-hourglass-half', 'label' => 'Waiting List'],
    ['key' => 'profile', 'href' => 'committeeProfile.php', 'icon' => 'fa-user-circle', 'label' => 'My Profile'],
];
?>
<nav class="sidebar">
    <ul>
        <?php foreach ($committeeNavItems as $item): ?>
            <li<?php echo $item['key'] === $committeeNavActive ? ' class="active"' : ''; ?>>
                <a href="<?php echo htmlspecialchars($item['href']); ?>">
                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                    <?php echo htmlspecialchars($item['label']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
