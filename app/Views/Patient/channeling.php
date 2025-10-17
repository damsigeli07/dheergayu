<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/Models/AppointmentModel.php';


$model = new AppointmentModel($conn);
$doctors = $model->getDoctors();

$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
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
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="/dheergayu/public/assets/images/Patient/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div class="header-right">
            <a href="home.php" class="nav-btn">Home</a>
            <a href="channeling.php" class="nav-btn">Consultations</a>
            <a href="treatment.php" class="nav-btn">Treatments</a>
            <div class="profile-container">
                <button class="profile-btn" onclick="toggleProfileDropdown()">👤</button>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="patient_profile.php" class="dropdown-item">My Profile</a>
                    <a href="patient_appointments.php" class="dropdown-item">My Appointments</a>
                    <a href="logout.php" class="dropdown-item">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="doctor-card">
            <div class="doctor-header">
                <div class="doctor-name">Book Consultation</div>
            </div>

            <div class="booking-content">
                <div class="left-section">
                    <div class="card">
                        <h3 class="section-title">Select Doctor</h3>
                        <select id="doctorSelect" name="doctor" required onchange="updateSummary()">
                            <option value="">-- Choose Doctor --</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>" data-name="<?php echo $doctor['name']; ?>" data-fee="<?php echo $doctor['consultation_fee']; ?>">
                                    <?php echo $doctor['name']; ?> - <?php echo $doctor['specialty']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="form-group">
                            <label for="appointmentDate">Date</label>
                            <input type="date" id="appointmentDate" name="appointmentDate" required onchange="updateSummary()">
                        </div>

                        <h3 class="section-title">Available Slots</h3>
                        <div class="availability-grid" id="slotsContainer">
                            <p style="padding: 20px; color: #999;">Select a date first</p>
                        </div>
                    </div>
                </div>

                <div class="right-section">
                    <form id="consultationForm">
                        <div class="form-group">
                            <label for="patientName">Full Name</label>
                            <input type="text" id="patientName" name="patient_name" value="<?php echo htmlspecialchars($userName); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" min="1" max="120" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="">-- Select Gender --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" placeholder="0712345678" required>
                        </div>

                        <button type="submit" class="book-btn" id="bookBtn" disabled>Book Consultation</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Panel -->
        <div class="card summary-card">
            <h3 class="section-title">Summary</h3>
            <div id="summaryContent">
                <p style="color: #999;">Fill in details to see summary</p>
            </div>
        </div>
    </div>

    <script>
        // Fixed JavaScript for channeling.php
        let selectedTimeSlot = '';
        let appointmentDate = document.getElementById('appointmentDate');

        appointmentDate.min = new Date().toISOString().split('T')[0];

        appointmentDate.addEventListener('change', function() {
            if (this.value) {
                const doctorSelect = document.getElementById('doctorSelect');
                if (doctorSelect.value) {
                    loadAvailableSlots(this.value);
                }
            }
        });

        // Also load slots when doctor changes
        document.getElementById('doctorSelect').addEventListener('change', function() {
            const dateValue = appointmentDate.value;
            if (dateValue) {
                loadAvailableSlots(dateValue);
            }
            updateSummary();
        });

        function loadAvailableSlots(date) {
            const container = document.getElementById('slotsContainer');
            container.innerHTML = '<p style="padding: 20px; color: #999;">Loading slots...</p>';

            fetch(`/dheergayu/public/api/available-slots.php?date=${date}`)
                .then(res => res.json())
                .then(data => {
                    console.log('Slots response:', data); // Debug
                    if (data.slots && data.slots.length > 0) {
                        container.innerHTML = data.slots.map(slot =>
                            `<button type="button" class="time-slot" onclick="selectSlot(this, '${slot}')">${formatTime(slot)}</button>`
                        ).join('');
                    } else {
                        container.innerHTML = '<p style="padding: 20px; color: #999;">No slots available for this date</p>';
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    container.innerHTML = '<p style="padding: 20px; color: #f44336;">Error loading slots</p>';
                });
        }

        function formatTime(time) {
            const [hours, minutes] = time.split(':');
            const h = parseInt(hours);
            const period = h >= 12 ? 'PM' : 'AM';
            const displayHours = h % 12 || 12;
            return `${displayHours}:${minutes} ${period}`;
        }

        function selectSlot(button, slot) {
            document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
            button.classList.add('selected');
            selectedTimeSlot = slot;
            updateSummary();
            checkFormValidity();
        }

        function updateSummary() {
            const doctorSelect = document.getElementById('doctorSelect');
            const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
            const doctorName = selectedOption.dataset.name || '';
            const fee = selectedOption.dataset.fee || '';
            const date = document.getElementById('appointmentDate').value;

            let summary = '<p style="color: #999;">Fill in details to see summary</p>';
            if (doctorName && date && selectedTimeSlot) {
                const dateObj = new Date(date);
                const formattedDate = dateObj.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                summary = `
            <div style="color: #333;">
                <p><strong>Doctor:</strong> ${doctorName}</p>
                <p><strong>Date:</strong> ${formattedDate}</p>
                <p><strong>Time:</strong> ${formatTime(selectedTimeSlot)}</p>
                <p><strong>Fee:</strong> Rs ${fee}</p>
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                <p style="color: #5CB85C; font-weight: 600;">Ready to book</p>
            </div>
        `;
            }
            document.getElementById('summaryContent').innerHTML = summary;
        }

        function checkFormValidity() {
            const doctorSelect = document.getElementById('doctorSelect');
            const appointmentDate = document.getElementById('appointmentDate');
            const patientName = document.getElementById('patientName');
            const age = document.getElementById('age');
            const gender = document.getElementById('gender');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const bookBtn = document.getElementById('bookBtn');

            // Check if all required fields are filled
            const doctorValid = doctorSelect.value.trim() !== '';
            const dateValid = appointmentDate.value.trim() !== '';
            const timeValid = selectedTimeSlot !== '';
            const nameValid = patientName.value.trim() !== '';
            const ageValid = age.value.trim() !== '' && parseInt(age.value) > 0;
            const genderValid = gender.value.trim() !== '' && gender.value !== 'Select';
            const emailValid = email.value.trim() !== '';
            const phoneValid = phone.value.trim() !== '';

            const allValid = doctorValid && dateValid && timeValid && nameValid &&
                ageValid && genderValid && emailValid && phoneValid;

            console.log('Form validation:', {
                doctor: doctorValid,
                date: dateValid,
                time: timeValid,
                name: nameValid,
                age: ageValid,
                gender: genderValid,
                email: emailValid,
                phone: phoneValid,
                allValid: allValid
            });

            bookBtn.disabled = !allValid;

            if (allValid) {
                bookBtn.style.opacity = '1';
                bookBtn.style.cursor = 'pointer';
            } else {
                bookBtn.style.opacity = '0.5';
                bookBtn.style.cursor = 'not-allowed';
            }
        }

        // Add event listeners to all form fields
        document.querySelectorAll('#consultationForm input, #consultationForm select').forEach(field => {
            field.addEventListener('change', checkFormValidity);
            field.addEventListener('input', checkFormValidity);
        });

        // Form submission
        document.getElementById('consultationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!selectedTimeSlot) {
                alert('Please select a time slot');
                return;
            }

            const doctorSelect = document.getElementById('doctorSelect');
            if (!doctorSelect.value) {
                alert('Please select a doctor');
                return;
            }

            const bookBtn = document.getElementById('bookBtn');
            bookBtn.disabled = true;
            bookBtn.textContent = 'Booking...';

            const formData = new FormData(this);
            formData.append('doctor_id', doctorSelect.value);
            formData.append('appointment_date', document.getElementById('appointmentDate').value);
            formData.append('appointment_time', selectedTimeSlot);
            formData.append('payment_method', 'onsite');

            console.log('Submitting consultation booking...'); // Debug

            fetch('/dheergayu/public/api/book-consultation.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    console.log('Response:', data); // Debug
                    if (data.success) {
                        alert('Consultation booked successfully!');
                        window.location.href = 'patient_appointments.php';
                    } else {
                        alert('Error: ' + (data.error || 'Failed to book consultation'));
                        bookBtn.disabled = false;
                        bookBtn.textContent = 'Book Consultation';
                    }
                })
                .catch(err => {
                    console.error('Booking error:', err);
                    alert('Error: ' + err.message);
                    bookBtn.disabled = false;
                    bookBtn.textContent = 'Book Consultation';
                });
        });

        function toggleProfileDropdown() {
            document.getElementById('profileDropdown').classList.toggle('show');
        }

        window.addEventListener('click', function(e) {
            if (!e.target.matches('.profile-btn')) {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });

        // Initial check
        checkFormValidity();
    </script>
</body>

</html>