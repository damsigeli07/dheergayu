<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <link rel="stylesheet" href="css/password_reset.css?v=<?php echo time(); ?>">
</head>
<body>


<div class="form-container-main">
    <div class="reset-container">
        <div class="title">
            Reset your Password
        </div>
        <div class="form-container">
            <form id="resetForm">
                <div class="form-group">
                    <label for="verificationCode">Enter the code sent to your email</label>
                    <input 
                        type="text" 
                        id="verificationCode" 
                        placeholder="Enter verification code"
                        required
                    >
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
            
            const code = document.getElementById('verificationCode').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!code || !newPassword || !confirmPassword) {
                alert('Please fill in all fields');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            if (newPassword.length < 6) {
                alert('Password must be at least 6 characters long');
                return;
            }
            
            alert('Password reset successful!');
            this.reset();
        });

        
    </script>
</body>
</html>