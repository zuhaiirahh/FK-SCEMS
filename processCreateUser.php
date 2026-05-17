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
    $student_id = trim($_POST['student_id']);
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $role       = trim($_POST['role']); // Values: student, committee, admin

    // Validate form inputs are not empty
    if (!empty($student_id) && !empty($full_name) && !empty($email) && !empty($role)) {

        // Use the Student ID as their default initial password and hash it securely
        $default_password = $student_id;
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

        // 4. SQL prepared statement using your exact column names
        // NOTE: Change 'users' to your exact table name if it is named differently
        $sql = "INSERT INTO user (username, password, name, email, userType) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {

            // "sssss" binds 5 string values to the query placeholder parameters
            mysqli_stmt_bind_param($stmt, "sssss", $student_id, $hashed_password, $full_name, $email, $role);

            // 5. Execute statement
            if (mysqli_stmt_execute($stmt)) {
                // Success redirect back to the dashboard layout
                header("Location: adminDashboard.php?insert=success");
                exit();
            } else {
                echo "Database Error: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "SQL Statement Error: " . mysqli_error($conn);
        }
    } else {
        echo "Please completely fill out all fields in the setup form.";
    }
}

// Close connection
mysqli_close($conn);
