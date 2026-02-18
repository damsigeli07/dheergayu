<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// IMPORTANT: Only patients can book consultations
$user_role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? $_SESSION['role'] ?? '');
if ($user_role !== 'patient') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - Dheergayu</title>
        <style>
            body {
                font-family: 'Roboto', Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .error-container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .error-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                margin-bottom: 10px;
                font-size: 28px;
            }
            p {
                color: #666;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .role-info {
                background: #f0f0f0;
                padding: 15px;
                border-radius: 10px;
                margin-bottom: 20px;
                font-size: 14px;
            }
            .btn-group {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
            }
            .btn {
                padding: 12px 30px;
                border-radius: 25px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s;
                display: inline-block;
            }
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
            }
            .btn-secondary {
                background: #f0f0f0;
                color: #333;
            }
            .btn-secondary:hover {
                background: #e0e0e0;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">🚫</div>
            <h1>Access Restricted</h1>
            <p>Only patients can book consultations through this page.</p>
            <div class="role-info">
                <strong>Your current role:</strong> <?php echo ucfirst($user_role); ?><br>
                <strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
            </div>
            <p>Please log in with a patient account to book a consultation.</p>
            <div class="btn-group">
                <a href="login.php" class="btn btn-primary">Login as Patient</a>
                <a href="home.php" class="btn btn-secondary">Go to Home</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/Models/AppointmentModel.php';

$model = new AppointmentModel($conn);

// Get patient information (only for patients)
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';

// Fetch patient details from patients table
$patientStmt = $conn->prepare("SELECT first_name, last_name, email, dob FROM patients WHERE id = ?");
$patientStmt->bind_param('i', $_SESSION['user_id']);
$patientStmt->execute();
$patientResult = $patientStmt->get_result();
$profileAge = '';
$profileGender = '';
$profilePhone = '';
if ($patientData = $patientResult->fetch_assoc()) {
    $userName = $patientData['first_name'] . ' ' . $patientData['last_name'];
    $userEmail = $patientData['email'];
}
$patientStmt->close();

// Prefer patient_info (profile) for age, gender, phone — user edits these in profile
$infoStmt = $conn->prepare("SELECT date_of_birth, gender, phone FROM patient_info WHERE patient_id = ? LIMIT 1");
if ($infoStmt) {
    $infoStmt->bind_param('i', $_SESSION['user_id']);
    $infoStmt->execute();
    $infoRow = $infoStmt->get_result()->fetch_assoc();
    $infoStmt->close();
    if ($infoRow) {
        if (!empty(trim($infoRow['date_of_birth'] ?? ''))) {
            $dob = new DateTime(trim($infoRow['date_of_birth']));
            $profileAge = (string) $dob->diff(new DateTime('today'))->y;
        }
        if (!empty(trim($infoRow['gender'] ?? ''))) $profileGender = trim($infoRow['gender']);
        if (!empty(trim($infoRow['phone'] ?? ''))) $profilePhone = trim($infoRow['phone']);
    }
}
// Fallback to patients.dob for age only if profile has no date_of_birth
if ($profileAge === '' && is_array($patientData ?? null) && !empty($patientData['dob'])) {
    $dob = new DateTime($patientData['dob']);
    $profileAge = (string) $dob->diff(new DateTime('today'))->y;
}

// Fetch doctor schedules
$scheduleQuery = "SELECT * FROM doctor_schedule WHERE is_active = 1 ORDER BY 
    FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), 
    start_time";
$scheduleResult = $conn->query($scheduleQuery);
$doctorSchedules = [];
while ($row = $scheduleResult->fetch_assoc()) {
    $doctorSchedules[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Book Consultation</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/channeling.css?v=<?php echo time(); ?>">
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
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php" class="active">BOOKING</a></li>
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
            <h1 class="main-title">Book Ayurvedic Consultation</h1>
        </div>

        <!-- Doctor Timetable Section -->
        <div class="timetable-section">
            <h3 class="timetable-title">Doctor Availability Schedule</h3>
            <div class="timetable-grid">
                <?php
                $doctorGroups = [];
                foreach ($doctorSchedules as $schedule) {
                    $doctorGroups[$schedule['doctor_name']][] = $schedule;
                }

                foreach ($doctorGroups as $doctorName => $schedules):
                ?>
                    <div class="doctor-schedule-card">
                        <div class="doctor-name"><?php echo htmlspecialchars($doctorName); ?></div>
                        <?php foreach ($schedules as $schedule): ?>
                            <div class="schedule-item">
                                <span class="schedule-day"><?php echo htmlspecialchars($schedule['day_of_week']); ?></span>
                                <span class="schedule-time">
                                    <?php 
                                    echo date('g:i A', strtotime($schedule['start_time'])); 
                                    echo ' - '; 
                                    echo date('g:i A', strtotime($schedule['end_time'])); 
                                    ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="booking-content">
            <div class="left-section">
                <div class="card">
                    <h3 class="section-title">Select Date & Time</h3>

                    <div class="form-group">
                        <label for="consultationDate">Consultation Date</label>
                        <input type="date" id="consultationDate" name="consultationDate" required onchange="updateSummary()">
                    </div>

                    <h3 class="section-title">Available Slots</h3>
                    <div class="availability-grid" id="slotsContainer">
                        <p style="padding: 20px; color: #999;">Select a date first</p>
                    </div>
                </div>
            </div>

            <div class="right-section">
                <div class="card">
                    <h3 class="section-title">Patient Information</h3>
                    <form id="consultationForm">
                        <div class="form-group">
                            <label for="patientName">Full Name *</label>
                            <input type="text" id="patientName" name="patient_name" value="<?php echo htmlspecialchars($userName); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="age">Age *</label>
                            <input type="number" id="age" name="age" min="1" max="120" value="<?php echo htmlspecialchars($profileAge); ?>" placeholder="<?php echo $profileAge === '' ? 'Enter age' : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="">-- Select Gender --</option>
                                <option value="Male"<?php echo $profileGender === 'Male' ? ' selected' : ''; ?>>Male</option>
                                <option value="Female"<?php echo $profileGender === 'Female' ? ' selected' : ''; ?>>Female</option>
                                <option value="Other"<?php echo $profileGender === 'Other' ? ' selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($profilePhone); ?>" placeholder="<?php echo $profilePhone === '' ? '0712345678' : ''; ?>" required>
                        </div>

                        <button type="submit" class="book-btn" id="bookBtn" disabled>Book Consultation</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Panel -->
        <div class="card summary-card">
            <h3 class="section-title">Booking Summary</h3>
            <div id="summaryContent">
                <p style="color: #999;">Fill in details to see summary</p>
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
                <p>Sri Lanka –</p>
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
// Use the FIXED JavaScript from earlier that I provided
let selectedTimeSlot = '';
let selectedDoctorId = null;
let selectedDoctorName = '';
let consultationDate = document.getElementById('consultationDate');

consultationDate.min = new Date().toISOString().split('T')[0];

consultationDate.addEventListener('change', function() {
    if (this.value) {
        loadAvailableSlots(this.value);
    }
});

function isSlotInPast(selectedDate, slotTime) {
    const now = new Date();
    const today = now.toISOString().split('T')[0];
    
    if (selectedDate !== today) {
        return false;
    }
    
    const [hours, minutes] = slotTime.split(':').map(Number);
    const slotDateTime = new Date();
    slotDateTime.setHours(hours, minutes, 0, 0);
    
    return slotDateTime < now;
}

function loadAvailableSlots(date) {
    fetch(`/dheergayu/public/api/available-slots.php?date=${date}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('slotsContainer');
            if (data.slots && data.slots.length > 0) {
                container.innerHTML = data.slots.map(slot => {
                    let className = 'time-slot';
                    let disabled = '';
                    let onclick = `selectSlot(this, '${slot.time}', ${slot.doctor_id}, '${slot.doctor_name.replace(/'/g, "\\'")}')`;
                    
                    if (isSlotInPast(date, slot.time)) {
                        className += ' slot-locked';
                        disabled = 'disabled';
                        onclick = '';
                    } else if (slot.status === 'booked') {
                        className += ' slot-booked';
                        disabled = 'disabled';
                        onclick = '';
                    } else if (slot.status === 'locked') {
                        className += ' slot-locked';
                        disabled = 'disabled';
                        onclick = '';
                    }
                    
                    return `<button type="button" class="${className}" ${disabled} onclick="${onclick}" title="${slot.doctor_name}">${formatTime(slot.time)}<br><small style="font-size:11px;">${slot.doctor_name}</small></button>`;
                }).join('');
            } else {
                container.innerHTML = '<p style="padding: 20px; color: #999;">No slots available for this date</p>';
            }
        })
        .catch(error => {
            console.error('Error loading slots:', error);
            document.getElementById('slotsContainer').innerHTML = '<p style="padding: 20px; color: #c33;">Error loading slots. Please try again.</p>';
        });
}

function selectSlot(button, slot, doctorId, doctorName) {
    const date = document.getElementById('consultationDate').value;
    
    if (isSlotInPast(date, slot)) {
        alert('This time slot has already passed. Please select a future time.');
        return;
    }
    
    document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
    button.classList.add('selected');
    selectedTimeSlot = slot;
    selectedDoctorId = doctorId;
    selectedDoctorName = doctorName;
    updateSummary();
    checkFormValidity();
}

function formatTime(time) {
    const [hours, minutes] = time.split(':');
    const h = parseInt(hours);
    const period = h >= 12 ? 'PM' : 'AM';
    const displayHours = h % 12 || 12;
    return `${displayHours}:${minutes} ${period}`;
}

function updateSummary() {
    const date = document.getElementById('consultationDate').value;

    let summary = '<p style="color: #999;">Fill in details to see summary</p>';
    if (date && selectedTimeSlot) {
        const dateObj = new Date(date);
        const formattedDate = dateObj.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        summary = `
            <div style="color: #333;">
                <p><strong>Service:</strong> Ayurvedic Consultation</p>
                <p><strong>Doctor:</strong> ${selectedDoctorName}</p>
                <p><strong>Date:</strong> ${formattedDate}</p>
                <p><strong>Time:</strong> ${formatTime(selectedTimeSlot)}</p>
                <p><strong>Duration:</strong> 30-45 minutes</p>
                <p><strong>Consultation Fee:</strong> Rs 2,000.00</p>
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                <p style="color: #5CB85C; font-weight: 700; font-size: 15px;">✓ Ready to book</p>
            </div>
        `;
    }
    document.getElementById('summaryContent').innerHTML = summary;
}

function checkFormValidity() {
    const form = document.getElementById('consultationForm');
    const fields = ['patientName', 'age', 'gender', 'email', 'phone'];
    const allFilled = fields.every(id => document.getElementById(id).value.trim());
    document.getElementById('bookBtn').disabled = !(allFilled && selectedTimeSlot);
}

document.querySelectorAll('input, select').forEach(field => {
    field.addEventListener('change', checkFormValidity);
    field.addEventListener('input', checkFormValidity);
});

document.getElementById('consultationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!selectedTimeSlot) {
        alert('Please select a time slot');
        return;
    }
    
    if (!selectedDoctorId) {
        alert('Doctor information is missing. Please select a slot again.');
        return;
    }
    
    const date = document.getElementById('consultationDate').value;
    
    if (isSlotInPast(date, selectedTimeSlot)) {
        alert('This time slot has already passed. Please select a future time.');
        return;
    }

    const submitBtn = document.getElementById('bookBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Booking...';

    const formData = new FormData(this);
    formData.append('treatment_type', 'General Consultation');
    formData.append('appointment_date', date);
    formData.append('appointment_time', selectedTimeSlot);
    formData.append('payment_method', 'onsite');
    formData.append('doctor_id', selectedDoctorId);
    formData.append('doctor_name', selectedDoctorName);

    console.log('Sending booking with doctor_id:', selectedDoctorId, 'doctor_name:', selectedDoctorName);

    fetch('/dheergayu/public/api/book-consultation.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.text();
    })
    .then(text => {
        console.log('Server response:', text);
        
        if (!text || text.trim() === '') {
            throw new Error('Empty response from server');
        }
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON response:', text);
            throw new Error('Server returned invalid response: ' + text.substring(0, 100));
        }
    })
    .then(data => {
        console.log('Parsed response:', data);
        
        if (data.success) {
            alert('Consultation booked successfully with ' + selectedDoctorName + '!');
            window.location.href = 'patient_appointments.php';
        } else {
            alert('Error: ' + (data.error || 'Failed to book consultation'));
            submitBtn.disabled = false;
            submitBtn.textContent = 'Book Consultation';
        }
    })
    .catch(err => {
        console.error('Booking error:', err);
        alert('Error: ' + err.message + '. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Book Consultation';
    });
});
    </script>
</body>

</html>