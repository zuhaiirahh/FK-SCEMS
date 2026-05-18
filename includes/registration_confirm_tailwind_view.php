<div class="flex flex-col gap-2">
    <h1 class="text-display-lg font-display-lg text-primary">Event Registration Confirmation</h1>
    <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant" aria-label="Breadcrumb">
        <a href="browseEvents.php" class="hover:text-primary">Events</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-primary font-bold">Registration</span>
    </nav>
</div>

<?php if ($flashError): ?>
    <div class="bg-error-container border border-error/30 text-on-error-container p-4 rounded-xl flex items-center gap-3" role="alert">
        <span class="material-symbols-outlined">error</span>
        <p class="text-body-md"><?php echo htmlspecialchars($flashError); ?></p>
    </div>
<?php endif; ?>
<?php if ($flashSuccess): ?>
    <div class="bg-surface-container border border-primary/20 text-on-surface p-4 rounded-xl flex items-center gap-3" role="status">
        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
        <p class="text-body-md"><?php echo htmlspecialchars($flashSuccess); ?></p>
    </div>
<?php endif; ?>

<?php if (!$viewOnly): ?>
<div class="bg-surface-container-low border border-outline-variant p-4 rounded-xl flex items-center gap-4">
    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">info</span>
    <p class="text-body-md text-on-surface-variant"><?php echo htmlspecialchars($infoMessage); ?></p>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-md items-start">
    <div class="lg:col-span-7 bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-outline-variant bg-surface-container-low flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center">
                <span class="material-symbols-outlined text-white">calendar_month</span>
            </div>
            <h2 class="text-headline-md font-headline-md text-on-surface">1. Event Information</h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-3 py-2 border-b border-outline-variant/30">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">sell</span> Event Name</span>
                <span class="col-span-2 text-body-md font-bold text-on-surface">: <?php echo htmlspecialchars($event['eventTitle']); ?></span>
            </div>
            <div class="grid grid-cols-3 py-2 border-b border-outline-variant/30">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">groups</span> Club</span>
                <span class="col-span-2 text-body-md text-on-surface">: <?php echo htmlspecialchars($clubName); ?></span>
            </div>
            <div class="grid grid-cols-3 py-2 border-b border-outline-variant/30">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">event</span> Date</span>
                <span class="col-span-2 text-body-md text-on-surface">: <?php echo formatEventDate($event['eventDate']); ?></span>
            </div>
            <div class="grid grid-cols-3 py-2 border-b border-outline-variant/30">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">schedule</span> Time</span>
                <span class="col-span-2 text-body-md text-on-surface">: <?php echo formatEventTime($event['startTime'] ?? ''); ?></span>
            </div>
            <div class="grid grid-cols-3 py-2 border-b border-outline-variant/30">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">location_on</span> Venue</span>
                <span class="col-span-2 text-body-md text-on-surface">: <?php echo $venue !== '' ? $venue : '—'; ?></span>
            </div>
            <div class="grid grid-cols-3 py-2 border-b border-outline-variant/30">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">star</span> Merit Value</span>
                <span class="col-span-2 text-body-md text-on-surface font-bold text-tertiary-container">: <?php echo (int) $merit; ?></span>
            </div>
            <div class="grid grid-cols-3 py-2 <?php echo ($viewOnly && ($registration || $waitingEntry)) ? 'border-b border-outline-variant/30' : ''; ?>">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">person_add</span> Available Slot</span>
                <span class="col-span-2 text-body-md text-on-surface">: <?php echo htmlspecialchars($slotDisplay); ?></span>
            </div>
            <?php if ($viewOnly && $registration): ?>
            <div class="grid grid-cols-3 py-2 border-b border-outline-variant/30">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">badge</span> Registration ID</span>
                <span class="col-span-2 text-body-md text-on-surface">: REG<?php echo str_pad((string) $registration['registrationID'], 5, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="grid grid-cols-3 py-2 border-b border-outline-variant/30">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">fact_check</span> Status</span>
                <span class="col-span-2 text-body-md text-on-surface">: <?php echo htmlspecialchars(ucfirst($registration['registrationStatus'] ?? 'registered')); ?></span>
            </div>
            <div class="grid grid-cols-3 py-2">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">verified</span> Confirmation</span>
                <span class="col-span-2 text-body-md text-on-surface">: <?php echo htmlspecialchars(ucfirst($registration['confirmationStatus'] ?? 'pending')); ?></span>
            </div>
            <?php elseif ($viewOnly && $waitingEntry): ?>
            <div class="grid grid-cols-3 py-2">
                <span class="text-label-lg text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">hourglass_top</span> Waiting List</span>
                <span class="col-span-2 text-body-md text-on-surface">: <?php echo htmlspecialchars(ucfirst($waitingEntry['waitingStatus'] ?? 'waiting')); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-5 space-y-md">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-outline-variant bg-surface-container-low flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-secondary-container">person</span>
                </div>
                <h2 class="text-headline-md font-headline-md text-on-surface">2. Your Information</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                    <span class="text-label-sm text-on-surface-variant">FULL NAME</span>
                    <span class="text-body-md font-bold text-on-surface"><?php echo htmlspecialchars($student['name']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                    <span class="text-label-sm text-on-surface-variant">MATRIC ID</span>
                    <span class="text-body-md text-on-surface"><?php echo htmlspecialchars($student['matric'] ?: '—'); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                    <span class="text-label-sm text-on-surface-variant">PHONE</span>
                    <span class="text-body-md text-on-surface"><?php echo htmlspecialchars($student['phone'] ?: '—'); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-outline-variant/30">
                    <span class="text-label-sm text-on-surface-variant">EMAIL</span>
                    <span class="text-body-md text-on-surface text-primary"><?php echo htmlspecialchars($student['email'] ?: '—'); ?></span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-label-sm text-on-surface-variant">TOTAL MERIT</span>
                    <span class="text-body-md font-bold text-on-primary-fixed-variant bg-primary-fixed px-2 py-1 rounded"><?php echo (int) $student['totalMerit']; ?></span>
                </div>
            </div>
        </div>
        <div class="relative rounded-xl overflow-hidden h-48 border border-outline-variant">
            <img alt="<?php echo htmlspecialchars($event['eventTitle']); ?>" class="w-full h-full object-cover" src="<?php echo htmlspecialchars($poster); ?>">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-4">
                <p class="text-white text-label-lg"><?php echo htmlspecialchars($imageCaption); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($viewOnly): ?>
<div class="bg-white border border-outline-variant rounded-xl p-6 shadow-md space-y-6">
    <div class="flex items-start gap-4 p-4 bg-surface-container-low rounded-lg border-l-4 border-primary">
        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
        <div>
            <p class="text-body-md font-medium text-on-surface">
                <?php echo $userRegistered
                    ? 'You are registered for this event.'
                    : 'You are on the waiting list for this event.'; ?>
            </p>
            <p class="text-label-sm text-on-surface-variant mt-1">Keep this page for your records.</p>
        </div>
    </div>
    <div class="flex flex-col sm:flex-row gap-4 pt-2">
        <a href="studentRegistrationHistory.php"
            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-10 py-4 bg-primary text-on-primary rounded-xl font-headline-md hover:bg-primary-container transition-all">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">history</span>
            My Registration History
        </a>
        <?php if ($userRegistered && $registration): ?>
        <form method="POST" action="deleteRegistration_process.php" class="flex-1 sm:flex-none"
            onsubmit="return confirm('Cancel your registration for this event?');">
            <input type="hidden" name="registrationID" value="<?php echo (int) $registration['registrationID']; ?>">
            <input type="hidden" name="returnTo" value="confirmation">
            <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 px-10 py-4 bg-white text-error border border-error/30 rounded-xl font-headline-md hover:bg-error-container transition-all">
                <span class="material-symbols-outlined">delete</span>
                Cancel Registration
            </button>
        </form>
        <?php endif; ?>
        <a href="browseEvents.php"
            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-10 py-4 bg-white text-on-surface border border-outline-variant rounded-xl font-headline-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined">explore</span>
            Browse Events
        </a>
    </div>
</div>
<?php else: ?>
<div class="bg-white border border-outline-variant rounded-xl p-6 shadow-md space-y-6">
    <form method="POST" action="registerEvent_process.php" id="confirmForm">
        <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <div class="flex items-start gap-4 p-4 bg-surface-container-low rounded-lg border-l-4 border-primary">
            <div class="flex items-center h-6">
                <input class="h-5 w-5 rounded border-outline text-primary focus:ring-primary" id="confirm" name="confirm" value="1" type="checkbox" required>
            </div>
            <div class="text-body-md">
                <label class="font-medium text-on-surface cursor-pointer" for="confirm"><?php echo htmlspecialchars($confirmLabel); ?></label>
                <p class="text-label-sm text-on-surface-variant mt-1">By clicking confirm, you agree to the club's attendance policy and merit point guidelines.</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <button type="submit"
                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-10 py-4 bg-primary text-on-primary rounded-xl font-headline-md hover:bg-primary-container hover:scale-[1.02] active:scale-[0.98] transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <?php echo htmlspecialchars($submitLabel); ?>
            </button>
            <button type="button"
                onclick="window.location.href='viewEvent.php?id=<?php echo $eventID; ?>'"
                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-10 py-4 bg-white text-on-surface border border-outline-variant rounded-xl font-headline-md hover:bg-surface-container transition-all">
                <span class="material-symbols-outlined">close</span>
                Cancel
            </button>
        </div>
    </form>
</div>
<?php endif; ?>
