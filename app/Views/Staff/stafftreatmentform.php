<?php
require_once __DIR__ . '/../../../core/bootloader.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../patient/login.php');
    exit;
}

// Check if user is staff
$user_role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? $_SESSION['role'] ?? '');
if ($user_role !== 'staff') {
    header('Location: ../patient/login.php');
    exit;
}

// Handle POST request for saving treatment form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../Controllers/StaffTreatmentFormController.php';
    // The controller will handle the response and exit
}

// Handle GET request - Display the form
$plan_id = $_GET['plan_id'] ?? '';
$view_mode = isset($_GET['view']) && $_GET['view'] == '1';
$treatment_plan = null;
$consultation_form = null;
$patient_data = null;
$doctor_data = null;
$existing_form = null;

if ($plan_id) {
    $db = \Core\Database::connect();
    
    // Fetch treatment plan data
    $stmt = $db->prepare("
        SELECT 
            tp.*,
            p.first_name,
            p.last_name,
            c.age,
            c.gender,
            p.email,
            tl.treatment_name,
            c.id as appointment_id,
            c.doctor_id,
            c.doctor_name,
            c.notes as consultation_notes
        FROM treatment_plans tp
        LEFT JOIN patients p ON tp.patient_id = p.id
        LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
        LEFT JOIN consultations c ON tp.appointment_id = c.id
        WHERE tp.plan_id = ?
    ");

    if (!$stmt) {
        die('Database error while preparing treatment plan query: ' . $db->error);
    }

    $stmt->bind_param('i', $plan_id);
    $stmt->execute();
    $treatment_plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$treatment_plan) {
        header('Location: stafftreatment.php');
        exit;
    }
    
    // Fetch consultation form data if appointment_id exists
    if ($treatment_plan['appointment_id']) {
        require_once __DIR__ . '/../../Models/ConsultationFormModel.php';
        $cfModel = new ConsultationFormModel($db);
        $consultation_form = $cfModel->getConsultationFormByAppointmentId($treatment_plan['appointment_id']);
    }
    
    // Fetch existing staff treatment form if exists
    $staff_id = $_SESSION['user_id'] ?? null;
    if ($staff_id) {
        $stmt = $db->prepare("SELECT * FROM staff_treatment_forms WHERE plan_id = ? AND staff_id = ? LIMIT 1");
        $stmt->bind_param('ii', $plan_id, $staff_id);
        $stmt->execute();
        $existing_form = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    
    // If viewing existing form, use its data
    if ($view_mode && $existing_form) {
        // Data will be used in the form
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Treatment Form</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/stafftreatmentform.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>STAFF TREATMENT FORM</h1>
        </header>
        <hr class="title-divider" />

        <div class="form-container">
            <form id="treatmentForm" method="POST">
                <input type="hidden" name="plan_id" value="<?= htmlspecialchars($plan_id) ?>">
                <input type="hidden" name="view_mode" value="<?= $view_mode ? '1' : '0' ?>">
                
                <div class="main-content">
                    <div class="left-section">
                        <h2>Treatment Form</h2>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label>First Name (Patient's)</label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($treatment_plan['first_name'] ?? '') ?>" readonly>
                            </div>
                            <div class="form-group half">
                                <label>Last Name (Patient's)</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($treatment_plan['last_name'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group half">
                                <label>Age (Patient's)</label>
                                <input type="number" name="age" value="<?= htmlspecialchars($treatment_plan['age'] ?? '') ?>" readonly>
                            </div>
                            <div class="form-group half">
                                <label>Gender (Patient's)</label>
                                <input type="text" name="gender" value="<?= htmlspecialchars($treatment_plan['gender'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Prescribed Treatment Name</label>
                            <input type="text" name="treatment_name" value="<?= htmlspecialchars($treatment_plan['treatment_name'] ?? '') ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Assigned Doctor Name</label>
                            <input type="text" name="doctor_name" value="<?= htmlspecialchars($treatment_plan['doctor_name'] ?? 'N/A') ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Doctor's Notes</label>
                            <textarea name="doctor_notes" readonly><?= htmlspecialchars($consultation_form['notes'] ?? $treatment_plan['consultation_notes'] ?? 'No notes available') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Therapist Name<?= $view_mode ? '' : ' *' ?></label>
                            <input type="text" name="therapist_name" value="<?= htmlspecialchars($existing_form['therapist_name'] ?? $_SESSION['user_name'] ?? '') ?>" <?= $view_mode ? 'readonly' : 'required' ?>>
                        </div>

                        <div class="form-group">
                            <label>Notes *</label>
                            <textarea name="notes" required><?= htmlspecialchars($existing_form['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="button" class="btn btn-back" onclick="window.location.href='stafftreatment.php'">Back</button>
                    <?php if (!$view_mode): ?>
                        <button type="submit" name="save_type" value="save" class="btn btn-secondary">Save</button>
                    <?php else: ?>
                        <button type="submit" name="save_type" value="update" class="btn btn-secondary">Update Notes</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-tertiary" onclick="window.print()">Print</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form submission
        document.getElementById('treatmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const viewMode = document.querySelector('input[name="view_mode"]').value === '1';
            const therapistName = document.querySelector('input[name="therapist_name"]').value.trim();
            const notes = document.querySelector('textarea[name="notes"]').value.trim();
            
            if (!viewMode && !therapistName) {
                alert('Please fill in Therapist Name');
                return;
            }
            
            if (!notes) {
                alert('Please fill in Notes');
                return;
            }
            
            const formData = new FormData(this);
            const submitButton = e.submitter;
            
            const url = '/dheergayu/app/Controllers/StaffTreatmentFormController.php';
            
            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Treatment form saved successfully!');
                    window.location.href = 'stafftreatment.php';
                } else {
                    alert('Error: ' + (data.message || 'Failed to save'));
                    submitButton.disabled = false;
                    submitButton.textContent = submitButton.value === 'update' ? 'Update Notes' : 'Save';
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                alert('Error saving treatment form: ' + err.message);
                submitButton.disabled = false;
                submitButton.textContent = submitButton.value === 'update' ? 'Update Notes' : 'Save';
            });
        });
    </script>
</body>
</html>
