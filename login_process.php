<?php
session_start();
include 'fkscems.php';

if (isset($_POST['login'])) {
    // 1. Ambil input dan buang ruang kosong
    $username = mysqli_real_escape_string($conn, trim($_POST['userid']));
    $password_input = trim($_POST['password']);

    // 2. Cari user
    $query = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // 3. Bandingkan password input dengan hash dalam DB
        if (password_verify($password_input, $user['password'])) {

            // Set session
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['name']   = $user['name'];
            $_SESSION['type']   = $user['userType'];

            // 4. Redirect mengikut User Type
            if ($user['userType'] == 'admin') {
                header("Location: adminDashboard.php");
            } elseif ($user['userType'] == 'committee') {
                // Ke switcher seperti yang diminta
                header("Location: dashboardSwitcher.php");
            } else {
                header("Location: studentDashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid Password!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found!'); window.location='login.php';</script>";
    }
}
