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
        <?php elseif ($success === 'signup_complete'): ?>
            <script>
                alert('Signup successful! Redirecting to login page.');
                window.location.href = 'login.php';
            </script>
        <?php endif; ?>

        <div class="form-container">
            <form id="signupForm" method="POST" action="../../backend/patient/patient_signup.php">
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
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">👁</button>
                    </div>
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
    if (field.type === 'password') { field.type = 'text'; toggle.textContent = '🙈'; }
    else { field.type = 'password'; toggle.textContent = '👁'; }
}
</script>

</body>
</html>
