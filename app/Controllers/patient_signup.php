<?php
// app/Controllers/patient_signup.php

require_once(__DIR__ . "/../../config/config.php");

// Debug: Log that the script is being executed
error_log("Patient signup script executed - Method: " . $_SERVER["REQUEST_METHOD"]);
require_once(__DIR__ . "/../Models/PatientModel.php");

    
    // Validate passwords match
    if ($password !== $confirm_pw) {
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=password_mismatch");
        exit();
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=weak_password");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=invalid_email");
        exit();
    }
    
    // Check if NIC or email already exists in patients table
    $stmt = $conn->prepare("SELECT id FROM patients WHERE email = ? OR nic = ?");
    if (!$stmt) {
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=database_error");
        exit();
    }
    
    $stmt->bind_param("ss", $email, $nic);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=already_exists");
        exit();
    }
    $stmt->close();
    
    // Hash password
    $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new patient into patients table
    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, dob, nic, email, password) VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssss", $first_name, $last_name, $dob, $nic, $email, $hashed_pw);
    
    if ($stmt->execute()) {
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        error_log("Patient signup database error: " . $error);
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=database_error");
        exit();
    }
    
} else {
    header("Location: /dheergayu/app/Views/Patient/signup.php");
    exit();
}
?>