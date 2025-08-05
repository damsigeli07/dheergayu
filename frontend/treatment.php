<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Treatment Appointment</title>
    <link rel="stylesheet" href="css/treatment.css">
</head>
<body>
    <div class="header">
        TREATMENT APPOINTMENT
        <div class="user-icon" onclick="showUserMenu()" title="User Menu">👤</div>
    </div>

    <div class="container">
        <!-- Treatment Selection -->
        <div class="card">
            <h3 class="section-title">Treatment Type</h3>
            
            <div class="form-group">
                <label for="treatmentType">Select Treatment</label>
                <select id="treatmentType" name="treatmentType" required>
                    <option value="">Select...</option>
                    <option value="panchakarma">Panchakarma</option>
                    <option value="abhyanga">Abhyanga</option>
                    <option value="shirodhara">Shirodhara</option>
                    <option value="nasya">Nasya</option>
                    <option value="basti">Basti</option>
                    <option value="consultation">General Consultation</option>
                </select>
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

            <div class="form-group">
                <label for="appointmentDate">Date</label>
                <input type="date" id="appointmentDate" name="appointmentDate" required>
            </div>

            <div class="calendar-placeholder">
                📅 Calendar View
            </div>
        </div>

        <!-- Patient Information -->
        <div class="card">
            <h3 class="section-title">Patient Information</h3>
            
            <form id="appointmentForm">
                <div class="form-group">
                    <label for="patientName">Name</label>
                    <input type="text" id="patientName" name="patientName" placeholder="Enter patient name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter email address" required>
                </div>

                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" placeholder="Enter age" min="1" max="120" required>
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
                    <label for="contactNo">Contact No</label>
                    <input type="text" id="contactNo" name="contactNo" placeholder="Enter contact number" required>
                </div>

                <div class="form-group">
                    <label for="medicalHistory">Medical History (Optional)</label>
                    <textarea id="medicalHistory" name="medicalHistory" rows="4" placeholder="Brief medical history or current concerns..."></textarea>
                </div>

                <div class="action-buttons">
                    <button type="button" class="save-btn" onclick="saveDraft()">Save Draft</button>
                    <button type="submit" class="continue-btn" id="continueBtn" disabled>Continue</button>
                </div>
            </form>
        </div>

        <!-- Summary/Info Panel -->
        <div class="card">
            <h3 class="section-title">Appointment Summary</h3>
            
            <div id="appointmentSummary">
                <div style="color: #666; text-align: center; padding: 20px;">
                    Select treatment type and time slot to see summary
                </div>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #5CB85C;">
                <h4 style="color: #2d5a2d; margin-bottom: 10px;">Important Notes:</h4>
                <ul style="color: #2d5a2d; font-size: 13px; line-height: 1.5; padding-left: 15px;">
                    <li>Please arrive 15 minutes before your appointment</li>
                    <li>Bring any previous medical reports</li>
                    <li>Wear comfortable clothing</li>
                    <li>Avoid heavy meals 2 hours before treatment</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        let selectedTimeSlot = '';
        let selectedTreatment = '';

        function showUserMenu() {
            alert('User menu options:\n• Profile\n• My Appointments\n• Settings\n• Logout');
        }

        function selectTimeSlot(button, timeSlot) {
            // Remove selected class from all time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Add selected class to clicked slot
            button.classList.add('selected');
            selectedTimeSlot = timeSlot;
            
            updateSummary();
            validateForm();
        }

        function updateSummary() {
            const summaryDiv = document.getElementById('appointmentSummary');
            const treatmentSelect = document.getElementById('treatmentType');
            const dateInput = document.getElementById('appointmentDate');
            
            if (selectedTreatment || selectedTimeSlot || dateInput.value) {
                summaryDiv.innerHTML = `
                    <div style="color: #333;">
                        ${selectedTreatment ? `<p><strong>Treatment:</strong> ${selectedTreatment}</p>` : ''}
                        ${dateInput.value ? `<p><strong>Date:</strong> ${new Date(dateInput.value).toLocaleDateString()}</p>` : ''}
                        ${selectedTimeSlot ? `<p><strong>Time:</strong> ${selectedTimeSlot}</p>` : ''}
                        ${selectedTreatment ? `<p style="color: #5CB85C; font-weight: 600; margin-top: 15px;">Estimated Duration: 60-90 minutes</p>` : ''}
                    </div>
                `;
            } else {
                summaryDiv.innerHTML = '<div style="color: #666; text-align: center; padding: 20px;">Select treatment type and time slot to see summary</div>';
            }
        }

        function validateForm() {
            const form = document.getElementById('appointmentForm');
            const continueBtn = document.getElementById('continueBtn');
            const requiredFields = form.querySelectorAll('[required]');
            
            let allValid = selectedTimeSlot && selectedTreatment;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    allValid = false;
                }
            });
            
            continueBtn.disabled = !allValid;
            
            if (allValid) {
                continueBtn.textContent = 'Continue to Payment';
            } else {
                continueBtn.textContent = 'Complete All Fields';
            }
        }

        function saveDraft() {
            alert('Appointment draft saved successfully!');
        }

        // Treatment type change handler
        document.getElementById('treatmentType').addEventListener('change', function() {
            selectedTreatment = this.options[this.selectedIndex].text;
            updateSummary();
            validateForm();
        });

        // Date change handler
        document.getElementById('appointmentDate').addEventListener('change', function() {
            updateSummary();
            validateForm();
        });

        // Set minimum date to today
        document.getElementById('appointmentDate').min = new Date().toISOString().split('T')[0];

        // Form submission
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedTimeSlot) {
                alert('Please select a time slot!');
                return;
            }
            
            if (!selectedTreatment) {
                alert('Please select a treatment type!');
                return;
            }
            
            const formData = new FormData(this);
            const appointmentData = {
                treatment: selectedTreatment,
                date: formData.get('appointmentDate'),
                timeSlot: selectedTimeSlot,
                patientName: formData.get('patientName'),
                email: formData.get('email'),
                age: formData.get('age'),
                gender: formData.get('gender'),
                contactNo: formData.get('contactNo'),
                medicalHistory: formData.get('medicalHistory')
            };
            
            // Simulate processing
            const btn = document.getElementById('continueBtn');
            btn.textContent = 'PROCESSING...';
            btn.disabled = true;
            
            setTimeout(() => {
                alert('Appointment details confirmed!\n\nProceeding to payment...');
            }, 1500);
        });

        // Real-time validation for required fields
        const requiredFields = document.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('input', function() {
                validateField(this);
                validateForm();
            });
        });

        function validateField(field) {
            if (field.value.trim()) {
                field.style.borderColor = '#5CB85C';
            } else {
                field.style.borderColor = '#e1e5e9';
            }
        }

        // Format contact number
        document.getElementById('contactNo').addEventListener('input', function(e) {
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

        // Auto-capitalize patient name
        document.getElementById('patientName').addEventListener('input', function() {
            this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
        });

        // Initialize form validation on page load
        validateForm();
    </script>
</body>
</html>