<?php
// Check if there's a status message
$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Setup - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/signup.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="form-container-main">
    <div class="container">
        <a href="home.php" class="close-btn" aria-label="Close">×</a>
        <div class="title">Let's get your account set up</div>

        <?php if ($error === 'password_mismatch'): ?>
            <script>alert('Passwords do not match!');</script>
        <?php elseif ($error === 'already_exists'): ?>
            <script>alert('Email or NIC already registered!');</script>
        <?php elseif ($error === 'database_error'): ?>
            <script>alert('Database error! Please try again later.');</script>
        <?php elseif ($error === 'invalid_dob'): ?>
            <script>alert('Date of birth must be between 1925 and 2007!');</script>
        <?php elseif ($error === 'weak_password'): ?>
            <script>alert('Password does not meet security requirements!');</script>
        <?php elseif ($success === 'signup_complete'): ?>
            <script>
                alert('Signup successful! Redirecting to login page.');
                window.location.href = 'login.php';
            </script>
        <?php endif; ?>

        <div class="form-container">
            <form id="signupForm" method="POST" action="/dheergayu/app/Controllers/patient_signup.php" onsubmit="return validateForm(event)">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="first_name" placeholder="Enter your first name" required>
                </div>

                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="last_name" placeholder="Enter your last name" required>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                    <small id="dobError" class="error-message" style="color: red; display: none; font-size: 12px; margin-top: 5px;">Date must be between 1925 and 2007</small>
                </div>

                <div class="form-group">
                    <label for="nic">NIC</label>
                    <input type="text" id="nic" name="nic" placeholder="Enter your NIC number" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">👁</button>
                    </div>
                    <small style="color: #666; font-size: 11px; margin-top: 5px; display: block;">
                        Must be 8+ characters with uppercase, lowercase, number & special character
                    </small>
                    <div id="passwordStrength" style="margin-top: 8px;">
                        <div id="strengthBar" style="height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
                            <div id="strengthProgress" style="height: 100%; width: 0%; transition: all 0.3s;"></div>
                        </div>
                        <small id="strengthText" style="font-size: 11px; margin-top: 4px; display: block;"></small>
                    </div>
                    <ul id="passwordRequirements" style="list-style: none; padding: 0; margin-top: 8px; font-size: 11px;">
                        <li id="req-length" style="color: #999;">✗ At least 8 characters</li>
                        <li id="req-uppercase" style="color: #999;">✗ One uppercase letter</li>
                        <li id="req-lowercase" style="color: #999;">✗ One lowercase letter</li>
                        <li id="req-number" style="color: #999;">✗ One number</li>
                        <li id="req-special" style="color: #999;">✗ One special character (!@#$%^&*)</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">🙈</button>
                    </div>
                    <small id="confirmPasswordError" class="error-message" style="color: red; display: none; font-size: 12px; margin-top: 5px;">Passwords do not match</small>
                </div>

                <button type="submit" class="submit-btn">SIGN UP</button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.nextElementSibling;
    if (field.type === 'password') { 
        field.type = 'text'; 
        toggle.textContent = '🙈'; 
    }
    else { 
        field.type = 'password'; 
        toggle.textContent = '👁'; 
    }
}

function validatePassword(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };
    
    return requirements;
}

function updatePasswordStrength(password) {
    const requirements = validatePassword(password);
    const metCount = Object.values(requirements).filter(Boolean).length;
    const strengthProgress = document.getElementById('strengthProgress');
    const strengthText = document.getElementById('strengthText');
    
    // Update requirement checklist
    document.getElementById('req-length').style.color = requirements.length ? '#4CAF50' : '#999';
    document.getElementById('req-length').textContent = (requirements.length ? '✓' : '✗') + ' At least 8 characters';
    
    document.getElementById('req-uppercase').style.color = requirements.uppercase ? '#4CAF50' : '#999';
    document.getElementById('req-uppercase').textContent = (requirements.uppercase ? '✓' : '✗') + ' One uppercase letter';
    
    document.getElementById('req-lowercase').style.color = requirements.lowercase ? '#4CAF50' : '#999';
    document.getElementById('req-lowercase').textContent = (requirements.lowercase ? '✓' : '✗') + ' One lowercase letter';
    
    document.getElementById('req-number').style.color = requirements.number ? '#4CAF50' : '#999';
    document.getElementById('req-number').textContent = (requirements.number ? '✓' : '✗') + ' One number';
    
    document.getElementById('req-special').style.color = requirements.special ? '#4CAF50' : '#999';
    document.getElementById('req-special').textContent = (requirements.special ? '✓' : '✗') + ' One special character (!@#$%^&*)';
    
    // Update strength bar
    const percentage = (metCount / 5) * 100;
    strengthProgress.style.width = percentage + '%';
    
    if (metCount === 0) {
        strengthProgress.style.background = '#e0e0e0';
        strengthText.textContent = '';
    } else if (metCount <= 2) {
        strengthProgress.style.background = '#f44336';
        strengthText.textContent = 'Weak';
        strengthText.style.color = '#f44336';
    } else if (metCount <= 3) {
        strengthProgress.style.background = '#ff9800';
        strengthText.textContent = 'Fair';
        strengthText.style.color = '#ff9800';
    } else if (metCount === 4) {
        strengthProgress.style.background = '#2196F3';
        strengthText.textContent = 'Good';
        strengthText.style.color = '#2196F3';
    } else {
        strengthProgress.style.background = '#4CAF50';
        strengthText.textContent = 'Strong';
        strengthText.style.color = '#4CAF50';
    }
}

function validateDOB() {
    const dobInput = document.getElementById('dob').value;
    const dobError = document.getElementById('dobError');
    
    if (!dobInput) {
        dobError.style.display = 'none';
        return false;
    }
    
    const dob = new Date(dobInput);
    const year = dob.getFullYear();
    
    if (year < 1925 || year > 2007) {
        dobError.style.display = 'block';
        return false;
    }
    
    dobError.style.display = 'none';
    return true;
}

function validateForm(event) {
    let isValid = true;
    
    // Validate DOB
    if (!validateDOB()) {
        isValid = false;
    }
    
    // Validate password strength
    const password = document.getElementById('password').value;
    const requirements = validatePassword(password);
    const allRequirementsMet = Object.values(requirements).every(Boolean);
    
    if (!allRequirementsMet) {
        alert('Password does not meet all security requirements!');
        isValid = false;
    }
    
    // Validate password match
    const confirmPassword = document.getElementById('confirmPassword').value;
    const confirmError = document.getElementById('confirmPasswordError');
    
    if (password !== confirmPassword) {
        confirmError.style.display = 'block';
        isValid = false;
    } else {
        confirmError.style.display = 'none';
    }
    
    if (!isValid) {
        event.preventDefault();
    }
    
    return isValid;
}

// Real-time DOB validation
document.getElementById('dob').addEventListener('change', validateDOB);

// Real-time password validation
document.getElementById('password').addEventListener('input', function() {
    updatePasswordStrength(this.value);
});

// Real-time confirm password validation
document.getElementById('confirmPassword').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const confirmError = document.getElementById('confirmPasswordError');
    
    if (confirmPassword && password !== confirmPassword) {
        confirmError.style.display = 'block';
    } else {
        confirmError.style.display = 'none';
    }
});
</script>

</body>
</html>