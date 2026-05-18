<?php
session_start();

// 1. Guard Clause
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Connect to database using your file
require_once 'fkscems.php';

// 3. Process Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve form inputs
    $student_id = trim($_POST['student_id']); // Digunakan sebagai username & ID spesifik
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $role       = trim($_POST['role']); // Jangkaan nilai: 'student', 'committee', 'admin'

    // Validate form inputs are not empty
    if (!empty($student_id) && !empty($full_name) && !empty($email) && !empty($role)) {

        // Use the Student ID as their default initial password and hash it securely
        $default_password = $student_id;
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

        // Mulakan Transaction - Supaya kalau salah satu table gagal, tiada data sangkut
        mysqli_begin_transaction($conn);

        try {
            // ==========================================
            // LANGKAH 1: INSERT KE TABLE `user` DAHULU
            // ==========================================
            $sqlUser = "INSERT INTO user (username, password, name, email, userType) VALUES (?, ?, ?, ?, ?)";
            $stmtUser = mysqli_prepare($conn, $sqlUser);
            
            if (!$stmtUser) {
                throw new Exception("SQL User Error: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmtUser, "sssss", $student_id, $hashed_password, $full_name, $email, $role);
            
            if (!mysqli_stmt_execute($stmtUser)) {
                throw new Exception("Execute User Error: " . mysqli_stmt_error($stmtUser));
            }

            // Ambil userID yang baru sahaja dijana secara Auto Increment
            $newUserID = mysqli_insert_id($conn);
            mysqli_stmt_close($stmtUser);

            // ==========================================
            // LANGKAH 2: INSERT KE TABLE SPESIFIK BERDASARKAN ROLE
            // ==========================================
            if ($role === 'student') {
                // Sediakan nilai default untuk student baru
                $totalPoints = 0;
                $studentType = 'Undergraduate'; // Anda boleh tukar default ini atau ambil dari $_POST jika ada

                $sqlStudent = "INSERT INTO student (userID, studentID, totalPoints, studentType) VALUES (?, ?, ?, ?)";
                $stmtStudent = mysqli_prepare($conn, $sqlStudent);
                
                if (!$stmtStudent) throw new Exception(mysqli_error($conn));
                
                mysqli_stmt_bind_param($stmtStudent, "isis", $newUserID, $student_id, $totalPoints, $studentType);
                mysqli_stmt_execute($stmtStudent);
                mysqli_stmt_close($stmtStudent);

            } elseif ($role === 'committee') {
                // Anda boleh set nilai default atau kosongkan dahulu untuk dikemaskini kemudian
                $position = 'Member'; 
                $clubID = null; // Letak null dahulu atau masukkan ID club jika form ada input club

                $sqlCommittee = "INSERT INTO club_committee (userID, position, clubID) VALUES (?, ?, ?)";
                $stmtCommittee = mysqli_prepare($conn, $sqlCommittee);
                
                if (!$stmtCommittee) throw new Exception(mysqli_error($conn));
                
                // Menggunakan "isi" (i = int, s = string, i = int/null)
                mysqli_stmt_bind_param($stmtCommittee, "isi", $newUserID, $position, $clubID);
                mysqli_stmt_execute($stmtCommittee);
                mysqli_stmt_close($stmtCommittee);

            } elseif ($role === 'admin') {
                // Untuk administrator, biasanya memadai dengan userID sahaja atau jabatan (department)
                $department = 'FTKW'; 

                $sqlAdmin = "INSERT INTO administrator (userID, department) VALUES (?, ?)";
                $stmtAdmin = mysqli_prepare($conn, $sqlAdmin);
                
                if (!$stmtAdmin) throw new Exception(mysqli_error($conn));
                
                mysqli_stmt_bind_param($stmtAdmin, "is", $newUserID, $department);
                mysqli_stmt_execute($stmtAdmin);
                mysqli_stmt_close($stmtAdmin);
            }

            // Jika semua proses di atas tiada error, simpan data ke database secara kekal
            mysqli_commit($conn);

            // Success redirect back to the dashboard layout
            header("Location: adminDashboard.php?insert=success");
            exit();

        } catch (Exception $e) {
            // Jika ada apa-apa error berlaku, batalkan semua kemasukan data di atas serta-merta
            mysqli_rollback($conn);
            echo "Gagal mendaftarkan pengguna: " . $e->getMessage();
        }

    } else {
        echo "Please completely fill out all fields in the setup form.";
    }
}

// Close connection
mysqli_close($conn);
?>