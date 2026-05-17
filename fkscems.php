<?php
$username = 'root';
$password = '';
$dbname = 'fkscems';

// Try port 3307 first (XAMPP default in this project), then 3306
$conn = @mysqli_connect('127.0.0.1', $username, $password, $dbname, 3307);
if (!$conn) {
    $conn = @mysqli_connect('127.0.0.1', $username, $password, $dbname, 3306);
}
if (!$conn) {
    $conn = @mysqli_connect('localhost', $username, $password, $dbname, 3307);
}
if (!$conn) {
    $conn = @mysqli_connect('localhost', $username, $password, $dbname, 3306);
}

if (!$conn) {
    die('Connection failed. Start MySQL in XAMPP, then import fkscems.sql or setup.sql in phpMyAdmin.');
}

mysqli_set_charset($conn, 'utf8mb4');
