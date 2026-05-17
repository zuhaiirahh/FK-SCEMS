<?php

function clubDisplayName(string $category, ?string $clubName = null): string
{
    if ($clubName !== null && $clubName !== '') {
        return $clubName;
    }
    $map = [
        'Academic' => 'Computer Club',
        'Workshop' => 'Computer Club',
        'Seminar' => 'Computer Club',
        'Sports' => 'Sports Club',
        'Social' => 'Leadership Club',
        'Competition' => 'Sports Club',
        'Culture' => 'Arts & Culture',
    ];
    return $map[$category] ?? ($category !== '' ? $category . ' Club' : 'Student Club');
}

function clubIcon(string $category): string
{
    $map = [
        'Sports' => 'sports_soccer',
        'Competition' => 'sports_soccer',
        'Social' => 'groups',
        'Culture' => 'palette',
    ];
    return $map[$category] ?? 'shield';
}

function meritValue(string $category): int
{
    $map = [
        'Sports' => 3,
        'Competition' => 3,
        'Seminar' => 2,
        'Workshop' => 2,
        'Academic' => 2,
        'Social' => 2,
        'Culture' => 2,
    ];
    return $map[$category] ?? 2;
}

function eventPosterUrl(array $event): string
{
    if (!empty($event['eventPoster']) && is_file(__DIR__ . '/../' . $event['eventPoster'])) {
        return $event['eventPoster'];
    }

    $cat = $event['eventCategory'] ?? '';
    $defaults = [
        'Sports' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCd-7Q1JBXw_vjS5s-B6Y46w2od-Cv9kFfP5jHnQ1z3iz-sy9vHSFDkO4QKs98yZXJN41FkS8E8NfV4JcZBJ6L1NNhwlII5MCLlJYv-4AZJ5brlpu8Cc38gpHNG0LMBFIyvKH5IfqUQ2p29wPKwyHxUjgeUhn-f3B-8rhjyNw3yq6ZKCRlzEwtjP6edRO_VqiJ0URR737C_5IDgxHcFze74P3c8IJsTKXrH4UEyShlyBJ9V2vmRKvykLbmk8tII9OtkqpxsWJOv2ok',
        'Social' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBnvyIs_37l0-fLdM7JjsN0N7waihFZr2w38Kz_-vDCIWx_fzC9JH4CN5xm0Umy62jFxPmGghCK782_dgp_Dh0hYNaUME29WuMwLoQtHSD4kNcg0r4oq0n4let8uYLQGdTU3ganQjqa_DYUDFmaG4WbzWlFqTrud3C4JN69LE-k1ubivddCpKv33j7jxfrMNyjdleckxwKdvYg0xP7llWFGaXURg67J8KFc7Kw3ztgrAFuNLMO4kOv24fiKa-5BX0L3P7F7fxQkRHk',
    ];

    if (isset($defaults[$cat])) {
        return $defaults[$cat];
    }
    if (stripos($cat, 'sport') !== false) {
        return $defaults['Sports'];
    }

    return 'https://lh3.googleusercontent.com/aida-public/AB6AXuAeQCVphh9NvajSWwnqVCLnJ2Tvh6-nRNXV8wwAaP8JRQYyBwmTECczZBAgwXoVGvp32sEjEYzf40ATJ9DYtMJbuzGEu7JCG-_72288o1BGcfIwfPnM8GXkSZSyzmh5OiTCWUQTgfUj5W42XCBd-p_TrrjxgO_LOhp6SGDy4e64JiOLfwbgs9hOMRxniNPsLsKmQlkEjWYrw-HdVSwq2EfNYa0tQjY6jrQBuRbp2EXlv3cTYJhIdBqVwwZH6rBeZiGea951077bvqc';
}

function formatEventDate(string $date): string
{
    $ts = strtotime($date);
    return date('d M Y', $ts) . ' (' . date('l', $ts) . ')';
}

function formatEventTime(string $time): string
{
    return date('g:i A', strtotime($time));
}

function browseQueryUrl(array $overrides = []): string
{
    $params = array_merge([
        'search' => $_GET['search'] ?? '',
        'club' => $_GET['club'] ?? 'all',
        'page' => $_GET['page'] ?? 1,
    ], $overrides);

    if (($params['search'] ?? '') === '') {
        unset($params['search']);
    }
    if (($params['club'] ?? 'all') === 'all') {
        unset($params['club']);
    }
    if (($params['page'] ?? 1) == 1) {
        unset($params['page']);
    }

    $query = http_build_query($params);
    return 'browseEvents.php' . ($query ? '?' . $query : '');
}

function availableSlotClass(int $regCount, ?int $maxSlots): string
{
    if ($maxSlots === null || $maxSlots <= 0) {
        return 'font-bold text-on-surface';
    }
    $available = max(0, $maxSlots - $regCount);
    return ($available / $maxSlots) <= 0.25 ? 'font-bold text-error' : 'font-bold text-on-surface';
}
