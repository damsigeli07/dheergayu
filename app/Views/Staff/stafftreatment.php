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

// If staff has assigned treatment type, filter by it
if ($assignedTreatmentType) {
    $treatment_plans_query .= " AND tl.treatment_name = ?";
}

$treatment_plans_query .= " ORDER BY tp.created_at DESC";

$stmt = $db->prepare($treatment_plans_query);
if ($assignedTreatmentType) {
    $stmt->bind_param('s', $assignedTreatmentType);
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
