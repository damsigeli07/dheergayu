<?php
require_once __DIR__ . '/../../includes/auth_staff.php';
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

    $staff_id = $_SESSION['user_id'] ?? null;
    if ($staff_id) {
        $stmt = $db->prepare("SELECT * FROM staff_treatment_forms WHERE plan_id = ? AND staff_id = ? LIMIT 1");
        $stmt->bind_param('ii', $plan_id, $staff_id);
        $stmt->execute();
        $existing_form = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    $tpPay = ($treatment_plan['payment_status'] ?? '') === 'Completed';
    $tpStatus = $treatment_plan['status'] ?? '';
    $tpChange = !empty($treatment_plan['change_requested']);
    $tpConfirmed = in_array($tpStatus, ['Confirmed', 'InProgress', 'Completed'], true);
    $tpAssignedId = (int)($treatment_plan['assigned_staff_id'] ?? 0);
    $assignedToMe = ($staff_id && $tpAssignedId !== 0 && $tpAssignedId === (int)$staff_id);

    // Other staff should not open/submit this plan (only assigned staff can).
    if (!$assignedToMe) {
        echo "<script>alert('This treatment is selected by another staff.'); window.location.href='stafftreatment.php';</script>";
        exit;
    }

    $allowViewSaved = $view_mode && !empty($existing_form);
    if (!$allowViewSaved && (!$tpPay || !$tpConfirmed)) {
        $msg = 'Cannot open treatment — patient must complete payment and confirm the treatment plan.';
        if (!$tpPay) {
            $msg = 'Cannot start treatment — patient has not completed payment yet.';
        } elseif (!$tpConfirmed) {
            $msg = 'Cannot start treatment — patient has not confirmed the plan yet.';
        }
        echo "<script>alert(" . json_encode($msg) . "); window.location.href='stafftreatment.php';</script>";
        exit;
    }

    // Fetch consultation form data if appointment_id exists
    if ($treatment_plan['appointment_id']) {
        require_once __DIR__ . '/../../Models/ConsultationFormModel.php';
        $cfModel = new ConsultationFormModel($db);
        $consultation_form = $cfModel->getConsultationFormByAppointmentId($treatment_plan['appointment_id']);
    }

// Ensure session notes table exists
@$db->query("CREATE TABLE IF NOT EXISTS staff_treatment_session_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    staff_id INT NOT NULL,
    session_number INT NOT NULL,
    session_note LONGTEXT,
    is_completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_plan_staff_session (plan_id, staff_id, session_number)
)");

// Fetch session dates/times from treatment_sessions table
$session_meta = [];
$sm_stmt = $db->prepare("SELECT session_number, session_date, session_time, status FROM treatment_sessions WHERE plan_id = ? ORDER BY session_number ASC");
if ($sm_stmt) {
    $sm_stmt->bind_param('i', $plan_id);
    $sm_stmt->execute();
    $sm_result = $sm_stmt->get_result();
    while ($s = $sm_result->fetch_assoc()) {
        $session_meta[intval($s['session_number'])] = $s;
    }
    $sm_stmt->close();
}

// Get total sessions from actual session records
$total_sessions = max(1, count($session_meta));

// Min date for new session = day after the latest existing session date
$last_session_date = null;
foreach ($session_meta as $sm) {
    if (!empty($sm['session_date'])) {
        if ($last_session_date === null || $sm['session_date'] > $last_session_date) {
            $last_session_date = $sm['session_date'];
        }
    }
}
$new_session_min_date = $last_session_date
    ? date('Y-m-d', strtotime($last_session_date . ' +1 day'))
    : date('Y-m-d');

// Fetch saved session notes
$session_notes_map = [];
if ($staff_id) {
    $sn_stmt = $db->prepare("SELECT session_number, session_note, is_completed FROM staff_treatment_session_notes WHERE plan_id = ? AND staff_id = ?");
    if ($sn_stmt) {
        $sn_stmt->bind_param('ii', $plan_id, $staff_id);
        $sn_stmt->execute();
        $sn_result = $sn_stmt->get_result();
        while ($n = $sn_result->fetch_assoc()) {
            $session_notes_map[intval($n['session_number'])] = $n;
        }
        $sn_stmt->close();
    }
}

// Build session rows array
$session_rows = [];
for ($i = 1; $i <= $total_sessions; $i++) {
    $meta = $session_meta[$i] ?? null;
    $session_rows[] = [
        'session_number' => $i,
        'session_date'   => $meta['session_date'] ?? '',
        'session_time'   => $meta['session_time'] ?? '',
        'status'         => $meta['status'] ?? 'Pending',
        'session_note'   => $session_notes_map[$i]['session_note'] ?? '',
        'is_completed'   => isset($session_notes_map[$i])
                                ? intval($session_notes_map[$i]['is_completed']) === 1
                                : false,
    ];
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
                            <input type="text" name="therapist_name" 
                            value="<?= htmlspecialchars($existing_form['therapist_name'] ?? $_SESSION['user_name'] ?? '') ?>" 
                            readonly>
                        </div>

                        <!-- Session Progress -->
<div class="form-group">
    <label>Session Progress</label>
    <div class="session-table-wrapper">
        <table class="session-table">
            <thead>
                <tr>
                    <th>Session</th>
                    <th>Planned Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Completed</th>
                    <th>Session Note</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($session_rows as $row): ?>
                <tr>
                    <td class="session-number">Session <?= intval($row['session_number']) ?></td>
                    <td><?= htmlspecialchars($row['session_date'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['session_time'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td style="text-align:center;">
                        <input type="checkbox"
                            name="session_completed[<?= intval($row['session_number']) ?>]"
                            value="1"
                            <?= $row['is_completed'] ? 'checked' : '' ?>>
                    </td>
                    <td>
                        <textarea
                            name="session_notes[<?= intval($row['session_number']) ?>]"
                            placeholder="Note for session <?= intval($row['session_number']) ?>..."
                        ><?= htmlspecialchars($row['session_note']) ?></textarea>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
                    <!-- Add New Session -->
                    <div class="form-group" style="margin-top:20px;padding:16px;background:#f0f7f4;border-radius:8px;border:1px solid #c3e6cb;">
                        <label style="font-weight:600;color:#2d6a4f;font-size:15px;">Add New Session for Patient</label>
                        <p style="font-size:13px;color:#555;margin:4px 0 12px;">Schedule an additional session — patient will be asked to confirm and pay.</p>
                        <div style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                            <div>
                                <label style="font-size:12px;color:#666;display:block;margin-bottom:4px;">
                                    Date <span style="color:#999;">(after <?= date('M d, Y', strtotime($new_session_min_date)) ?>)</span>
                                </label>
                                <input type="date" id="newSessionDate" min="<?= $new_session_min_date ?>"
                                    style="padding:8px 10px;border:1px solid #ccc;border-radius:6px;"
                                    onchange="loadStaffSessionSlots()">
                            </div>
                            <div>
                                <label style="font-size:12px;color:#666;display:block;margin-bottom:4px;">Available Slots</label>
                                <div id="staffSlotContainer" style="min-width:200px;">
                                    <span style="font-size:12px;color:#999;">Select a date first</span>
                                </div>
                                <input type="hidden" id="newSessionTime">
                            </div>
                        </div>
                        <div style="margin-top:12px;">
                            <button type="button" id="addSessionBtn" onclick="addNewSession()"
                                style="padding:9px 20px;background:#28a745;color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;">
                                Save
                            </button>
                        </div>
                        <p id="addSessionMsg" style="font-size:13px;margin-top:10px;min-height:18px;"></p>
                    </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="button" class="btn btn-back" onclick="window.location.href='stafftreatment.php'">Back</button>
                    <button type="submit" name="save_type" value="update" class="btn btn-secondary">Save Session Notes</button>
                    <button type="button" class="btn btn-tertiary" onclick="window.print()">Print</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const STAFF_TREATMENT_ID  = <?= intval($treatment_plan['treatment_id'] ?? 0) ?>;
        const NEW_SESSION_MIN_DATE = '<?= $new_session_min_date ?>';

        function loadStaffSessionSlots() {
            const date = document.getElementById('newSessionDate').value;
            const container = document.getElementById('staffSlotContainer');
            document.getElementById('newSessionTime').value = '';
            if (!date) { container.innerHTML = '<span style="font-size:12px;color:#999;">Select a date first</span>'; return; }
            container.innerHTML = '<span style="font-size:12px;color:#999;">Loading slots…</span>';
            fetch('/dheergayu/public/api/treatment-slot-availability.php?treatment_id=' + STAFF_TREATMENT_ID + '&date=' + date)
                .then(r => r.json())
                .then(data => {
                    const slots = data.slots || [];
                    if (slots.length === 0) {
                        container.innerHTML = '<span style="font-size:12px;color:#c0392b;">No slots available for this date</span>';
                        return;
                    }
                    container.innerHTML = '';
                    slots.forEach(s => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        const label = formatTime12h(s.slot_time);
                        btn.textContent = label;
                        if (s.booked) {
                            btn.disabled = true;
                            btn.style.cssText = 'padding:6px 12px;margin:3px 4px 3px 0;border-radius:6px;border:1px dashed #ccc;background:#f5f5f5;color:#aaa;cursor:not-allowed;font-size:13px;';
                            btn.title = 'Already booked';
                        } else {
                            btn.style.cssText = 'padding:6px 12px;margin:3px 4px 3px 0;border-radius:6px;border:1px solid #28a745;background:#fff;color:#28a745;cursor:pointer;font-size:13px;font-weight:600;';
                            btn.onclick = function() {
                                document.querySelectorAll('#staffSlotContainer button').forEach(b => {
                                    b.style.background = '#fff'; b.style.color = '#28a745';
                                });
                                btn.style.background = '#28a745'; btn.style.color = '#fff';
                                document.getElementById('newSessionTime').value = s.slot_time;
                            };
                        }
                        container.appendChild(btn);
                    });
                })
                .catch(() => { container.innerHTML = '<span style="font-size:12px;color:#c0392b;">Failed to load slots</span>'; });
        }

        function formatTime12h(t) {
            const [h, m] = t.split(':').map(Number);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return h12 + ':' + String(m).padStart(2, '0') + ' ' + ampm;
        }

        function addNewSession() {
            const date = document.getElementById('newSessionDate').value;
            const time = document.getElementById('newSessionTime').value;
            const msg  = document.getElementById('addSessionMsg');
            const btn  = document.getElementById('addSessionBtn');

            if (!date || !time) {
                msg.style.color = '#c0392b';
                msg.textContent = !date ? 'Please select a date.' : 'Please select a time slot.';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Adding…';

            const fd = new FormData();
            fd.append('plan_id',      '<?= intval($plan_id) ?>');
            fd.append('session_date', date);
            fd.append('session_time', time);

            fetch('/dheergayu/public/api/add-treatment-session.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        msg.style.color = '#28a745';
                        msg.textContent = '✓ ' + data.message;
                        document.getElementById('newSessionDate').value = '';
                        document.getElementById('newSessionTime').value = '';
                        setTimeout(() => location.reload(), 1800);
                    } else {
                        msg.style.color = '#c0392b';
                        msg.textContent = 'Error: ' + data.error;
                        btn.disabled = false;
                        btn.textContent = 'Save';
                    }
                })
                .catch(err => {
                    msg.style.color = '#c0392b';
                    msg.textContent = 'Error: ' + err.message;
                    btn.disabled = false;
                    btn.textContent = 'Save';
                });
        }

        // Form submission
        document.getElementById('treatmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const therapistName = document.querySelector('input[name="therapist_name"]').value.trim();
if (!therapistName) {
    alert('Please fill in Therapist Name');
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
