<?php
session_start();

// Include database connection
require_once(dirname(__DIR__, 3) . "/config/db_connect.php");

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        // 1) Try patients table
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password FROM patients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type'] = 'patient';
                $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
                $_SESSION['role'] = 'Patient';
                $_SESSION['name'] = $_SESSION['user_name'];

                header("Location: home.php");
                exit();
            }
        }

        // 2) Try users table
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type'] = strtolower($row['role']);
                $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $_SESSION['user_name'];

                switch (strtolower($row['role'])) {
                    case 'admin':
                        header("Location: ../admin/admindashboard.php");
                        break;
                    case 'doctor':
                        header("Location: ../doctor/doctordashboard.php");
                        break;
                    case 'staff':
                        header("Location: ../staff/staffhome.php");
                        break;
                    case 'pharmacist':
                        header("Location: ../pharmacist/pharmacisthome.php");
                        break;
                    default:
                        header("Location: home.php");
                        break;
                }
                exit();
            }
        }

        // If neither matched
        $error_message = "Invalid email or password.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/login.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="form-container-main">
    <div class="container">
        <a href="home.php" class="close-btn" aria-label="Close">×</a>
        <div class="form_header">
            Login to your account
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message" style="color: red; text-align: center; margin: 10px 0; padding: 10px; background: #ffe6e6; border-radius: 5px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'signup_complete'): ?>
            <div class="success-message" style="color: green; text-align: center; margin: 10px 0; padding: 10px; background: #e6ffe6; border-radius: 5px;">
                Account created successfully! Please login with your credentials.
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