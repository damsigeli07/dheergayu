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
            <!-- Payment Information Card -->
            <div class="card">
                <h2 class="card-title">Payment Information</h2>
                
                <form id="paymentForm">
                    <div class="form-group">
                        <label for="cardNumber">Card Number</label>
                        <div class="card-input">
                            <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" required>
                            <div class="card-icon">CARD</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiryDate">Expiry Date</label>
                            <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="cvc">CVC</label>
                            <input type="text" id="cvc" name="cvc" placeholder="123" maxlength="3" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cardholderName">Cardholder Name</label>
                        <input type="text" id="cardholderName" name="cardholderName" placeholder="Enter name on card" required>
                    </div>

                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" name="country" required>
                            <option value="">Select country</option>
                            <option value="LK" selected>Sri Lanka</option>
                            <option value="IN">India</option>
                            <option value="US">United States</option>
                            <option value="UK">United Kingdom</option>
                            <option value="AU">Australia</option>
                        </select>
                    </div>

                    <div class="checkbox-group">
                        <div class="checkbox-container">
                            <input type="checkbox" id="saveCard" name="saveCard">
                            <label for="saveCard" class="checkbox-label">Save payment information safely and securely</label>
                        </div>
                    </div>

                    <div class="payment-buttons">
                        <button type="submit" class="pay-btn">Pay Now</button>
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

    <script>
        let cartItems = [];

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
            if (confirm('Are you sure you want to cancel this payment?')) {
                window.location.href = 'cart.php';
            }
        }

        // Format card number input
        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            let formattedValue = value.replace(/(.{4})/g, '$1 ').trim();
            
            if (formattedValue.length <= 19) {
                e.target.value = formattedValue;
            }

            // Update card icon based on card number
            const cardIcon = document.querySelector('.card-icon');
            if (value.startsWith('4')) {
                cardIcon.textContent = 'VISA';
                cardIcon.style.background = '#1a73e8';
            } else if (value.startsWith('5')) {
                cardIcon.textContent = 'MC';
                cardIcon.style.background = '#eb001b';
            } else if (value.startsWith('3')) {
                cardIcon.textContent = 'AMEX';
                cardIcon.style.background = '#006fcf';
            } else {
                cardIcon.textContent = 'CARD';
                cardIcon.style.background = '#666';
            }
        });

        // Format expiry date input
        document.getElementById('expiryDate').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.replace(/(\d{2})(\d+)/, '$1/$2');
            }
            e.target.value = value;
        });

        // Format CVC input
        document.getElementById('cvc').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Expiry date validation
        document.getElementById('expiryDate').addEventListener('blur', function() {
            const value = this.value;
            if (value.length === 5) {
                const [month, year] = value.split('/');
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear() % 100;
                const currentMonth = currentDate.getMonth() + 1;
                
                const inputMonth = parseInt(month);
                const inputYear = parseInt(year);
                
                if (inputMonth < 1 || inputMonth > 12) {
                    this.style.borderColor = '#dc3545';
                    alert('Please enter a valid month (01-12)');
                } else if (inputYear < currentYear || (inputYear === currentYear && inputMonth < currentMonth)) {
                    this.style.borderColor = '#dc3545';
                    alert('Card has expired. Please enter a valid expiry date.');
                } else {
                    this.style.borderColor = '#5CB85C';
                }
            }
        });

        // Auto-uppercase cardholder name
        document.getElementById('cardholderName').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Real-time validation
        const inputs = document.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#5CB85C';
                } else {
                    this.style.borderColor = '#e1e5e9';
                }
            });
        });

        // Form submission
        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
            const expiryDate = document.getElementById('expiryDate').value;
            const cvc = document.getElementById('cvc').value;
            const cardholderName = document.getElementById('cardholderName').value;
            const country = document.getElementById('country').value;
            
            // Basic validation
            if (cardNumber.length < 13 || cardNumber.length > 19) {
                alert('Please enter a valid card number!');
                return;
            }
            
            if (expiryDate.length !== 5) {
                alert('Please enter a valid expiry date (MM/YY)!');
                return;
            }
            
            if (cvc.length < 3) {
                alert('Please enter a valid CVC!');
                return;
            }
            
            if (!cardholderName.trim()) {
                alert('Please enter the cardholder name!');
                return;
            }
            
            if (!country) {
                alert('Please select your country!');
                return;
            }
            
            // Calculate total
            const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const shipping = subtotal > 5000 ? 0 : 250;
            const total = subtotal + shipping;
            
            // Simulate payment process
            const payBtn = document.querySelector('.pay-btn');
            payBtn.textContent = 'PROCESSING...';
            payBtn.disabled = true;
            
            setTimeout(async () => {
                const transactionId = 'TXN' + Math.random().toString(36).substr(2, 9).toUpperCase();
                
                alert(`Payment Successful!\n\nTransaction Details:\nAmount: Rs ${total.toFixed(2)}\nTransaction ID: ${transactionId}\n\nYour order has been confirmed. You will receive a confirmation email shortly with tracking information.`);
                
                payBtn.textContent = 'PAYMENT SUCCESSFUL ✓';
                payBtn.style.background = '#5CB85C';
                
                // Clear cart from database
                try {
                    const formData = new FormData();
                    formData.append('action', 'clear');
                    
                    await fetch('/dheergayu/public/api/cart-api.php', {
                        method: 'POST',
                        body: formData
                    });
                } catch (error) {
                    console.error('Error clearing cart:', error);
                }
                
                setTimeout(() => {
                    alert('Thank you for your purchase! Redirecting to home page...');
                    window.location.href = 'home.php';
                }, 2000);
            }, 3000);
        });

        // Load order summary on page load
        loadOrderSummary();
    </script>
</body>
</html>