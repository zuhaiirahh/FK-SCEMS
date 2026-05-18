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
