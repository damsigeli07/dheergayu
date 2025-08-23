<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Setup - Dheergayu</title>
    <link rel="stylesheet" href="css/signup.css?v=<?php echo time(); ?>">
</head>
<body>
<!-- Header -->
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
<div class="form-container-main">
    <div class="container">
        <div class="title">
            Let's get your account set up
        </div>
        <div class="form-container">
            <form id="signupForm">
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" class="date-input" required>
                </div>

                <div class="form-group">
                    <label for="nic">NIC</label>
                    <input type="text" id="nic" placeholder="Enter your NIC number" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" placeholder="Enter your email address" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" placeholder="Create a strong password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">👁</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirmPassword" placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">👁</button>
                    </div>
                </div>

                <button type="submit" class="submit-btn">SIGN UP</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Log In</a>
            </div>
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
            } else {
                field.type = 'password';
                toggle.textContent = '👁';
            }
        }

        function showLoginPage() {
            alert('Redirecting to login page...');
        }

        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters long!');
                return;
            }
            
            alert('Account created successfully! Please check your email for verification.');
        });

        // Add smooth focus animations
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>