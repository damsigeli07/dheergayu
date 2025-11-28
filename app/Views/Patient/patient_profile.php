<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/Models/PatientModel.php';

$model = new PatientModel($conn);
$user_id = $_SESSION['user_id'];

// Check if profile exists, if not create one
if (!$model->profileExists($user_id)) {
    $model->createProfile($user_id, $_SESSION['user_email'] ?? '', $_SESSION['user_name'] ?? '');
}

// Get profile data
$profile = $model->getProfileByUserId($user_id);
$stats = $model->getAppointmentStats($user_id);
$history = $model->getRecentMedicalHistory($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - My Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/patient_profile.css?v=<?php echo time(); ?>">
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
            <h1 class="page-title">My Profile</h1>
        </div>

        <div class="success-message" id="successMessage" style="display: none;">
            Profile updated successfully!
        </div>

        <div class="error-message" id="errorMessage" style="display: none;">
            Please check all required fields and try again.
        </div>

        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <img src="/dheergayu/public/assets/images/Patient/profile_photo.jpg" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div class="patient-name"><?php echo htmlspecialchars($profile['first_name'] ?? '') . ' ' . htmlspecialchars($profile['last_name'] ?? ''); ?></div>
                <div class="patient-id">Patient ID: P<?php echo str_pad($user_id, 5, '0', STR_PAD_LEFT); ?></div>
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
                            <input type="hidden" name="action" value="personal">
                            
                            <div class="form-section">
                                <div class="section-title">Basic Information</div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" 
                                               disabled required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" 
                                               disabled required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="date_of_birth">Date of Birth *</label>
                                        <input type="date" id="date_of_birth" name="date_of_birth" 
                                               value="<?php echo $profile['date_of_birth'] ?? ''; ?>" 
                                               disabled>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender">Gender *</label>
                                        <select id="gender" name="gender" disabled>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php echo ($profile['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($profile['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($profile['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nic">NIC Number</label>
                                    <input type="text" id="nic" name="nic" 
                                           value="<?php echo htmlspecialchars($profile['nic'] ?? ''); ?>" 
                                           disabled>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-title">Contact Information</div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" 
                                           disabled required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" 
                                               disabled>
                                    </div>
                                    <div class="form-group">
                                        <label for="emergency_contact">Emergency Contact</label>
                                        <input type="tel" id="emergency_contact" name="emergency_contact" 
                                               value="<?php echo htmlspecialchars($profile['emergency_contact'] ?? ''); ?>" 
                                               disabled>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address" rows="3" disabled><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
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
                        <form id="medicalInfoForm">
                            <input type="hidden" name="action" value="medical">
                            
                            <div class="form-section">
                                <div class="section-title">Health Information</div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="blood_type">Blood Type</label>
                                        <select id="blood_type" name="blood_type" disabled>
                                            <option value="">Select Blood Type</option>
                                            <option value="A+" <?php echo ($profile['blood_type'] ?? '') == 'A+' ? 'selected' : ''; ?>>A+</option>
                                            <option value="A-" <?php echo ($profile['blood_type'] ?? '') == 'A-' ? 'selected' : ''; ?>>A-</option>
                                            <option value="B+" <?php echo ($profile['blood_type'] ?? '') == 'B+' ? 'selected' : ''; ?>>B+</option>
                                            <option value="B-" <?php echo ($profile['blood_type'] ?? '') == 'B-' ? 'selected' : ''; ?>>B-</option>
                                            <option value="AB+" <?php echo ($profile['blood_type'] ?? '') == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                            <option value="AB-" <?php echo ($profile['blood_type'] ?? '') == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                            <option value="O+" <?php echo ($profile['blood_type'] ?? '') == 'O+' ? 'selected' : ''; ?>>O+</option>
                                            <option value="O-" <?php echo ($profile['blood_type'] ?? '') == 'O-' ? 'selected' : ''; ?>>O-</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="weight">Weight (kg)</label>
                                        <input type="number" id="weight" name="weight" step="0.01"
                                               value="<?php echo $profile['weight'] ?? ''; ?>" 
                                               disabled>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="allergies">Known Allergies</label>
                                    <textarea id="allergies" name="allergies" rows="3" disabled><?php echo htmlspecialchars($profile['allergies'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="current_medications">Current Medications</label>
                                    <textarea id="current_medications" name="current_medications" rows="3" disabled><?php echo htmlspecialchars($profile['current_medications'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="chronic_conditions">Chronic Conditions</label>
                                    <textarea id="chronic_conditions" name="chronic_conditions" rows="3" disabled><?php echo htmlspecialchars($profile['chronic_conditions'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div class="medical-history">
                                <div class="section-title">Recent Medical History</div>
                                
                                <?php if (!empty($history)): ?>
                                    <?php foreach ($history as $record): ?>
                                        <div class="history-item">
                                            <div class="history-date"><?php echo date('F d, Y', strtotime($record['appointment_date'])); ?></div>
                                            <div class="history-content">
                                                <strong><?php echo $record['type']; ?>
                                                <?php if ($record['doctor_name']): ?>
                                                    with Dr. <?php echo htmlspecialchars($record['doctor_name']); ?>
                                                <?php endif; ?>
                                                </strong><br>
                                                <?php echo htmlspecialchars($record['details']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color: #999; padding: 20px;">No medical history available</p>
                                <?php endif; ?>
                            </div>

                            <div class="btn-group">
                                <button type="button" class="btn btn-edit" onclick="enableMedicalEditing()">Edit Medical Info</button>
                                <button type="button" class="btn btn-secondary" onclick="cancelMedicalEditing()" style="display: none;">Cancel</button>
                                <button type="submit" class="btn btn-primary" style="display: none;">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Preferences Tab -->
                    <div id="preferences-tab" class="tab-panel" style="display: none;">
                        <form id="preferencesForm">
                            <input type="hidden" name="action" value="preferences">
                            
                            <div class="form-section">
                                <div class="section-title">Communication Preferences</div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="email_notifications" 
                                               <?php echo ($profile['email_notifications'] ?? 1) ? 'checked' : ''; ?>> 
                                        Email notifications for appointments
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="sms_notifications" 
                                               <?php echo ($profile['sms_notifications'] ?? 1) ? 'checked' : ''; ?>> 
                                        SMS reminders
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="marketing_communications" 
                                               <?php echo ($profile['marketing_communications'] ?? 0) ? 'checked' : ''; ?>> 
                                        Marketing communications
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label for="preferred_language">Preferred Language</label>
                                    <select id="preferred_language" name="preferred_language">
                                        <option value="en" <?php echo ($profile['preferred_language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="si" <?php echo ($profile['preferred_language'] ?? '') == 'si' ? 'selected' : ''; ?>>Sinhala</option>
                                        <option value="ta" <?php echo ($profile['preferred_language'] ?? '') == 'ta' ? 'selected' : ''; ?>>Tamil</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="preferred_time">Preferred Appointment Time</label>
                                    <select id="preferred_time" name="preferred_time">
                                        <option value="morning" <?php echo ($profile['preferred_time'] ?? 'morning') == 'morning' ? 'selected' : ''; ?>>Morning (8:00 AM - 12:00 PM)</option>
                                        <option value="afternoon" <?php echo ($profile['preferred_time'] ?? '') == 'afternoon' ? 'selected' : ''; ?>>Afternoon (12:00 PM - 5:00 PM)</option>
                                        <option value="evening" <?php echo ($profile['preferred_time'] ?? '') == 'evening' ? 'selected' : ''; ?>>Evening (5:00 PM - 8:00 PM)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-title">Privacy Settings</div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="allow_data_improvement" 
                                               <?php echo ($profile['allow_data_improvement'] ?? 1) ? 'checked' : ''; ?>> 
                                        Allow data for treatment improvement
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="share_research_data" 
                                               <?php echo ($profile['share_research_data'] ?? 0) ? 'checked' : ''; ?>> 
                                        Share anonymous health data for research
                                    </label>
                                </div>
                            </div>

                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">Save Preferences</button>
                                <button type="button" class="btn btn-danger" onclick="deleteAccount()">Delete Account</button>
                            </div>
                        </form>
                    </div>
                </div>
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
        function showTab(tabName) {
            document.querySelectorAll('.tab-panel').forEach(panel => panel.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName + '-tab').style.display = 'block';
            event.target.classList.add('active');
        }

        function enableEditing() {
            const form = document.getElementById('personalInfoForm');
            const inputs = form.querySelectorAll('input:not([name="action"]), select, textarea');
            const buttons = form.querySelectorAll('.btn');
            
            inputs.forEach(input => {
                if (input.name !== 'nic') {
                    input.disabled = false;
                }
            });
            
            buttons[0].style.display = 'none';
            buttons[1].style.display = 'inline-block';
            buttons[2].style.display = 'inline-block';
        }

        function cancelEditing() {
            location.reload();
        }

        function enableMedicalEditing() {
            const inputs = document.querySelectorAll('#medicalInfoForm input:not([name="action"]), #medicalInfoForm select, #medicalInfoForm textarea');
            const buttons = document.querySelectorAll('#medicalInfoForm .btn');
            
            inputs.forEach(input => input.disabled = false);
            
            buttons[0].style.display = 'none';
            buttons[1].style.display = 'inline-block';
            buttons[2].style.display = 'inline-block';
        }

        function cancelMedicalEditing() {
            location.reload();
        }

        function deleteAccount() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                if (confirm('This will permanently delete all your data including medical history. Are you absolutely sure?')) {
                    fetch('/dheergayu/public/api/delete-account.php', {
                        method: 'POST'
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Account deleted successfully');
                            window.location.href = 'login.php';
                        } else {
                            alert('Error: ' + (data.error || 'Failed to delete account'));
                        }
                    });
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

        // Personal Info Form Submit
        document.getElementById('personalInfoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/dheergayu/public/api/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorMessage(data.error || 'Failed to update profile');
                }
            })
            .catch(err => {
                showErrorMessage('An error occurred. Please try again.');
            });
        });

        // Medical Info Form Submit
        document.getElementById('medicalInfoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/dheergayu/public/api/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorMessage(data.error || 'Failed to update medical information');
                }
            })
            .catch(err => {
                showErrorMessage('An error occurred. Please try again.');
            });
        });

        // Preferences Form Submit
        document.getElementById('preferencesForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/dheergayu/public/api/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                } else {
                    showErrorMessage(data.error || 'Failed to save preferences');
                }
            })
            .catch(err => {
                showErrorMessage('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>