<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Channeling</title>
    <link rel="stylesheet" href="css/channeling.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="img/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div class="header-right">
            <a href="home.php" class="nav-btn">Home</a>
            <a href="channeling.php" class="nav-btn">Consultations</a>
            <a href="treatment.php" class="nav-btn">Our Treatments</a>
            <a href="products.php" class="nav-btn">Our Products</a>
            <a href="Signup.php" class="nav-btn"><u>Book Now</u></a>
        </div>
    </header>

    <div class="container">
        <div class="doctor-card">
            <div class="doctor-header">
                <div class="doctor-name">Channeling Form</div>
                <!-- <div class="date-display">9TH MAY 2025</div> -->
            </div>

            <div class="booking-content">
                <div class="left-section">
                    <div class="availability-section">
                        <div class="card">
                            
                            <h3 class="section-title">Select Doctor</h3>
                            <select id="doctor" name="doctor" required>
                                <option value="Doctor1">Dr. L.M.Perera</option>
                                <option value="Doctor2">Dr. K.Jayawardena</option>
                                <option value="Doctor3">Dr. A.T.Gunarathne</option>
                            </select>
                            <div class="form-group">
                                <label for="appointmentDate">Date</label>
                                <input type="date" id="appointmentDate" name="appointmentDate" required>
                            </div>
                            <h3 class="section-title">Available Slots</h3>
                            <div class="availability-grid">
                                <button class="time-slot" onclick="selectTimeSlot(this, '8:00 AM')">8:00 AM</button>
                                <button class="time-slot" onclick="selectTimeSlot(this, '10:00 AM')">10:00 AM</button>
                                <button class="time-slot" onclick="selectTimeSlot(this, '11:00 AM')">11:00 AM</button>
                                <button class="time-slot" onclick="selectTimeSlot(this, '1:00 PM')">1:00 PM</button>
                                <button class="time-slot" onclick="selectTimeSlot(this, '3:00 PM')">3:00 PM</button>
                                <button class="time-slot" onclick="selectTimeSlot(this, '4:00 PM')">4:00 PM</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="right-section">
                    <form id="channelingForm">
                        <div class="form-group">
                            <label for="patientName">Name</label>
                            <input type="text" id="patientName" name="patientName" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" placeholder="Age" min="1" max="120" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                        </div>

                        <div class="form-group">
                            <label for="contactNumber">Contact Number</label>
                            <input type="text" id="contactNumber" name="contactNumber" placeholder="Enter your contact number" required>
                        </div>

                        <button type="submit" class="book-btn" id="bookBtn">Book Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedTimeSlot = '';

        function selectTimeSlot(button, timeSlot) {
            // Remove selected class from all time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Add selected class to clicked slot
            button.classList.add('selected');
            selectedTimeSlot = timeSlot;
            
            // Enable book button if time slot is selected
            updateBookButton();
        }

        function updateBookButton() {
            const bookBtn = document.getElementById('bookBtn');
            if (selectedTimeSlot) {
                bookBtn.disabled = false;
                bookBtn.textContent = `Book for ${selectedTimeSlot}`;
            } else {
                bookBtn.disabled = true;
                bookBtn.textContent = 'Select Time Slot';
            }
        }

        function showUserMenu() {
            alert('User menu options:\n• Profile\n• My Appointments\n• Settings\n• Logout');
        }

        // Format contact number input
        document.getElementById('contactNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 10) {
                if (value.length > 3 && value.length <= 6) {
                    value = value.replace(/(\d{3})(\d+)/, '$1-$2');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1-$2-$3');
                }
                e.target.value = value;
            }
        });

        document.getElementById('channelingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedTimeSlot) {
                alert('Please select a time slot!');
                return;
            }
            
            const formData = new FormData(this);
            const bookingData = {
                doctor: 'Dr. <Name>',
                date: '9TH MAY 2025',
                timeSlot: selectedTimeSlot,
                patientName: formData.get('patientName'),
                age: formData.get('age'),
                gender: formData.get('gender'),
                email: formData.get('email'),
                contactNumber: formData.get('contactNumber')
            };
            
            // Simulate booking process
            const btn = document.getElementById('bookBtn');
            btn.textContent = 'BOOKING...';
            btn.disabled = true;
            
            setTimeout(() => {
                alert('Appointment booked successfully!\n\nBooking Details:\n' +
                      `Doctor: ${bookingData.doctor}\n` +
                      `Date: ${bookingData.date}\n` +
                      `Time: ${bookingData.timeSlot}\n` +
                      `Patient: ${bookingData.patientName}\n` +
                      '\nYou will receive a confirmation email shortly.');
                
                // Reset form
                this.reset();
                selectedTimeSlot = '';
                document.querySelectorAll('.time-slot').forEach(slot => {
                    slot.classList.remove('selected');
                });
                updateBookButton();
            }, 2000);
        });

        // Initialize the book button state
        updateBookButton();

        // Add real-time form validation
        const requiredFields = ['patientName', 'age', 'gender', 'email', 'contactNumber'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            field.addEventListener('input', function() {
                validateField(this);
            });
        });

        function validateField(field) {
            if (field.value.trim()) {
                field.style.borderColor = '#5CB85C';
            } else {
                field.style.borderColor = '#e1e5e9';
            }
        }

        // Email validation
        document.getElementById('email').addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = '#dc3545';
            } else if (this.value) {
                this.style.borderColor = '#5CB85C';
            } else {
                this.style.borderColor = '#e1e5e9';
            }
        });

        // Age validation
        document.getElementById('age').addEventListener('input', function() {
            const age = parseInt(this.value);
            if (age < 1 || age > 120) {
                this.style.borderColor = '#dc3545';
            } else if (this.value) {
                this.style.borderColor = '#5CB85C';
            } else {
                this.style.borderColor = '#e1e5e9';
            }
        });
    </script>
</body>
</html>