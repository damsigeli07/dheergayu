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
                            <button class="add-to-cart-btn" onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', <?= str_replace(',', '', $product['price']) ?>, 'patient')">
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
                            <div class="product-availability">Available in Store</div>
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
        // Cart functionality
        let cart = JSON.parse(localStorage.getItem('dheergayu_cart') || '[]');
        
        function addToCart(productId, productName, price, productType) {
            // Check if product already in cart
            const existingItem = cart.find(item => item.id === productId && item.type === productType);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: price,
                    type: productType,
                    quantity: 1
                });
            }
            
            // Save to localStorage
            localStorage.setItem('dheergayu_cart', JSON.stringify(cart));
            
            // Show feedback
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
            
            // Update cart count if exists
            updateCartCount();
        }
        
        function updateCartCount() {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) {
                cartBadge.textContent = totalItems;
                cartBadge.style.display = totalItems > 0 ? 'block' : 'none';
            }
        }
        
        // Initialize cart count on page load
        updateCartCount();
        
        // Smooth scroll to top
        document.querySelector('.scroll-to-top')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>