<?php
session_start();
require_once __DIR__ . '/../../../config/payhere_config.php';

// Check if user is logged in (optional - you can allow guest checkout)
$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? 'Guest User';
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';

// Generate unique order ID
$orderId = generateOrderId();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Checkout & Payment</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/payment.css?v=<?php echo time(); ?>">
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
                </ul>
            </nav>
            <div class="header-right">
                <a href="cart.php" class="back-btn">← Back to Cart</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="checkout-grid">
            <!-- Customer Information Card -->
            <div class="card">
                <h2 class="card-title">Customer Information</h2>
                
                <!-- Sandbox Mode Notice -->
                <div class="info-box" style="background: #fff3cd; border-left: 4px solid #ffc107; margin-bottom: 20px;">
                    <h3 style="color: #856404; margin-bottom: 10px;">🧪 Sandbox Mode Active</h3>
                    <p style="color: #555; font-size: 0.9rem; line-height: 1.5;">
                        This is a test environment. No real payments will be processed. 
                        On the PayHere page, you can complete payment using test options without entering real card details.
                    </p>
                </div>
                
                <form id="customerForm">
                    <div class="form-group">
                        <label for="customerName">Full Name *</label>
                        <input type="text" id="customerName" name="customerName" value="<?= htmlspecialchars($userName) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="customerEmail">Email *</label>
                        <input type="email" id="customerEmail" name="customerEmail" value="<?= htmlspecialchars($userEmail) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="customerPhone">Phone *</label>
                        <input type="tel" id="customerPhone" name="customerPhone" value="<?= htmlspecialchars($userPhone) ?>" placeholder="0712345678" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Delivery Address *</label>
                        <textarea id="address" name="address" rows="3" placeholder="Enter your delivery address" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" placeholder="e.g., Colombo" required>
                    </div>

                    <div class="payment-buttons">
                        <button type="button" class="pay-btn" onclick="proceedToPayment()">Proceed to Payment</button>
                        <button type="button" class="cancel-btn" onclick="cancelPayment()">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Order Summary Card -->
            <div class="card">
                <h2 class="card-title">Order Summary</h2>
                
                <div id="orderItems">
                    <!-- Items will be populated by JavaScript -->
                </div>

                <div style="margin-top: 25px;" id="orderSummary">
                    <!-- Summary will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden PayHere Form -->
    <form method="post" action="<?= PAYHERE_CHECKOUT_URL ?>" id="payhereForm" target="_blank" style="display: none;">
        <input type="hidden" name="merchant_id" value="<?= PAYHERE_MERCHANT_ID ?>">
        <input type="hidden" name="return_url" value="<?= PAYHERE_RETURN_URL ?>">
        <input type="hidden" name="cancel_url" value="<?= PAYHERE_CANCEL_URL ?>">
        <input type="hidden" name="notify_url" value="<?= PAYHERE_NOTIFY_URL ?>">
        
        <input type="hidden" name="order_id" id="order_id" value="<?= $orderId ?>">
        <input type="hidden" name="items" id="items" value="">
        <input type="hidden" name="currency" value="<?= PAYHERE_CURRENCY ?>">
        <input type="hidden" name="amount" id="amount" value="">
        
        <input type="hidden" name="first_name" id="first_name" value="">
        <input type="hidden" name="last_name" id="last_name" value="">
        <input type="hidden" name="email" id="email" value="">
        <input type="hidden" name="phone" id="phone" value="">
        <input type="hidden" name="address" id="delivery_address" value="">
        <input type="hidden" name="city" id="delivery_city" value="">
        <input type="hidden" name="country" value="Sri Lanka">
        
        <input type="hidden" name="hash" id="hash" value="">
        <input type="hidden" name="custom_1" id="custom_1" value="<?= (int)($userId ?? 0) ?>">
        <input type="hidden" name="custom_2" id="custom_2" value="">
    </form>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best Ayurvedic wellness centers in your area!</p>
            </div>
            <div class="footer-column">
                <h3>OFFICE</h3>
                <p>Sri Lanka —</p>
                <p>123 Wellness Street</p>
                <p>Colombo, LK 00100</p>
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
                    <li><a href="#" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="social-link">Facebook</a></li>
                    <li><a href="#" class="social-link">X</a></li>
                    <li><a href="#" class="social-link">LinkedIn</a></li>
                    <li><a href="#" class="social-link">Instagram</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="/dheergayu/public/assets/js/patient-form-utils.js"></script>
    <script>
        let cartItems = [];
        let cartId = null;

        async function loadOrderSummary() {
            try {
                const response = await fetch('/dheergayu/public/api/cart-api.php?action=get');
                const data = await response.json();
                
                if (!data.success || data.items.length === 0) {
                    alert('Your cart is empty! Redirecting to products page...');
                    window.location.href = 'products.php';
                    return;
                }
                
                cartItems = data.items;
                cartId = data.cart_id || null;
                renderOrderSummary(cartItems);
            } catch (error) {
                console.error('Error loading order:', error);
                alert('Failed to load order. Please try again.');
            }
        }

        function renderOrderSummary(items) {
            const orderItemsDiv = document.getElementById('orderItems');
            const orderSummaryDiv = document.getElementById('orderSummary');

            // Calculate totals
            const subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const shipping = subtotal > 5000 ? 0 : 250;
            const total = subtotal + shipping;

            // Render order items
            const itemsHTML = items.map(item => `
                <div class="order-item">
                    <div class="order-item-image">
                        <img src="${item.image || '/dheergayu/public/assets/images/dheergayu.png'}" 
                             alt="${item.name}" 
                             onerror="this.src='/dheergayu/public/assets/images/dheergayu.png'">
                    </div>
                    <div class="order-item-details">
                        <div class="order-item-name">${item.name}</div>
                        <div class="order-item-qty">Quantity: ${item.quantity}</div>
                    </div>
                    <div class="order-item-price">
                        Rs. ${(item.price * item.quantity).toFixed(2)}
                    </div>
                </div>
            `).join('');

            orderItemsDiv.innerHTML = itemsHTML;

            // Render summary
            orderSummaryDiv.innerHTML = `
                <div class="summary-row">
                    <span>Subtotal (${items.reduce((sum, item) => sum + item.quantity, 0)} items)</span>
                    <span>Rs. ${subtotal.toFixed(2)}</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>${shipping === 0 ? 'FREE' : 'Rs. ' + shipping.toFixed(2)}</span>
                </div>
                ${shipping === 0 ? '' : '<div class="summary-row" style="font-size: 0.85rem; color: #5CB85C;"><span style="font-size: 0.85rem;">Free shipping on orders over Rs. 5,000</span><span></span></div>'}
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span class="amount">Rs. ${total.toFixed(2)}</span>
                </div>
            `;
        }

        function cancelPayment() {
            if (confirm('Are you sure you want to cancel?')) {
                window.location.href = 'cart.php';
            }
        }

        async function proceedToPayment() {
            const customerName = document.getElementById('customerName').value.trim();
            const customerEmail = document.getElementById('customerEmail').value.trim();
            const customerPhone = PatientFormUtils.toDigits(document.getElementById('customerPhone').value, 10);
            const address = document.getElementById('address').value.trim();
            const city = document.getElementById('city').value.trim();
            document.getElementById('customerPhone').value = customerPhone;

            const formDataForValidation = new FormData();
            formDataForValidation.set('customerName', customerName);
            formDataForValidation.set('customerEmail', customerEmail);
            formDataForValidation.set('customerPhone', customerPhone);
            formDataForValidation.set('address', address);
            formDataForValidation.set('city', city);

            const validationError = PatientFormUtils.validateRules(formDataForValidation, {
                customerName: { required: true, message: 'Full name is required.' },
                customerEmail: {
                    required: true,
                    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    message: 'Please enter a valid email address.'
                },
                customerPhone: {
                    required: true,
                    pattern: /^0[0-9]{9}$/,
                    message: 'Please enter a valid phone number (e.g., 0712345678).'
                },
                address: { required: true, message: 'Delivery address is required.' },
                city: { required: true, message: 'City is required.' }
            });
            if (validationError) {
                alert(validationError);
                return;
            }
            if (!cartId) {
                alert('Cart session is not available. Please reload and try again.');
                return;
            }

            // Calculate total
            const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const shipping = subtotal > 5000 ? 0 : 250;
            const total = subtotal + shipping;

            // Prepare items description
            const itemsDesc = cartItems.map(item => `${item.name} x${item.quantity}`).join(', ');

            // Split name into first and last
            const nameParts = customerName.split(' ');
            const firstName = nameParts[0];
            const lastName = nameParts.slice(1).join(' ') || firstName;

            // Fill PayHere form
            document.getElementById('first_name').value = firstName;
            document.getElementById('last_name').value = lastName;
            document.getElementById('email').value = customerEmail;
            document.getElementById('phone').value = customerPhone;
            document.getElementById('delivery_address').value = address;
            document.getElementById('delivery_city').value = city;
            document.getElementById('items').value = itemsDesc;
            document.getElementById('amount').value = total.toFixed(2);
            document.getElementById('custom_2').value = String(cartId);

            // Generate hash via server
            try {
                const formData = new FormData();
                formData.append('order_id', '<?= $orderId ?>');
                formData.append('amount', total.toFixed(2));

                const response = await fetch('/dheergayu/public/api/generate-payhere-hash.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('hash').value = data.hash;
                    
                    // Open PayHere payment page in a new tab so current cart page stays open
                    alert('Payment window will open in a new browser tab. In sandbox mode, you can complete the test payment without entering real card details.');
                    document.getElementById('payhereForm').submit();
                } else {
                    alert('Error preparing payment. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to proceed to payment. Please try again.');
            }
        }

        // Load order summary on page load
        loadOrderSummary();

        // Phone validation
        document.getElementById('customerPhone').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    </script>
</body>
</html>