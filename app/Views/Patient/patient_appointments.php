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
$appointments = $model->getAllAppointments($patient_id);

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
                <button class="tab-btn" onclick="showTab('cancelled')">Cancelled</button>
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
                        <option value="08:00">08:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="16:00">04:00 PM</option>
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
                    <li><a href="#" class="footer-link">About Us</a></li>
                    <li><a href="channeling.php" class="footer-link">Booking</a></li>
                    <li><a href="#" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="social-link">f Facebook</a></li>
                    <li><a href="#" class="social-link">x X</a></li>
                    <li><a href="#" class="social-link">in LinkedIn</a></li>
                    <li><a href="#" class="social-link">📷 Instagram</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        let currentCancelId = null;
        let currentCancelType = null;

        function showTab(tabName) {
            document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            
            document.getElementById(tabName + '-tab').style.display = 'block';
            event.target.classList.add('active');
        }

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
            
            loadEditSlots(date);
            editModal.style.display = 'block';
        }

        function loadEditSlots(date) {
            if (!date) return;
            
            fetch(`/dheergayu/public/api/available-slots.php?date=${date}`)
                .then(res => res.json())
                .then(data => {
                    const timeSelect = document.getElementById('editTime');
                    const currentTime = timeSelect.value;
                    
                    timeSelect.innerHTML = '<option value="">Select Time</option>';
                    
                    if (data.slots && data.slots.length > 0) {
                        data.slots.forEach(slot => {
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

        document.getElementById('editDate').addEventListener('change', function() {
            loadEditSlots(this.value);
        });

        function formatTime(time) {
            const [hours, minutes] = time.split(':');
            const h = parseInt(hours);
            const period = h >= 12 ? 'PM' : 'AM';
            const displayHours = h % 12 || 12;
            return `${displayHours}:${minutes} ${period}`;
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('id', document.getElementById('editId').value);
            formData.append('type', document.getElementById('editType').value);
            formData.append('date', document.getElementById('editDate').value);
            formData.append('time', document.getElementById('editTime').value);

            fetch('/dheergayu/public/api/update-appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment updated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update'));
                }
            })
            .catch(error => {
                alert('Network error: ' + error.message);
            });
            closeEditModal();
        });

        function cancelAppointment(id, type) {
            currentCancelId = id;
            currentCancelType = type;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function confirmCancel() {
            const formData = new FormData();
            formData.append('id', currentCancelId);
            formData.append('type', currentCancelType);

            fetch('/dheergayu/public/api/cancel-appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment cancelled successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to cancel'));
                }
            });
            closeCancelModal();
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
            currentCancelId = null;
            currentCancelType = null;
        }

        function payNow(id, type) {
            window.location.href = `payment.php?appointment_id=${id}&type=${type}`;
        }

        window.addEventListener('click', function(e) {
            const editModal = document.getElementById('editModal');
            const cancelModal = document.getElementById('cancelModal');
            if (e.target === editModal) closeEditModal();
            if (e.target === cancelModal) closeCancelModal();
        });
    </script>
</body>
</html>