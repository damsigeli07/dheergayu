<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

$orderId       = htmlspecialchars($_GET['order_id'] ?? '');
$appointmentId = (int)($_GET['appointment_id'] ?? 0);
$planId        = (int)($_GET['plan_id'] ?? 0);
$type          = htmlspecialchars($_GET['type'] ?? '');
$simulated     = isset($_GET['simulated']);

$description = '';
$appointmentDate = '';
$appointmentTime = '';

$planRow = null;
if ($type === 'treatment_plan' && $planId) {
    $stmt = $conn->prepare("
        SELECT tp.*, tl.treatment_name AS list_name
        FROM treatment_plans tp
        LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
        WHERE tp.plan_id = ? AND tp.patient_id = ?
    ");
    $stmt->bind_param('ii', $planId, $_SESSION['user_id']);
    $stmt->execute();
    $planRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($planRow) {
        $description = ($planRow['treatment_name'] ?? $planRow['list_name'] ?? 'Treatment') . ' — Treatment Plan';
        $appointmentDate = $planRow['start_date'] ?? '';
        $appointmentTime = '';
    }
}

// Fetch order details
$orderRow = null;
if ($orderId) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? LIMIT 1");
    $stmt->bind_param('s', $orderId);
    $stmt->execute();
    $orderRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch appointment details
$appointment = null;

if ($type === 'consultation' && $appointmentId) {
    $stmt = $conn->prepare("SELECT * FROM consultations WHERE id = ?");
    $stmt->bind_param('i', $appointmentId);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($appointment) {
        $description = 'Consultation with ' . ($appointment['doctor_name'] ?? 'Doctor');
        $appointmentDate = $appointment['appointment_date'];
        $appointmentTime = $appointment['appointment_time'];
    }
} elseif ($type === 'treatment' && $appointmentId) {
    $stmt = $conn->prepare("
        SELECT tb.*, tl.treatment_name, ts.slot_time
        FROM treatment_bookings tb
        LEFT JOIN treatment_list tl ON tb.treatment_id = tl.treatment_id
        LEFT JOIN treatment_slots ts ON tb.slot_id = ts.slot_id
        WHERE tb.booking_id = ?
    ");
    $stmt->bind_param('i', $appointmentId);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($appointment) {
        $description = ($appointment['treatment_name'] ?? 'Treatment') . ' Session';
        $appointmentDate = $appointment['booking_date'];
        $appointmentTime = $appointment['slot_time'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Dheergayu</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .success-container {
            background: white; border-radius: 20px; padding: 50px 40px;
            text-align: center; max-width: 520px; width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }
        .success-icon {
            width: 100px; height: 100px; background: #4CAF50; border-radius: 50%;
            margin: 0 auto 28px; display: flex; align-items: center; justify-content: center;
            animation: scaleIn .5s ease;
        }
        .success-icon::before { content:'\2713'; font-size:58px; color:white; font-weight:bold; }
        @keyframes scaleIn {
            0%  { transform:scale(0); }
            50% { transform:scale(1.1); }
            100%{ transform:scale(1); }
        }
        h1 { color:#333; font-size:2rem; margin-bottom:12px; }
        .order-box {
            background:#f5f5f5; padding:16px; border-radius:10px;
            margin:22px 0; font-family:monospace; font-size:1rem; color:#555;
        }
        .order-box strong { color:#333; }
        .info-grid { display:grid; gap:14px; margin:22px 0; }
        .info-box {
            background:#e8f5e9; border-left:4px solid #4CAF50;
            padding:14px; border-radius:5px; text-align:left;
        }
        .info-box h3 { color:#2e7d32; margin-bottom:6px; font-size:.95rem; }
        .info-box p  { color:#555; font-size:.87rem; line-height:1.5; }
        .detail-row  { display:flex; justify-content:space-between; padding:6px 0;
                       border-bottom:1px solid #f0f0f0; font-size:.92rem; }
        .detail-row:last-child { border:none; }
        .detail-label { color:#666; }
        .detail-value { font-weight:600; color:#333; }
        .btn {
            display:inline-block; padding:13px 34px;
            background:linear-gradient(135deg,#8B7355,#A0916B);
            color:white; text-decoration:none; border-radius:10px;
            font-weight:600; font-size:.95rem; transition:all .3s; margin:8px;
        }
        .btn:hover { transform:translateY(-2px); box-shadow:0 5px 20px rgba(139,115,85,.4); }
        .btn-sec { background:linear-gradient(135deg,#667eea,#764ba2); }
        .type-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
            margin-bottom: 8px;
        }
        .type-badge.consultation { background: #17a2b8; }
        .type-badge.treatment { background: #28a745; }
        .type-badge.treatment_plan { background: #6f42c1; }
    </style>
</head>
<body>
<div class="success-container">
    <div class="success-icon"></div>
    <h1>Payment Successful!</h1>

    <?php if ($type): ?>
    <?php
        $typeClass = preg_replace('/[^a-z0-9_-]/i', '', $type);
        $typeLabel = $type === 'treatment_plan' ? 'Treatment plan' : ucfirst(str_replace('_', ' ', $type));
    ?>
    <span class="type-badge <?= htmlspecialchars($typeClass) ?>"><?= htmlspecialchars($typeLabel) ?> payment</span>
    <?php endif; ?>

    <div class="order-box">
        <strong>Order ID:</strong> <?= $orderId ?: 'N/A' ?>
    </div>

    <?php if ($orderRow || $appointment || ($type === 'treatment_plan' && $planRow)): ?>
    <div style="background:#f9f9f9;border-radius:10px;padding:18px;margin:18px 0;text-align:left;">
        <?php if ($orderRow): ?>
        <div class="detail-row">
            <span class="detail-label">Amount Paid</span>
            <span class="detail-value">Rs. <?= number_format((float)$orderRow['amount'], 2) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Customer</span>
            <span class="detail-value"><?= htmlspecialchars($orderRow['customer_name'] ?? '-') ?></span>
        </div>
        <?php endif; ?>
        <?php if ($description): ?>
        <div class="detail-row">
            <span class="detail-label"><?php
                if ($type === 'consultation') echo 'Consultation';
                elseif ($type === 'treatment_plan') echo 'Treatment plan';
                else echo 'Treatment';
            ?></span>
            <span class="detail-value"><?= htmlspecialchars($description) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($appointmentDate): ?>
        <div class="detail-row">
            <span class="detail-label"><?= $type === 'treatment_plan' ? 'Start date' : 'Appointment Date' ?></span>
            <span class="detail-value"><?= date('M d, Y', strtotime($appointmentDate)) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($appointmentTime): ?>
        <div class="detail-row">
            <span class="detail-label">Time</span>
            <span class="detail-value"><?= date('h:i A', strtotime($appointmentTime)) ?></span>
        </div>
        <?php endif; ?>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value" style="color:#4CAF50;">Paid</span>
        </div>
    </div>
    <?php endif; ?>

    <div class="info-grid">
        <div class="info-box">
            <h3>&#x1F4E7; Confirmation</h3>
            <?php if ($type === 'treatment_plan'): ?>
            <p>Your treatment plan payment has been recorded. You can confirm the schedule and next steps from your appointments page.</p>
            <?php else: ?>
            <p>Your payment has been recorded. Your appointment is now confirmed.</p>
            <?php endif; ?>
        </div>
        <div class="info-box">
            <h3>&#x1F4C5; Reminder</h3>
            <?php if ($type === 'treatment_plan'): ?>
            <p>Session dates and staff assignment may appear under your treatment plan after you confirm with the clinic.</p>
            <?php else: ?>
            <p>Please arrive 10 minutes before your scheduled time.
               Bring any relevant medical records or previous prescriptions.</p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top:30px;">
        <a href="/dheergayu/app/Views/Patient/patient_appointments.php" class="btn">View Appointments</a>
        <a href="/dheergayu/app/Views/Patient/home.php" class="btn btn-sec">Back to Home</a>
    </div>
</div>
</body>
</html>
