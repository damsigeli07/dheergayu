<!DOCTYPE html>
<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /dheergayu/app/Views/Patient/login.php');
    exit;
}
$error = $_SESSION['change_pw_error'] ?? '';
$success = $_SESSION['change_pw_success'] ?? '';
unset($_SESSION['change_pw_error'], $_SESSION['change_pw_success']);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Dheergayu</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .logo-wrap {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-wrap img {
            height: 60px;
        }
        .logo-wrap h1 {
            font-size: 1.4rem;
            color: #5b8a6e;
            margin-top: 0.5rem;
        }
        h2 {
            font-size: 1.2rem;
            color: #2d2d2d;
            margin-bottom: 0.4rem;
        }
        .subtitle {
            font-size: 0.88rem;
            color: #777;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.1rem;
        }
        label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 0.4rem;
        }
        input[type="password"] {
            width: 100%;
            padding: 0.65rem 0.9rem;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }
        input[type="password"]:focus { border-color: #5b8a6e; }
        .error-msg {
            background: #fde8e8;
            color: #c0392b;
            border-radius: 8px;
            padding: 0.7rem 1rem;
            font-size: 0.88rem;
            margin-bottom: 1.2rem;
        }
        .success-msg {
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 8px;
            padding: 0.7rem 1rem;
            font-size: 0.88rem;
            margin-bottom: 1.2rem;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #5b8a6e;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }
        .btn:hover { background: #4a7560; }
        .hint {
            font-size: 0.8rem;
            color: #aaa;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-wrap">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo">
            <h1>Dheergayu</h1>
        </div>

        <h2>Set Your Password</h2>
        <p class="subtitle">You're logging in for the first time. Please set a new password to continue.</p>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-msg"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="/dheergayu/app/Controllers/change_password_action.php">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="Enter new password">
                <p class="hint">Minimum 6 characters</p>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password">
            </div>
            <button type="submit" class="btn">Save Password &amp; Continue</button>
        </form>
    </div>
</body>
</html>
