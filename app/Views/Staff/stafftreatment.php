<?php
session_start();

// Check if user is logged in
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

// Fetch treatments from database
$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}

// Get staff user ID and name
$staffUserId = $_SESSION['user_id'] ?? null;
$staffName = $_SESSION['user_name'] ?? '';

// Get staff's assigned treatment type from StaffModel
require_once __DIR__ . '/../../Models/StaffModel.php';
$staffModel = new StaffModel($db);
$staffAssignment = $staffModel->getStaffRoomAssignment($staffName);
$assignedTreatmentType = $staffAssignment['treatment_type'] ?? null;

// Ensure treatment_plans has assigned_staff_id (set when staff confirms an assignment)
$chk = @$db->query("SHOW COLUMNS FROM treatment_plans LIKE 'assigned_staff_id'");
if ($chk && $chk->num_rows === 0) {
    @$db->query("ALTER TABLE treatment_plans ADD COLUMN assigned_staff_id INT NULL");
}

// Fetch treatment plans for this specific staff member
// Filter by treatment type that matches staff's assignment
$treatment_plans_query = "
    SELECT 
        tp.*,
        p.first_name,
        p.last_name,
        p.email,
        tl.treatment_name,
        tl.price as treatment_price,
        (SELECT COUNT(*) FROM treatment_sessions WHERE plan_id = tp.plan_id) as total_booked_sessions,
        (SELECT COUNT(*) FROM treatment_sessions WHERE plan_id = tp.plan_id AND status = 'Completed') as completed_sessions
    FROM treatment_plans tp
    LEFT JOIN patients p ON tp.patient_id = p.id
    LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
    WHERE 1=1
";

// Show plans: by assigned treatment type OR plans explicitly assigned to this staff (they confirmed)
if ($assignedTreatmentType) {
    $treatment_plans_query .= " AND (tl.treatment_name = ? OR tp.assigned_staff_id = ?)";
}

$treatment_plans_query .= " ORDER BY tp.created_at DESC";

$stmt = $db->prepare($treatment_plans_query);
if ($assignedTreatmentType) {
    $stmt->bind_param('si', $assignedTreatmentType, $staffUserId);
} else {
    $treatment_plans_query_alt = "
        SELECT tp.*, p.first_name, p.last_name, p.email, tl.treatment_name, tl.price as treatment_price,
            (SELECT COUNT(*) FROM treatment_sessions WHERE plan_id = tp.plan_id) as total_booked_sessions,
            (SELECT COUNT(*) FROM treatment_sessions WHERE plan_id = tp.plan_id AND status = 'Completed') as completed_sessions
        FROM treatment_plans tp
        LEFT JOIN patients p ON tp.patient_id = p.id
        LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
        WHERE tp.assigned_staff_id = ?
        ORDER BY tp.created_at DESC
    ";
    $stmt = $db->prepare($treatment_plans_query_alt);
    $stmt->bind_param('i', $staffUserId);
}
$stmt->execute();
$treatment_plans_result = $stmt->get_result();
$treatment_plans = [];
while ($row = $treatment_plans_result->fetch_assoc()) {
    $treatment_plans[] = $row;
}
$stmt->close();

// Compute treatment plan counts
$totalPlans = count($treatment_plans);
$pending_plans = 0;
$confirmed_plans = 0;
$change_requested_plans = 0;
$inprogress_plans = 0;

foreach ($treatment_plans as $plan) {
    switch ($plan['status']) {
        case 'Pending':
            $pending_plans++;
            break;
        case 'Confirmed':
            $confirmed_plans++;
            break;
        case 'ChangeRequested':
            $change_requested_plans++;
            break;
        case 'InProgress':
            $inprogress_plans++;
            break;
    }
}

// Pending assignments offered to this staff (patient confirmed; staff must confirm to take it)
$staff_offers = [];
@$db->query("CREATE TABLE IF NOT EXISTS treatment_plan_staff_offer (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    treatment_id INT NOT NULL,
    primary_staff1_id INT NOT NULL,
    primary_staff2_id INT NOT NULL,
    backup_staff_id INT NOT NULL,
    assigned_staff_id INT NULL,
    primary1_declined TINYINT(1) NOT NULL DEFAULT 0,
    primary2_declined TINYINT(1) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    UNIQUE KEY one_offer_per_plan (plan_id),
    INDEX idx_offer_staff (primary_staff1_id, primary_staff2_id, backup_staff_id),
    INDEX idx_offer_status (status)
)");
$offers_stmt = $db->prepare("
    SELECT o.id AS offer_id, o.plan_id, o.treatment_id, o.primary_staff1_id, o.primary_staff2_id, o.backup_staff_id,
           o.assigned_staff_id, o.primary1_declined, o.primary2_declined, o.status AS offer_status,
           tp.patient_id, tp.total_sessions, tp.diagnosis, tp.total_cost,
           tl.treatment_name, p.first_name, p.last_name,
           u.first_name AS assigned_first_name, u.last_name AS assigned_last_name,
           (SELECT ts.session_date FROM treatment_sessions ts WHERE ts.plan_id = tp.plan_id ORDER BY ts.session_number ASC LIMIT 1) AS first_session_date,
           (SELECT ts.session_time FROM treatment_sessions ts WHERE ts.plan_id = tp.plan_id ORDER BY ts.session_number ASC LIMIT 1) AS first_session_time
    FROM treatment_plan_staff_offer o
    JOIN treatment_plans tp ON o.plan_id = tp.plan_id
    LEFT JOIN treatment_list tl ON o.treatment_id = tl.treatment_id
    LEFT JOIN patients p ON tp.patient_id = p.id
    LEFT JOIN users u ON o.assigned_staff_id = u.id
    WHERE (o.primary_staff1_id = ? OR o.primary_staff2_id = ? OR o.backup_staff_id = ?)
    ORDER BY o.status ASC, o.created_at DESC
");
if ($offers_stmt) {
    $offers_stmt->bind_param('iii', $staffUserId, $staffUserId, $staffUserId);
    $offers_stmt->execute();
    $offers_res = $offers_stmt->get_result();
    while ($row = $offers_res->fetch_assoc()) {
        $row['my_role'] = '';
        if ((int)$row['primary_staff1_id'] === (int)$staffUserId) $row['my_role'] = 'primary1';
        elseif ((int)$row['primary_staff2_id'] === (int)$staffUserId) $row['my_role'] = 'primary2';
        else $row['my_role'] = 'backup';
        $row['backup_can_confirm'] = ($row['primary1_declined'] && $row['primary2_declined']);
        $row['assigned_staff_name'] = '';
        if (!empty($row['assigned_staff_id'])) {
            $row['assigned_staff_name'] = trim(($row['assigned_first_name'] ?? '') . ' ' . ($row['assigned_last_name'] ?? ''));
            if ($row['assigned_staff_name'] === '') $row['assigned_staff_name'] = 'Staff #' . $row['assigned_staff_id'];
        }
        $row['is_assigned_to_me'] = ((int)($row['assigned_staff_id'] ?? 0) === (int)$staffUserId);
        $staff_offers[] = $row;
    }
    $offers_stmt->close();
}

$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Schedule - Ayurvedic System</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/stafftreatment.css?v=1.1">
    <style>
        .badge {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 5px;
            font-weight: bold;
        }
        
        .status-badge.changerequested {
            background: #ff9800;
            color: white;
        }
        
        
        .progress-bar-container {
            background: #e0e0e0;
            height: 8px;
            border-radius: 4px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #28a745, #20c997);
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }


        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }

        .stat-box {
            flex: 1;
            background: white;
            padding: 25px 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #E6A85A;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #E6A85A;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
    </style>
</head>
<body class="has-sidebar">
    <!-- Header with ribbon style -->
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <button class="nav-btn active">
                Treatment Schedule
                <?php if ($pending_plans + $change_requested_plans > 0): ?>
                    <span class="badge"><?= $pending_plans + $change_requested_plans ?></span>
                <?php endif; ?>
            </button>
            <a href="staffappointment.php" class="nav-btn">Appointment</a>
            <a href="staffhomeReports.php" class="nav-btn">Reports</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Staff</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="staffprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Assignments offered to you (patient confirmed; you confirm to take it) -->
        <?php if (!empty($staff_offers)): ?>
        <div class="assignments-offered" style="margin-bottom:30px;background:#e8f5e9;border:1px solid #81c784;border-radius:10px;padding:20px;">
            <h3 style="margin:0 0 15px 0;color:#2e7d32;">Treatment assignments</h3>
            <p style="color:#555;font-size:14px;margin-bottom:15px;">Plans offered to you. Confirm to take one, or see who has taken it.</p>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($staff_offers as $off): ?>
                <div class="offer-card" style="background:#fff;padding:16px;border-radius:8px;border-left:4px solid <?= !empty($off['assigned_staff_id']) ? '#2196f3' : '#E6A85A' ?>;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div>
                        <strong>Plan #<?= (int)$off['plan_id'] ?></strong> — <?= htmlspecialchars($off['treatment_name'] ?? 'Treatment') ?>
                        <span style="color:#666;font-size:13px;"> • <?= htmlspecialchars($off['first_name'] . ' ' . $off['last_name']) ?> • <?= (int)$off['total_sessions'] ?> session(s)</span>
                        <?php
                        $firstDate = trim($off['first_session_date'] ?? '');
                        $firstTime = trim($off['first_session_time'] ?? '');
                        if ($firstDate !== '' || $firstTime !== ''):
                            $dateStr = $firstDate ? date('M j, Y', strtotime($firstDate)) : '—';
                            $timeStr = $firstTime ? date('g:i A', strtotime($firstTime)) : '—';
                        ?>
                        <span style="display:block;font-size:13px;color:#333;margin-top:4px;">
                            📅 <?= $dateStr ?> &nbsp; ⏰ <?= $timeStr ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($off['assigned_staff_id'])): ?>
                            <span style="display:block;font-size:13px;margin-top:6px;font-weight:600;color:#1976d2;">
                                <?php if ($off['is_assigned_to_me']): ?>
                                    ✓ You are doing this treatment
                                <?php else: ?>
                                    Assigned to: <?= htmlspecialchars($off['assigned_staff_name']) ?>
                                <?php endif; ?>
                            </span>
                        <?php elseif ($off['my_role'] === 'backup'): ?>
                            <span style="display:block;font-size:12px;color:#ff9800;margin-top:4px;">
                                <?= $off['backup_can_confirm'] ? 'You can confirm now (primaries declined).' : 'Waiting for primary staff to respond.' ?>
                            </span>
                        <?php else: ?>
                            <span style="display:block;font-size:12px;color:#666;margin-top:4px;">You are primary staff — confirm or decline</span>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($off['assigned_staff_id'])): ?>
                    <div style="display:flex;gap:8px;">
                        <?php if ($off['my_role'] !== 'backup' || $off['backup_can_confirm']): ?>
                        <button type="button" class="action-btn complete-btn btn-confirm-assign" data-offer-id="<?= (int)$off['offer_id'] ?>">I'll do this</button>
                        <?php endif; ?>
                        <?php if ($off['my_role'] !== 'backup'): ?>
                        <button type="button" class="action-btn btn-decline-assign" style="background:#6c757d;color:#fff;" data-offer-id="<?= (int)$off['offer_id'] ?>">Can't do</button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Treatment Schedule Section (showing Treatment Plans) -->
        <div id="treatment-schedule-section">
            
            <?php if ($assignedTreatmentType): ?>
                <p style="color:#666;margin-bottom:20px;font-size:14px;">Showing treatment plans for: <strong><?= htmlspecialchars($assignedTreatmentType) ?></strong></p>
            <?php endif; ?>
            
            <div class="stats-container">
                <div class="stat-box">
                    <div class="stat-number"><?= $totalPlans ?></div>
                    <div class="stat-label">Total Plans</div>
                </div>
                <div class="stat-box" style="border-left:4px solid #ffc107;">
                    <div class="stat-number"><?= $pending_plans ?></div>
                    <div class="stat-label">Pending Confirmation</div>
                </div>
                <div class="stat-box" style="border-left:4px solid #ff9800;">
                    <div class="stat-number"><?= $change_requested_plans ?></div>
                    <div class="stat-label">Change Requested</div>
                </div>
                <div class="stat-box" style="border-left:4px solid #28a745;">
                    <div class="stat-number"><?= $confirmed_plans ?></div>
                    <div class="stat-label">Confirmed</div>
                </div>
            </div>

            <div class="table-container">
                <table class="treatment-table">
                    <thead>
                        <tr>
                            <th>Plan ID</th>
                            <th>Patient Name</th>
                            <th>Treatment</th>
                            <th>Sessions</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Total Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($treatment_plans)): ?>
                            <?php foreach ($treatment_plans as $plan): ?>
                                <?php
                                    $progress_percent = $plan['total_booked_sessions'] > 0 
                                        ? ($plan['completed_sessions'] / $plan['total_booked_sessions'] * 100) 
                                        : 0;
                                ?>
                                <tr>
                                    <td><?= $plan['plan_id'] ?></td>
                                    <td><?= htmlspecialchars($plan['first_name'] . ' ' . $plan['last_name']) ?></td>
                                    <td><?= htmlspecialchars($plan['treatment_name']) ?></td>
                                    <td>
                                        <?= $plan['total_sessions'] ?> sessions<br>
                                        <small style="color:#666;"><?= $plan['sessions_per_week'] ?>x per week</small>
                                    </td>
                                    <td>
                                        <?= $plan['completed_sessions'] ?>/<?= $plan['total_booked_sessions'] ?> completed
                                        <div class="progress-bar-container">
                                            <div class="progress-bar" style="width:<?= $progress_percent ?>%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= strtolower($plan['status']) ?>">
                                            <?= $plan['status'] ?>
                                        </span>
                                        <?php if ($plan['change_requested']): ?>
                                            <span style="display:block;font-size:11px;color:#ff9800;margin-top:3px;">
                                                ⚠️ Changes requested
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>Rs <?= number_format($plan['total_cost'], 2) ?></td>
                                    <td>
                                        <button class="action-btn complete-btn" onclick="viewTreatmentPlan(<?= $plan['plan_id'] ?>)">View Details</button>
                                        <?php if ($plan['change_requested']): ?>
                                            <button class="action-btn" style="margin-top:5px;background:#ff9800;" onclick="viewChangeRequest(<?= $plan['plan_id'] ?>, '<?= htmlspecialchars(addslashes($plan['change_reason'] ?? '')) ?>')">
                                                View Request
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center;padding:40px;">
                                    <p style="color:#666;font-size:16px;">No treatment plans found</p>
                                    <p style="color:#999;font-size:14px;margin-top:10px;">
                                        <?php if ($assignedTreatmentType): ?>
                                            No treatment plans assigned for <?= htmlspecialchars($assignedTreatmentType) ?> yet.
                                        <?php else: ?>
                                            Treatment plans will appear here when assigned to you.
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function viewTreatmentPlan(planId) {
            window.open(
                '/dheergayu/app/Views/Doctor/view_treatment_plan.php?plan_id=' + planId,
                'treatment_plan',
                'width=900,height=700'
            );
        }

        function viewChangeRequest(planId, reason) {
            var message = 'Patient Change Request\n\n';
            message += 'Plan ID: ' + planId + '\n\n';
            message += 'Requested Changes:\n' + reason;
            alert(message);
        }

        document.querySelectorAll('.btn-confirm-assign').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var offerId = this.getAttribute('data-offer-id');
                if (!offerId) return;
                this.disabled = true;
                var formData = new FormData();
                formData.append('action', 'confirm');
                formData.append('offer_id', offerId);
                fetch('/dheergayu/public/api/staff-treatment-assignment.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message || 'Failed');
                            btn.disabled = false;
                        }
                    })
                    .catch(function() { alert('Network error'); btn.disabled = false; });
            });
        });
        document.querySelectorAll('.btn-decline-assign').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var offerId = this.getAttribute('data-offer-id');
                if (!offerId) return;
                if (!confirm('Decline this assignment? Backup staff will be able to take it.')) return;
                this.disabled = true;
                var formData = new FormData();
                formData.append('action', 'decline');
                formData.append('offer_id', offerId);
                fetch('/dheergayu/public/api/staff-treatment-assignment.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message || 'Failed');
                            btn.disabled = false;
                        }
                    })
                    .catch(function() { alert('Network error'); btn.disabled = false; });
            });
        });
    </script>

    <style>
        .action-btn {
            background: #5d9b57;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
        }
        
        .action-btn:hover {
            background: #4a7c47;
        }
        
        .complete-btn {
            background: #28a745;
        }
        
        .complete-btn:hover {
            background: #218838;
        }
        
        .completed-text {
            color: #28a745;
            font-weight: 500;
            font-size: 12px;
        }
        
        .status-text {
            color: #6c757d;
            font-size: 12px;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</body>
</html>
