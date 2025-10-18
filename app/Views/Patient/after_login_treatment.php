<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/Models/AppointmentModel.php';

$model = new AppointmentModel($conn);

// Fetch treatments from treatment_list table
$treatments_query = "SELECT treatment_name, description, duration, price, status FROM treatment_list WHERE status = 'Active'";
$treatments_result = $conn->query($treatments_query);
$treatments = [];
while ($row = $treatments_result->fetch_assoc()) {
    $treatments[$row['treatment_name']] = [
        'description' => $row['description'],
        'duration' => $row['duration'],
        'price' => $row['price']
    ];
}

$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Book Treatment</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/treatment.css?v=<?php echo time(); ?>">
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
            <a href="treatment.php" class="nav-btn active">Treatments</a>
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
        <div class="page-header">
            <h1 class="main-title">Book Ayurvedic Treatment</h1>
        </div>

        <div class="booking-content">
            <div class="left-section">
                <div class="card">
                    <h3 class="section-title">Select Treatment</h3>
                    <select id="treatmentSelect" name="treatment" required onchange="updateSummary()">
                        <option value="">-- Choose Treatment Type --</option>
                        <?php foreach ($treatments as $name => $details): ?>
                            <option value="<?php echo htmlspecialchars($name); ?>" 
                                    data-description="<?php echo htmlspecialchars($details['description']); ?>"
                                    data-duration="<?php echo htmlspecialchars($details['duration']); ?>"
                                    data-price="<?php echo htmlspecialchars($details['price']); ?>">
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="form-group">
                        <label for="treatmentDate">Treatment Date</label>
                        <input type="date" id="treatmentDate" name="treatmentDate" required onchange="updateSummary()">
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
                    <form id="treatmentForm">
                        <div class="form-group">
                            <label for="patientName">Full Name *</label>
                            <input type="text" id="patientName" name="patient_name" value="<?php echo htmlspecialchars($userName); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="age">Age *</label>
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
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="text" id="phone" name="phone" placeholder="0712345678" required>
                        </div>

                        <button type="submit" class="book-btn" id="bookBtn" disabled>Book Treatment</button>
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

    <script>
        let selectedTimeSlot = '';
        let treatmentDate = document.getElementById('treatmentDate');

        treatmentDate.min = new Date().toISOString().split('T')[0];

        treatmentDate.addEventListener('change', function() {
            if (this.value) {
                loadAvailableSlots(this.value);
            }
        });

        function loadAvailableSlots(date) {
            fetch(`/dheergayu/public/api/available-slots.php?date=${date}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('slotsContainer');
                    if (data.slots && data.slots.length > 0) {
                        container.innerHTML = data.slots.map(slot =>
                            `<button type="button" class="time-slot" onclick="selectSlot(this, '${slot}')">${formatTime(slot)}</button>`
                        ).join('');
                    } else {
                        container.innerHTML = '<p style="padding: 20px; color: #999;">No slots available for this date</p>';
                    }
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
            const treatmentSelect = document.getElementById('treatmentSelect');
            const selectedOption = treatmentSelect.options[treatmentSelect.selectedIndex];
            const treatmentName = selectedOption.value || '';
            const description = selectedOption.dataset.description || '';
            const duration = selectedOption.dataset.duration || '';
            const price = selectedOption.dataset.price || '';
            const date = document.getElementById('treatmentDate').value;

            let summary = '<p style="color: #999;">Fill in details to see summary</p>';
            if (treatmentName && date && selectedTimeSlot) {
                const dateObj = new Date(date);
                const formattedDate = dateObj.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                summary = `
                    <div style="color: #333;">
                        <p><strong>Treatment:</strong> ${treatmentName}</p>
                        <p><strong>Description:</strong> ${description}</p>
                        <p><strong>Date:</strong> ${formattedDate}</p>
                        <p><strong>Time:</strong> ${formatTime(selectedTimeSlot)}</p>
                        <p><strong>Duration:</strong> ${duration}</p>
                        <p><strong>Fee:</strong> Rs ${parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                        <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                        <p style="color: #5CB85C; font-weight: 700; font-size: 15px;">✓ Ready to book</p>
                    </div>
                `;
            }
            document.getElementById('summaryContent').innerHTML = summary;
        }

        function checkFormValidity() {
            const treatmentSelect = document.getElementById('treatmentSelect');
            const fields = ['patientName', 'age', 'gender', 'email', 'phone'];
            const allFilled = fields.every(id => document.getElementById(id).value.trim());
            const treatmentSelected = treatmentSelect.value !== '';
            document.getElementById('bookBtn').disabled = !(allFilled && treatmentSelected && selectedTimeSlot);
        }

        document.querySelectorAll('input, select').forEach(field => {
            field.addEventListener('change', checkFormValidity);
            field.addEventListener('input', checkFormValidity);
        });

        document.getElementById('treatmentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!selectedTimeSlot) {
                alert('Please select a time slot');
                return;
            }

            const treatmentSelect = document.getElementById('treatmentSelect');
            if (!treatmentSelect.value) {
                alert('Please select a treatment type');
                return;
            }

            const formData = new FormData(this);
            formData.append('treatment_type', treatmentSelect.value);
            formData.append('appointment_date', document.getElementById('treatmentDate').value);
            formData.append('appointment_time', selectedTimeSlot);
            formData.append('payment_method', 'onsite');

            fetch('/dheergayu/public/api/book-treatment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Treatment booked successfully!');
                        window.location.href = 'patient_appointments.php';
                    } else {
                        alert('Error: ' + (data.error || 'Failed to book treatment'));
                    }
                })
                .catch(err => alert('Error: ' + err));
        });

        function toggleProfileDropdown() {
            document.getElementById('profileDropdown').classList.toggle('show');
        }

        window.addEventListener('click', function(e) {
            if (!e.target.matches('.profile-btn')) {
                document.getElementById('profileDropdown').classList.remove('show');
            }
        });
    </script>
</body>

</html>