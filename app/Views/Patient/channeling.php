<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/Models/AppointmentModel.php';

$model = new AppointmentModel($conn);

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
        let selectedTimeSlot = '';
        let consultationDate = document.getElementById('consultationDate');

        consultationDate.min = new Date().toISOString().split('T')[0];

        consultationDate.addEventListener('change', function() {
            if (this.value) {
                loadAvailableSlots(this.value);
            }
        });

        // Function to check if a time slot is in the past
        function isSlotInPast(selectedDate, slotTime) {
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            
            // If selected date is not today, slot is not in the past
            if (selectedDate !== today) {
                return false;
            }
            
            // Parse the slot time
            const [hours, minutes] = slotTime.split(':').map(Number);
            const slotDateTime = new Date();
            slotDateTime.setHours(hours, minutes, 0, 0);
            
            // Check if slot time has passed
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
                            let onclick = `selectSlot(this, '${slot.time}')`;
                            
                            // Check if slot is in the past
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
                            
                            return `<button type="button" class="${className}" ${disabled} onclick="${onclick}">${formatTime(slot.time)}</button>`;
                        }).join('');
                    } else {
                        container.innerHTML = '<p style="padding: 20px; color: #999;">No slots available for this date</p>';
                    }
                });
        }

        function selectSlot(button, slot) {
            const date = document.getElementById('consultationDate').value;
            
            // Double-check if slot is in the past before allowing selection
            if (isSlotInPast(date, slot)) {
                alert('This time slot has already passed. Please select a future time.');
                return;
            }
            
            if (selectedTimeSlot) {
                fetch('/dheergayu/public/api/release-slot.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `date=${date}&time=${selectedTimeSlot}`
                });
            }
            
            fetch('/dheergayu/public/api/lock-slot.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `date=${date}&time=${slot}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
                    button.classList.add('selected');
                    selectedTimeSlot = slot;
                    updateSummary();
                    checkFormValidity();
                } else {
                    alert('This slot is no longer available. Please select another.');
                    loadAvailableSlots(date);
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
            
            const date = document.getElementById('consultationDate').value;
            
            // Final check before booking
            if (isSlotInPast(date, selectedTimeSlot)) {
                alert('This time slot has already passed. Please select a future time.');
                return;
            }

            const formData = new FormData(this);
            formData.append('treatment_type', 'General Consultation');
            formData.append('appointment_date', date);
            formData.append('appointment_time', selectedTimeSlot);
            formData.append('payment_method', 'onsite');

            fetch('/dheergayu/public/api/book-treatment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Consultation booked successfully!');
                        window.location.href = 'patient_appointments.php';
                    } else {
                        alert('Error: ' + (data.error || 'Failed to book consultation'));
                    }
                })
                .catch(err => alert('Error: ' + err));
        });
    </script>
</body>

</html>