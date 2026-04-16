<?php
// app/Views/Patient/session_payment.php
session_start();
require_once __DIR__ . '/../../../config/payhere_config.php';
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId    = $_SESSION['user_id'];
$userName  = $_SESSION['user_name']  ?? 'Guest User';
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = '';

$piStmt = $conn->prepare("SELECT phone FROM patient_info WHERE patient_id = ? LIMIT 1");
if ($piStmt) {
    $piStmt->bind_param('i', $userId);
    $piStmt->execute();
    $piRow = $piStmt->get_result()->fetch_assoc();
    $piStmt->close();
    $userPhone = $piRow['phone'] ?? '';
}

$planId        = (int)($_GET['plan_id'] ?? 0);
$sessionNumber = (int)($_GET['session_number'] ?? 0);

if (!$planId || !$sessionNumber) {
    header('Location: patient_appointments.php');
    exit;
}

// Fetch plan
$stmt = $conn->prepare("SELECT tp.*, tl.treatment_name AS list_treatment_name FROM treatment_plans tp LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id WHERE tp.plan_id = ? AND tp.patient_id = ?");
$stmt->bind_param('ii', $planId, $userId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    header('Location: patient_appointments.php');
    exit;
}

// Fetch the specific session
$sStmt = $conn->prepare("SELECT * FROM treatment_sessions WHERE plan_id = ? AND session_number = ? LIMIT 1");
$sStmt->bind_param('ii', $planId, $sessionNumber);
$sStmt->execute();
$session = $sStmt->get_result()->fetch_assoc();
$sStmt->close();

if (!$session || $session['status'] === 'Confirmed' || $session['status'] === 'Completed') {
    header('Location: patient_appointments.php');
    exit;
}

$fee           = (float)$plan['total_cost'];
$treatmentName = $plan['treatment_name'] ?? $plan['list_treatment_name'] ?? 'Treatment';
$description   = $treatmentName . ' — Session #' . $sessionNumber;
$orderId       = generateOrderId();
$isSandbox     = (PAYHERE_MODE === 'sandbox');
$showTestPayment = payhere_test_payment_allowed();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay for Session - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/payment.css?v=<?php echo time(); ?>">
    <style>
        .sandbox-notice { background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 18px;margin-bottom:20px;font-size:.9rem;color:#856404; }
        .sandbox-notice strong { color:#533f03; }
        .test-pay-btn { width:100%;padding:18px;background:linear-gradient(135deg,#28a745,#218838);color:#fff;border:none;border-radius:10px;font-size:1.1rem;font-weight:600;cursor:pointer;margin-top:12px;transition:all .3s;letter-spacing:.5px; }
        .test-pay-btn:hover { transform:translateY(-2px);box-shadow:0 8px 20px rgba(40,167,69,.35); }
        .test-pay-btn:disabled { opacity:.6;cursor:not-allowed;transform:none; }
        .divider { text-align:center;margin:14px 0;color:#999;font-size:.9rem; }
        .appointment-summary { background:#f8f9fa;border-radius:12px;padding:24px; }
        .appointment-summary h3 { color:#333;margin-bottom:16px;font-size:1.1rem; }
        .summary-item { display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #e9ecef;font-size:.95rem; }
        .summary-item:last-child { border-bottom:none; }
        .summary-item .label { color:#666; }
        .summary-item .value { font-weight:600;color:#333; }
        .summary-total { display:flex;justify-content:space-between;padding:16px 0 0;margin-top:8px;border-top:2px solid #333;font-size:1.15rem; }
        .summary-total .label { font-weight:600;color:#333; }
        .summary-total .value { font-weight:700;color:#28a745;font-size:1.25rem; }
        .session-highlight { background:#e8f5e9;border:1px solid #4caf50;border-radius:8px;padding:14px 18px;margin-bottom:16px; }
        .session-highlight strong { color:#2e7d32;font-size:1.05rem; }
        .session-highlight p { color:#555;font-size:.9rem;margin:6px 0 0; }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="/dheergayu/public/assets/images/Patient/logo_modern.png" alt="Dheergayu Logo">
                <h1>DHEERGAYU <br> <span>AYURVEDIC MANAGEMENT CENTER</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php">HOME</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/contact_us.php">CONTACT US</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="patient_appointments.php" class="back-btn">&larr; Back to Appointments</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="checkout-grid">

            <!-- Payment Details -->
            <div class="card">
                <h2 class="card-title">Payment Details</h2>

                <?php if ($showTestPayment): ?>
                <div class="sandbox-notice">
                    <strong>&#9888;&#65039; Sandbox Mode Active.</strong>
                    Use <em>"Test Payment"</em> to simulate a successful payment.
                </div>
                <?php endif; ?>

                <form id="customerForm">
                    <div class="form-group">
                        <label for="customerName">Full Name *</label>
                        <input type="text" id="customerName" value="<?= htmlspecialchars($userName) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="customerEmail">Email *</label>
                        <input type="email" id="customerEmail" value="<?= htmlspecialchars($userEmail) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="customerPhone">Phone *</label>
                        <input type="tel" id="customerPhone" value="<?= htmlspecialchars($userPhone) ?>" placeholder="0712345678" required>
                    </div>

                    <?php if ($showTestPayment): ?>
                    <button type="button" class="test-pay-btn" id="testPayBtn" onclick="simulatePayment()">
                        &#10004; Test Payment — Rs. <?= number_format($fee, 2) ?> (no gateway)
                    </button>
                    <div class="divider">&mdash; or use PayHere gateway below &mdash;</div>
                    <?php endif; ?>

                    <div class="payment-buttons">
                        <button type="button" class="pay-btn" onclick="proceedToPayment()">
                            Proceed to PayHere — Rs. <?= number_format($fee, 2) ?>
                        </button>
                        <button type="button" class="cancel-btn" onclick="window.location.href='patient_appointments.php'">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Session Summary -->
            <div class="card">
                <h2 class="card-title">Session Summary</h2>
                <div class="appointment-summary">
                    <div class="session-highlight">
                        <strong>Session #<?= $sessionNumber ?> — <?= htmlspecialchars($treatmentName) ?></strong>
                        <?php if (!empty($session['session_date'])): ?>
                        <p>
                            📅 <?= date('l, M d, Y', strtotime($session['session_date'])) ?>
                            &nbsp;⏰ <?= date('g:i A', strtotime($session['session_time'])) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="summary-item">
                        <span class="label">Treatment</span>
                        <span class="value"><?= htmlspecialchars($treatmentName) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Session</span>
                        <span class="value">#<?= $sessionNumber ?></span>
                    </div>
                    <?php if (!empty($plan['diagnosis'])): ?>
                    <div class="summary-item">
                        <span class="label">Diagnosis</span>
                        <span class="value"><?= htmlspecialchars($plan['diagnosis']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-item">
                        <span class="label">Patient</span>
                        <span class="value"><?= htmlspecialchars($userName) ?></span>
                    </div>
                    <div class="summary-total">
                        <span class="label">Amount Due</span>
                        <span class="value">Rs. <?= number_format($fee, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="<?= PAYHERE_CHECKOUT_URL ?>" id="payhereForm" style="display:none;">
        <input type="hidden" name="merchant_id" value="<?= PAYHERE_MERCHANT_ID ?>">
        <input type="hidden" name="return_url"  value="<?= PAYHERE_SESSION_RETURN_URL ?>">
        <input type="hidden" name="cancel_url"  value="<?= PAYHERE_SESSION_CANCEL_URL ?>">
        <input type="hidden" name="notify_url"  value="<?= PAYHERE_NOTIFY_URL ?>">
        <input type="hidden" name="order_id"    id="ph_order_id" value="<?= $orderId ?>">
        <input type="hidden" name="items"       id="ph_items"    value="<?= htmlspecialchars($description) ?>">
        <input type="hidden" name="currency"    value="<?= PAYHERE_CURRENCY ?>">
        <input type="hidden" name="amount"      id="ph_amount"   value="<?= number_format($fee, 2, '.', '') ?>">
        <input type="hidden" name="first_name"  id="ph_first"    value="">
        <input type="hidden" name="last_name"   id="ph_last"     value="">
        <input type="hidden" name="email"       id="ph_email"    value="">
        <input type="hidden" name="phone"       id="ph_phone"    value="">
        <input type="hidden" name="address"     id="ph_address"  value="">
        <input type="hidden" name="city"        id="ph_city"     value="">
        <input type="hidden" name="country"     value="Sri Lanka">
        <input type="hidden" name="hash"        id="ph_hash"     value="">
    </form>

<script>
    const ORDER_ID      = '<?= $orderId ?>';
    const PLAN_ID       = <?= $planId ?>;
    const SESSION_NUM   = <?= $sessionNumber ?>;
    const SESSION_FEE   = <?= $fee ?>;

    function validateForm() {
        const name  = document.getElementById('customerName').value.trim();
        const email = document.getElementById('customerEmail').value.trim();
        const phone = document.getElementById('customerPhone').value.trim();
        if (!name || !email || !phone) { alert('Please fill in all required fields!'); return null; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { alert('Please enter a valid email address!'); return null; }
        if (!/^0[0-9]{9}$/.test(phone)) { alert('Please enter a valid phone number (e.g., 0712345678)!'); return null; }
        return { name, email, phone };
    }

    async function simulatePayment() {
        const fields = validateForm();
        if (!fields) return;
        const btn = document.getElementById('testPayBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Processing\u2026'; }
        try {
            const fd = new FormData();
            fd.append('action',         'simulate');
            fd.append('plan_id',        PLAN_ID);
            fd.append('session_number', SESSION_NUM);
            fd.append('order_id',       ORDER_ID);
            fd.append('payment_id',     'SIM_' + Date.now());
            fd.append('amount',         SESSION_FEE.toFixed(2));
            fd.append('customer_name',  fields.name);
            fd.append('customer_email', fields.email);
            fd.append('customer_phone', fields.phone);
            const res  = await fetch('/dheergayu/public/api/process-session-payment.php', { method:'POST', body:fd });
            const data = await res.json();
            if (data.success) {
                window.location.href = 'appointment_payment_success.php?order_id=' + ORDER_ID
                    + '&plan_id=' + PLAN_ID + '&type=session&session_number=' + SESSION_NUM + '&simulated=1';
            } else {
                throw new Error(data.error || 'Payment failed');
            }
        } catch (err) {
            alert('Payment simulation failed: ' + err.message);
            if (btn) { btn.disabled = false; btn.textContent = '\u2705 Test Payment \u2014 Rs. ' + SESSION_FEE.toFixed(2) + ' (no gateway)'; }
        }
    }

    async function proceedToPayment() {
        const fields = validateForm();
        if (!fields) return;
        const btn = document.querySelector('.pay-btn');
        btn.disabled = true; btn.textContent = 'Preparing\u2026';
        const parts = fields.name.split(' ');
        const firstName = parts[0];
        const lastName  = parts.slice(1).join(' ') || firstName;
        try {
            const pd = new FormData();
            pd.append('plan_id',        PLAN_ID);
            pd.append('session_number', SESSION_NUM);
            pd.append('order_id',       ORDER_ID);
            pd.append('amount',         SESSION_FEE.toFixed(2));
            pd.append('customer_name',  fields.name);
            pd.append('customer_email', fields.email);
            pd.append('customer_phone', fields.phone);
            pd.append('pending',        '1');
            await fetch('/dheergayu/public/api/process-session-payment.php', { method:'POST', body:pd });

            const hfd = new FormData();
            hfd.append('order_id', ORDER_ID);
            hfd.append('amount',   SESSION_FEE.toFixed(2));
            const hres  = await fetch('/dheergayu/public/api/generate-payhere-hash.php', { method:'POST', body:hfd });
            const hdata = await hres.json();
            if (!hdata.success) throw new Error(hdata.error || 'Hash generation failed');

            document.getElementById('ph_order_id').value = ORDER_ID;
            document.getElementById('ph_amount').value   = SESSION_FEE.toFixed(2);
            document.getElementById('ph_first').value    = firstName;
            document.getElementById('ph_last').value     = lastName;
            document.getElementById('ph_email').value    = fields.email;
            document.getElementById('ph_phone').value    = fields.phone;
            document.getElementById('ph_address').value  = 'N/A';
            document.getElementById('ph_city').value     = 'Colombo';
            document.getElementById('ph_hash').value     = hdata.hash;
            document.getElementById('payhereForm').submit();
        } catch (err) {
            alert('Payment setup failed: ' + err.message);
            btn.disabled = false; btn.textContent = 'Proceed to PayHere \u2014 Rs. ' + SESSION_FEE.toFixed(2);
        }
    }
</script>
</body>
</html>
