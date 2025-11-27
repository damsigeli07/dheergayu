<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/patienthistoryModel.php';

$model = new patienthistoryModel($conn);

// Read JSON input from fetch
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

$patient_no = $input['patient_no'] ?? "";
$patient_name = $input['patient_name'] ?? "";
$dob = $input['dob'] ?? ""; // send dob from JS instead of birthday

// Search patients in consultation table
$patients = $model->searchPatients($patient_no, $patient_name, $dob);

$response = [];

if (!empty($patients) && isset($patients[0]['patient_no'])) {
    $patient = $patients[0];
    $history = $model->getConsultationFormHistory($patient['patient_no']);

    $response = [
        'success' => true,
        'patient' => $patient,
        'history' => $history
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'No patients found.'
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
