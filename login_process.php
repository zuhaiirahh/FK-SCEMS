<?php
session_start();
include 'fkscems.php';

if (isset($_POST['login'])) {
    // 1. Ambil input dan buang ruang kosong (trim)
    $username = mysqli_real_escape_string($conn, trim($_POST['userid']));
    $password_input = trim($_POST['password']);

    // 2. Cari user berdasarkan username
    $query = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // 3. Sahkan password menggunakan password_verify (Kriteria Excellent Rubrik)
        if (password_verify($password_input, $user['password'])) {

            // Set Session Data
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['name']   = $user['name'];
            $_SESSION['type']   = $user['userType'];

            // 4. Redirect mengikut userType
            if ($user['userType'] == 'admin') {
                header("Location: adminDashboard.php");
            } elseif ($user['userType'] == 'committee') {
                header("Location: committeeDashboard.php");
            } else {
                header("Location: studentDashboard.php");
            }
            exit();
        } else {
            // Jika password salah
            echo "<script>alert('Invalid Password!'); window.location='login.php';</script>";
        }
    } else {
        // Jika username tidak wujud
        echo "<script>alert('User not found!'); window.location='login.php';</script>";
    }
}
