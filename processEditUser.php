<?php
session_start();
include 'fkscems.php';

// Sekatan keselamatan admin
if (!isset($_SESSION['userID']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Semak jika borang dihantar melalui kaedah POST
if (isset($_POST['update_user'])) {

    // Ambil data dari borang dan bersihkan daripada risiko SQL Injection
    $old_userID = mysqli_real_escape_string($conn, $_POST['old_userID']);
    $new_userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $userType    = mysqli_real_escape_string($conn, $_POST['userType']);

    // Jalankan arahan SQL UPDATE untuk mengubah data di pangkalan data
    $updateQuery = "UPDATE user SET 
                    userID = '$new_userID', 
                    name = '$name', 
                    userType = '$userType' 
                    WHERE userID = '$old_userID'";

    if (mysqli_query($conn, $updateQuery)) {

        // Jika admin edit ID miliknya sendiri yang sedang aktif, kemas kini sesi (session) semasa juga
        if ($old_userID === $_SESSION['userID']) {
            $_SESSION['userID'] = $new_userID;
            $_SESSION['name'] = $name;
            $_SESSION['type'] = $userType;
        }

        // Papar alert popup berjaya dan bawa semula ke senarai user control
        echo "<script>
                alert('Maklumat pengguna berjaya dikemas kini dalam pangkalan data!');
                window.location.href = 'adminUserControl.php';
              </script>";
        exit();
    } else {
        // Jika terdapat ralat (contoh: ID baharu bertembung dengan ID orang lain)
        echo "<script>
                alert('Gagal mengemas kini data: " . mysqli_error($conn) . "');
                window.location.href = 'adminUserControl.php';
              </script>";
        exit();
    }
} else {
    header("Location: adminUserControl.php");
    exit();
}
