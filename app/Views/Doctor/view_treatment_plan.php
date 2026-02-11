<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

$user_role = strtolower($_SESSION['role'] ?? $_SESSION['user_type'] ?? '');
if (!isset($_SESSION['user_id']) || ($user_role !== 'doctor' && $user_role !== 'staff')) {
    die('Access denied');
}

$plan_id = intval($_GET['plan_id'] ?? 0);

if (!$plan_id) {
    die('Invalid plan ID');
}

// Get treatment plan details
$query = "
    SELECT 
        tp.*,
        p.first_name,
        p.last_name,
        p.email,
        tl.treatment_name,
        tl.price as unit_price,
        c.appointment_date as consultation_date,
        c.phone
    FROM treatment_plans tp
    LEFT JOIN patients p ON tp.patient_id = p.id
    LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
    LEFT JOIN consultations c ON tp.appointment_id = c.id
    WHERE tp.plan_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $plan_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    die('Treatment plan not found');
}

// Get all sessions
$sessions_query = "
    SELECT * FROM treatment_sessions 
    WHERE plan_id = ? 
    ORDER BY session_number
";
$stmt = $conn->prepare($sessions_query);
$stmt->bind_param('i', $plan_id);
$stmt->execute();
$sessions_result = $stmt->get_result();
$sessions = [];
while ($row = $sessions_result->fetch_assoc()) {
    $sessions[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plan Details - Plan #<?= $plan_id ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px 12px 0 0; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 14px; }
        .section { padding: 25px 30px; border-bottom: 1px solid #e5e7eb; }
        .section:last-child { border-bottom: none; }
        .section-title { font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ''; width: 4px; height: 20px; background: #667eea; border-radius: 2px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .info-item { }
        .info-label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .info-value { font-size: 16px; color: #1f2937; font-weight: 500; }
        .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-badge.pending { background: #fef3c7; color: #92400e; }
        .status-badge.confirmed { background: #d1fae5; color: #065f46; }
        .status-badge.changerequested { background: #fed7d7; color: #991b1b; }
        .status-badge.inprogress { background: #dbeafe; color: #1e40af; }
        .sessions-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .sessions-table th { background: #f9fafb; padding: 12px; text-align: left; font-size: 12px; color: #6b7280; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
        .sessions-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; }
        .sessions-table tr:last-child td { border-bottom: none; }
        .session-status { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .session-status.pending { background: #fef3c7; color: #92400e; }
        .session-status.confirmed { background: #d1fae5; color: #065f46; }
        .session-status.completed { background: #dbeafe; color: #1e40af; }
        .alert { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 6px; margin-top: 15px; }
        .alert strong { color: #856404; display: block; margin-bottom: 5px; }
        .alert p { color: #856404; font-size: 14px; line-height: 1.5; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        .actions { display: flex; gap: 10px; margin-top: 20px; }
        .progress-container { background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden; margin-top: 10px; }
        .progress-bar { background: linear-gradient(90deg, #10b981, #34d399); height: 100%; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Treatment Plan #<?= $plan_id ?></h1>
            <p>Created on <?= date('F d, Y', strtotime($plan['created_at'])) ?></p>
        </div>

        <div class="section">
            <div class="section-title">Patient Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Patient Name</div>
                    <div class="info-value"><?= htmlspecialchars($plan['first_name'] . ' ' . $plan['last_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact</div>
                    <div class="info-value"><?= htmlspecialchars($plan['email']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Plan Status</div>
                    <div class="info-value">
                        <span class="status-badge <?= strtolower($plan['status']) ?>">
                            <?= $plan['status'] ?>
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Status</div>
                    <div class="info-value"><?= $plan['payment_status'] ?></div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Treatment Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Treatment Type</div>
                    <div class="info-value"><?= htmlspecialchars($plan['treatment_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Diagnosis</div>
                    <div class="info-value"><?= htmlspecialchars($plan['diagnosis']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Sessions</div>
                    <div class="info-value"><?= $plan['total_sessions'] ?> sessions (<?= $plan['sessions_per_week'] ?>x per week)</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Start Date</div>
                    <div class="info-value"><?= date('F d, Y', strtotime($plan['start_date'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Cost</div>
                    <div class="info-value">Rs <?= number_format($plan['total_cost'], 2) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Progress</div>
                    <div class="info-value">
                        <?php
                            $completed = 0;
                            foreach ($sessions as $s) {
                                if ($s['status'] === 'Completed') $completed++;
                            }
                            $progress = count($sessions) > 0 ? ($completed / count($sessions) * 100) : 0;
                        ?>
                        <?= $completed ?>/<?= count($sessions) ?> sessions completed
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($plan['change_requested']): ?>
                <div class="alert">
                    <strong>⚠️ Patient Requested Changes</strong>
                    <p><?= htmlspecialchars($plan['change_reason']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <div class="section-title">Session Schedule</div>
            <table class="sessions-table">
                <thead>
                    <tr>
                        <th>Session #</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td>Session <?= $session['session_number'] ?></td>
                            <td><?= date('l, M d, Y', strtotime($session['session_date'])) ?></td>
                            <td><?= date('g:i A', strtotime($session['session_time'])) ?></td>
                            <td>
                                <span class="session-status <?= strtolower($session['status']) ?>">
                                    <?= $session['status'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($session['notes'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="actions">
                <button class="btn btn-secondary" onclick="window.print()">Print Plan</button>
                <button class="btn btn-secondary" onclick="window.close()">Close</button>
                <?php if ($plan['change_requested']): ?>
                    <button class="btn btn-primary" onclick="handleChangeRequest()">Respond to Request</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function handleChangeRequest() {
        if (confirm('Would you like to modify this treatment plan?\n\nClick OK to reschedule sessions, or Cancel to discuss with patient.')) {
            window.location.href = 'reschedule_treatment_plan.php?plan_id=<?= $plan_id ?>';
        }
    }
    </script>
</body>
</html>