<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
  header('Location: login.php');
   exit();
}

$flashError = $_SESSION['createEvent_error'] ?? '';
$flashSuccess = $_SESSION['createEvent_success'] ?? '';
unset($_SESSION['createEvent_error'], $_SESSION['createEvent_success']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - FK-SCEMS</title>
    <link rel="stylesheet" href="committeeStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="dashboard-body">

    <header class="main-header">
        <div class="header-left">
            <img src="images/umpsaLogo.png" alt="UMPSA Logo" class="mini-logo">
            <span class="system-title">FK-SCEMS</span>
        </div>
        <div class="header-right">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <span class="user-role">Committee Member</span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul>
                <li>
                    <a href="committeeDashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                </li>
                <li class="active has-submenu open">
                    <a href="#"><i class="fas fa-calendar-alt"></i> Event</a>
                    <ul class="submenu">
                        <li><a href="manageEvent.php">My Events</a></li>
                        <li><a href="createEvent.php" class="active-sub">Create Event</a></li>
                        <li><a href="#">Registered Events</a></li>
                        <li><a href="#">Upcoming Events</a></li>
                        <li><a href="#">Past Events</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fas fa-clipboard-list"></i> My Registrations</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-user-circle"></i> Profile</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-bell"></i> Notifications</a>
                </li>
            </ul>
        </nav>

        <main class="main-content create-event-page">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="committeeDashboard.php">Dashboard</a>
                <span class="breadcrumb-sep">&gt;</span>
                <span>Event</span>
                <span class="breadcrumb-sep">&gt;</span>
                <span class="breadcrumb-current">Create Event</span>
            </nav>

            <div class="page-header">
                <h1>Create New Event</h1>
                <p class="page-subtitle">Fill in the details below to create a new event for your club.</p>
            </div>

            <?php if ($flashError): ?>
                <div class="alert alert-error" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($flashError); ?>
                </div>
            <?php endif; ?>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($flashSuccess); ?>
                </div>
            <?php endif; ?>

            <form class="create-event-form" action="createEvent_process.php" method="POST" enctype="multipart/form-data" id="createEventForm" novalidate>
                <div class="create-event-grid">
                    <div class="create-event-col">
                        <section class="form-section">
                            <h2 class="form-section-title">Event Information</h2>

                            <div class="form-group">
                                <label for="eventTitle">Event Title <span class="required">*</span></label>
                                <input type="text" id="eventTitle" name="eventTitle" placeholder="Enter event title" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="eventCategory">Event Category <span class="required">*</span></label>
                                    <select id="eventCategory" name="eventCategory" required>
                                        <option value="" disabled selected>Select category</option>
                                        <option value="Workshop">Workshop</option>
                                        <option value="Seminar">Seminar</option>
                                        <option value="Competition">Competition</option>
                                        <option value="Social">Social</option>
                                        <option value="Sports">Sports</option>
                                        <option value="Academic">Academic</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="eventDate">Event Date <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <input type="date" id="eventDate" name="eventDate" required>
                                        <i class="fas fa-calendar-alt input-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="startTime">Start Time <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <input type="time" id="startTime" name="startTime" required>
                                        <i class="fas fa-clock input-icon"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="endTime">End Time <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <input type="time" id="endTime" name="endTime" required>
                                        <i class="fas fa-clock input-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="venue">Venue <span class="required">*</span></label>
                                    <input type="text" id="venue" name="venue" placeholder="Enter venue" required>
                                </div>
                                <div class="form-group">
                                    <label for="locationRoom">Location / Room</label>
                                    <input type="text" id="locationRoom" name="locationRoom" placeholder="Enter location or room">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="eventDescription">Event Description <span class="required">*</span></label>
                                <div class="textarea-wrap">
                                    <textarea id="eventDescription" name="eventDescription" rows="5" maxlength="500" placeholder="Enter event description" required></textarea>
                                    <span class="char-counter" id="descCounter">0 / 500</span>
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <h2 class="form-section-title">Event Settings</h2>

                            <div class="form-group">
                                <span class="field-label">Registration Required?</span>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="registrationRequired" value="yes" checked>
                                        <span>Yes</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="registrationRequired" value="no">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-row registration-dates" id="registrationDates">
                                <div class="form-group">
                                    <label for="registrationStartDate">Registration Start Date</label>
                                    <div class="input-with-icon">
                                        <input type="date" id="registrationStartDate" name="registrationStartDate">
                                        <i class="fas fa-calendar-alt input-icon"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="registrationEndDate">Registration End Date</label>
                                    <div class="input-with-icon">
                                        <input type="date" id="registrationEndDate" name="registrationEndDate">
                                        <i class="fas fa-calendar-alt input-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <span class="field-label">Approval Required?</span>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="approvalRequired" value="yes" checked>
                                        <span>Yes</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="approvalRequired" value="no">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="eventVisibility">Event Visibility</label>
                                <select id="eventVisibility" name="eventVisibility">
                                    <option value="club_members" selected>Club Members Only</option>
                                    <option value="public">Public (All Students)</option>
                                    <option value="committee">Committee Only</option>
                                </select>
                                <p class="field-hint">Choose who can see this event.</p>
                            </div>
                        </section>
                    </div>

                    <div class="create-event-col">
                        <section class="form-section">
                            <h2 class="form-section-title">Event Image / Poster</h2>

                            <label class="upload-zone" id="uploadZone" for="eventPoster">
                                <input type="file" id="eventPoster" name="eventPoster" accept="image/png,image/jpeg,image/jpg" hidden>
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <i class="fas fa-cloud-arrow-up upload-icon"></i>
                                    <p class="upload-title">Click to upload or drag and drop</p>
                                    <p class="upload-hint">PNG, JPG or JPEG (Max. 5MB)</p>
                                </div>
                                <img src="" alt="Event poster preview" class="upload-preview hidden" id="posterPreview">
                            </label>

                            <div class="info-banner">
                                <i class="fas fa-info-circle"></i>
                                <span>Upload an image to make your event more attractive.</span>
                            </div>
                        </section>

                        <section class="form-section">
                            <h2 class="form-section-title">Additional Information</h2>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="estimatedCost">Estimated Cost (RM)</label>
                                    <input type="number" id="estimatedCost" name="estimatedCost" min="0" step="0.01" placeholder="Enter estimated cost">
                                </div>
                                <div class="form-group">
                                    <label for="maxParticipants">Maximum Participants</label>
                                    <input type="number" id="maxParticipants" name="maxParticipants" min="1" placeholder="Enter maximum participants">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="contactPerson">Contact Person</label>
                                <input type="text" id="contactPerson" name="contactPerson" placeholder="Enter contact person name">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contactEmail">Contact Email</label>
                                    <input type="email" id="contactEmail" name="contactEmail" placeholder="Enter contact email">
                                </div>
                                <div class="form-group">
                                    <label for="contactPhone">Contact Phone</label>
                                    <input type="tel" id="contactPhone" name="contactPhone" placeholder="Enter contact phone number">
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <footer class="form-footer">
                    <a href="manageEvent.php" class="btn btn-outline">
                        <i class="fas fa-xmark"></i> Cancel
                    </a>
                    <div class="form-footer-center">
                        <button type="submit" name="action" value="draft" class="btn btn-secondary">
                            <i class="fas fa-floppy-disk"></i> Save as Draft
                        </button>
                    </div>
                    <button type="submit" name="action" value="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                </footer>
            </form>
        </main>
    </div>

    <script>
        (function () {
            const desc = document.getElementById('eventDescription');
            const counter = document.getElementById('descCounter');
            const regRadios = document.querySelectorAll('input[name="registrationRequired"]');
            const regDates = document.getElementById('registrationDates');
            const regStart = document.getElementById('registrationStartDate');
            const regEnd = document.getElementById('registrationEndDate');
            const uploadZone = document.getElementById('uploadZone');
            const fileInput = document.getElementById('eventPoster');
            const preview = document.getElementById('posterPreview');
            const placeholder = document.getElementById('uploadPlaceholder');

            function updateCounter() {
                const len = desc.value.length;
                counter.textContent = len + ' / 500';
                counter.classList.toggle('at-limit', len >= 500);
            }

            desc.addEventListener('input', updateCounter);
            updateCounter();

            function toggleRegistrationDates() {
                const required = document.querySelector('input[name="registrationRequired"]:checked').value === 'yes';
                regDates.style.display = required ? '' : 'none';
                regStart.required = required;
                regEnd.required = required;
                if (!required) {
                    regStart.value = '';
                    regEnd.value = '';
                }
            }

            regRadios.forEach(function (radio) {
                radio.addEventListener('change', toggleRegistrationDates);
            });
            toggleRegistrationDates();

            function showPreview(file) {
                if (!file || !file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }

            fileInput.addEventListener('change', function () {
                if (fileInput.files[0]) showPreview(fileInput.files[0]);
            });

            uploadZone.addEventListener('dragover', function (e) {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });

            uploadZone.addEventListener('dragleave', function () {
                uploadZone.classList.remove('dragover');
            });

            uploadZone.addEventListener('drop', function (e) {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    showPreview(e.dataTransfer.files[0]);
                }
            });
        })();
    </script>

</body>

</html>
