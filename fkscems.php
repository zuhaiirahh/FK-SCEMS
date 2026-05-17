<?php
/**
 * Database connection for FK-SCEMS.
 * PHP 8+ throws mysqli_sql_exception on failure — we catch that and show clear instructions.
 */

mysqli_report(MYSQLI_REPORT_OFF);

$dbUser = 'root';
$dbPass = '';
$dbName = 'fkscems';
$dbHost = '127.0.0.1';
$dbPorts = [3306, 3307]; // 3306 = usual XAMPP default; 3307 = alternate

$conn = null;
$lastError = '';

foreach ($dbPorts as $port) {
    try {
        $attempt = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $port);
        if ($attempt) {
            $conn = $attempt;
            break;
        }
        $lastError = mysqli_connect_error();
    } catch (mysqli_sql_exception $e) {
        $lastError = $e->getMessage();
    }
}

if (!$conn) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    $detail = htmlspecialchars($lastError ?: 'MySQL is not running or not accepting connections.');
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database unavailable - FK-SCEMS</title>
    <style>
        body { font-family: Segoe UI, sans-serif; background: #f4f7f6; margin: 0; padding: 40px 20px; }
        .box { max-width: 520px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,.08); border: 1px solid #eee; }
        h1 { color: #c0392b; font-size: 1.25rem; margin: 0 0 12px; }
        p { color: #444; line-height: 1.6; font-size: 0.95rem; }
        ol { padding-left: 20px; color: #333; }
        li { margin-bottom: 8px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 4px; }
        .detail { margin-top: 16px; padding: 12px; background: #fdecea; border-radius: 8px; font-size: 0.85rem; color: #922; }
        a { color: #008b8b; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Cannot connect to MySQL</h1>
        <p>The login page needs the database. MySQL is not running or refused the connection.</p>
        <ol>
            <li>Open <strong>XAMPP Control Panel</strong></li>
            <li>Click <strong>Start</strong> next to <strong>MySQL</strong> (wait until it shows green)</li>
            <li>If MySQL will not start, check if port 3306 is in use by another program</li>
            <li>In phpMyAdmin, import <code>setup.sql</code> from your project folder</li>
            <li>Try again: <a href="login.php">login.php</a></li>
        </ol>
        <div class="detail"><strong>Technical detail:</strong> {$detail}</div>
    </div>
</body>
</html>
HTML;
    exit;
}

mysqli_set_charset($conn, 'utf8mb4');
