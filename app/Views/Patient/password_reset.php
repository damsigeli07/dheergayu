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
        <div class="title" style="position:relative;">
            Reset your Password
            <a href="/dheergayu/app/Views/Patient/login.php" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);color:#fff;font-size:1.3rem;text-decoration:none;line-height:1;" title="Close">&times;</a>
        </div>
        <div class="form-container">
            <form id="resetForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter account email" required>
                </div>

                <div class="form-group">
                    <label for="nic">NIC (or Registered Phone for Staff/Supplier/Doctor/Pharmacist)</label>
                    <input type="text" id="nic" name="nic" placeholder="Enter NIC (or phone number)" required>
                </div>

                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="newPassword"
                            placeholder="Enter new password"
                            required
                            autocomplete="off"
                        >
                        <svg class="eye-icon" onclick="togglePassword('newPassword')" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                    <div id="strength-bar-wrap" style="margin-top:8px;display:none;">
                        <div style="height:6px;border-radius:4px;background:#eee;overflow:hidden;">
                            <div id="strength-bar" style="height:100%;width:0;border-radius:4px;transition:width .3s,background .3s;"></div>
                        </div>
                        <p id="strength-label" style="font-size:.78rem;margin-top:4px;color:#aaa;"></p>
                    </div>
                    <ul id="pw-rules" style="list-style:none;margin-top:10px;padding:0;font-size:.8rem;display:none;">
                        <li id="rule-len">  ✗ At least 8 characters</li>
                        <li id="rule-upper">✗ One uppercase letter</li>
                        <li id="rule-lower">✗ One lowercase letter</li>
                        <li id="rule-num">  ✗ One number</li>
                        <li id="rule-spec"> ✗ One special character (!@#$%^&amp;*)</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="confirmPassword"
                            placeholder="Confirm new password"
                            required
                            autocomplete="off"
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

        const pwInput   = document.getElementById('newPassword');
        const barWrap   = document.getElementById('strength-bar-wrap');
        const bar       = document.getElementById('strength-bar');
        const lbl       = document.getElementById('strength-label');
        const ruleLen   = document.getElementById('rule-len');
        const ruleUpper = document.getElementById('rule-upper');
        const ruleLower = document.getElementById('rule-lower');
        const ruleNum   = document.getElementById('rule-num');
        const ruleSpec  = document.getElementById('rule-spec');
        const colors    = ['#e53935','#FF9800','#FDD835','#43a047'];
        const labels    = ['Weak','Fair','Good','Strong'];

        function setRule(el, pass) {
            el.textContent = (pass ? '✓' : '✗') + el.textContent.slice(1);
            el.style.color = pass ? '#43a047' : '#e53935';
        }

        pwInput.addEventListener('input', function() {
            const v = this.value;
            const rules = document.getElementById('pw-rules');
            rules.style.display   = v.length ? 'block' : 'none';
            barWrap.style.display = v.length ? 'block' : 'none';
            const checks = {
                len:   v.length >= 8,
                upper: /[A-Z]/.test(v),
                lower: /[a-z]/.test(v),
                num:   /[0-9]/.test(v),
                spec:  /[!@#$%^&*]/.test(v)
            };
            setRule(ruleLen,   checks.len);
            setRule(ruleUpper, checks.upper);
            setRule(ruleLower, checks.lower);
            setRule(ruleNum,   checks.num);
            setRule(ruleSpec,  checks.spec);
            const score = Object.values(checks).filter(Boolean).length;
            bar.style.width      = (score / 5 * 100) + '%';
            bar.style.background = colors[Math.min(score - 1, 3)] || '#eee';
            lbl.textContent      = score > 0 ? labels[Math.min(score - 1, 3)] : '';
            lbl.style.color      = colors[Math.min(score - 1, 3)] || '#aaa';
        });

        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.set('email', document.getElementById('email').value.trim());
            formData.set('nic', document.getElementById('nic').value.trim());
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
                nic: { required: true, message: 'NIC (or registered phone) is required.' }
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
            payload.append('email', String(formData.get('email')));
            payload.append('nic', String(formData.get('nic')));
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