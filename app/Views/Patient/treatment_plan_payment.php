<?php
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

// Pre-fill phone from patient_info if available
$piStmt = $conn->prepare("SELECT phone FROM patient_info WHERE patient_id = ? LIMIT 1");
if ($piStmt) {
    $piStmt->bind_param('i', $userId);
    $piStmt->execute();
    $piRow = $piStmt->get_result()->fetch_assoc();
    $piStmt->close();
    $userPhone = $piRow['phone'] ?? '';
}

$planId = (int)($_GET['plan_id'] ?? 0);

if (!$planId) {
    header('Location: patient_appointments.php');
    exit;
}

// Fetch treatment plan details
$stmt = $conn->prepare("
    SELECT tp.*, tl.treatment_name as list_treatment_name
    FROM treatment_plans tp
    LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
    WHERE tp.plan_id = ? AND tp.patient_id = ?
");
$stmt->bind_param('ii', $planId, $userId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    header('Location: patient_appointments.php');
    exit;
}

if ($plan['payment_status'] === 'Completed') {
    header('Location: patient_appointments.php');
    exit;
}

$fee = (float)$plan['total_cost'];
$treatmentName = $plan['treatment_name'] ?? $plan['list_treatment_name'] ?? 'Treatment';
$description = $treatmentName . ' - Treatment Plan';
$startDate = $plan['start_date'];
$diagnosis = $plan['diagnosis'] ?? '';

// Fetch sessions for this plan
$sessStmt = $conn->prepare("SELECT session_number, session_date, session_time, status FROM treatment_sessions WHERE plan_id = ? ORDER BY session_number");
$sessStmt->bind_param('i', $planId);
$sessStmt->execute();
$sessResult = $sessStmt->get_result();
$sessions = [];
while ($s = $sessResult->fetch_assoc()) {
    $sessions[] = $s;
}
$sessStmt->close();

$orderId   = generateOrderId();
$isSandbox = (PAYHERE_MODE === 'sandbox');
$showTestPayment = payhere_test_payment_allowed();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay for Treatment Plan - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/payment.css?v=<?php echo time(); ?>">
    <style>
        .sandbox-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #856404;
        }
        .sandbox-notice strong { color: #533f03; }
        .test-pay-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            transition: all 0.3s;
            letter-spacing: 0.5px;
        }
        .test-pay-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(40,167,69,.35); }
        .test-pay-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
        .divider { text-align: center; margin: 14px 0; color: #999; font-size: .9rem; }
        .appointment-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
        }
        .appointment-summary h3 {
            color: #333;
            margin-bottom: 16px;
            font-size: 1.1rem;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.95rem;
        }
        .summary-item:last-child { border-bottom: none; }
        .summary-item .label { color: #666; }
        .summary-item .value { font-weight: 600; color: #333; }
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding: 16px 0 0;
            margin-top: 8px;
            border-top: 2px solid #333;
            font-size: 1.15rem;
        }
        .summary-total .label { font-weight: 600; color: #333; }
        .summary-total .value { font-weight: 700; color: #28a745; font-size: 1.25rem; }
        .type-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
            background: #8B7355;
        }
        .sessions-list {
            margin-top: 16px;
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
        }
        .sessions-list h4 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .session-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }
        .session-item .session-num {
            font-weight: 600;
            color: #5a4a3a;
        }
        .session-item .session-date {
            color: #666;
        }
        .session-status {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #e8f5e9;
            color: #2e7d32;
        }
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

            <!-- Customer Information -->
            <div class="card">
                <h2 class="card-title">Payment Details</h2>

                <?php if ($showTestPayment): ?>
                <div class="sandbox-notice">
                    <?php if ($isSandbox): ?>
                    <strong>&#9888;&#65039; Sandbox Mode Active.</strong>
                    Use the <em>"Test Payment"</em> button below to simulate a successful payment
                    without going through the PayHere gateway.
                    <?php else: ?>
                    <strong>Test payment enabled.</strong>
                    Use <em>"Test Payment"</em> to simulate success without PayHere. Set <code>PAYHERE_ALLOW_TEST_PAYMENT</code> to <code>false</code> in production.
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <form id="customerForm">
                    <div class="form-group">
                        <label for="customerName">Full Name *</label>
                        <input type="text" id="customerName" name="customerName"
                               value="<?= htmlspecialchars($userName) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="customerEmail">Email *</label>
                        <input type="email" id="customerEmail" name="customerEmail"
                               value="<?= htmlspecialchars($userEmail) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="customerPhone">Phone *</label>
                        <input type="tel" id="customerPhone" name="customerPhone"
                               value="<?= htmlspecialchars($userPhone) ?>"
                               placeholder="0712345678" required>
                    </div>

                    <!-- Test payment (sandbox or PAYHERE_ALLOW_TEST_PAYMENT) -->
                    <?php if ($showTestPayment): ?>
                    <button type="button" class="test-pay-btn" id="testPayBtn"
                            onclick="simulatePayment()">
                        &#10004; Test Payment - Rs. <?= number_format($fee, 2) ?> (no gateway)
                    </button>
                    <div class="divider">&mdash; or use PayHere gateway below &mdash;</div>
                    <?php endif; ?>

                    <!-- PayHere button -->
                    <div class="payment-buttons">
                        <button type="button" class="pay-btn" onclick="proceedToPayment()">
                            Proceed to PayHere - Rs. <?= number_format($fee, 2) ?>
                        </button>
                        <button type="button" class="cancel-btn" onclick="cancelPayment()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Treatment Plan Summary -->
            <div class="card">
                <h2 class="card-title">Treatment Plan Summary</h2>
                <div class="appointment-summary">
                    <div style="margin-bottom: 16px;">
                        <span class="type-badge">Treatment Plan</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Treatment</span>
                        <span class="value"><?= htmlspecialchars($treatmentName) ?></span>
                    </div>
                    <?php if ($diagnosis): ?>
                    <div class="summary-item">
                        <span class="label">Diagnosis</span>
                        <span class="value"><?= htmlspecialchars($diagnosis) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-item">
                        <span class="label">Total Sessions</span>
                        <span class="value"><?= $plan['session_count'] ?? '1' ?> session(s)</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Start Date</span>
                        <span class="value"><?= date('M d, Y', strtotime($startDate)) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Patient</span>
                        <span class="value"><?= htmlspecialchars($userName) ?></span>
                    </div>

                    <?php if (!empty($sessions)): ?>
                    <div class="sessions-list">
                        <h4>Session Schedule</h4>
                        <?php foreach ($sessions as $s): ?>
                        <div class="session-item">
                            <span class="session-num">Session <?= $s['session_number'] ?></span>
                            <span class="session-date"><?= date('M d, Y', strtotime($s['session_date'])) ?> at <?= date('h:i A', strtotime($s['session_time'])) ?></span>
                            <span class="session-status"><?= $s['status'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="summary-total">
                        <span class="label">Total Amount</span>
                        <span class="value">Rs. <?= number_format($fee, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden PayHere form -->
    <form method="post" action="<?= PAYHERE_CHECKOUT_URL ?>"
          id="payhereForm" style="display:none;">
        <input type="hidden" name="merchant_id"  value="<?= PAYHERE_MERCHANT_ID ?>">
        <input type="hidden" name="return_url"   value="<?= PAYHERE_TREATMENT_PLAN_RETURN_URL ?>">
        <input type="hidden" name="cancel_url"   value="<?= PAYHERE_TREATMENT_PLAN_CANCEL_URL ?>">
        <input type="hidden" name="notify_url"   value="<?= PAYHERE_NOTIFY_URL ?>">
        <input type="hidden" name="order_id"     id="ph_order_id"  value="<?= $orderId ?>">
        <input type="hidden" name="items"        id="ph_items"     value="<?= htmlspecialchars($description) ?>">
        <input type="hidden" name="currency"     value="<?= PAYHERE_CURRENCY ?>">
        <input type="hidden" name="amount"       id="ph_amount"    value="<?= number_format($fee, 2, '.', '') ?>">
        <input type="hidden" name="first_name"   id="ph_first"     value="">
        <input type="hidden" name="last_name"    id="ph_last"      value="">
        <input type="hidden" name="email"        id="ph_email"     value="">
        <input type="hidden" name="phone"        id="ph_phone"     value="">
        <input type="hidden" name="address"      id="ph_address"   value="">
        <input type="hidden" name="city"         id="ph_city"      value="">
        <input type="hidden" name="country"      value="Sri Lanka">
        <input type="hidden" name="hash"         id="ph_hash"      value="">
    </form>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best Ayurvedic wellness centers in your area!</p>
            </div>
            <div class="footer-column">
                <h3>OFFICE</h3>
                <p>123 Wellness Street</p>
                <p>Colombo, LK 00100</p>
                <p>Sri Lanka</p>
                <p><a href="mailto:info@dheergayu.com" class="footer-link">info@dheergayu.com</a></p>
                <p>+94 11 234 5678</p>
            </div>
            <div class="footer-column">
                <h3>LINKS</h3>
                <ul>
                    <li><a href="home.php" class="footer-link">Home</a></li>
                    <li><a href="treatment.php" class="footer-link">Treatments</a></li>
                    <li><a href="learn_more.php" class="footer-link">About Us</a></li>
                    <li><a href="channeling.php" class="footer-link">Booking</a></li>
                    <li><a href="contact_us.php" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/facebook.png" alt="Facebook" class="social-icon">
                            Facebook
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/twitter(x).png" alt="X" class="social-icon">
                            X
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/linkedin.png" alt="LinkedIn" class="social-icon">
                            LinkedIn
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/instagram.png" alt="Instagram" class="social-icon">
                            Instagram
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

<script>
    const ORDER_ID = '<?= $orderId ?>';
    const PLAN_ID  = <?= $planId ?>;
    const PLAN_FEE = <?= $fee ?>;

    function validateForm() {
        const name  = document.getElementById('customerName').value.trim();
        const email = document.getElementById('customerEmail').value.trim();
        const phone = document.getElementById('customerPhone').value.trim();

        if (!name || !email || !phone) {
            alert('Please fill in all required fields!');
            return null;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            alert('Please enter a valid email address!');
            return null;
        }
        if (!/^0[0-9]{9}$/.test(phone)) {
            alert('Please enter a valid phone number (e.g., 0712345678)!');
            return null;
        }
        return { name, email, phone };
    }

    /* Sandbox simulate path */
    async function simulatePayment() {
        const fields = validateForm();
        if (!fields) return;

        const btn = document.getElementById('testPayBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Processing\u2026'; }

        try {
            const fd = new FormData();
            fd.append('action',         'simulate');
            fd.append('plan_id',        PLAN_ID);
            fd.append('order_id',       ORDER_ID);
            fd.append('payment_id',     'SIM_' + Date.now());
            fd.append('amount',         PLAN_FEE.toFixed(2));
            fd.append('customer_name',  fields.name);
            fd.append('customer_email', fields.email);
            fd.append('customer_phone', fields.phone);

            const res  = await fetch('/dheergayu/public/api/process-treatment-plan-payment.php', {
                method: 'POST', body: fd
            });
            const data = await res.json();

            if (data.success) {
                window.location.href = 'appointment_payment_success.php?order_id=' + ORDER_ID
                    + '&plan_id=' + PLAN_ID + '&type=treatment_plan&simulated=1';
            } else {
                throw new Error(data.error || 'Payment processing failed');
            }
        } catch (err) {
            console.error('Simulate error:', err);
            alert('Payment simulation failed: ' + err.message);
            if (btn) { btn.disabled = false; btn.textContent = '\u2705 Test Payment - Rs. ' + PLAN_FEE.toFixed(2) + ' (no gateway)'; }
        }
    }

    /* PayHere gateway path */
    async function proceedToPayment() {
        const fields = validateForm();
        if (!fields) return;

        const btn = document.querySelector('.pay-btn');
        btn.disabled = true;
        btn.textContent = 'Preparing\u2026';

        const parts     = fields.name.split(' ');
        const firstName = parts[0];
        const lastName  = parts.slice(1).join(' ') || firstName;

        try {
            // Save pending state
            const pd = new FormData();
            pd.append('plan_id',        PLAN_ID);
            pd.append('order_id',       ORDER_ID);
            pd.append('amount',         PLAN_FEE.toFixed(2));
            pd.append('customer_name',  fields.name);
            pd.append('customer_email', fields.email);
            pd.append('customer_phone', fields.phone);
            pd.append('pending',        '1');
            await fetch('/dheergayu/public/api/process-treatment-plan-payment.php', {
                method: 'POST', body: pd
            });

            // Generate PayHere hash
            const hfd = new FormData();
            hfd.append('order_id', ORDER_ID);
            hfd.append('amount',   PLAN_FEE.toFixed(2));
            const hres  = await fetch('/dheergayu/public/api/generate-payhere-hash.php', { method:'POST', body:hfd });
            const hdata = await hres.json();

            if (!hdata.success) throw new Error(hdata.error || 'Hash generation failed');

            document.getElementById('ph_order_id').value = ORDER_ID;
            document.getElementById('ph_amount').value   = PLAN_FEE.toFixed(2);
            document.getElementById('ph_first').value    = firstName;
            document.getElementById('ph_last').value     = lastName;
            document.getElementById('ph_email').value    = fields.email;
            document.getElementById('ph_phone').value    = fields.phone;
            document.getElementById('ph_address').value  = 'N/A';
            document.getElementById('ph_city').value     = 'Colombo';
            document.getElementById('ph_hash').value     = hdata.hash;

            document.getElementById('payhereForm').submit();

        } catch (err) {
            console.error('Payment error:', err);
            alert('Could not prepare payment: ' + err.message +
                  '\n\nTip: Use the "Test Payment" button if test payments are enabled.');
            btn.disabled = false;
            btn.textContent = 'Proceed to PayHere - Rs. ' + PLAN_FEE.toFixed(2);
        }
    }

    function cancelPayment() {
        if (confirm('Are you sure you want to cancel?'))
            window.location.href = 'patient_appointments.php';
    }

    document.getElementById('customerPhone').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    });
</script>
</body>
</html>
