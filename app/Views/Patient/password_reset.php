<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/password_reset.css?v=<?php echo time(); ?>">
</head>
<body>


<div class="form-container-main">
    <div class="reset-container">
        <div class="title">Reset your Password</div>
        <div class="form-container">
            <form id="resetForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter account email" required>
                </div>

                <div class="form-group">
                    <label for="nic">NIC</label>
                    <input type="text" id="nic" name="nic" placeholder="Enter NIC used at signup" required>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                </div>

                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="newPassword"
                            placeholder="Enter new password"
                            required
                        >
                        <svg class="eye-icon" onclick="togglePassword('newPassword')" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="confirmPassword"
                            placeholder="Confirm new password"
                            required
                        >
                        <svg class="eye-icon" onclick="togglePassword('confirmPassword')" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
    <script src="/dheergayu/public/assets/js/patient-form-utils.js"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.style.opacity = '1';
            } else {
                input.type = 'password';
                icon.style.opacity = '0.6';
            }
        }

        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.set('account_type', 'patient');
            formData.set('email', document.getElementById('email').value.trim());
            formData.set('nic', document.getElementById('nic').value.trim());
            formData.set('dob', document.getElementById('dob').value);
            formData.set('newPassword', document.getElementById('newPassword').value);
            formData.set('confirmPassword', document.getElementById('confirmPassword').value);

            const baseRules = {
                email: {
                    required: true,
                    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    message: 'Please enter a valid email address.'
                },
                newPassword: {
                    required: true,
                    pattern: /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/,
                    message: 'Password must be 8+ chars with uppercase, lowercase, number and special character.'
                }
            };

            const extraRules = {
                nic: { required: true, message: 'NIC is required for patient reset.' },
                dob: { required: true, message: 'Date of birth is required for patient reset.' }
            };

            const confirmRule = {
                confirmPassword: {
                    required: true,
                    custom: function (value, fd) {
                        return value !== String(fd.get('newPassword')) ? 'Passwords do not match.' : '';
                    }
                }
            };

            const error = PatientFormUtils.validateRules(formData, { ...baseRules, ...extraRules, ...confirmRule });
            if (error) {
                alert(error);
                return;
            }

            const payload = new FormData();
            payload.append('account_type', 'patient');
            payload.append('email', String(formData.get('email')));
            payload.append('nic', String(formData.get('nic')));
            payload.append('dob', String(formData.get('dob')));
            payload.append('new_password', String(formData.get('newPassword')));
            payload.append('confirm_password', String(formData.get('confirmPassword')));

            fetch('/dheergayu/public/api/reset-patient-password.php', {
                method: 'POST',
                body: payload
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Password reset failed.');
                    return;
                }
                alert(data.message || 'Password reset successful.');
                window.location.href = '/dheergayu/app/Views/Patient/login.php';
            })
            .catch(() => {
                alert('Unable to reset password now. Please try again.');
            });
        });

        
    </script>
</body>
</html>