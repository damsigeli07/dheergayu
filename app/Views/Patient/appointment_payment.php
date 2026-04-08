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

$appointmentId = (int)($_GET['appointment_id'] ?? 0);
$type          = $_GET['type'] ?? '';

if (!$appointmentId || !in_array($type, ['consultation', 'treatment'])) {
    header('Location: patient_appointments.php');
    exit;
}

// Fetch appointment details
$appointment = null;
$fee = 0;
$description = '';

if ($type === 'consultation') {
    $stmt = $conn->prepare("SELECT * FROM consultations WHERE id = ? AND patient_id = ?");
    $stmt->bind_param('ii', $appointmentId, $userId);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($appointment) {
        $fee = (float)($appointment['treatment_fee'] ?? 2000);
        $description = 'Consultation with ' . ($appointment['doctor_name'] ?? 'Doctor');
        $appointmentDate = $appointment['appointment_date'];
        $appointmentTime = $appointment['appointment_time'];
        if (!empty($appointment['phone'])) $userPhone = $appointment['phone'];
    }
} else {
    $stmt = $conn->prepare("
        SELECT tb.*, tl.treatment_name, tl.price, ts.slot_time
        FROM treatment_bookings tb
        LEFT JOIN treatment_list tl ON tb.treatment_id = tl.treatment_id
        LEFT JOIN treatment_slots ts ON tb.slot_id = ts.slot_id
        WHERE tb.booking_id = ? AND tb.patient_id = ?
    ");
    $stmt->bind_param('ii', $appointmentId, $userId);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($appointment) {
        $fee = (float)($appointment['price'] ?? 2000);
        $description = ($appointment['treatment_name'] ?? 'Treatment') . ' Session';
        $appointmentDate = $appointment['booking_date'];
        $appointmentTime = $appointment['slot_time'] ?? '';
    }
}

if (!$appointment) {
    header('Location: patient_appointments.php');
    exit;
}

$orderId   = generateOrderId();
$isSandbox = (PAYHERE_MODE === 'sandbox');
$showTestPayment = payhere_test_payment_allowed();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay for Appointment - Dheergayu</title>
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
        }
        .type-badge.consultation { background: #17a2b8; }
        .type-badge.treatment { background: #28a745; }
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

            <!-- Appointment Summary -->
            <div class="card">
                <h2 class="card-title">Appointment Summary</h2>
                <div class="appointment-summary">
                    <div style="margin-bottom: 16px;">
                        <span class="type-badge <?= $type ?>"><?= ucfirst($type) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label"><?= $type === 'consultation' ? 'Doctor' : 'Treatment' ?></span>
                        <span class="value"><?= htmlspecialchars($description) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Date</span>
                        <span class="value"><?= date('M d, Y', strtotime($appointmentDate)) ?></span>
                    </div>
                    <?php if ($appointmentTime): ?>
                    <div class="summary-item">
                        <span class="label">Time</span>
                        <span class="value"><?= date('h:i A', strtotime($appointmentTime)) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-item">
                        <span class="label">Patient</span>
                        <span class="value"><?= htmlspecialchars($userName) ?></span>
                    </div>
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
        <input type="hidden" name="return_url"   value="<?= PAYHERE_APPOINTMENT_RETURN_URL ?>">
        <input type="hidden" name="cancel_url"   value="<?= PAYHERE_APPOINTMENT_CANCEL_URL ?>">
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

    <footer class="main-footer">
        <div class="container">
            <div class="footer-column"><h3>HELLO</h3><p>Welcome to one of the best Ayurvedic wellness centers!</p></div>
            <div class="footer-column">
                <h3>OFFICE</h3><p>Sri Lanka &mdash; 123 Wellness Street, Colombo</p>
                <p><a href="mailto:info@dheergayu.com" class="footer-link">info@dheergayu.com</a></p>
            </div>
            <div class="footer-column">
                <h3>LINKS</h3>
                <ul>
                    <li><a href="home.php" class="footer-link">Home</a></li>
                    <li><a href="treatment.php" class="footer-link">Treatments</a></li>
                    <li><a href="channeling.php" class="footer-link">Booking</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="social-link">Facebook</a></li>
                    <li><a href="#" class="social-link">Instagram</a></li>
                </ul>
            </div>
        </div>
    </footer>

<script>
    const ORDER_ID       = '<?= $orderId ?>';
    const APPOINTMENT_ID = <?= $appointmentId ?>;
    const APT_TYPE       = '<?= $type ?>';
    const APT_FEE        = <?= $fee ?>;

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
            fd.append('appointment_id', APPOINTMENT_ID);
            fd.append('type',           APT_TYPE);
            fd.append('order_id',       ORDER_ID);
            fd.append('payment_id',     'SIM_' + Date.now());
            fd.append('amount',         APT_FEE.toFixed(2));
            fd.append('customer_name',  fields.name);
            fd.append('customer_email', fields.email);
            fd.append('customer_phone', fields.phone);

            const res  = await fetch('/dheergayu/public/api/process-appointment-payment.php', {
                method: 'POST', body: fd
            });
            const data = await res.json();

            if (data.success) {
                window.location.href = 'appointment_payment_success.php?order_id=' + ORDER_ID
                    + '&appointment_id=' + APPOINTMENT_ID + '&type=' + APT_TYPE + '&simulated=1';
            } else {
                throw new Error(data.error || 'Payment processing failed');
            }
        } catch (err) {
            console.error('Simulate error:', err);
            alert('Payment simulation failed: ' + err.message);
            if (btn) { btn.disabled = false; btn.textContent = '\u2705 Test Payment - Rs. ' + APT_FEE.toFixed(2) + ' (no gateway)'; }
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
            pd.append('appointment_id', APPOINTMENT_ID);
            pd.append('type',           APT_TYPE);
            pd.append('order_id',       ORDER_ID);
            pd.append('amount',         APT_FEE.toFixed(2));
            pd.append('customer_name',  fields.name);
            pd.append('customer_email', fields.email);
            pd.append('customer_phone', fields.phone);
            pd.append('pending',        '1');
            await fetch('/dheergayu/public/api/process-appointment-payment.php', {
                method: 'POST', body: pd
            });

            // Generate PayHere hash
            const hfd = new FormData();
            hfd.append('order_id', ORDER_ID);
            hfd.append('amount',   APT_FEE.toFixed(2));
            const hres  = await fetch('/dheergayu/public/api/generate-payhere-hash.php', { method:'POST', body:hfd });
            const hdata = await hres.json();

            if (!hdata.success) throw new Error(hdata.error || 'Hash generation failed');

            document.getElementById('ph_order_id').value = ORDER_ID;
            document.getElementById('ph_amount').value   = APT_FEE.toFixed(2);
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
            btn.textContent = 'Proceed to PayHere - Rs. ' + APT_FEE.toFixed(2);
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
