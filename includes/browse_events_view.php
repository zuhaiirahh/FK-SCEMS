<?php if ($flashSuccess): ?>
    <div class="mb-md p-sm rounded-lg border border-outline-variant bg-surface-container-low text-on-surface flex items-center gap-sm" role="status">
        <span class="material-symbols-outlined text-brand-teal">check_circle</span>
        <?php echo htmlspecialchars($flashSuccess); ?>
    </div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="mb-md p-sm rounded-lg border border-error bg-error-container text-on-error-container flex items-center gap-sm" role="alert">
        <span class="material-symbols-outlined">error</span>
        <?php echo htmlspecialchars($flashError); ?>
    </div>
<?php endif; ?>

<header class="mb-lg">
    <h1 class="text-display-lg font-display-lg text-on-surface mb-sm">Browse Upcoming Events</h1>
    <div class="w-20 h-1.5 bg-primary rounded-full"></div>
</header>

<form method="GET" action="browseEvents.php" id="browseFilterForm"
    class="bg-white p-md rounded-xl border border-outline-variant mb-lg shadow-sm flex flex-col md:flex-row md:items-end gap-md">
    <div class="flex-1">
        <label for="search" class="block text-label-lg font-bold text-on-surface mb-xs">Search Event</label>
        <div class="relative">
            <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
            <input type="text" id="search" name="search"
                class="w-full pl-xl pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all"
                placeholder="Search event title..."
                value="<?php echo htmlspecialchars($search); ?>">
        </div>
    </div>
    <div class="w-full md:w-64">
        <label for="club" class="block text-label-lg font-bold text-on-surface mb-xs">Filter Club</label>
        <div class="relative">
            <select id="club" name="club"
                class="w-full pl-sm pr-xl py-sm bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none appearance-none transition-all">
                <option value="all" <?php echo $clubFilter === 'all' ? 'selected' : ''; ?>>All Clubs</option>
                <?php foreach ($clubs as $club): ?>
                    <?php
                    $clubValue = is_array($club) ? (string) $club['id'] : (string) $club;
                    $clubLabel = is_array($club) ? $club['name'] : clubDisplayName($club);
                    ?>
                    <option value="<?php echo htmlspecialchars($clubValue); ?>" <?php echo (string) $clubFilter === $clubValue ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($clubLabel); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="material-symbols-outlined absolute right-sm top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">expand_more</span>
        </div>
        </div>
    <?php if ($search !== '' || $clubFilter !== 'all'): ?>
        <a href="browseEvents.php"
            class="text-label-lg font-bold text-primary hover:underline whitespace-nowrap pb-sm md:pb-[10px]">Clear filters</a>
    <?php endif; ?>
</form>

<?php if (!$hasRegTable): ?>
    <div class="mb-md p-sm rounded-lg border border-outline-variant bg-tertiary-fixed text-on-tertiary-fixed text-body-md">
        Registration tables are missing. Import <strong>student_registration_tables.sql</strong> in phpMyAdmin to enable Register and Waiting List.
    </div>
<?php endif; ?>

<div class="space-y-md">
    <?php if (empty($events)): ?>
        <div class="bg-white p-xl rounded-xl border border-outline-variant shadow-sm text-center text-on-surface-variant">
            <span class="material-symbols-outlined text-[48px] mb-sm block opacity-50">event_busy</span>
            <p class="text-headline-md font-headline-md text-on-surface mb-xs"><strong>No upcoming events found.</strong></p>
            <?php if ($search !== '' || $clubFilter !== 'all'): ?>
                <a href="browseEvents.php" class="text-primary font-bold hover:underline">Clear filters</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($events as $event):
            $regCount = (int) $event['regCount'];
            $maxSlots = $event['maxParticipants'] !== null ? (int) $event['maxParticipants'] : null;
            $isFull = $maxSlots !== null && $regCount >= $maxSlots;
            $available = $maxSlots !== null ? max(0, $maxSlots - $regCount) : null;
            $userRegistered = !empty($event['userRegistrationID']);
            $userWaiting = !empty($event['userWaitingID']);
            $venue = htmlspecialchars($event['venue']);
            if (!empty($event['locationRoom'])) {
                $venue .= ', ' . htmlspecialchars($event['locationRoom']);
            }
            $clubName = clubDisplayName($event['eventCategory'], $event['clubName'] ?? null);
            $clubIconName = clubIcon($event['eventCategory']);
            $merit = meritValue($event['eventCategory']);
            $slotLow = availableSlotClass($regCount, $maxSlots) === 'slot-warning';
            $slotTextClass = $slotLow ? 'text-error' : 'text-on-surface';
            $poster = eventPosterUrl($event);
            $eventId = (int) $event['eventID'];
            ?>
            <article class="bg-white p-md rounded-xl border border-outline-variant shadow-sm flex flex-col md:flex-row gap-md hover:border-primary transition-colors">
                <div class="w-full md:w-48 h-48 rounded-lg overflow-hidden shrink-0 border border-outline-variant">
                    <img class="w-full h-full object-cover" src="<?php echo htmlspecialchars($poster); ?>"
                        alt="<?php echo htmlspecialchars($event['eventTitle']); ?> poster">
                </div>
                <div class="flex-1 flex flex-col justify-between">
                    <div>
                        <h2 class="text-headline-md font-headline-md text-on-surface mb-xs"><?php echo htmlspecialchars($event['eventTitle']); ?></h2>
                        <div class="flex items-center gap-xs text-primary font-bold mb-md">
                            <span class="material-symbols-outlined text-[20px] material-symbols-filled"><?php echo htmlspecialchars($clubIconName); ?></span>
                            <span class="text-label-lg"><?php echo htmlspecialchars($clubName); ?></span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-sm gap-x-lg text-on-surface-variant">
                            <div class="flex items-center gap-xs">
                                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                                <span><?php echo formatEventDate($event['eventDate']); ?></span>
                            </div>
                            <div class="flex items-center gap-xs">
                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                                <span><?php echo formatEventTime($event['startTime']); ?></span>
                            </div>
                            <div class="flex items-center gap-xs">
                                <span class="material-symbols-outlined text-[18px]">location_on</span>
                                <span><?php echo $venue; ?></span>
                            </div>
                            <div class="flex items-center gap-xs">
                                <span class="material-symbols-outlined text-[18px]">local_activity</span>
                                <span>
                                    Merit Value: <span class="font-bold text-on-surface"><?php echo $merit; ?></span>
                                    <?php if ($maxSlots !== null): ?>
                                        | Available Slot: <span class="font-bold <?php echo $slotTextClass; ?>"><?php echo $available; ?> / <?php echo $maxSlots; ?></span>
                                    <?php else: ?>
                                        | <span class="font-bold text-on-surface">Open slots</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-sm mt-md flex-wrap">
                        <a href="viewEvent.php?id=<?php echo $eventId; ?>"
                            class="px-lg py-sm border border-primary text-primary font-bold rounded-lg hover:bg-surface-container-low transition-all">View Detail</a>
                        <?php if ($userRegistered): ?>
                            <a href="registrationConfirmation.php?registrationID=<?php echo (int) ($event['userRegistrationID'] ?? 0); ?>"
                                class="px-lg py-sm border border-outline-variant text-on-surface-variant font-bold rounded-lg">View Registration</a>
                        <?php elseif ($userWaiting): ?>
                            <span class="px-lg py-sm border border-outline-variant text-on-surface-variant font-bold rounded-lg">On Waiting List</span>
                        <?php elseif ($isFull && $hasWaitTable): ?>
                            <a href="registrationConfirmation.php?id=<?php echo $eventId; ?>&amp;action=waitlist"
                                class="px-lg py-sm bg-brand-teal text-white font-bold rounded-lg hover:opacity-90 transition-all">Join Waiting List</a>
                        <?php elseif (!$isFull && $hasRegTable): ?>
                            <a href="registrationConfirmation.php?id=<?php echo $eventId; ?>"
                                class="px-lg py-sm bg-brand-teal text-white font-bold rounded-lg hover:opacity-90 transition-all">Register</a>
                        <?php elseif ($isFull): ?>
                            <span class="px-lg py-sm bg-surface-container text-on-surface-variant font-bold rounded-lg opacity-70">Full</span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($totalEvents > 0 && $totalPages > 1): ?>
    <nav class="mt-xl flex justify-center items-center gap-xs" aria-label="Event list pagination">
        <?php if ($page > 1): ?>
            <a href="<?php echo htmlspecialchars(browseQueryUrl(['page' => $page - 1])); ?>"
                class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-all"
                aria-label="Previous page">
                <span class="material-symbols-outlined">chevron_left</span>
            </a>
        <?php else: ?>
            <span class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant opacity-50" aria-hidden="true">
                <span class="material-symbols-outlined">chevron_left</span>
            </span>
        <?php endif; ?>

        <?php foreach (browsePaginationLinks($page, $totalPages) as $link): ?>
            <?php if ($link === '...'): ?>
                <span class="px-xs text-outline">...</span>
            <?php elseif ($link === $page): ?>
                <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-white font-bold" aria-current="page"><?php echo (int) $link; ?></span>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars(browseQueryUrl(['page' => (int) $link])); ?>"
                    class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-all font-bold"><?php echo (int) $link; ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?php echo htmlspecialchars(browseQueryUrl(['page' => $page + 1])); ?>"
                class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-all"
                aria-label="Next page">
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        <?php else: ?>
            <span class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant opacity-50" aria-hidden="true">
                <span class="material-symbols-outlined">chevron_right</span>
            </span>
        <?php endif; ?>
    </nav>
<?php endif; ?>
