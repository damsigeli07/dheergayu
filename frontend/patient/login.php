<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // For demo purpose only (you'll use a database later)
    if ($email == "test@example.com" && $password == "1234") {
        // Set session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_type'] = 'patient'; // You can determine this from database
        $_SESSION['user_name'] = 'Test User'; // Get from database
        
        // Redirect to home page
        header("Location: home.php");
        exit();
    } else {
        $error_message = "Invalid credentials. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dheergayu</title>
    <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="form-container-main">
    <div class="container">
        <a href="home.php" class="close-btn" aria-label="Close">×</a>
        <div class="form_header">
            Login to your account
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message" style="color: red; text-align: center; margin: 10px 0; padding: 10px; background: #ffe6e6; border-radius: 5px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form id="loginForm" method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">👁</button>
                    </div>
                </div>

                <div class="forgot-password">
                    <a href="password_reset.php">Forgot Password?</a>
                </div>

                <button type="submit" class="submit-btn" id="loginBtn">LOGIN</button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">SIGN UP</a>
                <p>Not a Patient?</p>
            </div>
            
            <div class="login-link">
                <div class="user-link"><a href="/../../dheergayu/frontend/doctor/doctordashboard.php">Doctor</a></div> |
                <div class="user-link"><a href="/../../dheergayu/frontend/admin/admindashboard.php">Admin</a></div> |
                <div class="user-link"><a href="/../../dheergayu/frontend/pharmacist/pharmacisthome.php">Pharmacist</a></div> |
                <div class="user-link"><a href="/../../dheergayu/frontend/staff/staffhome.php">Staff</a></div>
            </div>
        </div>
    </div>
</div>

    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const toggle = document.querySelector('.password-toggle');
            if (field.type === 'password') {
                field.type = 'text';
                toggle.textContent = '🙈';
            } else {
                field.type = 'password';
                toggle.textContent = '👁';
            }
        }

        // Add loading state to login button
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('loginBtn');
            submitBtn.textContent = 'LOGGING IN...';
            submitBtn.disabled = true;
        });

        // Add input animation effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
    
</body>
</html>