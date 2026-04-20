<?php
require_once __DIR__ . '/../../includes/auth_doctor.php';
require_once __DIR__ . '/../../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctordashboard.css">
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
        
        #treatment-plans-section {
            display: none;
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
    </style>
</head>
<body class="has-sidebar">

<?php
$db = $conn;

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get doctor info from users table
$stmt = $db->prepare("SELECT id, first_name, last_name, email FROM users WHERE id = ? AND role = 'doctor'");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$doctorInfo = $result->fetch_assoc();
$stmt->close();

if (!$doctorInfo) {
    die("Error: Doctor information not found for user ID " . $_SESSION['user_id']);
}

$doctorName = 'Dr. ' . $doctorInfo['last_name'];
$doctorUserId = $doctorInfo['id'];

// Get appointments
$stmt = $db->prepare("
    SELECT 
        c.id as appointment_id,
        c.patient_id,
        c.doctor_id,
        c.doctor_name,
        c.patient_no,
        COALESCE(CONCAT(p.first_name, ' ', p.last_name), c.patient_name, 'Unknown Patient') as patient_name,
        CONCAT(c.appointment_date, ' ', c.appointment_time) as appointment_datetime,
        c.appointment_date,
        c.appointment_time,
        c.status,
        c.notes as reason
    FROM consultations c
    LEFT JOIN patients p ON c.patient_id = p.id
    WHERE (c.doctor_id = ? OR c.doctor_name LIKE ?) 
    AND c.treatment_type = 'General Consultation'
    ORDER BY c.appointment_date DESC, c.appointment_time DESC
");

$doctorNamePattern = '%' . $doctorInfo['last_name'] . '%';
$stmt->bind_param('is', $doctorUserId, $doctorNamePattern);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();

$today = date('Y-m-d');
$deriveAppointmentStatus = function(array $apt) use ($today): string {
    $statusRaw = strtoupper(trim($apt['status'] ?? ''));
    $appointmentDate = substr((string)($apt['appointment_date'] ?? ''), 0, 10);

    // Treat future-dated "Completed" records as upcoming in dashboard views.
    if ($statusRaw === 'COMPLETED' && $appointmentDate !== '' && $appointmentDate > $today) {
        return 'UPCOMING';
    }

    if ($statusRaw === 'PENDING' || $statusRaw === 'CONFIRMED') {
        return 'UPCOMING';
    }
    if ($statusRaw === 'COMPLETED') {
        return 'COMPLETED';
    }
    if ($statusRaw === 'CANCELLED') {
        return 'CANCELLED';
    }

    return $statusRaw;
};

// Get treatment plans
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
    LEFT JOIN consultations c ON tp.appointment_id = c.id
    WHERE c.doctor_id = ?
    ORDER BY tp.created_at DESC
";

$stmt = $db->prepare($treatment_plans_query);
$stmt->bind_param('i', $doctorUserId);
$stmt->execute();
$treatment_plans_result = $stmt->get_result();
$treatment_plans = [];
while ($row = $treatment_plans_result->fetch_assoc()) {
    $treatment_plans[] = $row;
}
$stmt->close();

// Compute appointment counts
$totalAppointments = count($appointments);
$upcomingAppointments = 0;
$completedAppointments = 0;
$cancelledAppointments = 0;

foreach ($appointments as $apt) {
    $derivedStatus = $deriveAppointmentStatus($apt);
    if ($derivedStatus === 'UPCOMING') {
        $upcomingAppointments++;
    } elseif ($derivedStatus === 'COMPLETED') {
        $completedAppointments++;
    } elseif ($derivedStatus === 'CANCELLED') {
        $cancelledAppointments++;
    }
}

// Compute treatment plan counts
$totalPlans = count($treatment_plans);
$pending_plans = 0;
$confirmed_plans = 0;
$change_requested_plans = 0;
$inprogress_plans = 0;
$completed_plans = 0;

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
        case 'Completed':
            $completed_plans++;
            break;
    }
}
?>

<header class="header">
    <div class="header-top">
        <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
        <h1 class="header-title">Dheergayu</h1>
    </div>
    
    <nav class="navigation">
        <button class="nav-btn active" id="appointments-tab" onclick="showAppointments()">Appointments</button>
        <button class="nav-btn" id="treatment-plans-tab" onclick="showTreatmentPlans()">Treatment Plans</button>
        <a href="patienthistory.php" class="nav-btn">Patient History</a>
        <a href="doctorreport.php" class="nav-btn">Reports</a>
    </nav>
    
    <div class="user-section">
        <div class="user-icon" id="user-icon">👤</div>
        <span class="user-role"><?php echo htmlspecialchars($doctorName); ?></span>
        <div class="user-dropdown" id="user-dropdown">
            <a href="doctorprofile.php" class="profile-btn">Profile</a>
            <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
    <!-- Appointments Section -->
    <div id="appointments-section">
        <div class="search-container">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" placeholder="Search by patient name or number" class="search-input" id="search-input">
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?= $totalAppointments ?></div>
                <div class="stat-label">Total Appointments</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $upcomingAppointments ?></div>
                <div class="stat-label">Upcoming Appointments</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $completedAppointments ?></div>
                <div class="stat-label">Completed Appointments</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $cancelledAppointments ?></div>
                <div class="stat-label">Cancelled Appointments</div>
            </div>
        </div>

        <div class="tab-container">
            <button class="tab-btn active" data-tab="all">All Appointments</button>
            <button class="tab-btn" data-tab="today">Today's Appointments</button>
            <button class="tab-btn" data-tab="upcoming">Upcoming Appointments</button>
            <button class="tab-btn" data-tab="completed">Completed Appointments</button>
            <button class="tab-btn" data-tab="cancelled">Cancelled Appointments</button>
        </div>

        <div class="table-container">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient No.</th>
                        <th>Patient Name</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="appointments-tbody">
                    <?php if (!empty($appointments)) : ?>
                        <?php foreach ($appointments as $apt) : ?>
                            <?php
                                $derivedStatus = $deriveAppointmentStatus($apt);
                                if ($derivedStatus === 'UPCOMING') {
                                    $status = 'Upcoming';
                                } elseif ($derivedStatus === 'COMPLETED') {
                                    $status = 'Completed';
                                } elseif ($derivedStatus === 'CANCELLED') {
                                    $status = 'Cancelled';
                                } else {
                                    $status = $apt['status'];
                                }
                            ?>
                            <?php $isToday = ($apt['appointment_date'] === $today); ?>
                            <tr class="appointment-row <?= strtolower($status) ?>" data-status="<?= strtolower($status) ?>" data-today="<?= $isToday ? 'true' : 'false' ?>" data-datetime="<?= htmlspecialchars(($apt['appointment_date'] ?? '') . 'T' . ($apt['appointment_time'] ?? '00:00:00')) ?>">
                                <td><?= htmlspecialchars($apt['appointment_id']) ?></td>
                                <td><?= htmlspecialchars($apt['patient_no'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                                <td><?= htmlspecialchars($apt['appointment_datetime']) ?></td>
                                <td>
                                    <span class="status-badge <?= strtolower($status) ?>"><?= $status ?></span>
                                </td>
                                <td class="actions">
                                    <?php if ($status === 'Upcoming') : ?>
                                        <?php if ($isToday): ?>
                                            <button class="btn-start" onclick="window.location.href='doctorconsultform.php?appointment_id=<?= htmlspecialchars($apt['appointment_id']) ?>'">Start Consultation</button>
                                        <?php else: ?>
                                            <button class="btn-start" disabled style="opacity:0.5;cursor:not-allowed;" title="Only today's appointments can be started">Start Consultation</button>
                                            <span style="display:block;font-size:11px;color:#888;margin-top:4px;">Not today</span>
                                        <?php endif; ?>
                                        <button class="btn-cancel" onclick="showCancelReason(<?= $apt['appointment_id'] ?>)">Cancel</button>
                                        <button class="btn-cancel" style="background:#6c757d;" onclick="markDayUnavailable('<?= htmlspecialchars($apt['appointment_date']) ?>')">Cancel Day</button>
                                    <?php elseif ($status === 'Completed') : ?>
                                        <button class="btn-view" onclick="showConsultationModal(<?= $apt['appointment_id'] ?>)">View</button>
                                    <?php elseif ($status === 'Cancelled') : ?>
                                        <button class="btn-view" onclick="showCancelDetails('<?= htmlspecialchars(addslashes($apt['reason'] ?? 'No reason provided')) ?>')">View</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px;">
                            <p>No appointments found for <?= htmlspecialchars($doctorName) ?>.</p>
                            <p style="font-size:12px;color:#666;margin-top:10px;">
                                Logged in as User ID: <?= $doctorUserId ?><br>
                                When patients book appointments, they will appear here.
                            </p>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="pagination-container">
                <div class="pagination-info">
                    <span id="pagination-info">Showing <?= min(1, $totalAppointments) ?>-<?= min(10, $totalAppointments) ?> of <?= $totalAppointments ?> appointments</span>
                </div>
                <div class="pagination-controls">
                    <button id="prev-page" class="pagination-btn" disabled>Previous</button>
                    <span id="page-numbers"></span>
                    <button id="next-page" class="pagination-btn">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Treatment Plans Section -->
    <div id="treatment-plans-section">
        <h2 style="margin:20px 0;color:#333;font-size:24px;">Treatment Plans Management</h2>
        
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?= $totalPlans ?></div>
                <div class="stat-label">Total Plans</div>
            </div>
            <div class="stat-box" style="border-left:4px solid #ffc107;">
                <div class="stat-number"><?= $inprogress_plans ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-box" style="border-left:4px solid #28a745;">
                <div class="stat-number"><?= $completed_plans ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <div class="table-container">
            <table class="appointments-table">
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
                                    <?= $plan['total_booked_sessions'] ?> session(s)
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
                                </td>
                                <td>Rs <?= number_format($plan['total_cost'], 2) ?></td>
                                <td>
                                    <button class="btn-view" onclick="viewTreatmentPlan(<?= $plan['plan_id'] ?>)">View Details</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;">
                                <p style="color:#666;font-size:16px;">No treatment plans created yet</p>
                                <p style="color:#999;font-size:14px;margin-top:10px;">Treatment plans will appear here when you create them during consultations</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Consultation Modal -->
<div id="consultationModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div id="consultationModalContent" style="background:linear-gradient(135deg,#f8fafc 0%,#e3e6f3 100%);padding:0;border-radius:16px;box-shadow:0 4px 24px #333;max-width:520px;width:90%;margin:auto;position:relative;">
        <div style="padding:24px 32px 16px 32px;border-radius:16px 16px 0 0;background:#E6A85A;color:#fff;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="margin:0;font-size:22px;font-weight:600;">Consultation Details</h2>
            <button onclick="closeConsultationModal()" style="background:#fff;color:#E6A85A;border:none;border-radius:50%;width:32px;height:32px;font-size:20px;cursor:pointer;">&times;</button>
        </div>
        <div id="consultationFormData" style="padding:24px 32px 32px 32px;max-height:70vh;overflow-y:auto;"></div>
    </div>
</div>

<script>
var currentAppointmentId = null;

// Toggle between Appointments and Treatment Plans
function showAppointments() {
    document.getElementById('appointments-section').style.display = 'block';
    document.getElementById('treatment-plans-section').style.display = 'none';
    document.getElementById('appointments-tab').classList.add('active');
    document.getElementById('treatment-plans-tab').classList.remove('active');
}

function showTreatmentPlans() {
    document.getElementById('appointments-section').style.display = 'none';
    document.getElementById('treatment-plans-section').style.display = 'block';
    document.getElementById('appointments-tab').classList.remove('active');
    document.getElementById('treatment-plans-tab').classList.add('active');
}

function showConsultationModal(appointmentId) {
    currentAppointmentId = appointmentId;
    var modal = document.getElementById('consultationModal');
    var content = document.getElementById('consultationFormData');
    content.innerHTML = '<div style="text-align:center;padding:40px;">Loading...</div>';
    modal.style.display = 'flex';

    function renderProductsToText(val) {
        if (!val) return '';
        try {
            var arr = (typeof val === 'string') ? JSON.parse(val) : val;
            if (!Array.isArray(arr)) return '';
            return arr.map(p => (p.product || '') + (p.qty ? ' x' + p.qty : '')).join(', ');
        } catch (e) { return String(val || ''); }
    }

    function productsTextToJson(text) {
        if (!text) return [];
        return text.split(',').map(s => {
            var part = s.trim(); if (!part) return null;
            var m = part.match(/^(.*) x(\\d+)$/i);
            if (m) return {product: m[1].trim(), qty: parseInt(m[2],10)};
            return {product: part, qty: 1};
        }).filter(Boolean);
    }

    function htmlspecialchars(str){
        if (str === null || typeof str === 'undefined') return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;');
    }

    function renderView(data) {
        var formData = data.form || {};
        var allowed = {
            first_name: 'First Name', last_name: 'Last Name', age: 'Age', gender: 'Gender',
            diagnosis: 'Diagnosis', personal_products: 'Prescribed Products', recommended_treatment: 'Recommended Treatment', notes: 'Notes'
        };
        var html = '<table style="width:100%;border-collapse:separate;border-spacing:0 8px;">';
        for (var key in allowed) {
            var value = '';
            if (formData && typeof formData[key] !== 'undefined' && formData[key] !== null) value = formData[key];
            else if (data && data.merged && typeof data.merged[key] !== 'undefined' && data.merged[key] !== null) value = data.merged[key];
            else value = '';
            // sanitize accidental numeric-zero stored as string '0'
            if (String(value) === '0') value = '';
            // fallback to patient name when form value is empty
            if ((key === 'first_name' || key === 'last_name') && (!value || String(value).trim() === '')) {
                if (data && key === 'first_name' && data.patient_first_name) value = data.patient_first_name;
                if (data && key === 'last_name' && data.patient_last_name) value = data.patient_last_name;
            }

            if (key === 'personal_products') {
                try { value = renderProductsToText(value); } catch (e) { value = ''; }
            }

            html += '<tr style="background:#fff;box-shadow:0 2px 8px #e3e6f3;border-radius:8px;">';
            html += '<td style="font-weight:500;padding:10px 16px;color:#E6A85A;width:40%;">' + allowed[key] + '</td>';
            html += '<td style="padding:10px 16px;">' + (value || '') + '</td></tr>';
        }
        html += '</table>';
        html += '<div style="display:flex;justify-content:flex-end;margin-top:12px;gap:8px;">';
        // Only render Edit button when server indicates editing is allowed
        if (!(data && data.can_edit === false)) {
            html += '<button id="consultation-edit-btn" style="background:#fff;border:1px solid #ccc;padding:8px 12px;border-radius:6px;cursor:pointer;">Edit</button>';
        }
        html += '</div>';
        content.innerHTML = html;

        var editBtn = document.getElementById('consultation-edit-btn');
        if (editBtn && !editBtn.disabled) {
            editBtn.addEventListener('click', function(){
                // Navigate to the full consultation form page to allow editing there
                window.location.href = 'doctorconsultform.php?appointment_id=' + appointmentId;
            });
        }
    }

    function renderEdit(formData, dataMerged) {
        var first_name = (formData.first_name && String(formData.first_name) !== '0') ? formData.first_name : (dataMerged.patient_first_name || '');
        var last_name = (formData.last_name && String(formData.last_name) !== '0') ? formData.last_name : (dataMerged.patient_last_name || '');
        var age = (formData.age && String(formData.age) !== '0') ? formData.age : '';
        var gender = (formData.gender && String(formData.gender) !== '0') ? formData.gender : '';
        var diagnosis = formData.diagnosis || (dataMerged.merged && dataMerged.merged.diagnosis) || '';
        var personal_products = renderProductsToText(formData.personal_products || '');
        var recommended_treatment = formData.recommended_treatment || (dataMerged.merged && dataMerged.merged.recommended_treatment) || '';
        var notes = formData.notes || '';

        var html = '<form id="consultation-edit-form" style="width:100%;">';
        html += '<div style="display:flex;gap:8px;margin-bottom:10px;">';
        html += '<div style="flex:1"><label style="color:#E6A85A;font-weight:600">First Name</label><input name="first_name" value="' + htmlspecialchars(first_name) + '" style="width:100%;padding:8px;margin-top:6px;border-radius:6px;border:1px solid #ddd"/></div>';
        html += '<div style="flex:1"><label style="color:#E6A85A;font-weight:600">Last Name</label><input name="last_name" value="' + htmlspecialchars(last_name) + '" style="width:100%;padding:8px;margin-top:6px;border-radius:6px;border:1px solid #ddd"/></div>';
        html += '</div>';
        html += '<div style="display:flex;gap:8px;margin-bottom:10px;">';
        html += '<div style="flex:1"><label style="color:#E6A85A;font-weight:600">Age</label><input name="age" type="number" value="' + htmlspecialchars(age) + '" style="width:100%;padding:8px;margin-top:6px;border-radius:6px;border:1px solid #ddd"/></div>';
        html += '<div style="flex:1"><label style="color:#E6A85A;font-weight:600">Gender</label><select name="gender" style="width:100%;padding:8px;margin-top:6px;border-radius:6px;border:1px solid #ddd"><option value="">Select</option><option value="Male" ' + (gender==='Male' ? 'selected' : '') + '>Male</option><option value="Female" ' + (gender==='Female' ? 'selected' : '') + '>Female</option><option value="Other" ' + (gender==='Other' ? 'selected' : '') + '>Other</option></select></div>';
        html += '</div>';
        html += '<div style="margin-bottom:10px;"><label style="color:#E6A85A;font-weight:600">Diagnosis</label><textarea name="diagnosis" style="width:100%;min-height:60px;padding:8px;margin-top:6px;border-radius:6px;border:1px solid #ddd">' + htmlspecialchars(diagnosis) + '</textarea></div>';
        html += '<div style="margin-bottom:10px;"><label style="color:#E6A85A;font-weight:600">Prescribed Products (comma separated, e.g. "Ashwagandha Capsules x1, Oil x2")</label><textarea name="personal_products_text" style="width:100%;min-height:50px;padding:8px;margin-top:6px;border-radius:6px;border:1px solid #ddd">' + htmlspecialchars(personal_products) + '</textarea></div>';
        html += '<div style="margin-bottom:10px;"><label style="color:#E6A85A;font-weight:600">Recommended Treatment</label><textarea name="recommended_treatment" style="width:100%;min-height:50px;padding:8px;margin-top:6px;border-radius:6px;border:1px solid #ddd">' + htmlspecialchars(recommended_treatment) + '</textarea></div>';
        html += '<div style="margin-bottom:10px;"><label style="color:#E6A85A;font-weight:600">Notes</label><textarea name="notes" style="width:100%;min-height:40px;padding:8px;margin-top:6px;border-radius:6px;border:1px solid #ddd">' + htmlspecialchars(notes) + '</textarea></div>';
        html += '<div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">';
        html += '<button type="button" id="consultation-cancel-btn" style="background:#fff;border:1px solid #ccc;padding:8px 12px;border-radius:6px;cursor:pointer;margin-right:8px;">Cancel</button>';
        html += '<button id="consultation-save-btn" type="button" style="background:#E6A85A;color:#fff;border:none;padding:8px 14px;border-radius:6px;cursor:pointer;">Save</button></div>';
        html += '</form>';

        content.innerHTML = html;

        document.getElementById('consultation-cancel-btn').addEventListener('click', function(){ renderView(dataMerged); });

        document.getElementById('consultation-save-btn').addEventListener('click', function(){
            var form = document.getElementById('consultation-edit-form');
            // basic client-side validation
            if (!form.elements['first_name'].value.trim() || !form.elements['last_name'].value.trim() || !form.elements['age'].value.trim() || !form.elements['gender'].value) {
                alert('Please fill First name, Last name, Age and Gender.');
                return;
            }

            var fd = new FormData();
            fd.append('appointment_id', appointmentId);
            fd.append('first_name', form.elements['first_name'].value.trim());
            fd.append('last_name', form.elements['last_name'].value.trim());
            fd.append('age', form.elements['age'].value.trim());
            fd.append('gender', form.elements['gender'].value);
            fd.append('diagnosis', form.elements['diagnosis'].value.trim());
            fd.append('personal_products', JSON.stringify(productsTextToJson(form.elements['personal_products_text'].value)));
            fd.append('recommended_treatment', form.elements['recommended_treatment'].value.trim());
            fd.append('notes', form.elements['notes'].value.trim());

            fetch('/dheergayu/app/Controllers/ConsultationFormController.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(resp => {
                    if (resp && resp.status === 'success') {
                            alert('Consultation saved');
                            // refresh modal view with latest saved data so user can re-edit without full page reload
                            fetch('/dheergayu/app/Controllers/ConsultationFormController.php?action=get_consultation_form&appointment_id=' + appointmentId)
                                .then(r => r.text())
                                .then(function(text){
                                    try {
                                        var newData = JSON.parse(text);
                                    } catch (e) {
                                        content.innerHTML = '<div style="text-align:left;padding:16px;color:#e74c3c;white-space:pre-wrap;">Error parsing server response after save:\n' + htmlspecialchars(text.substring(0,2000)) + '</div>';
                                        setTimeout(function(){ closeConsultationModal(); location.reload(); }, 1800);
                                        return;
                                    }
                                    if (newData && Object.keys(newData).length > 0) {
                                        renderView(newData);
                                    } else {
                                        closeConsultationModal();
                                        location.reload();
                                    }
                                }).catch(function(){
                                    // fallback: close modal and reload
                                    closeConsultationModal();
                                    location.reload();
                                });
                        } else {
                        alert('Error: ' + (resp.message || 'Failed to save'));
                    }
                }).catch(() => { alert('Network error while saving'); });
        });
    }

    fetch('/dheergayu/app/Controllers/ConsultationFormController.php?action=get_consultation_form&appointment_id=' + appointmentId)
        .then(r => r.text())
        .then(text => {
            try {
                var data = JSON.parse(text);
            } catch (e) {
                content.innerHTML = '<div style="text-align:left;padding:16px;color:#e74c3c;white-space:pre-wrap;">Error parsing server response:\n' + htmlspecialchars(text.substring(0, 2000)) + '</div>';
                return;
            }
            if (data && Object.keys(data).length > 0) {
                renderView(data);
            } else {
                content.innerHTML = '<div style="text-align:center;padding:40px;color:#e74c3c;">No data found</div>';
            }
        })
        .catch((err) => {
            content.innerHTML = '<div style="text-align:center;padding:40px;color:#e74c3c;">Error loading data</div>';
        });
}

function closeConsultationModal() {
    document.getElementById('consultationModal').style.display = 'none';
}

function showCancelReason(appointmentId) {
    var reason = prompt('Please enter reason for cancellation:');
    if (!reason || !reason.trim()) return;
    
    fetch('/dheergayu/app/Controllers/AppointmentController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=cancel&appointment_id=' + appointmentId + '&reason=' + encodeURIComponent(reason)
    })
    .then(r => r.text())
    .then(result => {
        if (result === 'success') {
            alert('Appointment cancelled!');
            location.reload();
        } else {
            alert('Error cancelling appointment');
        }
    });
}

function markDayUnavailable(dateStr) {
    if (!dateStr) return;
    if (!confirm('Mark ' + dateStr + ' as unavailable for the full day?\n\nThis will cancel all active appointments for that date and block all slots.')) {
        return;
    }

    const formData = new FormData();
    formData.append('date', dateStr);

    fetch('/dheergayu/public/api/doctor-unavailable-day.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert('Day blocked successfully.\nCancelled appointments: ' + (data.cancelled_count || 0) + '\nLocked slots: ' + (data.locked_slots_count || 0));
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to block day'));
        }
    })
    .catch(function() {
        alert('Network error while blocking day');
    });
}

function showCancelDetails(reason) {
    alert('Cancellation Reason:\n' + reason);
}

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

document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-btn');
    const rows = document.querySelectorAll('.appointment-row');
    const search = document.getElementById('search-input');
    
    let currentPage = 1, rowsPerPage = 10, filteredRows = Array.from(rows);
    
    function updatePagination() {
        const total = filteredRows.length;
        const pages = Math.ceil(total / rowsPerPage) || 1;
        const start = total > 0 ? ((currentPage - 1) * rowsPerPage + 1) : 0;
        const end = Math.min(currentPage * rowsPerPage, total);
        
        document.getElementById('pagination-info').textContent = `Showing ${start}-${end} of ${total} appointments`;
        
        const pageNums = document.getElementById('page-numbers');
        pageNums.innerHTML = '';
        for (let i = 1; i <= pages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'pagination-btn page-number' + (i === currentPage ? ' active' : '');
            btn.onclick = () => showPage(i);
            pageNums.appendChild(btn);
        }
        
        document.getElementById('prev-page').disabled = currentPage === 1;
        document.getElementById('next-page').disabled = currentPage === pages;
    }
    
    function showPage(page) {
        currentPage = page;
        rows.forEach(r => r.style.display = 'none');
        filteredRows.slice((page-1)*rowsPerPage, page*rowsPerPage).forEach(r => r.style.display = '');
        updatePagination();
    }
    
    function filter() {
        const term = search.value.toLowerCase();
        const tab = document.querySelector('.tab-btn.active').dataset.tab;
        filteredRows = Array.from(rows).filter(r => {
            const name = r.cells[2].textContent.toLowerCase();
            const num = r.cells[1].textContent.toLowerCase();
            const status = r.dataset.status;
            const isToday = r.dataset.today === 'true';
            const matchSearch = name.includes(term) || num.includes(term);
            if (tab === 'today') return isToday && status === 'upcoming' && matchSearch;
            return (tab === 'all' || status === tab) && matchSearch;
        });

        if (tab === 'upcoming' || tab === 'today') {
            filteredRows.sort((a, b) => {
                const aTime = new Date(a.dataset.datetime || '').getTime();
                const bTime = new Date(b.dataset.datetime || '').getTime();
                return aTime - bTime;
            });
        } else if (tab === 'all') {
            filteredRows.sort((a, b) => {
                const aTime = new Date(a.dataset.datetime || '').getTime();
                const bTime = new Date(b.dataset.datetime || '').getTime();
                return aTime - bTime;
            });
        }

        currentPage = 1;
        showPage(1);
    }
    
    tabs.forEach(b => b.onclick = function() {
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        filter();
    });
    
    search.oninput = filter;
    document.getElementById('prev-page').onclick = () => { if (currentPage > 1) showPage(currentPage - 1); };
    document.getElementById('next-page').onclick = () => { 
        const pages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < pages) showPage(currentPage + 1);
    };
    
    filter();
});

// Check URL parameters on page load to show treatment plans if needed
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get('view');
    
    if (view === 'treatment-plans') {
        showTreatmentPlans();
    }
});

</script>
</body>
</html>