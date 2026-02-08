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

// Fetch appointments from database - ALL appointments from ALL doctors
$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get ALL appointments from all doctors (no filter)
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
    WHERE treatment_type = 'General Consultation'
    ORDER BY appointment_date DESC, appointment_time DESC
");

$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
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

$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Ayurvedic System</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctordashboard.css">
    <style>
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            display: inline-block;
            border: 1px solid transparent;
        }
        .status-badge.upcoming {
            background: #e8f5e9;
            color: #7a5a2b;
            border-color: #f3e6d5;
        }
        .status-badge.completed {
            background: #e3f2fd;
            color: #1976d2;
            border-color: #bbdefb;
        }
        .status-badge.cancelled {
            background: #ffebee !important;
            color: #c62828 !important;
            border-color: #ffcdd2 !important;
        }
    </style>
<script>
var currentAppointmentId = null;

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
            const doctor = r.cells[3].textContent.toLowerCase();
            const status = r.dataset.status;
            return (tab === 'all' || status === tab) && (name.includes(term) || num.includes(term) || doctor.includes(term));
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
            <a href="staffhome.php" class="nav-btn">Home</a>
            <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
            <button class="nav-btn active">Appointment</button>
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
                            <th>Doctor Name</th>
                            <th>Date & Time</th>
                            <th>Status</th>
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
                                    <td><?= htmlspecialchars($apt['doctor_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($apt['appointment_datetime']) ?></td>
                                    <td>
                                        <span class="status-badge <?= strtolower($status) ?>"><?= $status ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="6" style="text-align:center;padding:40px;">
                                <p>No appointments found.</p>
                                <p style="font-size:12px;color:#666;margin-top:10px;">
                                    When patients book appointments with doctors, they will appear here.
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
</body>
</html>
