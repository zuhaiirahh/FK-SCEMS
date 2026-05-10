<?php
session_start();
include 'db_connect.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['userid']); // Ini input username
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Kita join table user dengan student/administrator untuk dapatkan ID spesifik
    $query = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $uid = $user['userID'];

        // Simpan info asas dalam session
        $_SESSION['userID'] = $uid;
        $_SESSION['name'] = $user['name'];
        $_SESSION['type'] = $user['userType'];

        // SEMAK USER TYPE UNTUK REDIRECT
        if ($user['userType'] == 'student') {
            // Ambil studentID dari table student
            $sq = "SELECT studentID FROM student WHERE userID = '$uid'";
            $resq = mysqli_query($conn, $sq);
            $rowStudent = mysqli_fetch_assoc($resq);
            $_SESSION['studentID'] = $rowStudent['studentID'];

            header("Location: dashboardSwitcher.php");
        } else if ($user['userType'] == 'admin') {
            // Ambil staffID dari table administrator
            $aq = "SELECT staffID FROM administrator WHERE userID = '$uid'";
            $reaq = mysqli_query($conn, $aq);
            $rowAdmin = mysqli_fetch_assoc($reaq);
            $_SESSION['staffID'] = $rowAdmin['staffID'];

            header("Location: adminDashboard.php");
        }
        exit();
    } else {
        echo "<script>alert('Username atau Password salah!'); window.location='login.php';</script>";
    }
}
