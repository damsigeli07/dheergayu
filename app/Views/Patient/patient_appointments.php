
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/Models/AppointmentModel.php';

$model = new AppointmentModel($conn);
$patient_id = $_SESSION['user_id'];
$appointments = $model->getPatientAppointments($patient_id);

// Get treatment plans for patient
$plans_query = "
    SELECT tp.*, tl.treatment_name
    FROM treatment_plans tp
    LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
    WHERE tp.patient_id = ?
    ORDER BY tp.created_at DESC
";
$stmt = $conn->prepare($plans_query);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$plans_result = $stmt->get_result();
$treatment_plans = [];
while ($row = $plans_result->fetch_assoc()) {
    $treatment_plans[] = $row;
}
$stmt->close();

// Fetch oils used per treatment
$treatmentOils = [];
$oils_result = $conn->query("
    SELECT tp.treatment_id, p.name AS oil_name, tp.quantity_per_session
    FROM treatment_products tp
    JOIN products p ON p.product_id = tp.product_id
");
if ($oils_result) {
    while ($orow = $oils_result->fetch_assoc()) {
        $treatmentOils[(int)$orow['treatment_id']][] = $orow;
    }
}

$planByTreatmentId = [];
foreach ($treatment_plans as $planRow) {
    $tid = (int)($planRow['treatment_id'] ?? 0);
    if ($tid > 0 && !isset($planByTreatmentId[$tid])) {
        $planByTreatmentId[$tid] = $planRow;
    }
}

// Helper functions
function getTreatmentPrice($conn, $treatment_type) {
    $treatment_map = [
        'Asthma' => 'Abhyanga',
        'Diabetes' => 'Abhyanga',
        'Skin Diseases' => 'Shirodhara',
        'Respiratory Disorders' => 'Shirodhara',
        'Arthritis' => 'Panchakarma',
        'ENT Disorders' => 'Udvartana',
        'Neurological Diseases' => 'Panchakarma',
        'Osteoporosis' => 'Vashpa Sweda',
        'Stress and Depression' => 'Shirodhara',
        'Cholesterol' => 'Nasya'
    ];
    
    $stmt = $conn->prepare("SELECT price FROM treatment_list WHERE treatment_name = ?");
    $stmt->bind_param("s", $treatment_type);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($result) {
        return $result['price'];
    }
    
    if (isset($treatment_map[$treatment_type])) {
        $mapped_name = $treatment_map[$treatment_type];
        $stmt = $conn->prepare("SELECT price FROM treatment_list WHERE treatment_name = ?");
        $stmt->bind_param("s", $mapped_name);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($result) {
            return $result['price'];
        }
    }
    
    return 2000.00;
}

function renderAppointmentCard($apt, $conn) {
    $type = $apt['type'];
    $isConsultation = $type === 'consultation';
    $status = $apt['status'];
    $noteText = trim((string)($apt['notes'] ?? ''));
    $isRescheduledOnce = stripos($noteText, '[PATIENT_RESCHEDULED_ONCE]') !== false;
    $isTreatmentRescheduledOnce = stripos($noteText, '[TREATMENT_RESCHEDULED_ONCE]') !== false;
    $canRescheduleWithin24h = false;
    if (!empty($apt['created_at'])) {
        $createdAtTs = strtotime($apt['created_at']);
        if ($createdAtTs !== false) {
            $canRescheduleWithin24h = (time() <= ($createdAtTs + 86400));
        }
    }
    $isDoctorCancelled = $isConsultation
        && $status === 'Cancelled'
        && (
            stripos($noteText, 'Doctor Cancelled:') === 0
            || $noteText !== ''
        );
    $doctorCancelledWindowOpen = false;
    if ($isDoctorCancelled && !empty($apt['updated_at'])) {
        $cancelledAtTs = strtotime($apt['updated_at']);
        if ($cancelledAtTs !== false) {
            $doctorCancelledWindowOpen = (time() <= ($cancelledAtTs + 172800));
        }
    }
    
    if ($isConsultation) {
        $fee = 2000;
    } else {
        $fee = getTreatmentPrice($conn, $apt['treatment_type']);
    }
    
    echo '<div class="appointment-card ' . $type . '">';
    echo '<div class="appointment-header">';
    echo '<div class="appointment-type ' . $type . '">' . ucfirst($type) . '</div>';
    echo '<div class="appointment-status status-' . strtolower($status) . '">' . $status . '</div>';
    echo '</div>';
    echo '<div class="appointment-details">';
    
    if ($isConsultation) {
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Doctor</span>';
        echo '<span class="detail-value">' . ($apt['doctor_name'] ?? 'General Consultation') . '</span>';
        echo '</div>';
    } else {
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Treatment</span>';
        echo '<span class="detail-value">' . ($apt['treatment_type'] ?? 'N/A') . '</span>';
        echo '</div>';

        $diagnosisText = trim((string)($apt['plan_diagnosis'] ?? ''));
        if ($diagnosisText === '') {
            $diagnosisText = trim((string)($apt['notes'] ?? ''));
        }
        if ($diagnosisText !== '') {
            echo '<div class="detail-item">';
            echo '<span class="detail-label">Diagnosis</span>';
            echo '<span class="detail-value">' . htmlspecialchars($diagnosisText) . '</span>';
            echo '</div>';
        }

        $sessionInfo = '';
        if (!empty($apt['plan_total_sessions'])) {
            $sessionInfo = (int)$apt['plan_total_sessions'] . ' sessions';
            if (!empty($apt['plan_sessions_per_week'])) {
                $sessionInfo .= ' (' . (int)$apt['plan_sessions_per_week'] . 'x per week)';
            }
        }
        if ($sessionInfo !== '') {
            echo '<div class="detail-item">';
            echo '<span class="detail-label">Session Info</span>';
            echo '<span class="detail-value">' . htmlspecialchars($sessionInfo) . '</span>';
            echo '</div>';
        }
    }
    
    echo '<div class="detail-item">';
    echo '<span class="detail-label">Date & Time</span>';
    echo '<span class="detail-value">' . date('M d, Y - h:i A', strtotime($apt['appointment_date'] . ' ' . $apt['appointment_time'])) . '</span>';
    echo '</div>';
    
    echo '<div class="detail-item">';
    echo '<span class="detail-label">Fee</span>';
    echo '<span class="detail-value">Rs ' . number_format($fee, 2) . '</span>';
    echo '</div>';
    
    if ($status !== 'Cancelled') {
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Payment Status</span>';
        $paymentColor = ($apt['payment_status'] ?? 'Pending') === 'Completed' ? '#28a745' : '#ff9800';
        echo '<span class="detail-value" style="color: ' . $paymentColor . ';">' . ($apt['payment_status'] ?? 'Pending') . '</span>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Show "View Prescription" button for completed consultations
    if ($isConsultation && $status === 'Completed') {
        echo '<div class="appointment-actions">';
        echo '<button class="action-btn btn-prescription" onclick="viewPrescription(' . $apt['id'] . ')">View Prescription</button>';
        echo '</div>';
    }

    if ($status !== 'Cancelled' && $status !== 'Completed') {
        echo '<div class="appointment-actions">';
        if ($isConsultation) {
            if (($apt['payment_status'] ?? 'Pending') !== 'Completed') {
                echo '<button class="action-btn btn-primary" onclick="payNow(' . $apt['id'] . ', \'' . $type . '\')">Pay Now</button>';
            }
            if (!$isRescheduledOnce && $canRescheduleWithin24h) {
                echo '<button class="action-btn btn-warning" onclick="editAppointment(' . $apt['id'] . ', \'' . $type . '\', \'' . $apt['appointment_date'] . '\', \'' . $apt['appointment_time'] . '\', ' . (int)($apt['doctor_id'] ?? 0) . ', \'' . ($apt['created_at'] ?? '') . '\', \'' . ($apt['updated_at'] ?? '') . '\', \'' . addslashes($status) . '\', \'' . addslashes((string)($apt['notes'] ?? '')) . '\')">Reschedule</button>';
            }
            if ($isRescheduledOnce) {
                echo '<div style="width:100%;margin-bottom:8px;font-size:12px;color:#155724;background:#d4edda;border:1px solid #c3e6cb;border-radius:6px;padding:8px 10px;">Rescheduled. Cancellation is disabled for this appointment.</div>';
            }
        }

        if (!$isConsultation && $status === 'Pending' && ($apt['payment_status'] ?? 'Pending') !== 'Completed') {
            echo '<button class="action-btn btn-primary" onclick="payNow(' . $apt['id'] . ', \'' . $type . '\')">Confirm & Pay</button>';
            if (!$isTreatmentRescheduledOnce) {
                echo '<button class="action-btn btn-warning" onclick="rescheduleAndPayTreatment(' . $apt['id'] . ', \'' . $apt['appointment_date'] . '\', \'' . $apt['appointment_time'] . '\')">Reschedule & Pay</button>';
            } else {
                echo '<div style="width:100%;margin-bottom:8px;font-size:12px;color:#155724;background:#d4edda;border:1px solid #c3e6cb;border-radius:6px;padding:8px 10px;">Treatment already rescheduled once. Please proceed to payment.</div>';
            }
        }
        echo '</div>';
    } elseif ($isDoctorCancelled) {
        echo '<div class="appointment-actions">';
        if ($isRescheduledOnce) {
            echo '<div style="width:100%;margin-bottom:8px;font-size:12px;color:#155724;background:#d4edda;border:1px solid #c3e6cb;border-radius:6px;padding:8px 10px;">Already rescheduled once. Further rescheduling is disabled.</div>';
        } elseif ($doctorCancelledWindowOpen) {
            echo '<div style="width:100%;margin-bottom:8px;font-size:12px;color:#8a6d3b;background:#fcf8e3;border:1px solid #faebcc;border-radius:6px;padding:8px 10px;">Cancelled by doctor. You can reschedule within 48 hours.</div>';
            echo '<button class="action-btn btn-warning" onclick="editAppointment(' . $apt['id'] . ', \'' . $type . '\', \'' . $apt['appointment_date'] . '\', \'' . $apt['appointment_time'] . '\', ' . (int)($apt['doctor_id'] ?? 0) . ', \'' . ($apt['created_at'] ?? '') . '\', \'' . ($apt['updated_at'] ?? '') . '\', \'' . addslashes($status) . '\', \'' . addslashes((string)($apt['notes'] ?? '')) . '\', true)">Reschedule</button>';
        } else {
            echo '<div style="width:100%;margin-bottom:8px;font-size:12px;color:#8a6d3b;background:#fcf8e3;border:1px solid #faebcc;border-radius:6px;padding:8px 10px;">Cancelled by doctor. Reschedule window expired (48 hours).</div>';
        }
        echo '</div>';
    }
    
    echo '</div>';
}

function countAll($appointments) {
    return count($appointments['consultations'] ?? []) + count($appointments['treatments'] ?? []);
}

function getConsultationAppointments($appointments) {
    $consultations = [];
    foreach ($appointments['consultations'] ?? [] as $apt) {
        $apt['type'] = 'consultation';
        $consultations[] = $apt;
    }
    usort($consultations, function ($a, $b) {
        return strtotime($b['appointment_date'] . ' ' . $b['appointment_time']) <=> strtotime($a['appointment_date'] . ' ' . $a['appointment_time']);
    });
    return $consultations;
}

function getTreatmentAppointments($appointments) {
    $treatments = [];
    foreach ($appointments['treatments'] ?? [] as $apt) {
        $apt['type'] = 'treatment';
        $treatments[] = $apt;
    }
    usort($treatments, function ($a, $b) {
        return strtotime($b['appointment_date'] . ' ' . ($b['appointment_time'] ?? '00:00:00')) <=> strtotime($a['appointment_date'] . ' ' . ($a['appointment_time'] ?? '00:00:00'));
    });
    return $treatments;
}

function countUpcoming($appointments) {
    $count = 0;
    foreach ($appointments['consultations'] ?? [] as $apt) {
        if ($apt['status'] !== 'Cancelled' && $apt['status'] !== 'Completed') $count++;
    }
    foreach ($appointments['treatments'] ?? [] as $apt) {
        if ($apt['status'] !== 'Cancelled' && $apt['status'] !== 'Completed') $count++;
    }
    return $count;
}

function getAllAppointments($appointments) {
    $all = [];
    foreach ($appointments['consultations'] ?? [] as $apt) {
        $apt['type'] = 'consultation';
        $all[] = $apt;
    }
    foreach ($appointments['treatments'] ?? [] as $apt) {
        $apt['type'] = 'treatment';
        $all[] = $apt;
    }
    usort($all, fn($a, $b) => strtotime($b['appointment_date']) <=> strtotime($a['appointment_date']));
    return $all;
}

function getUpcomingAppointments($appointments) {
    $upcoming = [];
    foreach ($appointments['consultations'] ?? [] as $apt) {
        if ($apt['status'] !== 'Cancelled' && $apt['status'] !== 'Completed') {
            $apt['type'] = 'consultation';
            $upcoming[] = $apt;
        }
    }
    foreach ($appointments['treatments'] ?? [] as $apt) {
        if ($apt['status'] !== 'Cancelled' && $apt['status'] !== 'Completed') {
            $apt['type'] = 'treatment';
            $upcoming[] = $apt;
        }
    }
    usort($upcoming, fn($a, $b) => strtotime($a['appointment_date']) <=> strtotime($b['appointment_date']));
    return $upcoming;
}

function getCancelledAppointments($appointments) {
    $cancelled = [];
    foreach ($appointments['consultations'] ?? [] as $apt) {
        if ($apt['status'] === 'Cancelled') {
            $apt['type'] = 'consultation';
            $cancelled[] = $apt;
        }
    }
    foreach ($appointments['treatments'] ?? [] as $apt) {
        if ($apt['status'] === 'Cancelled') {
            $apt['type'] = 'treatment';
            $cancelled[] = $apt;
        }
    }
    return $cancelled;
}

$consultationOnly = getConsultationAppointments($appointments);
$treatmentOnly = getTreatmentAppointments($appointments);
$cancelled = getCancelledAppointments($appointments);
$treatmentPlansTabCount = count($treatment_plans);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - My Appointments</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/patient_appointments.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="/dheergayu/public/assets/images/Patient/logo_modern.png" alt="Dheergayu Logo">
                <h1>DHEERGAYU <br> <span>AYURVEDIC MANAGEMENT CENTER</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php">HOME</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/contact_us.php">CONTACT US</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="home.php" class="back-btn">← Back to Home</a>
            </div>
        </div>
    </header>

    <div class="content-wrapper">
        <div class="page-header">
            <h1 class="page-title">My Appointments</h1>
            <p class="page-subtitle">Manage your consultations and treatments</p>
        </div>

        <div class="appointments-container">
<div class="appointments-tabs">
    <button class="tab-btn active" onclick="showTab('consultations')">
        Consultations <span class="tab-badge"><?php echo count($consultationOnly); ?></span>
    </button>
    <button class="tab-btn" onclick="showTab('treatment-plans')">
        Treatment Plans <span class="tab-badge"><?php echo $treatmentPlansTabCount; ?></span>
    </button>
    <button class="tab-btn" onclick="showTab('cancelled')">
        Cancelled
    </button>
</div>

            <div class="tab-content">
                <!-- Consultations -->
                <div id="consultations-tab" class="tab-panel" style="display: block;">
                    <?php 
                    if (empty($consultationOnly)): 
                    ?>
                        <div class="empty-state">
                            <h3>No Appointments</h3>
                            <p>Book your first consultation today</p>
                            <div style="margin-top: 20px;">
                                <a href="channeling.php" class="book-btn">Book Consultation</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="appointments-list" style="margin-bottom: 28px;">
                            <?php foreach ($consultationOnly as $apt): ?>
                                <?php renderAppointmentCard($apt, $conn); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Treatment Plans Tab -->
                <div id="treatment-plans-tab" class="tab-panel" style="display: none;">
                    <?php if (empty($treatment_plans)): ?>
                        <div class="empty-state">
                            <h3>No Treatment Plans</h3>
                            <p>Your doctor will create treatment plans based on consultations</p>
                        </div>
                    <?php else: ?>
                        <h3 style="margin:0 0 12px 0;color:#2d3748;">Doctor Treatment Plans</h3>

                        <div class="appointments-list">
                            <?php foreach ($treatment_plans as $plan):
                                // Get sessions for this plan — coerce NULL status to AwaitingPayment for sessions added by staff after initial plan payment
                                $planAlreadyPaid = ($plan['payment_status'] ?? '') === 'Completed';
                                $sessions_query = "SELECT session_number, session_date, session_time,
                                    CASE WHEN (status IS NULL OR status = '') THEN 'AwaitingPayment' ELSE status END AS status
                                    FROM treatment_sessions WHERE plan_id = ? ORDER BY session_number";
                                $stmt = $conn->prepare($sessions_query);
                                $stmt->bind_param('i', $plan['plan_id']);
                                $stmt->execute();
                                $sessions_result = $stmt->get_result();
                                $sessions = [];
                                while ($row = $sessions_result->fetch_assoc()) {
                                    $sessions[] = $row;
                                }
                                $stmt->close();
                            ?>
                                <div class="appointment-card treatment-plan" style="border-left: 5px solid #28a745;">
                                    <div class="appointment-header">
                                        <div class="appointment-type" style="background: #28a745;">Treatment Plan</div>
                                        <div class="appointment-status status-<?= strtolower($plan['status']) ?>">
                                            <?= $plan['status'] ?>
                                        </div>
                                    </div>
                                    
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Treatment</span>
                                            <span class="detail-value"><?= htmlspecialchars(trim($plan['treatment_name'] ?? '') ?: '—') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Diagnosis</span>
                                            <span class="detail-value"><?= htmlspecialchars($plan['diagnosis'] ?? '') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Total Sessions</span>
                                            <span class="detail-value">1 session</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Start Date</span>
                                            <span class="detail-value"><?= date('M d, Y', strtotime($plan['start_date'])) ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Total Cost</span>
                                            <span class="detail-value">Rs <?= number_format($plan['total_cost'], 2) ?></span>
                                        </div>
                                        <?php
                                            $planOils = $treatmentOils[(int)($plan['treatment_id'] ?? 0)] ?? [];
                                        ?>
                                        <?php if (!empty($planOils)): ?>
                                        <div style="grid-column: 1 / -1; margin-top: 4px;">
                                            <div style="background:#f0f7f4;border:1px solid #c3e6cb;border-radius:8px;padding:10px 14px;font-size:13px;color:#2d6a4f;">
                                                🌿 <strong>Medicines included in your treatment:</strong>
                                                <ul style="margin:6px 0 0 0;padding-left:18px;">
                                                    <?php foreach ($planOils as $oil): ?>
                                                        <li><?= htmlspecialchars($oil['oil_name']) ?> (<?= (int)$oil['quantity_per_session'] ?> bottle<?= $oil['quantity_per_session'] > 1 ? 's' : '' ?> per session)</li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <p style="margin:6px 0 0 0;color:#555;font-size:12px;">* Cost of medicines is included in the total treatment price.</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Payment Status</span>
                                            <span class="detail-value" style="color: <?= $plan['payment_status'] === 'Completed' ? '#28a745' : '#ff9800' ?>">
                                                <?= $plan['payment_status'] ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Sessions List -->
                                    <div style="margin-top:20px;background:#f8f9fa;padding:15px;border-radius:8px;">
                                        <strong style="color:#333;font-size:15px;">Treatment Sessions:</strong>
                                        <div style="margin-top:12px;">
                                            <?php foreach ($sessions as $idx => $session):
                                                $isAwaitingPayment = ($session['status'] === 'AwaitingPayment');
                                                $borderColor = $session['status'] === 'Completed' ? '#28a745'
                                                    : ($isAwaitingPayment ? '#E8A020'
                                                    : ($session['status'] === 'Confirmed' ? '#17a2b8'
                                                    : '#ffc107'));
                                                // Min date = day after previous session's date
                                                $prevSession = $idx > 0 ? $sessions[$idx - 1] : null;
                                                $reschedMinDate = ($prevSession && !empty($prevSession['session_date']))
                                                    ? date('Y-m-d', strtotime($prevSession['session_date'] . ' +1 day'))
                                                    : date('Y-m-d');
                                            ?>
                                                <div style="background:#fff;padding:12px;margin:8px 0;border-radius:6px;border-left:3px solid <?= $borderColor ?>;<?= $isAwaitingPayment ? 'box-shadow:0 2px 8px rgba(156,39,176,.15);' : '' ?>">
                                                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                                                        <div>
                                                            <strong style="color:#333;">Session <?= $session['session_number'] ?></strong>
                                                            <?php if ($isAwaitingPayment): ?>
                                                                <span style="background:#f3e5f5;color:#7b1fa2;font-size:11px;padding:2px 8px;border-radius:10px;margin-left:6px;">New</span>
                                                            <?php endif; ?>
                                                            <div style="font-size:13px;color:#666;margin-top:4px;">
                                                                <?php if (!empty($session['session_date']) && !empty($session['session_time'])): ?>
                                                                    📅 <?= date('l, M d, Y', strtotime($session['session_date'])) ?> at ⏰ <?= date('g:i A', strtotime($session['session_time'])) ?>
                                                                <?php else: ?>
                                                                    🗓 Date to be scheduled by staff
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <span class="status-badge status-<?= strtolower(str_replace(' ', '', $session['status'])) ?>" style="font-size:11px;">
                                                            <?= $isAwaitingPayment ? 'Payment Required' : $session['status'] ?>
                                                        </span>
                                                    </div>

                                                    <?php if ($isAwaitingPayment): ?>
                                                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid #f3e5f5;">
                                                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                                            <a href="session_payment.php?plan_id=<?= $plan['plan_id'] ?>&session_number=<?= $session['session_number'] ?>"
                                                               class="action-btn btn-primary" style="text-decoration:none;">
                                                                ✓ Confirm &amp; Pay (Rs <?= number_format($plan['total_cost'], 2) ?>)
                                                            </a>
                                                            <button class="action-btn btn-warning" onclick="openSessionReschedule(<?= $plan['plan_id'] ?>, <?= $session['session_number'] ?>, <?= intval($plan['treatment_id'] ?? 0) ?>, '<?= $reschedMinDate ?>')">
                                                                Reschedule &amp; Pay
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <?php
                                        $firstSessionDate = '';
                                        $firstSessionTime = '';
                                        $sessionsPayload = [];
                                        foreach ($sessions as $sessionRow) {
                                            $sessionsPayload[] = [
                                                'session_number' => (int)($sessionRow['session_number'] ?? 0),
                                                'session_date' => (string)($sessionRow['session_date'] ?? ''),
                                                'session_time' => (string)($sessionRow['session_time'] ?? ''),
                                                'status' => (string)($sessionRow['status'] ?? ''),
                                            ];
                                            if ($firstSessionDate === '' && ($sessionRow['status'] ?? '') !== 'Cancelled') {
                                                $firstSessionDate = $sessionRow['session_date'] ?? '';
                                                $firstSessionTime = $sessionRow['session_time'] ?? '';
                                            }
                                        }
                                        if ($firstSessionDate === '' && !empty($sessions)) {
                                            $firstSessionDate = $sessions[0]['session_date'] ?? '';
                                            $firstSessionTime = $sessions[0]['session_time'] ?? '';
                                        }
                                        $sessionsPayloadJson = json_encode($sessionsPayload);
                                        $sessionsPayloadB64 = base64_encode($sessionsPayloadJson ?: '[]');
                                    ?>

                                    <?php if (($plan['payment_status'] ?? 'Pending') !== 'Completed'): ?>
                                        <?php
                                            $firstTs = ($firstSessionDate && $firstSessionTime)
                                                ? strtotime($firstSessionDate . ' ' . $firstSessionTime)
                                                : 0;
                                            $now = time();
                                            $sessionPassed = $firstTs > 0 && $firstTs < $now;
                                            $deadlinePassed = false;
                                        ?>
                                        <div class="appointment-actions" style="margin-top:20px;background:<?= $deadlinePassed ? '#fde8e8' : '#fff3cd' ?>;padding:12px;border-radius:8px;">

                                            <?php if ($sessionPassed): ?>
                                                <p style="margin:0 0 12px 0;font-size:14px;color:#c0392b;">
                                                    ❌ Your first session date has passed. Please reschedule before paying.
                                                </p>
                                            <?php else: ?>
                                                <p style="margin:0 0 12px 0;font-size:14px;color:#856404;">
                                                    ⚠️ Please confirm and pay before your first session
                                                    (<?= $firstSessionDate ? date('M d, Y', strtotime($firstSessionDate)) : '' ?>).
                                                </p>
                                            <?php endif; ?>

                                            <div style="display:flex;gap:10px;">
                                                <?php if (!$deadlinePassed): ?>
                                                    <?php if (($plan['status'] ?? '') === 'Pending' || ($plan['status'] ?? '') === 'ChangeRequested'): ?>
                                                    <button class="action-btn btn-primary" onclick="confirmTreatmentPlan(<?= $plan['plan_id'] ?>)" style="flex:1;">
                                                        ✓ Confirm & Pay (Rs <?= number_format($plan['total_cost'], 2) ?>)
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="action-btn btn-primary" onclick="payTreatmentPlan(<?= $plan['plan_id'] ?>)" style="flex:1;">
                                                        ✓ Confirm & Pay (Rs <?= number_format($plan['total_cost'], 2) ?>)
                                                    </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <button class="action-btn btn-warning" data-sessions-b64="<?= htmlspecialchars($sessionsPayloadB64, ENT_QUOTES, 'UTF-8') ?>" onclick="rescheduleAndPayTreatmentPlan(<?= $plan['plan_id'] ?>, <?= (int)($plan['treatment_id'] ?? 0) ?>, this)">
                                                    Reschedule & Pay
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Cancelled Appointments -->
                <div id="cancelled-tab" class="tab-panel" style="display: none;">
                    <?php 
                    if (empty($cancelled)): 
                    ?>
                        <div class="empty-state">
                            <h3>No Cancelled Appointments</h3>
                        </div>
                    <?php else: ?>
                        <div class="appointments-list">
                            <?php foreach ($cancelled as $apt): ?>
                                <?php renderAppointmentCard($apt, $conn); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Reschedule Appointment</h3>
            <form id="editForm">
                <input type="hidden" id="editId">
                <input type="hidden" id="editType">
                <input type="hidden" id="editDoctorId">
                <div class="form-group">
                    <label for="editDate">Date</label>
                    <input type="date" id="editDate" required>
                </div>
                <div class="form-group">
                    <label for="editTime">Time</label>
                    <select id="editTime" required>
                        <option value="">Select Time</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="action-btn btn-primary">Confirm Reschedule</button>
                    <button type="button" class="action-btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="planRescheduleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePlanRescheduleModal()">&times;</span>
            <h3>Reschedule & Pay</h3>
            <p style="margin-top:0;color:#666;font-size:14px;">Set date and available slot for each session.</p>
            <form id="planRescheduleForm">
                <input type="hidden" id="planReschedulePlanId">
                <input type="hidden" id="planRescheduleTreatmentId">
                <div id="planSessionRows" style="display:flex;flex-direction:column;gap:12px;"></div>
                <div id="planSessionHelper" style="margin-top:6px;font-size:12px;color:#666;"></div>
                <div class="form-group" style="margin-top:10px;">
                    <label for="planRescheduleNote">Additional Note (optional)</label>
                    <input type="text" id="planRescheduleNote" placeholder="Any extra instruction for doctor">
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="action-btn btn-primary">Update Schedule</button>
                    <button type="button" class="action-btn btn-secondary" onclick="closePlanRescheduleModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Session Reschedule Modal -->
    <div id="sessionRescheduleModal" class="modal">
        <div class="modal-content" style="max-width:440px;">
            <span class="close" onclick="closeSessionReschedule()">&times;</span>
            <h3>Reschedule Session</h3>
            <p style="margin-top:0;color:#666;font-size:14px;">Pick a new date and available slot. You can pay after updating.</p>
            <input type="hidden" id="reschedPlanId">
            <input type="hidden" id="reschedSessionNum">
            <input type="hidden" id="reschedTreatmentId">
            <input type="hidden" id="reschedTime">
            <div class="form-group" style="margin-top:12px;">
                <label>New Date</label>
                <input type="date" id="reschedDate" min="<?= date('Y-m-d') ?>" onchange="loadReschedSlots()">
            </div>
            <div class="form-group">
                <label>Available Slots</label>
                <div id="reschedSlotContainer" style="min-height:36px;">
                    <span style="font-size:13px;color:#999;">Select a date first</span>
                </div>
            </div>
            <p id="reschedMsg" style="font-size:13px;min-height:18px;"></p>
            <div class="modal-buttons">
                <button class="action-btn btn-primary" onclick="submitSessionReschedule()">Update Schedule</button>
                <button class="action-btn btn-secondary" onclick="closeSessionReschedule()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Prescription Modal -->
    <div id="prescriptionModal" class="modal" style="z-index:1100;">
        <div class="modal-content" style="max-width:600px;width:95%;max-height:85vh;overflow-y:auto;">
            <span class="close" onclick="closePrescriptionModal()" style="font-size:24px;cursor:pointer;float:right;">&times;</span>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="background:linear-gradient(135deg,#00b4d8,#0077b6);border-radius:8px;padding:8px 14px;color:#fff;font-weight:700;font-size:13px;letter-spacing:1px;">PRESCRIPTION</div>
                <h3 style="margin:0;color:#1a202c;font-size:18px;">Consultation Details</h3>
            </div>
            <div id="prescriptionContent">
                <div style="text-align:center;padding:30px;color:#888;">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCancelModal()">&times;</span>
            <h3>Cancel Appointment</h3>
            <p>Are you sure you want to cancel this appointment?</p>
            <div class="modal-buttons">
                <button class="action-btn btn-danger" onclick="confirmCancel()">Yes, Cancel</button>
                <button class="action-btn btn-secondary" onclick="closeCancelModal()">Keep Appointment</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best Ayurvedic wellness centers in your area!</p>
            </div>
            <div class="footer-column">
                <h3>OFFICE</h3>
                <p>123 Wellness Street</p>
                <p>Colombo, LK 00100</p>
                <p>Sri Lanka</p>
                <p><a href="mailto:info@dheergayu.com" class="footer-link">info@dheergayu.com</a></p>
                <p>+94 11 234 5678</p>
            </div>
            <div class="footer-column">
                <h3>LINKS</h3>
                <ul>
                    <li><a href="home.php" class="footer-link">Home</a></li>
                    <li><a href="treatment.php" class="footer-link">Treatments</a></li>
                    <li><a href="learn_more.php" class="footer-link">About Us</a></li>
                    <li><a href="channeling.php" class="footer-link">Booking</a></li>
                    <li><a href="contact_us.php" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/facebook.png" alt="Facebook" class="social-icon">
                            Facebook
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/twitter(x).png" alt="X" class="social-icon">
                            X
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/linkedin.png" alt="LinkedIn" class="social-icon">
                            LinkedIn
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/instagram.png" alt="Instagram" class="social-icon">
                            Instagram
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
// Fixed JavaScript for patient_appointments.php tab switching
// Replace the entire <script> section with this code

let currentCancelId = null;
let currentCancelType = null;
let treatmentRescheduleThenPay = false;

// FIX 1: Properly handle tab switching without event parameter
function showTab(tabName) {
    console.log('Switching to tab:', tabName);
    
    // Hide all tab panels
    const allPanels = document.querySelectorAll('.tab-panel');
    allPanels.forEach(function(panel) {
        panel.style.display = 'none';
    });
    
    // Remove active class from all tab buttons
    const allButtons = document.querySelectorAll('.tab-btn');
    allButtons.forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    // Show the selected panel
    const targetPanel = document.getElementById(tabName + '-tab');
    if (targetPanel) {
        targetPanel.style.display = 'block';
    } else {
        console.error('Panel not found:', tabName + '-tab');
    }
    
    // Add active class to the clicked button
    // Find button by checking onclick attribute
    allButtons.forEach(function(btn) {
        const onclickAttr = btn.getAttribute('onclick');
        if (onclickAttr && onclickAttr.includes("'" + tabName + "'")) {
            btn.classList.add('active');
        }
    });
}

function showAllSplit(section) {
    const consultationsPanel = document.getElementById('all-split-consultations-panel');
    const treatmentsPanel = document.getElementById('all-split-treatments-panel');
    const consultationsBtn = document.getElementById('all-split-consultations-btn');
    const treatmentsBtn = document.getElementById('all-split-treatments-btn');

    if (!consultationsPanel || !treatmentsPanel || !consultationsBtn || !treatmentsBtn) {
        return;
    }

    if (section === 'treatments') {
        consultationsPanel.style.display = 'none';
        treatmentsPanel.style.display = 'block';
        consultationsBtn.classList.remove('active');
        treatmentsBtn.classList.add('active');
    } else {
        consultationsPanel.style.display = 'block';
        treatmentsPanel.style.display = 'none';
        consultationsBtn.classList.add('active');
        treatmentsBtn.classList.remove('active');
    }
}

// FIX 2: Treatment plan confirmation functions
function confirmTreatmentPlan(planId) {
    if (confirm('Confirm all treatment sessions and proceed to payment?')) {
        fetch('/dheergayu/public/api/confirm-treatment-plan.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'plan_id=' + planId + '&action=confirm'
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = 'treatment_plan_payment.php?plan_id=' + planId;
            } else {
                alert('Error: ' + (data.message || 'Failed to confirm plan'));
            }
        })
        .catch(function(err) {
            console.error('Error:', err);
            alert('Network error. Please try again.');
        });
    }
}

function requestPlanChange(planId) {
    const reason = prompt('Please describe the changes you need (e.g., different dates, times):');
    if (reason && reason.trim()) {
        fetch('/dheergayu/public/api/confirm-treatment-plan.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'plan_id=' + planId + '&action=request_change&reason=' + encodeURIComponent(reason)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Change request sent to doctor successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to send request'));
            }
        })
        .catch(function(err) {
            console.error('Error:', err);
            alert('Network error. Please try again.');
        });
    }
}

function rescheduleAndPayTreatmentPlan(planId, treatmentId, triggerButton) {
    if (!planId || !treatmentId) {
        alert('Unable to load treatment details for rescheduling.');
        return;
    }

    const modal = document.getElementById('planRescheduleModal');
    const planIdInput = document.getElementById('planReschedulePlanId');
    const treatmentIdInput = document.getElementById('planRescheduleTreatmentId');
    const rowsContainer = document.getElementById('planSessionRows');
    const helper = document.getElementById('planSessionHelper');

    let sessions = [];
    try {
        const rawB64 = triggerButton ? (triggerButton.getAttribute('data-sessions-b64') || '') : '';
        if (rawB64) {
            sessions = JSON.parse(atob(rawB64));
        } else {
            const legacyRaw = triggerButton ? (triggerButton.getAttribute('data-sessions') || '[]') : '[]';
            sessions = JSON.parse(legacyRaw);
        }
    } catch (e) {
        sessions = [];
    }
    if (!Array.isArray(sessions) || sessions.length === 0) {
        sessions = [{ session_number: 1, session_date: '', session_time: '', status: 'Pending' }];
    }

    planIdInput.value = planId;
    treatmentIdInput.value = treatmentId;

    rowsContainer.innerHTML = '';
    const today = new Date().toISOString().split('T')[0];

    sessions.forEach(function(session, index) {
        const row = document.createElement('div');
        row.className = 'plan-session-row';
        row.style.cssText = 'border:1px solid #eee;border-radius:8px;padding:10px;background:#fafafa;';

        const safeSessionNo = session.session_number || (index + 1);
        const defaultDate = session.session_date || today;

        row.innerHTML =
            '<div style="font-weight:600;margin-bottom:8px;">Session ' + safeSessionNo + '</div>' +
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">' +
                '<div>' +
                    '<label style="display:block;font-size:12px;color:#555;margin-bottom:4px;">Date</label>' +
                    '<input type="date" class="plan-session-date" data-index="' + index + '" data-session-number="' + safeSessionNo + '" value="' + defaultDate + '" required style="width:100%;">' +
                '</div>' +
                '<div>' +
                    '<label style="display:block;font-size:12px;color:#555;margin-bottom:4px;">Available Time Slot</label>' +
                    '<select class="plan-session-time" data-index="' + index + '" required style="width:100%;"><option value="">Select Time</option></select>' +
                '</div>' +
            '</div>';

        rowsContainer.appendChild(row);

        const dateInputEl = row.querySelector('.plan-session-date');
        const timeSelectEl = row.querySelector('.plan-session-time');
        loadPlanRescheduleSlotsForRow(defaultDate, treatmentId, timeSelectEl, session.session_time || '');

        dateInputEl.addEventListener('change', function() {
            loadPlanRescheduleSlotsForRow(this.value, treatmentId, timeSelectEl, '');
        });
    });

    helper.textContent = 'All sessions can be rescheduled. Select a date and available slot for each session.';
    modal.style.display = 'block';
}

function formatTime12h(t) {
    const [h, m] = t.split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return h12 + ':' + String(m).padStart(2, '0') + ' ' + ampm;
}

function openSessionReschedule(planId, sessionNumber, treatmentId, minDate) {
    document.getElementById('reschedPlanId').value      = planId;
    document.getElementById('reschedSessionNum').value  = sessionNumber;
    document.getElementById('reschedTreatmentId').value = treatmentId;
    document.getElementById('reschedDate').value        = '';
    document.getElementById('reschedDate').min          = minDate || '';
    document.getElementById('reschedTime').value        = '';
    document.getElementById('reschedMsg').textContent   = '';
    document.getElementById('reschedSlotContainer').innerHTML = '<span style="font-size:13px;color:#999;">Select a date first</span>';
    document.getElementById('sessionRescheduleModal').style.display = 'flex';
}
function closeSessionReschedule() {
    document.getElementById('sessionRescheduleModal').style.display = 'none';
}
function loadReschedSlots() {
    const date        = document.getElementById('reschedDate').value;
    const treatmentId = document.getElementById('reschedTreatmentId').value;
    const container   = document.getElementById('reschedSlotContainer');
    document.getElementById('reschedTime').value = '';
    if (!date || !treatmentId) { container.innerHTML = '<span style="font-size:13px;color:#999;">Select a date first</span>'; return; }
    container.innerHTML = '<span style="font-size:13px;color:#999;">Loading slots…</span>';
    fetch('/dheergayu/public/api/treatment-slot-availability.php?treatment_id=' + treatmentId + '&date=' + date)
        .then(r => r.json())
        .then(data => {
            const slots = data.slots || [];
            if (slots.length === 0) {
                container.innerHTML = '<span style="font-size:13px;color:#c0392b;">No slots available for this date</span>';
                return;
            }
            container.innerHTML = '';
            slots.forEach(s => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = formatTime12h(s.slot_time);
                if (s.booked) {
                    btn.disabled = true;
                    btn.style.cssText = 'padding:6px 12px;margin:3px 4px 3px 0;border-radius:6px;border:1px dashed #ccc;background:#f5f5f5;color:#aaa;cursor:not-allowed;font-size:13px;';
                } else {
                    btn.style.cssText = 'padding:6px 12px;margin:3px 4px 3px 0;border-radius:6px;border:1px solid #E8A020;background:#fff;color:#E8A020;cursor:pointer;font-size:13px;font-weight:600;';
                    btn.onclick = function() {
                        container.querySelectorAll('button').forEach(b => { b.style.background='#fff'; b.style.color='#E8A020'; });
                        btn.style.background = '#E8A020'; btn.style.color = '#fff';
                        document.getElementById('reschedTime').value = s.slot_time;
                    };
                }
                container.appendChild(btn);
            });
        })
        .catch(() => { container.innerHTML = '<span style="font-size:13px;color:#c0392b;">Failed to load slots</span>'; });
}
function submitSessionReschedule() {
    const planId  = document.getElementById('reschedPlanId').value;
    const sessNum = document.getElementById('reschedSessionNum').value;
    const date    = document.getElementById('reschedDate').value;
    const time    = document.getElementById('reschedTime').value;
    const msg     = document.getElementById('reschedMsg');

    if (!date) { msg.style.color = '#c0392b'; msg.textContent = 'Please select a date.'; return; }
    if (!time) { msg.style.color = '#c0392b'; msg.textContent = 'Please select a time slot.'; return; }

    msg.style.color = '#555'; msg.textContent = 'Updating…';
    const fd = new FormData();
    fd.append('plan_id', planId); fd.append('session_number', sessNum);
    fd.append('session_date', date); fd.append('session_time', time);

    fetch('/dheergayu/public/api/update-session-date.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                msg.style.color = '#28a745'; msg.textContent = '✓ ' + data.message;
                setTimeout(() => { closeSessionReschedule(); location.reload(); }, 1500);
            } else { msg.style.color = '#c0392b'; msg.textContent = 'Error: ' + data.error; }
        })
        .catch(err => { msg.style.color = '#c0392b'; msg.textContent = 'Error: ' + err.message; });
}

function closePlanRescheduleModal() {
    const modal = document.getElementById('planRescheduleModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function loadPlanRescheduleSlotsForRow(date, treatmentId, slotSelect, preferredTime) {
    slotSelect.innerHTML = '<option value="">Select Time</option>';

    if (!date || !treatmentId) {
        return;
    }

    const formData = new FormData();
    formData.append('treatment_id', treatmentId);
    formData.append('date', date);

    fetch('/dheergayu/public/api/treatment_selection.php?action=loadSlots', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success || !Array.isArray(data.slots) || data.slots.length === 0) {
            throw new Error(data.error || 'No available slots found');
        }

        data.slots.forEach(function(slot) {
            const option = document.createElement('option');
            option.value = slot.slot_time;
            option.textContent = formatTime(slot.slot_time);

            if (slot.booked) {
                option.disabled = true;
                option.textContent += ' (Not Available)';
            }

            if (!slot.booked && preferredTime && slot.slot_time === preferredTime) {
                option.selected = true;
            }

            slotSelect.appendChild(option);
        });
    })
    .catch(function(error) {
        console.error('Error loading slots:', error);
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No available slots';
        option.disabled = true;
        slotSelect.appendChild(option);
    });
}

function payTreatmentPlan(planId) {
    window.location.href = 'treatment_plan_payment.php?plan_id=' + planId;
}

function rescheduleAndPayTreatment(bookingId, date, time) {
    treatmentRescheduleThenPay = true;
    editAppointment(bookingId, 'treatment', date, time, '', '', '', 'Pending', '', false);
}

// Appointment editing functions
function isRescheduleWindowOpen(createdAt) {
    if (!createdAt) return true;
    const created = new Date(createdAt.replace(' ', 'T'));
    if (isNaN(created.getTime())) return true;
    return (Date.now() - created.getTime()) <= (24 * 60 * 60 * 1000);
}

function isDoctorCancelledWindowOpen(updatedAt) {
    if (!updatedAt) return false;
    const cancelledAt = new Date(updatedAt.replace(' ', 'T'));
    if (isNaN(cancelledAt.getTime())) return false;
    return (Date.now() - cancelledAt.getTime()) <= (48 * 60 * 60 * 1000);
}

function editAppointment(id, type, date, time, doctorId, createdAt, updatedAt, status, notes, isDoctorCancelReschedule) {
    const editModal = document.getElementById('editModal');
    const editId = document.getElementById('editId');
    const editType = document.getElementById('editType');
    const editDoctorId = document.getElementById('editDoctorId');
    const editDate = document.getElementById('editDate');
    const editTime = document.getElementById('editTime');
    
    if (!editModal || !editId || !editType || !editDoctorId || !editDate || !editTime) {
        alert('Error: Edit form not properly loaded');
        return;
    }

    const noteText = String(notes || '').trim().toLowerCase();
    const doctorCancelled = String(status || '') === 'Cancelled' && (noteText.startsWith('doctor cancelled:') || noteText.length > 0);

    if (type === 'consultation') {
        if (doctorCancelled || isDoctorCancelReschedule === true) {
            if (!isDoctorCancelledWindowOpen(updatedAt)) {
                alert('Doctor-cancelled appointments can be rescheduled only within 48 hours.');
                return;
            }
        } else if (!isRescheduleWindowOpen(createdAt)) {
            alert('Rescheduling is allowed only within 24 hours of booking time.');
            return;
        }
    }
    
    editId.value = id;
    editType.value = type;
    editDoctorId.value = doctorId || '';
    editDate.value = date;
    editTime.value = time;
    editDate.min = new Date().toISOString().split('T')[0];
    
    if (type === 'treatment') {
        loadTreatmentEditSlots(date, id);
    } else {
        loadEditSlots(date, doctorId);
    }
    
    editModal.style.display = 'block';
}

function loadTreatmentEditSlots(date, bookingId) {
    if (!date) return;
    
    fetch('/dheergayu/public/api/treatment-booking-handler.php?action=get_booking&booking_id=' + bookingId)
        .then(function(res) { return res.json(); })
        .then(function(bookingData) {
            if (bookingData.success && bookingData.booking) {
                const treatmentId = bookingData.booking.treatment_id;
                const formData = new FormData();
                formData.append('treatment_id', treatmentId);
                formData.append('date', date);
                
                return fetch('/dheergayu/public/api/treatment_selection.php?action=loadSlots', {
                    method: 'POST',
                    body: formData
                });
            } else {
                throw new Error('Failed to get booking info');
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            const timeSelect = document.getElementById('editTime');
            const currentTime = timeSelect.value;
            
            timeSelect.innerHTML = '<option value="">Select Time</option>';
            
            if (data.success && data.slots && data.slots.length > 0) {
                data.slots.forEach(function(slot) {
                    const option = document.createElement('option');
                    option.value = slot.slot_time;
                    option.textContent = formatTime(slot.slot_time);
                    option.setAttribute('data-slot-id', slot.slot_id);
                    
                    if (slot.booked && slot.slot_time !== currentTime) {
                        option.disabled = true;
                        option.textContent += ' (Not Available)';
                    }
                    
                    timeSelect.appendChild(option);
                });
                
                if (currentTime) {
                    timeSelect.value = currentTime;
                }
            }
        })
        .catch(function(error) {
            console.error('Error loading treatment slots:', error);
            alert('Error loading available slots');
        });
}

function loadEditSlots(date, doctorId) {
    if (!date) return;

    if (!doctorId) {
        alert('Doctor information missing for this appointment.');
        return;
    }
    
    fetch('/dheergayu/public/api/available-slots.php?date=' + encodeURIComponent(date) + '&doctor_id=' + encodeURIComponent(doctorId))
        .then(function(res) { return res.json(); })
        .then(function(data) {
            const timeSelect = document.getElementById('editTime');
            const currentTime = timeSelect.value;
            
            timeSelect.innerHTML = '<option value="">Select Time</option>';
            
            if (data.slots && data.slots.length > 0) {
                data.slots.forEach(function(slot) {
                    const option = document.createElement('option');
                    option.value = slot.time;
                    option.textContent = formatTime(slot.time);
                    
                    if ((slot.status === 'booked' || slot.status === 'locked') && slot.time !== currentTime) {
                        option.disabled = true;
                        option.textContent += ' (Not Available)';
                    }
                    
                    timeSelect.appendChild(option);
                });
                
                if (currentTime) {
                    timeSelect.value = currentTime;
                }
            }
        });
}

// Add event listener for date change on page load
document.addEventListener('DOMContentLoaded', function() {
    const editDateInput = document.getElementById('editDate');
    if (editDateInput) {
        editDateInput.addEventListener('change', function() {
            const type = document.getElementById('editType').value;
            const id = document.getElementById('editId').value;
            const doctorId = document.getElementById('editDoctorId').value;
            
            if (type === 'treatment') {
                loadTreatmentEditSlots(this.value, id);
            } else {
                loadEditSlots(this.value, doctorId);
            }
        });
    }

    const requestedTab = new URLSearchParams(window.location.search).get('tab');
    if (requestedTab) {
        const normalizedTab = (requestedTab === 'all') ? 'consultations' : requestedTab;
        if (['consultations', 'treatment-plans', 'cancelled'].indexOf(normalizedTab) !== -1) {
            showTab(normalizedTab);
        }
    }

    const planRescheduleForm = document.getElementById('planRescheduleForm');
    if (planRescheduleForm) {
        planRescheduleForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const planId = document.getElementById('planReschedulePlanId').value;
            const sessionRows = document.querySelectorAll('#planSessionRows .plan-session-row');
            if (!sessionRows.length) {
                alert('No sessions found for rescheduling.');
                return;
            }

            const scheduleRows = [];
            for (let i = 0; i < sessionRows.length; i++) {
                const row = sessionRows[i];
                const dateInput = row.querySelector('.plan-session-date');
                const timeSelect = row.querySelector('.plan-session-time');
                const selectedDate = dateInput ? dateInput.value : '';
                const selectedTime = timeSelect ? timeSelect.value : '';
                const sessionNumber = dateInput ? parseInt(dateInput.getAttribute('data-session-number') || (i + 1), 10) : (i + 1);
                if (!selectedDate || !selectedTime) {
                    alert('Please select date and time for all sessions.');
                    return;
                }
                scheduleRows.push({
                    session_number: sessionNumber,
                    session_date: selectedDate,
                    session_time: selectedTime
                });
            }

            const extraNote = (document.getElementById('planRescheduleNote').value || '').trim();

            fetch('/dheergayu/public/api/confirm-treatment-plan.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'plan_id=' + encodeURIComponent(planId)
                    + '&action=update_schedule'
                    + '&schedule=' + encodeURIComponent(JSON.stringify(scheduleRows))
                    + '&note=' + encodeURIComponent(extraNote)
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    closePlanRescheduleModal();
                    alert('Schedule updated successfully. You can now click Confirm & Pay.');
                    window.location.href = 'patient_appointments.php?tab=treatment-plans';
                } else {
                    alert('Error: ' + (data.message || 'Failed to update schedule'));
                }
            })
            .catch(function(err) {
                console.error('Error:', err);
                alert('Network error. Please try again.');
            });
        });
    }
});

function formatTime(time) {
    const parts = time.split(':');
    const hours = parseInt(parts[0]);
    const minutes = parts[1];
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 || 12;
    return displayHours + ':' + minutes + ' ' + period;
}

function closeEditModal(resetFlag = true) {
    document.getElementById('editModal').style.display = 'none';
    if (resetFlag) {
        treatmentRescheduleThenPay = false;
    }
}

// Form submission handler
const editForm = document.getElementById('editForm');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('editId').value;
        const type = document.getElementById('editType').value;
        const date = document.getElementById('editDate').value;
        const timeSelect = document.getElementById('editTime');
        const time = timeSelect.value;
        
        if (type === 'treatment') {
            closeEditModal(false);

            const selectedOption = timeSelect.options[timeSelect.selectedIndex];
            const slotId = selectedOption.getAttribute('data-slot-id');
            
            const formData = new FormData();
            formData.append('action', 'reschedule');
            formData.append('booking_id', id);
            formData.append('new_slot_id', slotId);
            formData.append('new_date', date);
            
            fetch('/dheergayu/public/api/treatment-booking-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    if (treatmentRescheduleThenPay) {
                        window.location.href = 'appointment_payment.php?appointment_id=' + encodeURIComponent(id) + '&type=treatment';
                    } else {
                        alert('Treatment rescheduled successfully');
                        location.reload();
                    }
                } else {
                    alert('Error: ' + (data.error || data.message || 'Failed to reschedule'));
                }
            })
            .catch(function(error) {
                alert('Network error: ' + error.message);
            })
            .finally(function() {
                treatmentRescheduleThenPay = false;
            });
        } else {
            closeEditModal();

            const formData = new FormData();
            formData.append('id', id);
            formData.append('type', type);
            formData.append('date', date);
            formData.append('time', time);

            fetch('/dheergayu/public/api/update-appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    alert('Appointment updated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update'));
                }
            })
            .catch(function(error) {
                alert('Network error: ' + error.message);
            });
        }
    });
}

function cancelAppointment(id, type) {
    currentCancelId = id;
    currentCancelType = type;
    document.getElementById('cancelModal').style.display = 'block';
}

function confirmCancel() {
    if (currentCancelType === 'treatment') {
        const formData = new FormData();
        formData.append('action', 'cancel');
        formData.append('booking_id', currentCancelId);
        formData.append('reason', 'Cancelled by patient');

        fetch('/dheergayu/public/api/treatment-booking-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Treatment booking cancelled successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.error || data.message || 'Failed to cancel'));
            }
        });
    } else {
        const formData = new FormData();
        formData.append('id', currentCancelId);
        formData.append('type', currentCancelType);

        fetch('/dheergayu/public/api/cancel-appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Appointment cancelled successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to cancel'));
            }
        });
    }
    closeCancelModal();
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
    currentCancelId = null;
    currentCancelType = null;
}

function payNow(id, type) {
    window.location.href = 'appointment_payment.php?appointment_id=' + id + '&type=' + type;
}

// Modal close on outside click
window.addEventListener('click', function(e) {
    const editModal = document.getElementById('editModal');
    const cancelModal = document.getElementById('cancelModal');
    const planRescheduleModal = document.getElementById('planRescheduleModal');
    const prescriptionModal = document.getElementById('prescriptionModal');
    if (e.target === editModal) closeEditModal();
    if (e.target === cancelModal) closeCancelModal();
    if (e.target === planRescheduleModal) closePlanRescheduleModal();
    if (e.target === prescriptionModal) closePrescriptionModal();
});

// --- Prescription Modal ---
function viewPrescription(appointmentId) {
    const modal = document.getElementById('prescriptionModal');
    const content = document.getElementById('prescriptionContent');
    content.innerHTML = '<div style="text-align:center;padding:30px;color:#888;">Loading...</div>';
    modal.style.display = 'block';

    fetch('/dheergayu/app/Controllers/ConsultationFormController.php?action=get_consultation_form&appointment_id=' + appointmentId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data || !data.form) {
                content.innerHTML = '<div style="text-align:center;padding:30px;color:#888;">No prescription found for this consultation.</div>';
                return;
            }
            const form = data.form;
            const merged = data.merged || {};

            // Parse prescribed products
            let productsHtml = '<em style="color:#888;">None</em>';
            try {
                const products = JSON.parse(form.personal_products || '[]');
                if (products && products.length > 0) {
                    productsHtml = '<ul style="margin:6px 0 0 0;padding-left:20px;">';
                    products.forEach(function(p) {
                        productsHtml += '<li><strong>' + escHtml(p.product) + '</strong> &times; ' + escHtml(String(p.qty)) + '</li>';
                    });
                    productsHtml += '</ul>';
                }
            } catch(e) {}

            const treatmentText = merged.recommended_treatment || form.recommended_treatment || 'No treatment needed';
            const notesText = form.notes ? form.notes.trim() : '';

            content.innerHTML =
                '<div style="border-bottom:1px solid #e2e8f0;padding-bottom:14px;margin-bottom:14px;">' +
                    '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px;">' +
                        '<div><span style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:.5px;">Patient</span>' +
                            '<div style="font-weight:600;color:#1a202c;">' + escHtml(form.first_name + ' ' + form.last_name) + '</div></div>' +
                        '<div><span style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:.5px;">Age / Gender</span>' +
                            '<div style="font-weight:600;color:#1a202c;">' + escHtml(String(form.age)) + ' / ' + escHtml(form.gender) + '</div></div>' +
                    '</div>' +
                '</div>' +
                '<div style="margin-bottom:14px;">' +
                    '<div style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Diagnosis</div>' +
                    '<div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;padding:10px 14px;color:#7c2d12;font-weight:500;">' + escHtml(form.diagnosis) + '</div>' +
                '</div>' +
                '<div style="margin-bottom:14px;">' +
                    '<div style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Prescribed Products</div>' +
                    '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px 14px;color:#14532d;">' + productsHtml + '</div>' +
                '</div>' +
                '<div style="margin-bottom:14px;">' +
                    '<div style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Recommended Treatment</div>' +
                    '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px 14px;color:#1e40af;">' + escHtml(treatmentText) + '</div>' +
                '</div>' +
                (notesText ? '<div style="margin-bottom:6px;">' +
                    '<div style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Doctor\'s Notes</div>' +
                    '<div style="background:#fafafa;border:1px solid #e2e8f0;border-radius:6px;padding:10px 14px;color:#4a5568;">' + escHtml(notesText) + '</div>' +
                '</div>' : '');
        })
        .catch(function() {
            content.innerHTML = '<div style="text-align:center;padding:30px;color:#e53e3e;">Failed to load prescription. Please try again.</div>';
        });
}

function closePrescriptionModal() {
    document.getElementById('prescriptionModal').style.display = 'none';
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

</script>
</body>
</html>