<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // For demo purpose only (you'll use a database later)
    if ($email === "test@example.com" && $password === "1234") {
        echo "<script>alert('✅ Login successful!');</script>";
    } else {
        echo "<script>alert('❌ Invalid credentials');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dheergayu</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <div class="header">
            Login to your account
        </div>
        
        <div class="form-container">
            <form id="loginForm" method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" placeholder="Enter your email address" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">👁</button>
                    </div>
                </div>

                <div class="forgot-password">
                    <a href="#" onclick="showForgotPassword()">Forgot Password?</a>
                </div>

                <button type="submit" class="submit-btn">LOGIN</button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="#" onclick="showSignupPage()">SIGN UP</a>
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

        function showForgotPassword() {
            alert('Redirecting to password reset page...');
        }

        function showSignupPage() {
            alert('Redirecting to sign up page...');
        }

        // document.getElementById('loginForm').addEventListener('submit', function(e) {
        //     e.preventDefault();
            
        //     const email = document.getElementById('email').value;
        //     const password = document.getElementById('password').value;
            
        //     if (!email || !password) {
        //         alert('Please fill in all fields!');
        //         return;
        //     }
            
        //     // Simulate login process
        //     const submitBtn = document.querySelector('.submit-btn');
        //     submitBtn.textContent = 'LOGGING IN...';
        //     submitBtn.disabled = true;
            
        //     setTimeout(() => {
        //         alert('Login successful! Welcome to Dheergayu.');
        //         submitBtn.textContent = 'LOGIN';
        //         submitBtn.disabled = false;
        //     }, 1500);
        //});

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