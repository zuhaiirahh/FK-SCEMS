<?php
session_start();
include 'fkscems.php';
require_once __DIR__ . '/includes/event_schema.php';

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    header('Location: login.php');
    exit();
}

$eventID = (int) ($_GET['id'] ?? 0);
if ($eventID <= 0) {
    header('Location: manageEvent.php');
    exit();
}

$event = fetchEventById($conn, $eventID);
if (!$event) {
    $_SESSION['manageEvent_error'] = 'Event not found.';
    header('Location: manageEvent.php');
    exit();
}

$venue = $event['venue'] ?? '';
$locationRoom = $event['locationRoom'] ?? '';
if ($locationRoom === '' && strpos($venue, ', ') !== false) {
    $parts = explode(', ', $venue, 2);
    $venue = $parts[0];
    $locationRoom = $parts[1] ?? '';
}

$categories = ['Workshop', 'Seminar', 'Competition', 'Social', 'Sports', 'Academic', 'Other'];
$selectedCategory = $event['eventCategory'] ?? 'Other';
if (!in_array($selectedCategory, $categories, true)) {
    $categories[] = $selectedCategory;
}

$flashError = $_SESSION['editEvent_error'] ?? '';
unset($_SESSION['editEvent_error']);

$posterRelative = $event['eventPoster'] ?? '';
$hasCustomPoster = false;
if ($posterRelative !== '') {
    $posterFile = __DIR__ . '/' . ltrim(str_replace('\\', '/', $posterRelative), '/');
    $hasCustomPoster = is_file($posterFile);
}
$currentPosterSrc = $hasCustomPoster ? $posterRelative : '';

require_once __DIR__ . '/includes/committee_layout.php';
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
committee_page_start('Edit Event', $userName, 'event-management', 'create-event-page');
?>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="manageEvent.php">Event</a>
                <span class="breadcrumb-sep">&gt;</span>
                <span class="breadcrumb-current">Edit Event</span>
            </nav>

            <div class="page-header">
                <h1>Edit Event</h1>
                <p class="page-subtitle">EVT<?php echo str_pad((string) $eventID, 4, '0', STR_PAD_LEFT); ?> — <?php echo htmlspecialchars($event['eventTitle']); ?></p>
            </div>

            <?php if ($flashError): ?>
                <div class="alert alert-error" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($flashError); ?>
                </div>
            <?php endif; ?>

            <form class="create-event-form" action="editEvent_process.php" method="POST" enctype="multipart/form-data" id="editEventForm">
                <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">

                <div class="create-event-grid">
                    <div class="create-event-col">
                <section class="form-section">
                    <h2 class="form-section-title">Event Information</h2>

                    <div class="form-group">
                        <label for="eventTitle">Event Title <span class="required">*</span></label>
                        <input type="text" id="eventTitle" name="eventTitle" required
                            value="<?php echo htmlspecialchars($event['eventTitle']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="eventCategory">Event Category <span class="required">*</span></label>
                            <select id="eventCategory" name="eventCategory" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"<?php echo $selectedCategory === $cat ? ' selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="eventDate">Event Date <span class="required">*</span></label>
                            <input type="date" id="eventDate" name="eventDate" required
                                value="<?php echo htmlspecialchars($event['eventDate']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="startTime">Start Time <span class="required">*</span></label>
                            <input type="time" id="startTime" name="startTime" required
                                value="<?php echo htmlspecialchars(substr($event['startTime'] ?? '', 0, 5)); ?>">
                        </div>
                        <div class="form-group">
                            <label for="endTime">End Time <span class="required">*</span></label>
                            <input type="time" id="endTime" name="endTime" required
                                value="<?php echo htmlspecialchars(substr($event['endTime'] ?? $event['startTime'] ?? '', 0, 5)); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="venue">Venue <span class="required">*</span></label>
                            <input type="text" id="venue" name="venue" required
                                value="<?php echo htmlspecialchars($venue); ?>">
                        </div>
                        <div class="form-group">
                            <label for="locationRoom">Location / Room</label>
                            <input type="text" id="locationRoom" name="locationRoom"
                                value="<?php echo htmlspecialchars($locationRoom); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="eventDescription">Event Description <span class="required">*</span></label>
                        <textarea id="eventDescription" name="eventDescription" rows="5" maxlength="500" required><?php echo htmlspecialchars($event['eventDescription'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="maxParticipants">Maximum Participants</label>
                        <input type="number" id="maxParticipants" name="maxParticipants" min="1"
                            value="<?php echo htmlspecialchars((string) ($event['maxParticipants'] ?? '')); ?>">
                    </div>
                </section>
                    </div>

                    <div class="create-event-col">
                        <section class="form-section">
                            <h2 class="form-section-title">Event Image / Poster</h2>
                            <label class="upload-zone" id="uploadZone" for="eventPoster">
                                <input type="file" id="eventPoster" name="eventPoster" accept="image/png,image/jpeg,image/jpg" hidden>
                                <div class="upload-placeholder<?php echo $hasCustomPoster ? ' hidden' : ''; ?>" id="uploadPlaceholder">
                                    <i class="fas fa-cloud-arrow-up upload-icon"></i>
                                    <p class="upload-title">Click to upload or drag and drop</p>
                                    <p class="upload-hint">PNG, JPG or JPEG (Max. 5MB)</p>
                                </div>
                                <img src="<?php echo $hasCustomPoster ? htmlspecialchars($currentPosterSrc) : ''; ?>"
                                    alt="Event poster preview" class="upload-preview<?php echo $hasCustomPoster ? '' : ' hidden'; ?>" id="posterPreview">
                            </label>
                            <div class="info-banner">
                                <i class="fas fa-info-circle"></i>
                                <span>This image is shown on the student Browse Events page.</span>
                            </div>
                        </section>
                    </div>
                </div>

                <footer class="form-footer">
                    <a href="manageEvent.php" class="btn btn-outline"><i class="fas fa-xmark"></i> Cancel</a>
                    <div class="form-footer-center">
                        <button type="submit" name="action" value="draft" class="btn btn-secondary">
                            <i class="fas fa-floppy-disk"></i> Save as Draft
                        </button>
                    </div>
                    <button type="submit" name="action" value="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publish (Active)
                    </button>
                </footer>
            </form>

    <script>
        (function() {
            const uploadZone = document.getElementById('uploadZone');
            const fileInput = document.getElementById('eventPoster');
            const preview = document.getElementById('posterPreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            if (!uploadZone || !fileInput) return;

            function showPreview(file) {
                if (!file || !file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }

            fileInput.addEventListener('change', function() {
                if (fileInput.files[0]) showPreview(fileInput.files[0]);
            });

            uploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });
            uploadZone.addEventListener('dragleave', function() {
                uploadZone.classList.remove('dragover');
            });
            uploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    showPreview(e.dataTransfer.files[0]);
                }
            });
        })();
    </script>

            <form action="deleteEvent_process.php" method="POST" class="delete-event-form" style="margin-top:1.5rem;"
                onsubmit="return confirm('Delete this event permanently? This cannot be undone.');">
                <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
                <button type="submit" class="btn btn-outline" style="color:#b3261e;border-color:#b3261e;">
                    <i class="fas fa-trash"></i> Delete Event
                </button>
            </form>

<?php committee_page_end(); ?>
