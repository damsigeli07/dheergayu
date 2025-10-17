<?php
// Include database connection
require_once(__DIR__ . "/../../config/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $dob        = $_POST['dob'];
    $nic        = trim($_POST['nic']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm_pw = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_pw) {
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=password_mismatch");
        exit();
    }

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

    // Hash password
    $hashed_pw = password_hash($password, PASSWORD_DEFAULT);

    // Insert new patient
    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, dob, nic, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $last_name, $dob, $nic, $email, $hashed_pw);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: /dheergayu/app/Views/Patient/signup.php?success=signup_complete");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: /dheergayu/app/Views/Patient/signup.php?error=database_error");
        exit();
    }

} else {
    header("Location: /dheergayu/app/Views/Patient/signup.php");
    exit();
}
?>
