<?php
// public/api/get-profile.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Models/PatientModel.php';

$model = new PatientModel($conn);
$user_id = $_SESSION['user_id'];

// Check if profile exists
if (!$model->profileExists($user_id)) {
    $model->createProfile($user_id, $_SESSION['user_email'] ?? '', $_SESSION['user_name'] ?? '');
}

$profile = $model->getProfileByUserId($user_id);
$stats = $model->getAppointmentStats($user_id);
$history = $model->getRecentMedicalHistory($user_id);

if ($profile) {
    echo json_encode([
        'success' => true,
        'profile' => $profile,
        'stats' => $stats,
        'history' => $history
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Profile not found']);
}
?>