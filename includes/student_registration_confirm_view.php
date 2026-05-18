<?php if ($flashError): ?>
    <div class="flash-message error" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?>
    </div>
<?php endif; ?>
<?php if ($flashSuccess): ?>
    <div class="flash-message success" role="status">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flashSuccess); ?>
    </div>
<?php endif; ?>

<div class="reg-confirm-alert">
    <i class="fas fa-info-circle" aria-hidden="true"></i>
    <p><?php echo htmlspecialchars($infoMessage); ?></p>
</div>

<div class="reg-confirm-grid">
    <div class="reg-card reg-card-event">
        <header class="reg-card-head reg-card-head-blue">
            <i class="fas fa-calendar-alt"></i>
            <span>1. Event Information</span>
        </header>
        <div class="reg-card-body">
            <div class="reg-detail-row">
                <span class="reg-detail-label">Event Name</span>
                <span class="reg-detail-value"><?php echo htmlspecialchars($event['eventTitle']); ?></span>
            </div>
            <div class="reg-detail-row">
                <span class="reg-detail-label">Club</span>
                <span class="reg-detail-value"><?php echo htmlspecialchars($clubName); ?></span>
            </div>
            <div class="reg-detail-row">
                <span class="reg-detail-label">Date</span>
                <span class="reg-detail-value"><?php echo formatEventDate($event['eventDate']); ?></span>
            </div>
            <div class="reg-detail-row">
                <span class="reg-detail-label">Time</span>
                <span class="reg-detail-value"><?php echo formatEventTime($event['startTime'] ?? ''); ?></span>
            </div>
            <div class="reg-detail-row">
                <span class="reg-detail-label">Venue</span>
                <span class="reg-detail-value"><?php echo $venue; ?></span>
            </div>
            <div class="reg-detail-row">
                <span class="reg-detail-label">Merit Value</span>
                <span class="reg-detail-value reg-merit-highlight"><?php echo (int) $merit; ?></span>
            </div>
            <div class="reg-detail-row">
                <span class="reg-detail-label">Available Slot</span>
                <span class="reg-detail-value"><?php echo htmlspecialchars($slotDisplay); ?></span>
            </div>
            <?php if ($viewOnly && $registration): ?>
                <div class="reg-detail-row">
                    <span class="reg-detail-label">Registration ID</span>
                    <span class="reg-detail-value">REG<?php echo str_pad((string) $registration['registrationID'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="reg-detail-row">
                    <span class="reg-detail-label">Status</span>
                    <span class="reg-detail-value"><?php echo htmlspecialchars(ucfirst($registration['registrationStatus'] ?? 'registered')); ?></span>
                </div>
                <div class="reg-detail-row">
                    <span class="reg-detail-label">Confirmation</span>
                    <span class="reg-detail-value"><?php echo htmlspecialchars(ucfirst($registration['confirmationStatus'] ?? 'pending')); ?></span>
                </div>
                <div class="reg-detail-row">
                    <span class="reg-detail-label">Registered On</span>
                    <span class="reg-detail-value"><?php echo !empty($registration['registrationDate']) ? date('d M Y, g:i A', strtotime($registration['registrationDate'])) : '—'; ?></span>
                </div>
            <?php elseif ($viewOnly && $waitingEntry): ?>
                <div class="reg-detail-row">
                    <span class="reg-detail-label">Waiting List</span>
                    <span class="reg-detail-value"><?php echo htmlspecialchars(ucfirst($waitingEntry['waitingStatus'] ?? 'waiting')); ?></span>
                </div>
                <div class="reg-detail-row">
                    <span class="reg-detail-label">Requested On</span>
                    <span class="reg-detail-value"><?php echo !empty($waitingEntry['requestDate']) ? date('d M Y, g:i A', strtotime($waitingEntry['requestDate'])) : '—'; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="reg-confirm-right">
        <div class="reg-card reg-card-student">
            <header class="reg-card-head">
                <i class="fas fa-user"></i>
                <span>2. Your Information</span>
            </header>
            <div class="reg-card-body reg-student-fields">
                <div class="reg-student-row">
                    <span class="reg-student-label">Full Name</span>
                    <span class="reg-student-value"><?php echo htmlspecialchars($student['name']); ?></span>
                </div>
                <div class="reg-student-row">
                    <span class="reg-student-label">Matric ID</span>
                    <span class="reg-student-value"><?php echo htmlspecialchars($student['matric'] ?: '—'); ?></span>
                </div>
                <div class="reg-student-row">
                    <span class="reg-student-label">Phone</span>
                    <span class="reg-student-value"><?php echo htmlspecialchars($student['phone'] ?: '—'); ?></span>
                </div>
                <div class="reg-student-row">
                    <span class="reg-student-label">Email</span>
                    <span class="reg-student-value">
                        <?php if ($student['email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" class="reg-email-link"><?php echo htmlspecialchars($student['email']); ?></a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </span>
                </div>
                <div class="reg-student-row">
                    <span class="reg-student-label">Total Merit</span>
                    <span class="reg-student-value">
                        <span class="reg-merit-badge"><?php echo (int) $student['totalMerit']; ?></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="reg-venue-banner">
            <img src="<?php echo htmlspecialchars($poster); ?>" alt="<?php echo htmlspecialchars($event['eventTitle']); ?> venue">
            <div class="reg-venue-caption"><?php echo htmlspecialchars($imageCaption); ?></div>
        </div>
    </div>
</div>

<?php if ($viewOnly): ?>
    <div class="reg-confirm-footer">
        <div class="reg-confirm-alert reg-confirm-alert-success">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <div>
                <p><strong><?php echo $userRegistered ? 'You are registered for this event.' : 'You are on the waiting list for this event.'; ?></strong></p>
                <p class="reg-confirm-alert-sub">Keep this page for your records.</p>
            </div>
        </div>
        <div class="reg-confirm-actions">
            <a href="studentRegistrationHistory.php" class="btn-reg-confirm"><i class="fas fa-history"></i> My Registration History</a>
            <?php if ($userRegistered && $registration): ?>
                <form method="POST" action="deleteRegistration_process.php" class="reg-cancel-form" onsubmit="return confirm('Cancel your registration for this event?');">
                    <input type="hidden" name="registrationID" value="<?php echo (int) $registration['registrationID']; ?>">
                    <input type="hidden" name="returnTo" value="confirmation">
                    <button type="submit" class="btn-reg-cancel btn-reg-danger"><i class="fas fa-trash"></i> Cancel Registration</button>
                </form>
            <?php endif; ?>
            <a href="browseEvents.php" class="btn-reg-cancel"><i class="fas fa-compass"></i> Browse Events</a>
        </div>
    </div>
<?php else: ?>
    <form method="POST" action="registerEvent_process.php" id="confirmForm" class="reg-confirm-footer">
        <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <div class="reg-confirm-agree">
            <input id="confirm" name="confirm" value="1" type="checkbox" required>
            <div>
                <label for="confirm"><?php echo htmlspecialchars($confirmLabel); ?></label>
                <p>By clicking confirm, you agree to the club's attendance policy and merit point guidelines.</p>
            </div>
        </div>
        <div class="reg-confirm-actions reg-confirm-actions-center">
            <button type="submit" class="btn-reg-confirm"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($submitLabel); ?></button>
            <button type="button" class="btn-reg-cancel" onclick="window.location.href='viewEvent.php?id=<?php echo $eventID; ?>'"><i class="fas fa-times"></i> Cancel</button>
        </div>
    </form>
<?php endif; ?>
