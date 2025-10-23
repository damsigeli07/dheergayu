<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Models/AppointmentModel.php';

$model = new AppointmentModel($conn);
$user_id = $_SESSION['user_id'];
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';

if (!$date || !$time) {
    echo json_encode(['success' => false, 'error' => 'Missing date or time']);
    exit;
}

$success = $model->lockSlot($date, $time, $user_id);

echo json_encode(['success' => $success]);
?>