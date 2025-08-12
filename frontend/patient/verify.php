<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Verify Account</title>
    <link rel="stylesheet" href="css/verify.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            Verify Your Account
        </div>
        
        <div class="form-container">
            <div class="verification-icon">📧</div>
            
            <div class="info-text">
                Enter the verification code sent to your email.
            </div>

            <div class="success-message" id="successMessage">
                Account verified successfully! Redirecting to login...
            </div>

            <div class="error-message" id="errorMessage">
                Invalid verification code. Please try again.
            </div>

            <form id="verifyForm">
                <div class="form-group">
                    <label for="verificationCode">Verification Code</label>
                    <input type="text" id="verificationCode" name="verificationCode" placeholder="Enter 6-digit code" required maxlength="6">
                </div>

                <button type="submit" class="verify-btn" id="verifyBtn">Verify</button>
            </form>

            <div class="resend-section">
                <div class="resend-text">Didn't receive code?</div>
                <a href="#" class="resend-link" id="resendLink" onclick="resendCode()">Resend</a>
                <div class="timer" id="timer" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script>
        let resendTimer = 0;
        let timerInterval;

        // Auto-format verification code input
        document.getElementById('verificationCode').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length <= 6) {
                e.target.value = value;
            }
            
            // Enable/disable verify button based on input length
            const verifyBtn = document.getElementById('verifyBtn');
            if (value.length === 6) {
                verifyBtn.disabled = false;
            } else {
                verifyBtn.disabled = true;
            }
        });

        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const verificationCode = document.getElementById('verificationCode').value;
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            
            // Hide previous messages
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
            
            if (verificationCode.length !== 6) {
                errorMessage.style.display = 'block';
                return;
            }
            
            // Simulate verification process
            const btn = document.getElementById('verifyBtn');
            btn.textContent = 'VERIFYING...';
            btn.disabled = true;
            
            setTimeout(() => {
                // Simulate successful verification (in real app, this would be an API call)
                if (verificationCode === '123456' || verificationCode.length === 6) {
                    successMessage.style.display = 'block';
                    btn.textContent = 'VERIFIED ✓';
                    btn.style.background = '#5CB85C';
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        alert('Redirecting to login page...');
                    }, 2000);
                } else {
                    errorMessage.style.display = 'block';
                    btn.textContent = 'Verify';
                    btn.disabled = false;
                }
            }, 1500);
        });

        function resendCode() {
            const resendLink = document.getElementById('resendLink');
            const timer = document.getElementById('timer');
            
            if (resendTimer > 0) return;
            
            // Simulate sending code
            alert('Verification code sent to your email!');
            
            // Start countdown
            resendTimer = 60;
            resendLink.classList.add('disabled');
            resendLink.style.display = 'none';
            timer.style.display = 'block';
            
            timerInterval = setInterval(() => {
                timer.textContent = `Resend in ${resendTimer}s`;
                resendTimer--;
                
                if (resendTimer < 0) {
                    clearInterval(timerInterval);
                    resendLink.classList.remove('disabled');
                    resendLink.style.display = 'inline';
                    timer.style.display = 'none';
                }
            }, 1000);
        }

        // Auto-focus on verification code input
        window.addEventListener('load', function() {
            document.getElementById('verificationCode').focus();
        });

        // Handle paste event for verification code
        document.getElementById('verificationCode').addEventListener('paste', function(e) {
            setTimeout(() => {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 6) {
                    value = value.substring(0, 6);
                }
                this.value = value;

                if (value.length === 6) {
                    document.getElementById('verifyBtn').disabled = false;
                }
            }, 10);
        });
    </script>
</body>
</html>