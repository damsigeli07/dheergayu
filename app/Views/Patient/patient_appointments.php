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
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="/dheergayu/public/assets/images/Patient/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div>
            <a href="home.php" class="back-btn">← Back to Home</a>
        </div>
    </header>

    <div class="container">
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
            document.getElementById('editId').value = id;
            document.getElementById('editType').value = type;
            document.getElementById('editDate').value = date;
            document.getElementById('editTime').value = time;
            document.getElementById('editDate').min = new Date().toISOString().split('T')[0];
            document.getElementById('editModal').style.display = 'block';
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

<?php
function getTreatmentPrice($conn, $treatment_type) {
    // Normalize treatment names for database matching
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
    
    // Check if it's directly a treatment from treatment_list
    $stmt = $conn->prepare("SELECT price FROM treatment_list WHERE treatment_name = ?");
    $stmt->bind_param("s", $treatment_type);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($result) {
        return $result['price'];
    }
    
    // If not found, try mapped treatment
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
    
    // Get fee
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