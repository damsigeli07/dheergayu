<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$_role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? '');
if (empty($_SESSION['logged_in']) || ($_role !== 'doctor' && $_role !== 'staff' && $_role !== 'admin')) {
    header('Location: /dheergayu/app/Views/Patient/login.php'); exit;
}