
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

// Get treatment plans for patient (join treatment_list so treatment type and diagnosis show correctly)
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
    
    if ($status !== 'Cancelled') {
        echo '<div class="appointment-actions">';
        if (($apt['payment_status'] ?? 'Pending') !== 'Completed') {
            echo '<button class="action-btn btn-primary" onclick="payNow(' . $apt['id'] . ', \'' . $type . '\')">Pay Now</button>';
        }
        echo '<button class="action-btn btn-warning" onclick="editAppointment(' . $apt['id'] . ', \'' . $type . '\', \'' . $apt['appointment_date'] . '\', \'' . $apt['appointment_time'] . '\')">Edit</button>';
        echo '<button class="action-btn btn-danger" onclick="cancelAppointment(' . $apt['id'] . ', \'' . $type . '\')">Cancel</button>';
        echo '</div>';
    }
    
    echo '</div>';
}

function countAll($appointments) {
    return count($appointments['consultations'] ?? []) + count($appointments['treatments'] ?? []);
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
    <button class="tab-btn active" onclick="showTab('all')">
        All Appointments <span class="tab-badge"><?php echo countAll($appointments); ?></span>
    </button>
    <button class="tab-btn" onclick="showTab('upcoming')">
        Upcoming <span class="tab-badge"><?php echo countUpcoming($appointments); ?></span>
    </button>
    <button class="tab-btn" onclick="showTab('treatment-plans')">
        Treatment Plans <span class="tab-badge"><?php echo count($treatment_plans); ?></span>
    </button>
    <button class="tab-btn" onclick="showTab('cancelled')">
        Cancelled
    </button>
</div>

            <div class="tab-content">
                <!-- All Appointments -->
                <div id="all-tab" class="tab-panel" style="display: block;">
                    <?php 
                    $all = getAllAppointments($appointments);
                    if (empty($all)): 
                    ?>
                        <div class="empty-state">
                            <h3>No Appointments</h3>
                            <p>Book your first consultation or treatment today</p>
                            <div style="margin-top: 20px;">
                                <a href="channeling.php" class="book-btn">Book Consultation</a>
                                <a href="treatment.php" class="book-btn">Book Treatment</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="appointments-list">
                            <?php foreach ($all as $apt): ?>
                                <?php renderAppointmentCard($apt, $conn); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Appointments -->
                <div id="upcoming-tab" class="tab-panel" style="display: none;">
                    <?php 
                    $upcoming = getUpcomingAppointments($appointments);
                    if (empty($upcoming)): 
                    ?>
                        <div class="empty-state">
                            <h3>No Upcoming Appointments</h3>
                            <p>Book your first consultation or treatment today</p>
                            <div style="margin-top: 20px;">
                                <a href="channeling.php" class="book-btn">Book Consultation</a>
                                <a href="treatment.php" class="book-btn">Book Treatment</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="appointments-list">
                            <?php foreach ($upcoming as $apt): ?>
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
                        <div class="appointments-list">
                            <?php foreach ($treatment_plans as $plan): 
                                // Get sessions for this plan
                                $sessions_query = "SELECT * FROM treatment_sessions WHERE plan_id = ? ORDER BY session_number";
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
                                            <span class="detail-value"><?= $plan['total_sessions'] ?> sessions (<?= $plan['sessions_per_week'] ?>x per week)</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Start Date</span>
                                            <span class="detail-value"><?= date('M d, Y', strtotime($plan['start_date'])) ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Total Cost</span>
                                            <span class="detail-value">Rs <?= number_format($plan['total_cost'], 2) ?></span>
                                        </div>
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
                                            <?php foreach ($sessions as $session): ?>
                                                <div style="background:#fff;padding:12px;margin:8px 0;border-radius:6px;display:flex;justify-content:space-between;align-items:center;border-left:3px solid <?= $session['status'] === 'Completed' ? '#28a745' : ($session['status'] === 'Pending' ? '#ffc107' : '#17a2b8') ?>;">
                                                    <div>
                                                        <strong style="color:#333;">Session <?= $session['session_number'] ?></strong>
                                                        <div style="font-size:13px;color:#666;margin-top:4px;">
                                                            📅 <?= date('l, M d, Y', strtotime($session['session_date'])) ?> 
                                                            at ⏰ <?= date('g:i A', strtotime($session['session_time'])) ?>
                                                        </div>
                                                    </div>
                                                    <span class="status-badge status-<?= strtolower($session['status']) ?>" style="font-size:11px;">
                                                        <?= $session['status'] ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <?php if ($plan['status'] === 'Pending'): ?>
                                        <div class="appointment-actions" style="margin-top:20px;background:#fff3cd;padding:12px;border-radius:8px;">
                                            <p style="margin:0 0 12px 0;font-size:14px;color:#856404;">
                                                ⚠️ Please confirm your treatment schedule to proceed
                                            </p>
                                            <div style="display:flex;gap:10px;">
                                                <button class="action-btn btn-primary" onclick="confirmTreatmentPlan(<?= $plan['plan_id'] ?>)" style="flex:1;">
                                                    ✓ Confirm All & Pay (Rs <?= number_format($plan['total_cost'], 2) ?>)
                                                </button>
                                                <button class="action-btn btn-warning" onclick="requestPlanChange(<?= $plan['plan_id'] ?>)">
                                                    📝 Request Changes
                                                </button>
                                            </div>
                                        </div>
                                    <?php elseif ($plan['status'] === 'Confirmed' && $plan['payment_status'] === 'Pending'): ?>
                                        <div class="appointment-actions" style="margin-top:20px;">
                                            <button class="action-btn btn-primary" onclick="payTreatmentPlan(<?= $plan['plan_id'] ?>)">
                                                💳 Pay Now (Rs <?= number_format($plan['total_cost'], 2) ?>)
                                            </button>
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
                    $cancelled = getCancelledAppointments($appointments);
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

        <div style="text-align: center; margin-top: 30px;">
            <a href="channeling.php" class="book-new-btn">Book New Appointment</a>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Appointment</h3>
            <form id="editForm">
                <input type="hidden" id="editId">
                <input type="hidden" id="editType">
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
                    <button type="submit" class="action-btn btn-primary">Save Changes</button>
                    <button type="button" class="action-btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
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

    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best Ayurvedic wellness centers in your area!</p>
            </div>
            <div class="footer-column">
                <h3>OFFICE</h3>
                <p>Sri Lanka —</p>
                <p>123 Wellness Street</p>
                <p>Colombo, LK 00100</p>
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
                    <li><a href="#" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="social-link">Facebook</a></li>
                    <li><a href="#" class="social-link">X</a></li>
                    <li><a href="#" class="social-link">LinkedIn</a></li>
                    <li><a href="#" class="social-link">Instagram</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
// Fixed JavaScript for patient_appointments.php tab switching
// Replace the entire <script> section with this code

let currentCancelId = null;
let currentCancelType = null;

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
                window.location.href = 'payment.php?plan_id=' + planId + '&type=treatment_plan';
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

function payTreatmentPlan(planId) {
    window.location.href = 'payment.php?plan_id=' + planId + '&type=treatment_plan';
}

// Appointment editing functions
function editAppointment(id, type, date, time) {
    const editModal = document.getElementById('editModal');
    const editId = document.getElementById('editId');
    const editType = document.getElementById('editType');
    const editDate = document.getElementById('editDate');
    const editTime = document.getElementById('editTime');
    
    if (!editModal || !editId || !editType || !editDate || !editTime) {
        alert('Error: Edit form not properly loaded');
        return;
    }
    
    editId.value = id;
    editType.value = type;
    editDate.value = date;
    editTime.value = time;
    editDate.min = new Date().toISOString().split('T')[0];
    
    if (type === 'treatment') {
        loadTreatmentEditSlots(date, id);
    } else {
        loadEditSlots(date);
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

function loadEditSlots(date) {
    if (!date) return;
    
    fetch('/dheergayu/public/api/available-slots.php?date=' + date)
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
            
            if (type === 'treatment') {
                loadTreatmentEditSlots(this.value, id);
            } else {
                loadEditSlots(this.value);
            }
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

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
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
                    alert('Treatment rescheduled successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || data.message || 'Failed to reschedule'));
                }
            })
            .catch(function(error) {
                alert('Network error: ' + error.message);
            });
        } else {
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
        
        closeEditModal();
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
    window.location.href = 'payment.php?appointment_id=' + id + '&type=' + type;
}

// Modal close on outside click
window.addEventListener('click', function(e) {
    const editModal = document.getElementById('editModal');
    const cancelModal = document.getElementById('cancelModal');
    if (e.target === editModal) closeEditModal();
    if (e.target === cancelModal) closeCancelModal();
});

</script>
</body>
</html>