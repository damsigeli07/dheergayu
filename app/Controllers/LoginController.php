<?php
// app/Controllers/LoginController.php or wherever your login logic is
session_start();

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Please enter both email and password';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Query user from database
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        $_SESSION['login_error'] = 'Invalid email or password';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Invalid email or password';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Password is correct - Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['logged_in'] = true;
    
    // Clear any login errors
    unset($_SESSION['login_error']);
    
    // Redirect based on role
    switch ($user['role']) {
        case 'doctor':
            header('Location: /dheergayu/app/Views/Doctor/doctordashboard.php');
            exit;
            
        case 'staff':
            header('Location: /dheergayu/app/Views/Staff/staffdashboard.php');
            exit;
            
        case 'pharmacist':
            header('Location: /dheergayu/app/Views/Pharmacist/pharmacistdashboard.php');
            exit;
            
        case 'admin':
            header('Location: /dheergayu/app/Views/Admin/admindashboard.php');
            exit;
            
        case 'patient':
        default:
            header('Location: /dheergayu/app/Views/Patient/home.php');
            exit;
    }
} else {
    // If not POST request, redirect to login page
    header('Location: /dheergayu/app/Views/Patient/login.php');
    exit;
}
?>