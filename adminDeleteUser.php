<?php
session_start();
include 'fkscems.php'; // Menyambung ke pangkalan data mengikut port masing-masing

// 1. Sekatan Keselamatan: Pastikan hanya admin sahaja boleh akses fail proses padam ini
if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Semak jika parameter ID dihantar melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {

    // Ambil ID dan bersihkan daripada risiko SQL Injection
    $userIDToDelete = mysqli_real_escape_string($conn, $_GET['id']);

    // Elakkan admin daripada terpadam akaun diri sendiri secara tidak sengaja
    if ($userIDToDelete === $_SESSION['userID']) {
        echo "<script>
                alert('Ralat: Anda tidak boleh memadam akaun anda sendiri yang sedang digunakan!');
                window.location.href = 'adminUserControl.php';
              </script>";
        exit();
    }

    // 3. Jalankan arahan SQL untuk memadam rekod daripada jadual 'user'
    $deleteQuery = "DELETE FROM user WHERE userID = '$userIDToDelete'";

    if (mysqli_query($conn, $deleteQuery)) {
        // Jika berjaya padam di database, keluar popup berjaya dan bawa semula ke senarai user
        echo "<script>
                alert('Pengguna berjaya dipadamkan daripada sistem secara kekal!');
                window.location.href = 'adminUserControl.php';
              </script>";
        exit();
    } else {
        // Jika terdapat ralat pangkalan data (contohnya kekangan Foreign Key)
        echo "<script>
                alert('Gagal memadam pengguna: " . mysqli_error($conn) . "');
                window.location.href = 'adminUserControl.php';
              </script>";
        exit();
    }
} else {
    // Jika fail ini diakses secara terus tanpa membawa sebarang ID melalui URL
    header("Location: adminUserControl.php");
    exit();
}
