<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - My Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/patient_profile.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="/public/assets/images/Patient/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div>
            <a href="home.php" class="back-btn">← Back to Home</a>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">My Profile</h1>
        </div>

        <div class="success-message" id="successMessage">
            Profile updated successfully!
        </div>

        <div class="error-message" id="errorMessage">
            Please check all required fields and try again.
        </div>

        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="quick-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Visits:</span>
                        <span class="stat-value">12</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Last Visit:</span>
                        <span class="stat-value">Mar 15, 2024</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Next Appointment:</span>
                        <span class="stat-value">Mar 25, 2024</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Member Since:</span>
                        <span class="stat-value">Jan 2023</span>
                    </div>
                </div>
            </div>

            <div class="profile-main">
                <div class="profile-tabs">
                    <button class="tab-btn active" onclick="showTab('personal')">Personal Info</button>
                    <button class="tab-btn" onclick="showTab('medical')">Medical History</button>
                    <button class="tab-btn" onclick="showTab('preferences')">Preferences</button>
                </div>

                <div class="tab-content">
                    <!-- Personal Information Tab -->
                    <div id="personal-tab" class="tab-panel">
                        <form id="personalInfoForm">
                            <div class="form-section">
                                <div class="section-title">Basic Information</div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="firstName">First Name *</label>
                                        <input type="text" id="firstName" name="firstName" value="John" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName">Last Name *</label>
                                        <input type="text" id="lastName" name="lastName" value="Doe" disabled>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="dateOfBirth">Date of Birth *</label>
                                        <input type="date" id="dateOfBirth" name="dateOfBirth" value="1985-06-15" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender">Gender *</label>
                                        <select id="gender" name="gender" disabled>
                                            <option value="male" selected>Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nic">NIC Number *</label>
                                    <input type="text" id="nic" name="nic" value="850165123V" disabled>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-title">Contact Information</div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" value="john.doe@email.com" disabled>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Phone Number *</label>
                                        <input type="tel" id="phone" name="phone" value="+94 77 123 4567" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label for="emergencyContact">Emergency Contact</label>
                                        <input type="tel" id="emergencyContact" name="emergencyContact" value="+94 77 987 6543" disabled>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address" rows="3" disabled>No. 123, Main Street, Colombo 07, Sri Lanka</textarea>
                                </div>
                            </div>

                            <div class="btn-group">
                                <button type="button" class="btn btn-edit" onclick="enableEditing()">Edit Profile</button>
                                <button type="button" class="btn btn-secondary" onclick="cancelEditing()" style="display: none;">Cancel</button>
                                <button type="submit" class="btn btn-primary" style="display: none;">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Medical History Tab -->
                    <div id="medical-tab" class="tab-panel" style="display: none;">
                        <div class="form-section">
                            <div class="section-title">Health Information</div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="bloodType">Blood Type</label>
                                    <select id="bloodType" name="bloodType" disabled>
                                        <option value="">Select Blood Type</option>
                                        <option value="A+" selected>A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="weight">Weight (kg)</label>
                                    <input type="text" id="weight" name="weight" value="70" disabled>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="allergies">Known Allergies</label>
                                <textarea id="allergies" name="allergies" rows="3" disabled>Peanuts, Shellfish</textarea>
                            </div>

                            <div class="form-group">
                                <label for="currentMedications">Current Medications</label>
                                <textarea id="currentMedications" name="currentMedications" rows="3" disabled>None</textarea>
                            </div>

                            <div class="form-group">
                                <label for="chronicConditions">Chronic Conditions</label>
                                <textarea id="chronicConditions" name="chronicConditions" rows="3" disabled>Mild hypertension</textarea>
                            </div>
                        </div>

                        <div class="medical-history">
                            <div class="section-title">Recent Medical History</div>
                            
                            <div class="history-item">
                                <div class="history-date">March 15, 2024</div>
                                <div class="history-content">
                                    <strong>Consultation with Dr. L.M. Perera</strong><br>
                                    Chronic back pain treatment. Prescribed herbal pain relief oil and anti-inflammatory tablets.
                                </div>
                            </div>
                            
                            <div class="history-item">
                                <div class="history-date">February 28, 2024</div>
                                <div class="history-content">
                                    <strong>Follow-up Treatment</strong><br>
                                    Oil massage therapy session. Reported improvement in pain levels.
                                </div>
                            </div>
                            
                            <div class="history-item">
                                <div class="history-date">February 20, 2024</div>
                                <div class="history-content">
                                    <strong>Panchakarma Treatment</strong><br>
                                    Completed 7-day Panchakarma therapy program. Detoxification process successful.
                                </div>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-edit" onclick="enableMedicalEditing()">Edit Medical Info</button>
                            <button type="button" class="btn btn-secondary" onclick="cancelMedicalEditing()" style="display: none;">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="saveMedicalInfo()" style="display: none;">Save Changes</button>
                        </div>
                    </div>

                    <!-- Preferences Tab -->
                    <div id="preferences-tab" class="tab-panel" style="display: none;">
                        <div class="form-section">
                            <div class="section-title">Communication Preferences</div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" checked> Email notifications for appointments
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" checked> SMS reminders
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox"> Marketing communications
                                </label>
                            </div>

                            <div class="form-group">
                                <label for="preferredLanguage">Preferred Language</label>
                                <select id="preferredLanguage" name="preferredLanguage">
                                    <option value="en" selected>English</option>
                                    <option value="si">Sinhala</option>
                                    <option value="ta">Tamil</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="preferredTime">Preferred Appointment Time</label>
                                <select id="preferredTime" name="preferredTime">
                                    <option value="morning" selected>Morning (8:00 AM - 12:00 PM)</option>
                                    <option value="afternoon">Afternoon (12:00 PM - 5:00 PM)</option>
                                    <option value="evening">Evening (5:00 PM - 8:00 PM)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-title">Privacy Settings</div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" checked> Allow data for treatment improvement
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox"> Share anonymous health data for research
                                </label>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" onclick="savePreferences()">Save Preferences</button>
                            <button type="button" class="btn btn-danger" onclick="deleteAccount()">Delete Account</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab panels
            const tabPanels = document.querySelectorAll('.tab-panel');
            tabPanels.forEach(panel => {
                panel.style.display = 'none';
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab panel
            const selectedPanel = document.getElementById(tabName + '-tab');
            if (selectedPanel) {
                selectedPanel.style.display = 'block';
            }
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        function enableEditing() {
            const form = document.getElementById('personalInfoForm');
            const inputs = form.querySelectorAll('input, select, textarea');
            const buttons = form.querySelectorAll('.btn');
            
            // Enable all inputs except NIC (usually not editable)
            inputs.forEach(input => {
                if (input.name !== 'nic') {
                    input.disabled = false;
                }
            });
            
            // Show/hide appropriate buttons
            buttons[0].style.display = 'none'; // Edit button
            buttons[1].style.display = 'inline-block'; // Cancel button
            buttons[2].style.display = 'inline-block'; // Save button
            
            form.classList.add('edit-mode');
        }

        function cancelEditing() {
            const form = document.getElementById('personalInfoForm');
            const inputs = form.querySelectorAll('input, select, textarea');
            const buttons = form.querySelectorAll('.btn');
            
            // Disable all inputs
            inputs.forEach(input => {
                input.disabled = true;
            });
            
            // Reset form to original values
            form.reset();
            
            // Show/hide appropriate buttons
            buttons[0].style.display = 'inline-block'; // Edit button
            buttons[1].style.display = 'none'; // Cancel button
            buttons[2].style.display = 'none'; // Save button
            
            form.classList.remove('edit-mode');
        }

        function enableMedicalEditing() {
            const inputs = document.querySelectorAll('#medical-tab input, #medical-tab select, #medical-tab textarea');
            const buttons = document.querySelectorAll('#medical-tab .btn');
            
            inputs.forEach(input => {
                input.disabled = false;
            });
            
            buttons[0].style.display = 'none'; // Edit button
            buttons[1].style.display = 'inline-block'; // Cancel button
            buttons[2].style.display = 'inline-block'; // Save button
        }

        function cancelMedicalEditing() {
            const inputs = document.querySelectorAll('#medical-tab input, #medical-tab select, #medical-tab textarea');
            const buttons = document.querySelectorAll('#medical-tab .btn');
            
            inputs.forEach(input => {
                input.disabled = true;
            });
            
            buttons[0].style.display = 'inline-block'; // Edit button
            buttons[1].style.display = 'none'; // Cancel button
            buttons[2].style.display = 'none'; // Save button
        }

        function saveMedicalInfo() {
            showSuccessMessage('Medical information updated successfully!');
            cancelMedicalEditing();
        }

        function savePreferences() {
            showSuccessMessage('Preferences saved successfully!');
        }

        function deleteAccount() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                if (confirm('This will permanently delete all your data including medical history. Are you absolutely sure?')) {
                    alert('Account deletion initiated. You will receive a confirmation email within 24 hours.');
                }
            }
        }

        function showSuccessMessage(message) {
            const successMsg = document.getElementById('successMessage');
            successMsg.textContent = message;
            successMsg.style.display = 'block';
            
            setTimeout(() => {
                successMsg.style.display = 'none';
            }, 3000);
        }

        function showErrorMessage(message) {
            const errorMsg = document.getElementById('errorMessage');
            errorMsg.textContent = message;
            errorMsg.style.display = 'block';
            
            setTimeout(() => {
                errorMsg.style.display = 'none';
            }, 3000);
        }

        // Form submission handler
        document.getElementById('personalInfoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validate required fields
            const requiredFields = ['firstName', 'lastName', 'dateOfBirth', 'gender', 'email', 'phone'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!data[field] || !data[field].trim()) {
                    isValid = false;
                }
            });
            
            if (isValid) {
                // Simulate API call
                setTimeout(() => {
                    showSuccessMessage('Profile updated successfully!');
                    cancelEditing();
                }, 1000);
            } else {
                showErrorMessage('Please fill in all required fields.');
            }
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('94')) {
                value = '+94 ' + value.substring(2).replace(/(\d{2})(\d{3})(\d{4})/, '$1 $2 $3');
            }
            e.target.value = value;
        });

        // Emergency contact formatting
        document.getElementById('emergencyContact').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('94')) {
                value = '+94 ' + value.substring(2).replace(/(\d{2})(\d{3})(\d{4})/, '$1 $2 $3');
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
                    <div class="avatar-upload" title="Change Photo">
                        📷
                    </div>
                </div>
                <div class="patient-name">John Doe</div>
                <div class="patient-id">Patient ID: P12345</div>
                
                <div class