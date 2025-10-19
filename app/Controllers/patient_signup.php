<?php
// Include database connection
require_once(__DIR__ . "/../../config/config.php");

// Debug: Log that the script is being executed
error_log("Patient signup script executed - Method: " . $_SERVER["REQUEST_METHOD"]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST request received for patient signup");
    
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $dob        = $_POST['dob'];
    $nic        = trim($_POST['nic']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm_pw = $_POST['confirm_password'];
    
    // Validate Date of Birth
    $dob_date = new DateTime($dob);
    $dob_year = (int)$dob_date->format('Y');
    
    if ($dob_year < 1925 || $dob_year > 2007) {
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=invalid_dob");
        exit();
    }
    
    // Validate passwords match
    if ($password !== $confirm_pw) {
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=password_mismatch");
        exit();
    }
    
    // Password strength validation removed for testing
    
    // Check if NIC or email already exists
    $stmt = $conn->prepare("SELECT id FROM patients WHERE email=? OR nic=?");
    $stmt->bind_param("ss", $email, $nic);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=already_exists");
        exit();
    }
    $stmt->close();
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=invalid_email");
        exit();
    }
    
    // Hash password
    $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new patient
    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, dob, nic, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $last_name, $dob, $nic, $email, $hashed_pw);
    
    error_log("Attempting to insert patient: " . $first_name . " " . $last_name . " - Email: " . $email);
    
    if ($stmt->execute()) {
        error_log("Patient inserted successfully");
        $stmt->close();
        $conn->close();
        echo "<script>alert('Patient added successfully!'); window.location.href='/dheergayu/app/Views/Patient/signup.php?success=signup_complete';</script>";
        exit();
    } else {
        error_log("Database error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo "<script>alert('Database error: " . addslashes($stmt->error) . "'); window.location.href='/dheergayu/app/Views/Patient/signup.php?error=database_error';</script>";
        exit();
    }
    
} else {
    header("Location: /dheergayu/app/Views/Patient/signup.php");
    exit();
}
?>