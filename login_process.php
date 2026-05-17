<?php
session_start();
include 'fkscems.php';

if (!isset($_POST['login'])) {
    header('Location: login.php');
    exit();
}

$username = trim($_POST['userid'] ?? '');
$passwordInput = trim($_POST['password'] ?? '');

if ($username === '' || $passwordInput === '') {
    $_SESSION['login_error'] = 'Please enter your User ID and password.';
    header('Location: login.php');
    exit();
}

$stmt = mysqli_prepare($conn, 'SELECT userID, username, password, name, userType FROM user WHERE username = ? LIMIT 1');

if (!$stmt) {
    $_SESSION['login_error'] = 'Database error. Please import fkscems.sql in phpMyAdmin.';
    header('Location: login.php');
    exit();
}

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    $_SESSION['login_error'] = 'User not found. Please check your User ID or contact the administrator.';
    header('Location: login.php');
    exit();
}

$storedPassword = (string) $user['password'];
$passwordOk = password_verify($passwordInput, $storedPassword);

// Support legacy plain-text passwords and upgrade them to bcrypt
if (!$passwordOk && hash_equals($storedPassword, $passwordInput)) {
    $passwordOk = true;
    $newHash = password_hash($passwordInput, PASSWORD_DEFAULT);
    $update = mysqli_prepare($conn, 'UPDATE user SET password = ? WHERE userID = ?');
    if ($update) {
        mysqli_stmt_bind_param($update, 'si', $newHash, $user['userID']);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
    }
}

if (!$passwordOk) {
    $_SESSION['login_error'] = 'Invalid password.';
    header('Location: login.php');
    exit();
}

$userType = strtolower(trim((string) $user['userType']));

$_SESSION['userID'] = (int) $user['userID'];
$_SESSION['name'] = $user['name'];
$_SESSION['type'] = $userType;

if ($userType === 'admin') {
    header('Location: adminDashboard.php');
} elseif ($userType === 'committee') {
    header('Location: createEvent.php');
} else {
    header('Location: studentDashboard.php');
}
exit();
