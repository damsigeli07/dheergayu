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
                <button class="tab-btn active" onclick="showTab('upcoming')">
                    Upcoming <span class="tab-badge"><?php echo countUpcoming($appointments); ?></span>
                </button>
                <button class="tab-btn" onclick="showTab('completed')">Completed</button>
                <button class="tab-btn" onclick="showTab('cancelled')">Cancelled</button>
            </div>

            <div class="tab-content">
                <!-- Upcoming Appointments -->
                <div id="upcoming-tab" class="tab-panel" style="display: block;">
                    <?php 
                    $upcoming = getUpcomingAppointments($appointments);
                    if (empty($upcoming)): 
                    ?>
                        <div class="empty-state">
                            <h3>No Upcoming Appointments</h3>
                            <p>Book your first consultation or treatment today</p>
                            <div style="margin-top: 20px;">
                                <a href="channeling.php" class="book-btn">Book Consultation</a>
                                <a href="after_login_treatment.php" class="book-btn">Book Treatment</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="appointments-list">
                            <?php foreach ($upcoming as $apt): ?>
                                <?php 
                                $type = $apt['type'] ?? (isset($apt['treatment_type']) ? 'treatment' : 'consultation');
                                $isConsultation = $type === 'consultation' || !isset($apt['treatment_type']);
                                ?>
                                <div class="appointment-card <?php echo $type; ?>">
                                    <div class="appointment-header">
                                        <div class="appointment-type <?php echo $type; ?>">
                                            <?php echo ucfirst($type); ?>
                                        </div>
                                        <div class="appointment-status status-<?php echo strtolower($apt['status']); ?>">
                                            <?php echo $apt['status']; ?>
                                        </div>
                                    </div>
                                    <div class="appointment-details">
                                        <?php if ($isConsultation): ?>
                                            <div class="detail-item">
                                                <span class="detail-label">Doctor</span>
                                                <span class="detail-value"><?php echo $apt['doctor_name'] ?? 'N/A'; ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="detail-item">
                                                <span class="detail-label">Treatment</span>
                                                <span class="detail-value"><?php echo $apt['treatment_type'] ?? 'N/A'; ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Date & Time</span>
                                            <span class="detail-value">
                                                <?php echo date('M d, Y - h:i A', strtotime($apt['appointment_date'] . ' ' . $apt['appointment_time'])); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Fee</span>
                                            <span class="detail-value">Rs <?php echo number_format($apt['consultation_fee'] ?? $apt['treatment_fee'] ?? 0, 2); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Payment Status</span>
                                            <span class="detail-value" style="color: <?php echo $apt['payment_status'] === 'Completed' ? '#28a745' : '#ff9800'; ?>;">
                                                <?php echo $apt['payment_status'] ?? 'Pending'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="appointment-actions">
                                        <?php if ($apt['payment_status'] !== 'Completed'): ?>
                                            <button class="action-btn btn-primary" onclick="payNow(<?php echo $apt['id']; ?>, '<?php echo $type; ?>')">
                                                Pay Now
                                            </button>
                                        <?php endif; ?>
                                        <button class="action-btn btn-danger" onclick="cancelAppointment(<?php echo $apt['id']; ?>, '<?php echo $type; ?>')">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Completed Appointments -->
                <div id="completed-tab" class="tab-panel" style="display: none;">
                    <?php 
                    $completed = getCompletedAppointments($appointments);
                    if (empty($completed)): 
                    ?>
                        <div class="empty-state">
                            <h3>No Completed Appointments</h3>
                        </div>
                    <?php else: ?>
                        <div class="appointments-list">
                            <?php foreach ($completed as $apt): ?>
                                <?php 
                                $type = $apt['type'] ?? (isset($apt['treatment_type']) ? 'treatment' : 'consultation');
                                $isConsultation = $type === 'consultation' || !isset($apt['treatment_type']);
                                ?>
                                <div class="appointment-card <?php echo $type; ?>">
                                    <div class="appointment-header">
                                        <div class="appointment-type <?php echo $type; ?>">
                                            <?php echo ucfirst($type); ?>
                                        </div>
                                        <div class="appointment-status status-completed">Completed</div>
                                    </div>
                                    <div class="appointment-details">
                                        <?php if ($isConsultation): ?>
                                            <div class="detail-item">
                                                <span class="detail-label">Doctor</span>
                                                <span class="detail-value"><?php echo $apt['doctor_name'] ?? 'N/A'; ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="detail-item">
                                                <span class="detail-label">Treatment</span>
                                                <span class="detail-value"><?php echo $apt['treatment_type'] ?? 'N/A'; ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Date</span>
                                            <span class="detail-value">
                                                <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                                            </span>
                                        </div>
                                    </div>
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
                                <?php 
                                $type = $apt['type'] ?? (isset($apt['treatment_type']) ? 'treatment' : 'consultation');
                                ?>
                                <div class="appointment-card <?php echo $type; ?>">
                                    <div class="appointment-header">
                                        <div class="appointment-type <?php echo $type; ?>">
                                            <?php echo ucfirst($type); ?>
                                        </div>
                                        <div class="appointment-status status-cancelled">Cancelled</div>
                                    </div>
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Date</span>
                                            <span class="detail-value">
                                                <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
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
            const modal = document.getElementById('cancelModal');
            if (e.target === modal) {
                closeCancelModal();
            }
        });
    </script>
</body>
</html>

<?php
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

function getCompletedAppointments($appointments) {
    $completed = [];
    foreach ($appointments['consultations'] ?? [] as $apt) {
        if ($apt['status'] === 'Completed') {
            $apt['type'] = 'consultation';
            $completed[] = $apt;
        }
    }
    foreach ($appointments['treatments'] ?? [] as $apt) {
        if ($apt['status'] === 'Completed') {
            $apt['type'] = 'treatment';
            $completed[] = $apt;
        }
    }
    return $completed;
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