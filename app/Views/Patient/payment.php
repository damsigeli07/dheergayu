<?php
session_start();
require_once __DIR__ . '/../../../config/payhere_config.php';

$userId    = $_SESSION['user_id']    ?? null;
$userName  = $_SESSION['user_name']  ?? 'Guest User';
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = '';

// Pre-fill phone from patient_info if available
if ($userId) {
    require_once __DIR__ . '/../../../config/config.php';
    $piStmt = $conn->prepare("SELECT phone FROM patient_info WHERE patient_id = ? LIMIT 1");
    if ($piStmt) {
        $piStmt->bind_param('i', $userId);
        $piStmt->execute();
        $piRow = $piStmt->get_result()->fetch_assoc();
        $piStmt->close();
        $userPhone = $piRow['phone'] ?? '';
    }
}

$orderId    = generateOrderId();
$isSandbox  = (PAYHERE_MODE === 'sandbox');
$showTestPayment = payhere_test_payment_allowed();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Checkout & Payment</title>
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
                <a href="cart.php" class="back-btn">← Back to Cart</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="checkout-grid">

            <!-- Customer Information -->
            <div class="card">
                <h2 class="card-title">Customer Information</h2>



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
                    <div class="form-group">
                        <label for="address">Delivery Address *</label>
                        <textarea id="address" name="address" rows="3"
                                  placeholder="Enter your delivery address" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city"
                               placeholder="e.g., Colombo" required>
                    </div>



                    <!-- ── Test Payment (sandbox) ────────────────────── -->
                    <?php if ($showTestPayment): ?>
                    <div class="sandbox-notice">
                        <?php if ($isSandbox): ?>
                        <strong>&#9888;&#65039; Sandbox Mode Active.</strong>
                        Use <em>"Test Payment"</em> to simulate a successful payment without the PayHere gateway.
                        <?php else: ?>
                        <strong>Test payment enabled.</strong>
                        Use <em>"Test Payment"</em> to simulate success without PayHere.
                        <?php endif; ?>
                    </div>
                    <button type="button" class="test-pay-btn" id="testPayBtn" onclick="simulatePayment()">
                        &#10004; Test Payment (no gateway)
                    </button>
                    <div class="divider">&mdash; or use PayHere gateway below &mdash;</div>
                    <?php endif; ?>

                    <!-- ── PayHere button ──────────────────────────────── -->
                    <div class="payment-buttons">
                        <button type="button" class="pay-btn" onclick="proceedToPayment()">
                            Proceed to PayHere
                        </button>
                        <button type="button" class="cancel-btn" onclick="cancelPayment()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="card">
                <h2 class="card-title">Order Summary</h2>
                <div id="orderItems"></div>
                <div style="margin-top:25px;" id="orderSummary"></div>
            </div>
        </div>
    </div>

    <!-- Hidden PayHere form -->
    <form method="post" action="<?= PAYHERE_CHECKOUT_URL ?>"
          id="payhereForm" style="display:none;">
        <input type="hidden" name="merchant_id"  value="<?= PAYHERE_MERCHANT_ID ?>">
        <input type="hidden" name="return_url"   value="<?= PAYHERE_RETURN_URL ?>">
        <input type="hidden" name="cancel_url"   value="<?= PAYHERE_CANCEL_URL ?>">
        <input type="hidden" name="notify_url"   value="<?= PAYHERE_NOTIFY_URL ?>">
        <input type="hidden" name="order_id"     id="ph_order_id"  value="<?= $orderId ?>">
        <input type="hidden" name="items"        id="ph_items"     value="">
        <input type="hidden" name="currency"     value="<?= PAYHERE_CURRENCY ?>">
        <input type="hidden" name="amount"       id="ph_amount"    value="">
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
    const ORDER_ID    = '<?= $orderId ?>';
    let   cartItems   = [];

    /* ── Load cart on page load ───────────────────────────────────── */
    async function loadOrderSummary() {
        try {
            const res  = await fetch('/dheergayu/public/api/cart-api.php?action=get');
            const data = await res.json();

            if (!data.success || data.items.length === 0) {
                alert('Your cart is empty! Redirecting to products page...');
                window.location.href = 'products.php';
                return;
            }
            cartItems = data.items;
            renderOrderSummary(cartItems);
        } catch (e) {
            console.error('Error loading order:', e);
            alert('Failed to load order. Please try again.');
        }
    }

    function renderOrderSummary(items) {
        const subtotal = items.reduce((s, i) => s + i.price * i.quantity, 0);
        const total    = subtotal;

        document.getElementById('orderItems').innerHTML = items.map(item => `
            <div class="order-item">
                <div class="order-item-image">
                    <img src="${item.image || '/dheergayu/public/assets/images/dheergayu.png'}"
                         alt="${item.name}"
                         onerror="this.src='/dheergayu/public/assets/images/dheergayu.png'">
                </div>
                <div class="order-item-details">
                    <div class="order-item-name">${item.name}</div>
                    <div class="order-item-qty">Qty: ${item.quantity}</div>
                </div>
                <div class="order-item-price">Rs. ${(item.price * item.quantity).toFixed(2)}</div>
            </div>`).join('');

        document.getElementById('orderSummary').innerHTML = `
            <div class="summary-row">
                <span>Subtotal (${items.reduce((s,i)=>s+i.quantity,0)} items)</span>
                <span>Rs. ${subtotal.toFixed(2)}</span>
            </div>
            <div class="summary-row total">
                <span>Total Amount</span>
                <span class="amount">Rs. ${total.toFixed(2)}</span>
            </div>`;
    }

    function getTotal() {
        return cartItems.reduce((s,i) => s + i.price*i.quantity, 0);
    }

    function validateForm() {
        const name  = document.getElementById('customerName').value.trim();
        const email = document.getElementById('customerEmail').value.trim();
        const phone = document.getElementById('customerPhone').value.trim();
        const addr  = document.getElementById('address').value.trim();
        const city  = document.getElementById('city').value.trim();

        if (!name || !email || !phone || !addr || !city) {
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
        return { name, email, phone, addr, city };
    }

    /* ── Save pending order to DB + session before redirect ──────── */
    async function savePendingOrder(fields, total) {
        const fd = new FormData();
        fd.append('action',         'pending');
        fd.append('order_id',       ORDER_ID);
        fd.append('amount',         total.toFixed(2));
        fd.append('customer_name',  fields.name);
        fd.append('customer_email', fields.email);
        fd.append('customer_phone', fields.phone);
        fd.append('address',        fields.addr);
        fd.append('city',           fields.city);
        await fetch('/dheergayu/public/api/process-order.php', { method:'POST', body:fd });
    }

    /* ── PayHere gateway path ─────────────────────────────────────── */
    async function proceedToPayment() {
        const fields = validateForm();
        if (!fields) return;

        const btn = document.querySelector('.pay-btn');
        btn.disabled = true;
        btn.textContent = 'Preparing...';

        const total    = getTotal();
        const itemsStr = cartItems.map(i => `${i.name} x${i.quantity}`).join(', ');
        const parts    = fields.name.split(' ');
        const firstName = parts[0];
        const lastName  = parts.slice(1).join(' ') || firstName;

        try {
            // Save pending order so success page can finalise it
            await savePendingOrder(fields, total);

            // Generate PayHere hash
            const hfd = new FormData();
            hfd.append('order_id', ORDER_ID);
            hfd.append('amount',   total.toFixed(2));
            const hres  = await fetch('/dheergayu/public/api/generate-payhere-hash.php', { method:'POST', body:hfd });
            const hdata = await hres.json();

            if (!hdata.success) throw new Error(hdata.error || 'Hash generation failed');

            // Fill hidden form
            document.getElementById('ph_order_id').value = ORDER_ID;
            document.getElementById('ph_amount').value   = total.toFixed(2);
            document.getElementById('ph_items').value    = itemsStr;
            document.getElementById('ph_first').value    = firstName;
            document.getElementById('ph_last').value     = lastName;
            document.getElementById('ph_email').value    = fields.email;
            document.getElementById('ph_phone').value    = fields.phone;
            document.getElementById('ph_address').value  = fields.addr;
            document.getElementById('ph_city').value     = fields.city;
            document.getElementById('ph_hash').value     = hdata.hash;

            document.getElementById('payhereForm').submit();

        } catch (err) {
            console.error('Payment error:', err);
            alert('Could not prepare payment: ' + err.message +
                  '\n\nTip: Use the "Test Payment" button if test payments are enabled.');
            btn.disabled = false;
            btn.textContent = 'Proceed to PayHere';
        }
    }

    /* ── Sandbox simulate path ────────────────────────────────────── */
    async function simulatePayment() {
        const fields = validateForm();
        if (!fields) return;

        const btn = document.getElementById('testPayBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Processing…'; }

        const total = getTotal();

        try {
            const fd = new FormData();
            fd.append('action',         'simulate');
            fd.append('order_id',       ORDER_ID);
            fd.append('payment_id',     'SIM_' + Date.now());
            fd.append('amount',         total.toFixed(2));
            fd.append('customer_name',  fields.name);
            fd.append('customer_email', fields.email);
            fd.append('customer_phone', fields.phone);
            fd.append('address',        fields.addr);
            fd.append('city',           fields.city);

            const res  = await fetch('/dheergayu/public/api/process-order.php', { method:'POST', body:fd });
            const data = await res.json();

            if (data.success) {
                window.location.href = '/dheergayu/app/Views/Patient/payment_success.php?order_id=' + ORDER_ID + '&simulated=1';
            } else {
                throw new Error(data.error || 'Order processing failed');
            }
        } catch (err) {
            console.error('Simulate error:', err);
            alert('Payment simulation failed: ' + err.message);
            if (btn) { btn.disabled = false; btn.textContent = '✅ Test Payment (no gateway)'; }
        }
    }

    function cancelPayment() {
        if (confirm('Are you sure you want to cancel?')) window.location.href = 'cart.php';
    }

    /* Phone: digits only */
    document.getElementById('customerPhone').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    });

    loadOrderSummary();
</script>
</body>
</html>