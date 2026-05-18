<?php

/**
 * Supports both legacy fkscems event table and extended setup.sql schema.
 */

function eventUsesLegacySchema(mysqli $conn): bool
{
    static $legacy = null;
    if ($legacy !== null) {
        return $legacy;
    }
    $r = mysqli_query($conn, "SHOW COLUMNS FROM event LIKE 'eventCategory'");
    $legacy = !($r && mysqli_num_rows($r) > 0);
    return $legacy;
}

/** SQL fragment: upcoming events students may browse */
function browseStudentEventsWhere(mysqli $conn, string $today, string $alias = 'e'): string
{
    if (eventUsesLegacySchema($conn)) {
        $a = $alias;
        return "
            {$a}.eventDate >= '{$today}'
            AND LOWER({$a}.eventStatus) IN ('active', 'approved', 'open')
        ";
    }
    return "
        {$a}.eventDate >= '{$today}'
        AND {$a}.status = 'approved'
        AND {$a}.eventVisibility IN ('public', 'club_members')
    ";
}

/** Normalize one event row for templates/helpers */
function normalizeEventRow(mysqli $conn, array $row): array
{
    if (!eventUsesLegacySchema($conn)) {
        $row['eventCategory'] = $row['eventCategory'] ?? 'General';
        $row['venue'] = $row['venue'] ?? '';
        $row['startTime'] = $row['startTime'] ?? '';
        $row['maxParticipants'] = $row['maxParticipants'] ?? null;
        $row['status'] = $row['status'] ?? '';
        return $row;
    }

    $category = $row['clubCategory'] ?? $row['clubName'] ?? 'General';
    return array_merge($row, [
        'eventCategory' => $category,
        'clubName' => $row['clubName'] ?? $category,
        'venue' => $row['eventVenue'] ?? '',
        'locationRoom' => $row['locationRoom'] ?? null,
        'startTime' => $row['eventTime'] ?? '',
        'endTime' => $row['eventTime'] ?? '',
        'maxParticipants' => isset($row['maxParticipations']) ? $row['maxParticipations'] : ($row['maxParticipants'] ?? null),
        'status' => $row['eventStatus'] ?? '',
        'eventPoster' => $row['eventPoster'] ?? null,
        'organizerName' => $row['organizerName'] ?? ($row['clubName'] ?? 'Club Organizer'),
    ]);
}

/** @return array<string, mixed>|null */
function fetchBrowsableEvent(mysqli $conn, int $eventID, string $today): ?array
{
    if (eventUsesLegacySchema($conn)) {
        $browseWhere = browseStudentEventsWhere($conn, $today, 'e');
        $sql = "
            SELECT e.*, c.clubName, c.clubCategory
            FROM event e
            INNER JOIN club c ON e.clubID = c.clubID
            WHERE e.eventID = ? AND {$browseWhere}
            LIMIT 1
        ";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return null;
        }
        mysqli_stmt_bind_param($stmt, 'i', $eventID);
        mysqli_stmt_execute($stmt);
        $row = mysqli_stmt_get_result($stmt)->fetch_assoc();
        mysqli_stmt_close($stmt);
        return $row ? normalizeEventRow($conn, $row) : null;
    }

    $stmt = mysqli_prepare($conn, "
        SELECT e.*, u.name AS organizerName
        FROM event e
        INNER JOIN user u ON e.createdBy = u.userID
        WHERE e.eventID = ?
          AND e.status = 'approved'
          AND e.eventDate >= ?
          AND e.eventVisibility IN ('public', 'club_members')
        LIMIT 1
    ");
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'is', $eventID, $today);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);
    return $row ? normalizeEventRow($conn, $row) : null;
}

/** @return array<string, mixed>|null */
function fetchEventById(mysqli $conn, int $eventID): ?array
{
    if (eventUsesLegacySchema($conn)) {
        $sql = "
            SELECT e.*, c.clubName, c.clubCategory
            FROM event e
            INNER JOIN club c ON e.clubID = c.clubID
            WHERE e.eventID = ?
            LIMIT 1
        ";
    } else {
        $sql = "
            SELECT e.*, u.name AS organizerName
            FROM event e
            INNER JOIN user u ON e.createdBy = u.userID
            WHERE e.eventID = ?
            LIMIT 1
        ";
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'i', $eventID);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    return $row ? normalizeEventRow($conn, $row) : null;
}

/** Map form action to legacy eventStatus (students browse Active only). */
function legacyEventStatusFromAction(string $action): string
{
    return $action === 'submit' ? 'Active' : 'Draft';
}

/** Map form action to extended-schema status column. */
function extendedEventStatusFromAction(string $action): string
{
    return $action === 'submit' ? 'approved' : 'draft';
}

function clubTableName(mysqli $conn): string
{
    static $name = null;
    if ($name !== null) {
        return $name;
    }
    $lower = mysqli_query($conn, "SHOW TABLES LIKE 'club'");
    $name = ($lower && mysqli_num_rows($lower) > 0) ? 'club' : 'Club';
    return $name;
}

/** Resolve or create a club row from event category for legacy inserts. */
function resolveClubIdForCategory(mysqli $conn, string $category): int
{
    require_once __DIR__ . '/browse_event_helpers.php';

    $category = trim($category) ?: 'General';
    $clubName = clubDisplayName($category);
    $table = clubTableName($conn);

    $stmt = mysqli_prepare($conn, "
        SELECT clubID FROM {$table}
        WHERE clubCategory = ? OR clubName = ?
        ORDER BY clubID ASC
        LIMIT 1
    ");
    if (!$stmt) {
        throw new RuntimeException('Could not read club data. Check that the club table exists.');
    }
    mysqli_stmt_bind_param($stmt, 'ss', $category, $clubName);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    if ($row) {
        return (int) $row['clubID'];
    }

    $ins = mysqli_prepare($conn, "
        INSERT INTO {$table} (clubName, clubCategory, clubStatus)
        VALUES (?, ?, 'Active')
    ");
    if (!$ins) {
        throw new RuntimeException('Could not create club for category: ' . $category);
    }
    mysqli_stmt_bind_param($ins, 'ss', $clubName, $category);
    if (!mysqli_stmt_execute($ins)) {
        mysqli_stmt_close($ins);
        throw new RuntimeException('Failed to save club for category: ' . $category);
    }
    $clubID = (int) mysqli_insert_id($conn);
    mysqli_stmt_close($ins);

    if ($clubID <= 0) {
        throw new RuntimeException('Invalid club ID after insert. Run fix_legacy_event_schema.sql in phpMyAdmin.');
    }

    return $clubID;
}

function repairEventClubIndex(mysqli $conn): void
{
    $fk = mysqli_query($conn, "
        SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'event'
          AND COLUMN_NAME = 'clubID' AND REFERENCED_TABLE_NAME IS NOT NULL
        LIMIT 1
    ");
    if ($fk && ($row = mysqli_fetch_assoc($fk))) {
        $name = $row['CONSTRAINT_NAME'];
        @mysqli_query($conn, "ALTER TABLE `event` DROP FOREIGN KEY `{$name}`");
    }

    @mysqli_query($conn, 'ALTER TABLE `event` DROP INDEX `clubID`');
    @mysqli_query($conn, 'ALTER TABLE `event` ADD KEY `idx_event_clubID` (`clubID`)');

    $clubTable = clubTableName($conn);
    @mysqli_query($conn, "ALTER TABLE `event` ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`clubID`) REFERENCES `{$clubTable}` (`clubID`)");
}

/** Add eventPoster column when using legacy event table. */
function ensureEventPosterColumn(mysqli $conn): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $col = mysqli_query($conn, "SHOW COLUMNS FROM event LIKE 'eventPoster'");
    if ($col && mysqli_num_rows($col) > 0) {
        return;
    }

    @mysqli_query($conn, 'ALTER TABLE `event` ADD COLUMN `eventPoster` varchar(255) DEFAULT NULL');
}

function ensureLegacyEventSchema(mysqli $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    @mysqli_query($conn, 'DELETE FROM `event` WHERE `eventID` = 0');
    @mysqli_query($conn, 'ALTER TABLE `event` MODIFY `eventID` int(11) NOT NULL AUTO_INCREMENT');

    $clubTable = clubTableName($conn);
    @mysqli_query($conn, "DELETE FROM `{$clubTable}` WHERE `clubID` = 0");
    @mysqli_query($conn, "ALTER TABLE `{$clubTable}` MODIFY `clubID` int(11) NOT NULL AUTO_INCREMENT");

    $idx = mysqli_query($conn, "SHOW INDEX FROM event WHERE Column_name = 'clubID' AND Non_unique = 0");
    if ($idx && mysqli_num_rows($idx) > 0) {
        repairEventClubIndex($conn);
    }

    ensureEventPosterColumn($conn);
}

/**
 * @param callable(string): void|null $onError
 */
function uploadEventPosterFile(?callable $onError = null): ?string
{
    if (empty($_FILES['eventPoster']['name'])) {
        return null;
    }

    $file = $_FILES['eventPoster'];
    $fail = function (string $message) use ($onError): void {
        if ($onError) {
            $onError($message);
        }
        throw new RuntimeException($message);
    };

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $fail('Failed to upload event poster.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $fail('Event poster must be 5MB or smaller.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png'], true)) {
        $fail('Event poster must be PNG or JPEG.');
    }

    $uploadDir = dirname(__DIR__) . '/uploads/events/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $fail('Failed to save event poster.');
    }

    return 'uploads/events/' . $filename;
}

function deleteEventPosterFile(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    $full = dirname(__DIR__) . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
    if (is_file($full)) {
        @unlink($full);
    }
}

function syncLegacyEventsForStudentBrowse(mysqli $conn): void
{
    @mysqli_query($conn, "
        UPDATE `event`
        SET `eventStatus` = 'Active'
        WHERE LOWER(`eventStatus`) IN ('pending', 'pending_approval', 'approved', 'open')
    ");
}

/** Badge label/class for committee manage-event table. */
function committeeEventDisplayStatus(string $status, string $eventDate): array
{
    $today = date('Y-m-d');
    $key = strtolower(trim($status));

    if (in_array($key, ['cancelled', 'canceled'], true)) {
        return ['label' => 'Cancelled', 'class' => 'bg-secondary-container text-on-secondary-container'];
    }
    if (in_array($key, ['draft', 'pending', 'pending_approval'], true)) {
        return ['label' => 'Draft', 'class' => 'bg-surface-container-high text-on-surface-variant'];
    }
    if ($key === 'rejected' || $key === 'closed') {
        return ['label' => 'Closed', 'class' => 'bg-error-container text-on-error-container'];
    }
    if (in_array($key, ['active', 'approved', 'open'], true)) {
        if ($eventDate < $today) {
            return ['label' => 'Closed', 'class' => 'bg-error-container text-on-error-container'];
        }
        return ['label' => 'Active', 'class' => 'bg-green-100 text-green-700'];
    }

    return ['label' => ucwords(str_replace('_', ' ', $status)), 'class' => 'bg-surface-container-high text-on-surface-variant'];
}

function committeeStatusFilterSql(mysqli $conn, string $filter): string
{
    $today = date('Y-m-d');
    $legacy = eventUsesLegacySchema($conn);
    $statusCol = $legacy ? 'e.eventStatus' : 'e.status';

    switch ($filter) {
        case 'active':
            if ($legacy) {
                return " AND LOWER({$statusCol}) IN ('active', 'approved', 'open') AND e.eventDate >= '{$today}'";
            }
            return " AND {$statusCol} = 'approved' AND e.eventDate >= '{$today}'";
        case 'draft':
            if ($legacy) {
                return " AND LOWER({$statusCol}) IN ('draft', 'pending', 'pending_approval')";
            }
            return " AND {$statusCol} IN ('draft', 'pending_approval')";
        case 'closed':
            if ($legacy) {
                return " AND (LOWER({$statusCol}) IN ('rejected', 'closed') OR (LOWER({$statusCol}) IN ('active', 'approved', 'open') AND e.eventDate < '{$today}'))";
            }
            return " AND ({$statusCol} = 'rejected' OR ({$statusCol} = 'approved' AND e.eventDate < '{$today}'))";
        case 'cancelled':
            return " AND LOWER({$statusCol}) IN ('cancelled', 'canceled')";
        default:
            return '';
    }
}

function normalizeCommitteeEventListRow(mysqli $conn, array $row): array
{
    if (eventUsesLegacySchema($conn)) {
        return $row;
    }

    return [
        'eventID' => $row['eventID'],
        'eventTitle' => $row['eventTitle'],
        'eventDate' => $row['eventDate'],
        'eventTime' => $row['startTime'] ?? '',
        'eventVenue' => $row['venue'] ?? '',
        'maxParticipations' => $row['maxParticipants'] ?? null,
        'eventStatus' => $row['status'] ?? '',
        'regCount' => $row['regCount'] ?? 0,
    ];
}
