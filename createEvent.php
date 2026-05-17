<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'committee') {
    $_SESSION['login_notice'] = 'Please log in with your club organiser account (e.g. fkscems01) to open Create Event.';
    header('Location: login.php');
    exit();
}

$flashError = $_SESSION['createEvent_error'] ?? '';
$flashSuccess = $_SESSION['createEvent_success'] ?? '';
unset($_SESSION['createEvent_error'], $_SESSION['createEvent_success']);

$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create New Event - FK-SCEMS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9ff;
        }

        .upload-preview:not(.hidden) {
            display: block;
            max-height: 200px;
            margin: 0 auto;
            object-fit: contain;
        }

        .upload-placeholder.hidden {
            display: none;
        }

        #registrationDates.hidden {
            display: none;
        }

        .radio-dot.hidden {
            display: none;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-fixed-variant": "#00419c",
                        "error-container": "#ffdad6",
                        "surface-container": "#e5eeff",
                        "on-secondary": "#ffffff",
                        "surface": "#f8f9ff",
                        "on-secondary-fixed": "#151c22",
                        "on-primary-fixed": "#001945",
                        "surface-tint": "#0058cc",
                        "on-error": "#ffffff",
                        "on-surface": "#0b1c30",
                        "secondary-fixed-dim": "#c0c7cf",
                        "on-error-container": "#93000a",
                        "primary-container": "#0061e0",
                        "inverse-primary": "#b0c6ff",
                        "inverse-on-surface": "#eaf1ff",
                        "surface-container-high": "#dce9ff",
                        "surface-container-highest": "#d3e4fe",
                        "secondary": "#585f66",
                        "tertiary": "#8f3200",
                        "on-secondary-fixed-variant": "#40484e",
                        "primary": "#004aaf",
                        "on-primary-container": "#e2e8ff",
                        "surface-container-low": "#eff4ff",
                        "on-tertiary-fixed-variant": "#7f2b00",
                        "background": "#f8f9ff",
                        "on-tertiary-container": "#ffe3d9",
                        "primary-fixed": "#d9e2ff",
                        "surface-variant": "#d3e4fe",
                        "on-surface-variant": "#424654",
                        "surface-bright": "#f8f9ff",
                        "inverse-surface": "#213145",
                        "on-primary": "#ffffff",
                        "on-tertiary": "#ffffff",
                        "tertiary-container": "#b74200",
                        "on-background": "#0b1c30",
                        "secondary-container": "#dce3eb",
                        "outline": "#727786",
                        "surface-dim": "#cbdbf5",
                        "secondary-fixed": "#dce3eb",
                        "tertiary-fixed-dim": "#ffb599",
                        "primary-fixed-dim": "#b0c6ff",
                        "tertiary-fixed": "#ffdbce",
                        "on-tertiary-fixed": "#370e00",
                        "on-secondary-container": "#5e656c",
                        "outline-variant": "#c2c6d7",
                        "surface-container-lowest": "#ffffff",
                        "error": "#ba1a1a"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    spacing: {
                        gutter: "24px",
                        lg: "32px",
                        md: "24px",
                        margin: "24px",
                        sm: "16px",
                        xs: "8px",
                        base: "4px",
                        xl: "48px"
                    },
                    fontFamily: {
                        "body-md": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "body-md": ["14px", { lineHeight: "20px", fontWeight: "400" }],
                        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }],
                        "display-lg": ["24px", { lineHeight: "32px", fontWeight: "700" }],
                        "label-lg": ["14px", { lineHeight: "20px", fontWeight: "600" }],
                        "headline-md": ["18px", { lineHeight: "24px", fontWeight: "600" }]
                    }
                }
            }
        };
    </script>
</head>

<body class="bg-background text-on-background">

    <header class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center w-full px-gutter h-20 bg-surface-container-lowest border-b border-outline-variant">
        <div class="flex items-center gap-4">
            <img alt="FK-SCEMS Logo" class="h-12 w-auto object-contain" src="images/umpsaLogo.png"
                onerror="this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuCseBg41XqB3iWYgoGKcIy61jZ8zaAtGGY-to9Q_S6-0lmkrXbgyhr1ucCBjjg3ri1km9XPNQO8SKhZK9BeJTMVmg2hxb4OLehmcg5pI0js7TaT3fCL4KwVYe3DQXA_-C1OvlthasIXP-89wjMFM_PKa4uG8b5TQUFa5zIc3NxRNOqORuIiUytHfxnudfj3f4jLsN_8RMgJvTB4Ztrwo3WT7XAUpbIpHxaKx_4ia0WyhFGbKf1oQ35phQNPHWS3Clk4wczPYMBrGuE'" />
            <span class="text-display-lg font-display-lg text-on-primary-fixed-variant">FK-SCEMS</span>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right hidden md:block">
                <p class="text-label-lg font-label-lg text-on-surface"><?php echo $userName; ?></p>
                <p class="text-label-sm font-label-sm text-on-surface-variant">Club Member</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="material-symbols-outlined text-primary cursor-pointer text-[28px]">account_circle</span>
                <a href="logout.php"
                    class="bg-primary text-on-primary px-4 py-2 rounded-lg text-label-lg font-label-lg hover:bg-primary-container transition-colors">Logout</a>
            </div>
        </div>
    </header>

    <aside class="flex flex-col h-screen fixed left-0 top-0 pt-20 w-64 bg-surface-container-lowest border-r border-outline-variant z-40">
        <nav class="flex-1 px-2 space-y-1 mt-4">
            <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group"
                href="committeeDashboard.php">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-label-lg font-label-lg">Dashboard</span>
            </a>
            <a class="bg-surface-container text-primary px-4 py-3 flex items-center gap-3 rounded-lg"
                href="manageEvent.php">
                <span class="material-symbols-outlined">calendar_today</span>
                <span class="text-label-lg font-label-lg">Event</span>
            </a>
            <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group pl-12"
                href="manageEvent.php">
                <span class="text-label-lg font-label-lg">My Events</span>
            </a>
            <a class="text-primary px-4 py-3 flex items-center gap-3 bg-primary-fixed/30 rounded-lg pl-12"
                href="createEvent.php">
                <span class="text-label-lg font-label-lg font-semibold">Create Event</span>
            </a>
            <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group"
                href="#">
                <span class="material-symbols-outlined">assignment_ind</span>
                <span class="text-label-lg font-label-lg">My Registrations</span>
            </a>
            <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group"
                href="#">
                <span class="material-symbols-outlined">person</span>
                <span class="text-label-lg font-label-lg">Profile</span>
            </a>
            <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg group"
                href="#">
                <span class="material-symbols-outlined">notifications</span>
                <span class="text-label-lg font-label-lg">Notifications</span>
            </a>
        </nav>
        <div class="mt-auto px-2 pb-6 border-t border-outline-variant pt-4">
            <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg"
                href="#">
                <span class="material-symbols-outlined">settings</span>
                <span class="text-label-lg font-label-lg">Settings</span>
            </a>
            <a class="text-on-surface-variant px-4 py-3 flex items-center gap-3 hover:bg-surface-container transition-all rounded-lg"
                href="#">
                <span class="material-symbols-outlined">help</span>
                <span class="text-label-lg font-label-lg">Help</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 pt-20 pb-24 min-h-screen">
        <div class="px-gutter py-8">
            <nav class="flex items-center gap-2 text-label-sm font-label-sm text-primary mb-6" aria-label="Breadcrumb">
                <a href="committeeDashboard.php" class="hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="manageEvent.php" class="hover:underline">Event</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span class="text-on-surface-variant">Create Event</span>
            </nav>

            <h1 class="text-[32px] font-bold text-on-surface leading-tight mb-2">Create New Event</h1>
            <p class="text-body-md font-body-md text-on-surface-variant mb-10">Fill in the details below to create a new event.</p>

            <?php if ($flashError): ?>
                <div class="mb-6 flex items-start gap-3 p-4 bg-error-container border border-error/20 rounded-lg text-on-error-container"
                    role="alert">
                    <span class="material-symbols-outlined text-error">error</span>
                    <p class="text-body-md font-body-md"><?php echo htmlspecialchars($flashError); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($flashSuccess): ?>
                <div class="mb-6 flex items-start gap-3 p-4 bg-surface-container border border-primary/20 rounded-lg text-on-surface"
                    role="status">
                    <span class="material-symbols-outlined text-primary">check_circle</span>
                    <p class="text-body-md font-body-md"><?php echo htmlspecialchars($flashSuccess); ?></p>
                </div>
            <?php endif; ?>

            <form action="createEvent_process.php" method="POST" enctype="multipart/form-data" id="createEventForm" novalidate>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-8 space-y-8">
                        <section class="bg-surface-container-lowest p-8 border border-outline-variant rounded-lg">
                            <h2 class="text-headline-md font-headline-md text-primary mb-8 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-label-lg">1</span>
                                Event Information
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-1">
                                    <label class="block text-label-lg font-label-lg mb-2" for="eventTitle">Event Title <span class="text-error">*</span></label>
                                    <input class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant"
                                        id="eventTitle" name="eventTitle" placeholder="Enter event title" type="text" required />
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-label-lg font-label-lg mb-2" for="eventCategory">Event Category <span class="text-error">*</span></label>
                                    <select class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-white"
                                        id="eventCategory" name="eventCategory" required>
                                        <option value="" disabled selected>Select category</option>
                                        <option value="Academic">Academic</option>
                                        <option value="Sports">Sports</option>
                                        <option value="Culture">Culture</option>
                                        <option value="Workshop">Workshop</option>
                                        <option value="Seminar">Seminar</option>
                                        <option value="Competition">Competition</option>
                                        <option value="Social">Social</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-label-lg font-label-lg mb-2" for="eventDate">Event Date <span class="text-error">*</span></label>
                                    <div class="relative">
                                        <input class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all"
                                            id="eventDate" name="eventDate" type="date" required />
                                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">calendar_today</span>
                                    </div>
                                </div>
                                <div class="md:col-span-1 flex gap-4">
                                    <div class="flex-1">
                                        <label class="block text-label-lg font-label-lg mb-2" for="startTime">Start Time <span class="text-error">*</span></label>
                                        <div class="relative">
                                            <input class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all"
                                                id="startTime" name="startTime" type="time" required />
                                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">schedule</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-label-lg font-label-lg mb-2" for="endTime">End Time <span class="text-error">*</span></label>
                                        <div class="relative">
                                            <input class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all"
                                                id="endTime" name="endTime" type="time" required />
                                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">schedule</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-label-lg font-label-lg mb-2" for="venue">Venue <span class="text-error">*</span></label>
                                    <input class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant"
                                        id="venue" name="venue" placeholder="Enter venue" type="text" required />
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-label-lg font-label-lg mb-2" for="locationRoom">Location / Room</label>
                                    <input class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant"
                                        id="locationRoom" name="locationRoom" placeholder="Enter location or room" type="text" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-label-lg font-label-lg mb-2" for="eventDescription">Event Description <span class="text-error">*</span></label>
                                    <div class="relative">
                                        <textarea class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant resize-none"
                                            id="eventDescription" name="eventDescription" rows="6" maxlength="500" placeholder="Enter event description" required></textarea>
                                        <span class="absolute bottom-3 right-4 text-label-sm font-label-sm text-outline-variant" id="descCounter">0 / 500</span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="bg-surface-container-lowest p-8 border border-outline-variant rounded-lg">
                            <h2 class="text-headline-md font-headline-md text-primary mb-8 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-label-lg">2</span>
                                Event Settings
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div class="space-y-4">
                                    <div>
                                        <span class="block text-label-lg font-label-lg mb-3">Registration Required?</span>
                                        <div class="flex items-center gap-6">
                                            <label class="flex items-center gap-2 cursor-pointer group custom-radio">
                                                <div class="radio-circle w-5 h-5 rounded-full border-2 border-primary flex items-center justify-center">
                                                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary"></div>
                                                </div>
                                                <span class="text-body-md font-body-md text-on-surface">Yes</span>
                                                <input checked class="sr-only" name="registrationRequired" type="radio" value="yes" />
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer group custom-radio">
                                                <div class="radio-circle w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center group-hover:border-primary transition-colors">
                                                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary hidden"></div>
                                                </div>
                                                <span class="text-body-md font-body-md text-on-surface">No</span>
                                                <input class="sr-only" name="registrationRequired" type="radio" value="no" />
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="block text-label-lg font-label-lg mb-3">Approval Required?</span>
                                        <div class="flex items-center gap-6">
                                            <label class="flex items-center gap-2 cursor-pointer group custom-radio">
                                                <div class="radio-circle w-5 h-5 rounded-full border-2 border-primary flex items-center justify-center">
                                                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary"></div>
                                                </div>
                                                <span class="text-body-md font-body-md text-on-surface">Yes</span>
                                                <input checked class="sr-only" name="approvalRequired" type="radio" value="yes" />
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer group custom-radio">
                                                <div class="radio-circle w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center group-hover:border-primary transition-colors">
                                                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary hidden"></div>
                                                </div>
                                                <span class="text-body-md font-body-md text-on-surface">No</span>
                                                <input class="sr-only" name="approvalRequired" type="radio" value="no" />
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-6">
                                    <div class="grid grid-cols-2 gap-4" id="registrationDates">
                                        <div>
                                            <label class="block text-label-lg font-label-lg mb-2" for="registrationStartDate">Registration Start Date</label>
                                            <div class="relative">
                                                <input class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all"
                                                    id="registrationStartDate" name="registrationStartDate" type="date" />
                                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">calendar_today</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-label-lg font-label-lg mb-2" for="registrationEndDate">Registration End Date</label>
                                            <div class="relative">
                                                <input class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all"
                                                    id="registrationEndDate" name="registrationEndDate" type="date" />
                                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">calendar_today</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-label-lg font-label-lg mb-2" for="eventVisibility">Event Visibility</label>
                                        <select class="w-full px-4 py-3 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-white"
                                            id="eventVisibility" name="eventVisibility">
                                            <option value="club_members" selected>Club Members Only</option>
                                            <option value="public">Public</option>
                                            <option value="internal_faculty">Internal Faculty Only</option>
                                        </select>
                                        <p class="mt-2 text-label-sm font-label-sm text-on-surface-variant">Choose who can see this event.</p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="lg:col-span-4 space-y-8">
                        <section class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
                            <div class="p-6 border-b border-outline-variant">
                                <h3 class="text-label-lg font-label-lg text-primary uppercase tracking-wider">Event Image / Poster</h3>
                            </div>
                            <div class="p-8 text-center">
                                <label class="block border-2 border-dashed border-primary-fixed bg-surface-container-low rounded-lg p-10 flex flex-col items-center gap-4 hover:bg-surface-container-high transition-colors cursor-pointer group"
                                    id="uploadZone" for="eventPoster">
                                    <input type="file" id="eventPoster" name="eventPoster" accept="image/png,image/jpeg,image/jpg" class="sr-only" />
                                    <div class="upload-placeholder flex flex-col items-center gap-4" id="uploadPlaceholder">
                                        <div class="w-16 h-16 rounded-full bg-surface-container-lowest flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                            <span class="material-symbols-outlined text-primary text-[32px]">cloud_upload</span>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-label-lg font-label-lg text-on-surface">Click to upload <span class="font-normal text-on-surface-variant">or drag and drop</span></p>
                                            <p class="text-label-sm font-label-sm text-on-surface-variant">PNG, JPG or JPEG (Max. 5MB)</p>
                                        </div>
                                    </div>
                                    <img src="" alt="Event poster preview" class="upload-preview hidden rounded-lg" id="posterPreview" />
                                </label>
                                <div class="mt-6 flex items-start gap-3 p-4 bg-surface-container border border-surface-variant rounded text-left">
                                    <span class="material-symbols-outlined text-primary text-[20px]" style='font-variation-settings: "FILL" 1;'>info</span>
                                    <p class="text-body-md font-body-md text-on-surface">Upload an image to make your event more attractive.</p>
                                </div>
                            </div>
                        </section>

                        <section class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
                            <div class="p-6 border-b border-outline-variant">
                                <h2 class="text-label-lg font-label-lg text-primary uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[11px] font-bold">3</span>
                                    Additional Information
                                </h2>
                            </div>
                            <div class="p-6 space-y-5">
                                <div>
                                    <label class="block text-label-lg font-label-lg mb-2" for="estimatedCost">Estimated Cost (RM)</label>
                                    <input class="w-full px-4 py-2.5 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant"
                                        id="estimatedCost" name="estimatedCost" min="0" step="0.01" placeholder="Enter estimated cost" type="number" />
                                </div>
                                <div>
                                    <label class="block text-label-lg font-label-lg mb-2" for="maxParticipants">Maximum Participants</label>
                                    <input class="w-full px-4 py-2.5 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant"
                                        id="maxParticipants" name="maxParticipants" min="1" placeholder="Enter maximum participants" type="number" />
                                </div>
                                <div>
                                    <label class="block text-label-lg font-label-lg mb-2" for="contactPerson">Contact Person</label>
                                    <input class="w-full px-4 py-2.5 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant"
                                        id="contactPerson" name="contactPerson" placeholder="Enter contact person name" type="text" />
                                </div>
                                <div>
                                    <label class="block text-label-lg font-label-lg mb-2" for="contactEmail">Contact Email</label>
                                    <input class="w-full px-4 py-2.5 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant"
                                        id="contactEmail" name="contactEmail" placeholder="Enter contact email" type="email" />
                                </div>
                                <div>
                                    <label class="block text-label-lg font-label-lg mb-2" for="contactPhone">Contact Phone</label>
                                    <input class="w-full px-4 py-2.5 border border-outline-variant rounded focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline-variant"
                                        id="contactPhone" name="contactPhone" placeholder="Enter contact phone number" type="tel" />
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <footer class="fixed bottom-0 left-64 right-0 bg-surface-container-lowest border-t border-outline-variant py-4 px-gutter flex items-center justify-between z-40">
                    <div class="flex items-center gap-4">
                        <a href="manageEvent.php"
                            class="px-8 py-3 border border-outline-variant rounded hover:bg-surface-container transition-colors text-label-lg font-label-lg text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                            Cancel
                        </a>
                        <button type="submit" name="action" value="draft"
                            class="px-8 py-3 bg-primary-container text-white border border-primary-container rounded hover:bg-primary transition-colors text-label-lg font-label-lg flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Save as Draft
                        </button>
                    </div>
                    <button type="submit" name="action" value="submit"
                        class="px-12 py-3 bg-primary text-white rounded hover:bg-on-primary-fixed-variant transition-all text-label-lg font-label-lg flex items-center gap-3 shadow-md active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">send</span>
                        Submit for Approval
                    </button>
                </footer>
            </form>
        </div>
    </main>

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
                counter.classList.toggle('text-error', len >= 500);
            }

            desc.addEventListener('input', updateCounter);
            updateCounter();

            function syncRadioVisuals(groupName) {
                document.querySelectorAll('input[name="' + groupName + '"]').forEach(function (radio) {
                    const label = radio.closest('.custom-radio');
                    if (!label) return;
                    const circle = label.querySelector('.radio-circle');
                    const dot = label.querySelector('.radio-dot');
                    if (radio.checked) {
                        circle.classList.add('border-primary');
                        circle.classList.remove('border-outline-variant');
                        dot.classList.remove('hidden');
                    } else {
                        circle.classList.remove('border-primary');
                        circle.classList.add('border-outline-variant');
                        dot.classList.add('hidden');
                    }
                });
            }

            document.querySelectorAll('.custom-radio input[type="radio"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    syncRadioVisuals(radio.name);
                });
            });
            syncRadioVisuals('registrationRequired');
            syncRadioVisuals('approvalRequired');

            function toggleRegistrationDates() {
                const required = document.querySelector('input[name="registrationRequired"]:checked').value === 'yes';
                regDates.classList.toggle('hidden', !required);
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
                uploadZone.classList.add('bg-surface-container-high');
            });

            uploadZone.addEventListener('dragleave', function () {
                uploadZone.classList.remove('bg-surface-container-high');
            });

            uploadZone.addEventListener('drop', function (e) {
                e.preventDefault();
                uploadZone.classList.remove('bg-surface-container-high');
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    showPreview(e.dataTransfer.files[0]);
                }
            });
        })();
    </script>

</body>

</html>
