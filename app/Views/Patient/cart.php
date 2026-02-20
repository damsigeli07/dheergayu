<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/cart.css?v=<?php echo time(); ?>">
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
                <a href="products.php" class="back-btn">← Back to Products</a>
            </div>
        </div>
    </header>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review your items before checkout</p>
        </div>

        <div id="cartContent">
            <!-- Cart will be populated by JavaScript -->
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
        // Fetch cart from database (using the CRUD API)
        async function loadCart() {
            try {
                const response = await fetch('/dheergayu/public/api/cart-api.php?action=get');
                const data = await response.json();
                
                if (data.success) {
                    renderCart(data.items);
                } else {
                    console.error('Failed to load cart:', data.error);
                    renderCart([]);
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                renderCart([]);
            }
        }

        function renderCart(cartItems) {
            const cartContent = document.getElementById('cartContent');

            if (cartItems.length === 0) {
                cartContent.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <h2>Your cart is empty</h2>
                        <p>Add some products to get started!</p>
                        <button class="checkout-btn" onclick="window.location.href='products.php'">
                            Browse Products
                        </button>
                    </div>
                `;
                return;
            }

            const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const shipping = subtotal > 5000 ? 0 : 250;
            const total = subtotal + shipping;

            let itemsHTML = cartItems.map((item) => `
                <div class="cart-item">
                    <div class="item-image">
                        <img src="${item.image || '/dheergayu/public/assets/images/dheergayu.png'}" 
                             alt="${item.name}" 
                             onerror="this.src='/dheergayu/public/assets/images/dheergayu.png'">
                    </div>
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-type">${item.type} product</div>
                        <div class="item-price">Rs. ${item.price.toFixed(2)}</div>
                    </div>
                    <div class="item-actions">
                        <div class="quantity-control">
                            <button class="quantity-btn" onclick="updateQuantity(${item.cart_item_id}, ${item.quantity - 1})">−</button>
                            <span class="quantity-display">${item.quantity}</span>
                            <button class="quantity-btn" onclick="updateQuantity(${item.cart_item_id}, ${item.quantity + 1})">+</button>
                        </div>
                        <button class="remove-btn" onclick="removeItem(${item.cart_item_id})">Remove</button>
                    </div>
                </div>
            `).join('');

            cartContent.innerHTML = `
                <div class="cart-content">
                    <div class="cart-items">
                        ${itemsHTML}
                    </div>
                    <div class="cart-summary">
                        <div class="summary-title">Order Summary</div>
                        <div class="summary-row">
                            <span>Subtotal (${cartItems.reduce((sum, item) => sum + item.quantity, 0)} items)</span>
                            <span>Rs. ${subtotal.toFixed(2)}</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>${shipping === 0 ? 'FREE' : 'Rs. ' + shipping.toFixed(2)}</span>
                        </div>
                        ${shipping === 0 ? '' : '<div class="summary-row" style="font-size: 0.85rem; color: #5CB85C;"><span>Free shipping on orders over Rs. 5,000</span><span></span></div>'}
                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="amount">Rs. ${total.toFixed(2)}</span>
                        </div>
                        <button class="checkout-btn" onclick="proceedToCheckout()">
                            Proceed to Checkout
                        </button>
                        <button class="continue-shopping" onclick="window.location.href='products.php'">
                            Continue Shopping
                        </button>
                    </div>
                </div>
            `;
        }

        async function updateQuantity(cartItemId, newQuantity) {
            if (newQuantity < 0) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('cart_item_id', cartItemId);
                formData.append('quantity', newQuantity);
                
                const response = await fetch('/dheergayu/public/api/cart-api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reload cart
                    loadCart();
                } else {
                    alert('Failed to update quantity: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to update cart. Please try again.');
            }
        }

        async function removeItem(cartItemId) {
            if (!confirm('Remove this item from cart?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'remove');
                formData.append('cart_item_id', cartItemId);
                
                const response = await fetch('/dheergayu/public/api/cart-api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reload cart
                    loadCart();
                } else {
                    alert('Failed to remove item: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to remove item. Please try again.');
            }
        }

        async function proceedToCheckout() {
            // Check if cart has items first
            const response = await fetch('/dheergayu/public/api/cart-api.php?action=get');
            const data = await response.json();
            
            if (!data.success || data.items.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            window.location.href = 'payment.php';
        }

        // Initialize cart on page load
        loadCart();
    </script>
</body>
</html>