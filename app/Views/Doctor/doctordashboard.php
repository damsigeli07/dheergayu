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
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../patient/login.php');
    exit;
}

// Check if user is a doctor
$user_role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? $_SESSION['role'] ?? '');
if ($user_role !== 'doctor') {
    echo '<!DOCTYPE html>
<html>
<head><title>Access Denied</title>
<style>body{font-family:Arial;margin:50px;text-align:center;}
.error{max-width:500px;margin:0 auto;padding:30px;border:1px solid #ddd;border-radius:10px;}
.btn{display:inline-block;padding:10px 20px;background:#5cb85c;color:white;text-decoration:none;border-radius:5px;margin:5px;}</style>
</head>
<body>
<div class="error">
<h2 style="color:#d9534f;">Access Denied</h2>
<p>This page is for doctors only. Your current role: ' . htmlspecialchars($user_role) . '</p>
<a href="../patient/login.php" class="btn">Login</a>
<a href="../patient/home.php" class="btn">Home</a>
</div>
</body>
</html>';
    exit;
}

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

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
        id as appointment_id,
        patient_id,
        doctor_id,
        doctor_name,
        patient_no,
        patient_name,
        CONCAT(appointment_date, ' ', appointment_time) as appointment_datetime,
        appointment_date,
        appointment_time,
        status,
        notes as reason
    FROM consultations 
    WHERE (doctor_id = ? OR doctor_name LIKE ?) 
    AND treatment_type = 'General Consultation'
    ORDER BY appointment_date DESC, appointment_time DESC
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
    $statusRaw = strtoupper(trim($apt['status'] ?? ''));
    if ($statusRaw === 'PENDING' || $statusRaw === 'CONFIRMED') {
        $upcomingAppointments++;
    } elseif ($statusRaw === 'COMPLETED') {
        $completedAppointments++;
    } elseif ($statusRaw === 'CANCELLED') {
        $cancelledAppointments++;
    }
}

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
?>

<header class="header">
    <div class="header-top">
        <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
        <h1 class="header-title">Dheergayu</h1>
    </div>
    
    <nav class="navigation">
        <button class="nav-btn active" id="appointments-tab" onclick="showAppointments()">Appointments</button>
        <button class="nav-btn" id="treatment-plans-tab" onclick="showTreatmentPlans()">
            Treatment Plans
            <?php if ($pending_plans + $change_requested_plans > 0): ?>
                <span class="badge"><?= $pending_plans + $change_requested_plans ?></span>
            <?php endif; ?>
        </button>
        <a href="patienthistory.php" class="nav-btn">Patient History</a>
        <a href="doctorreport.php" class="nav-btn">Reports</a>
    </nav>
    
    <div class="user-section">
        <div class="user-icon" id="user-icon">👤</div>
        <span class="user-role"><?php echo htmlspecialchars($doctorName); ?></span>
        <div class="user-dropdown" id="user-dropdown">
            <a href="doctorprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
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
                                $statusUpper = strtoupper(trim($apt['status'] ?? ''));
                                if ($statusUpper === 'PENDING' || $statusUpper === 'CONFIRMED') {
                                    $status = 'Upcoming';
                                } elseif ($statusUpper === 'COMPLETED') {
                                    $status = 'Completed';
                                } elseif ($statusUpper === 'CANCELLED') {
                                    $status = 'Cancelled';
                                } else {
                                    $status = $apt['status'];
                                }
                            ?>
                            <tr class="appointment-row <?= strtolower($status) ?>" data-status="<?= strtolower($status) ?>">
                                <td><?= htmlspecialchars($apt['appointment_id']) ?></td>
                                <td><?= htmlspecialchars($apt['patient_no'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                                <td><?= htmlspecialchars($apt['appointment_datetime']) ?></td>
                                <td>
                                    <span class="status-badge <?= strtolower($status) ?>"><?= $status ?></span>
                                </td>
                                <td class="actions">
                                    <?php if ($status === 'Upcoming') : ?>
                                        <button class="btn-start" onclick="window.location.href='doctorconsultform.php?appointment_id=<?= htmlspecialchars($apt['appointment_id']) ?>'">Start Consultation</button>
                                        <button class="btn-cancel" onclick="showCancelReason(<?= $apt['appointment_id'] ?>)">Cancel</button>
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
                                    <button class="btn-view" onclick="viewTreatmentPlan(<?= $plan['plan_id'] ?>)">View Details</button>
                                    <?php if ($plan['change_requested']): ?>
                                        <button class="btn-start" style="margin-top:5px;" onclick="viewChangeRequest(<?= $plan['plan_id'] ?>, '<?= htmlspecialchars(addslashes($plan['change_reason'] ?? '')) ?>')">
                                            View Request
                                        </button>
                                    <?php endif; ?>
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
    
    fetch('/dheergayu/app/Controllers/ConsultationFormController.php?action=get_consultation_form&appointment_id=' + appointmentId)
        .then(r => r.json())
        .then(data => {
            if (data && Object.keys(data).length > 0) {
                var html = '<table style="width:100%;border-collapse:separate;border-spacing:0 8px;">';
                var allowed = {first_name:'First Name',last_name:'Last Name',age:'Age',gender:'Gender',
                              diagnosis:'Diagnosis',personal_products:'Prescribed Products',
                              recommended_treatment:'Recommended Treatment',notes:'Notes'};
                
                for (var key in data) {
                    if (data.hasOwnProperty(key) && key !== 'id' && key !== 'appointment_id' && allowed[key.toLowerCase()]) {
                        var value = data[key] || '';
                        if (key.toLowerCase() === 'personal_products') {
                            try {
                                var items = JSON.parse(value);
                                value = Array.isArray(items) ? items.map(p => (p.product || '') + (p.qty ? ' x'+p.qty : '')).join(', ') : 'None';
                            } catch(e) { value = value || 'None'; }
                        }
                        html += '<tr style="background:#fff;box-shadow:0 2px 8px #e3e6f3;border-radius:8px;">';
                        html += '<td style="font-weight:500;padding:10px 16px;color:#E6A85A;width:40%;">' + allowed[key.toLowerCase()] + '</td>';
                        html += '<td style="padding:10px 16px;">' + value + '</td></tr>';
                    }
                }
                content.innerHTML = html + '</table>';
            } else {
                content.innerHTML = '<div style="text-align:center;padding:40px;color:#e74c3c;">No data found</div>';
            }
        })
        .catch(() => {
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
            return (tab === 'all' || status === tab) && (name.includes(term) || num.includes(term));
        });
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
    
    updatePagination();
    if (filteredRows.length > 0) showPage(1);
});
</script>
</body>
</html>