<?php
$patientProducts = [];
$adminProducts = [];
$productsError = '';

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    $productsError = 'Failed to load products. Please try again later.';
} else {
    // Fetch patient products first
    $patientQuery = "SELECT product_id, name, price, description, image FROM patient_products ORDER BY name ASC";
    if ($result = $db->query($patientQuery)) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = trim((string)($row['image'] ?? ''));
            if ($imagePath !== '') {
                $imagePath = '/dheergayu/public/assets/images/Admin/' . ltrim(str_replace('images/', '', $imagePath), '/');
            } else {
                $imagePath = '/dheergayu/public/assets/images/dheergayu.png';
            }

            $productName = $row['name'] ?? 'Unnamed Product';
            // Remove (Patient) and (Sachets) suffixes
            $productName = str_replace(' (Patient)', '', $productName);
            $productName = str_replace(' (Sachets)', '', $productName);
            
            $patientProducts[] = [
                'id' => (int)$row['product_id'],
                'name' => $productName,
                'price' => number_format((float)($row['price'] ?? 0), 2),
                'description' => $row['description'] ?? 'No description available.',
                'image' => $imagePath,
                'type' => 'patient'
            ];
        }
        $result->free();
    }
    
    // Fetch admin products
    $adminQuery = "SELECT product_id, name, price, description, image FROM products WHERE COALESCE(product_type, 'admin') = 'admin' ORDER BY name ASC";
    if ($result = $db->query($adminQuery)) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = trim((string)($row['image'] ?? ''));
            if ($imagePath !== '') {
                $imagePath = '/dheergayu/public/assets/images/Admin/' . ltrim(str_replace('images/', '', $imagePath), '/');
            } else {
                $imagePath = '/dheergayu/public/assets/images/dheergayu.png';
            }

            $adminProducts[] = [
                'id' => (int)$row['product_id'],
                'name' => $row['name'] ?? 'Unnamed Product',
                'price' => number_format((float)($row['price'] ?? 0), 2),
                'description' => $row['description'] ?? 'No description available.',
                'image' => $imagePath,
                'type' => 'admin'
            ];
        }
        $result->free();
    }
    
    if (empty($patientProducts) && empty($adminProducts)) {
        $productsError = 'Failed to load products. Please try again later.';
    }
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - Dheergayu Pharmacy</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/products.css?v=<?php echo time(); ?>">
    <style>
        /* Floating Cart Button */
        .floating-cart {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #8B7355, #A0916B);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(139, 115, 85, 0.4);
            z-index: 1000;
            transition: all 0.3s ease;
            font-size: 24px;
        }

        .floating-cart:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 30px rgba(139, 115, 85, 0.6);
        }

        .floating-cart .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            font-size: 12px;
            font-weight: bold;
            min-width: 22px;
            height: 22px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        /* Cart notification animation */
        @keyframes cartBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .floating-cart.bounce {
            animation: cartBounce 0.5s ease;
        }

        /* Success message */
        .cart-notification {
            position: fixed;
            top: 100px;
            right: 30px;
            background: #5CB85C;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1001;
            opacity: 0;
            transform: translateX(400px);
            transition: all 0.4s ease;
        }

        .cart-notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .cart-notification .close-notif {
            margin-left: 15px;
            cursor: pointer;
            font-weight: bold;
            opacity: 0.8;
        }

        .cart-notification .close-notif:hover {
            opacity: 1;
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
                    <li><a href="/dheergayu/app/Views/Patient/products.php" class="active">SHOP</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="home.php" class="back-btn">← Back to Home</a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="main-title">Our Products</h1>
            <div class="page-description">
                <p>Discover our comprehensive range of authentic Ayurvedic medicines and herbal products. In our pharmacy, you can buy these high-quality products to support your health and wellness journey.</p>
            </div>
        </div>

        <div class="products-grid">
            <?php if ($productsError): ?>
                <div class="product-card" style="grid-column: 1 / -1; text-align:center;">
                    <p><?= htmlspecialchars($productsError) ?></p>
                </div>
            <?php elseif (empty($patientProducts) && empty($adminProducts)): ?>
                <div class="product-card" style="grid-column: 1 / -1; text-align:center;">
                    <h3 class="product-name">No products available</h3>
                    <p class="product-use">Please check back soon. Our pharmacy team is adding new wellness products.</p>
                </div>
            <?php else: ?>
                <!-- Patient Products (First Row) -->
                <?php foreach ($patientProducts as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>" data-product-type="patient">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price">Rs. <?= htmlspecialchars($product['price']) ?></div>
                            <p class="product-use"><?= htmlspecialchars($product['description']) ?></p>
                            <button class="add-to-cart-btn" onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', <?= str_replace(',', '', $product['price']) ?>, 'patient', '<?= htmlspecialchars($product['image']) ?>')">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Admin Products -->
                <?php foreach ($adminProducts as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>" data-product-type="admin">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price">Rs. <?= htmlspecialchars($product['price']) ?></div>
                            <p class="product-use"><?= htmlspecialchars($product['description']) ?></p>
                            <button class="add-to-cart-btn" onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', <?= str_replace(',', '', $product['price']) ?>, 'admin', '<?= htmlspecialchars($product['image']) ?>')">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            <h3>Visit Our Pharmacy</h3>
            <p>All our products are available for purchase at our pharmacy location. Our experienced staff is ready to assist you with product selection and provide guidance on usage. We ensure all products are authentic and of the highest quality.</p>
        </div>
    </div>

    <!-- Floating Cart Button -->
    <div class="floating-cart" onclick="goToCart()">
        🛒
        <span class="cart-badge" id="cartBadge" style="display: none;">0</span>
    </div>

    <!-- Cart Notification -->
    <div class="cart-notification" id="cartNotification">
        <span id="notificationText">Added to cart!</span>
        <span class="close-notif" onclick="closeNotification()">✕</span>
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
// Updated JavaScript for products.php - Database-backed cart with user authentication

// Initialize - Load cart from database
async function initializeCart() {
    try {
        const response = await fetch('/dheergayu/public/api/cart-api.php?action=get');
        const data = await response.json();
        
        if (data.success) {
            updateCartBadge(data.count);
        }
    } catch (error) {
        console.error('Error loading cart:', error);
    }
}

async function addToCart(productId, productName, price, productType, image) {
    try {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('product_name', productName);
        formData.append('price', price);
        formData.append('product_type', productType);
        formData.append('image', image);
        formData.append('quantity', 1);
        
        const response = await fetch('/dheergayu/public/api/cart-api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update cart badge
            updateCartBadge(data.cart_count);
            
            // Show feedback on button
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = 'Added!';
            btn.style.backgroundColor = '#4CAF50';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.backgroundColor = '';
                btn.disabled = false;
            }, 1500);
            
            // Show notification
            showNotification(productName);
            
            // Bounce animation for cart icon
            const cartIcon = document.querySelector('.floating-cart');
            if (cartIcon) {
                cartIcon.classList.add('bounce');
                setTimeout(() => cartIcon.classList.remove('bounce'), 500);
            }
        } else {
            alert('Error adding to cart: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to add item to cart. Please try again.');
    }
}

function updateCartBadge(count) {
    const cartBadge = document.getElementById('cartBadge');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function showNotification(productName) {
    const notification = document.getElementById('cartNotification');
    const notifText = document.getElementById('notificationText');
    notifText.textContent = `"${productName}" added to cart!`;
    
    notification.classList.add('show');
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

function closeNotification() {
    document.getElementById('cartNotification').classList.remove('show');
}

function goToCart() {
    window.location.href = 'cart.php';
}

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', initializeCart);
    </script>
</body>
</html>