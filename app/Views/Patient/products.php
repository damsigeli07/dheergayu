<?php
$patientProducts = [];
$adminProducts   = [];
$productsError   = '';

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    $productsError = 'Failed to load products. Please try again later.';
} else {
    $patientQuery = "SELECT product_id, name, price, description, image FROM patient_products ORDER BY name ASC";
    if ($result = $db->query($patientQuery)) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = trim((string)($row['image'] ?? ''));
            $imagePath = $imagePath !== ''
                ? '/dheergayu/public/assets/images/Admin/' . ltrim(str_replace('images/', '', $imagePath), '/')
                : '/dheergayu/public/assets/images/dheergayu.png';

            $productName = str_replace([' (Patient)', ' (Sachets)'], '', $row['name'] ?? 'Unnamed Product');

            $patientProducts[] = [
                'id'          => (int)$row['product_id'],
                'name'        => $productName,
                'price'       => number_format((float)($row['price'] ?? 0), 2),
                'description' => $row['description'] ?? 'No description available.',
                'image'       => $imagePath,
                'type'        => 'patient',
            ];
        }
        $result->free();
    }

    $adminQuery = "SELECT product_id, name, price, description, image FROM products
                   WHERE COALESCE(product_type, 'admin') = 'admin' ORDER BY name ASC";
    if ($result = $db->query($adminQuery)) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = trim((string)($row['image'] ?? ''));
            $imagePath = $imagePath !== ''
                ? '/dheergayu/public/assets/images/Admin/' . ltrim(str_replace('images/', '', $imagePath), '/')
                : '/dheergayu/public/assets/images/dheergayu.png';

            $adminProducts[] = [
                'id'          => (int)$row['product_id'],
                'name'        => $row['name'] ?? 'Unnamed Product',
                'price'       => number_format((float)($row['price'] ?? 0), 2),
                'description' => $row['description'] ?? 'No description available.',
                'image'       => $imagePath,
                'type'        => 'admin',
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
        /* Cart notification toast */
        .cart-notification {
            position: fixed; top: 90px; right: 30px; z-index: 2000;
            background: #5CB85C; color: white;
            padding: 14px 22px; border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,.2);
            opacity: 0; transform: translateX(400px);
            transition: all .4s ease; pointer-events: none;
        }
        .cart-notification.show { opacity: 1; transform: translateX(0); pointer-events: auto; }
        .cart-notification .close-notif {
            margin-left: 14px; cursor: pointer; font-weight: bold; opacity: .8;
        }
        .cart-notification .close-notif:hover { opacity: 1; }
    </style>
</head>
<body>

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="/dheergayu/public/assets/images/Patient/logo_modern.png" alt="Dheergayu Logo">
                <h1>DHEERGAYU <br><span>AYURVEDIC MANAGEMENT CENTER</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php">HOME</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php" class="active">SHOP</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/contact_us.php">CONTACT US</a></li>
                </ul>
            </nav>

            <!-- header-right: cart icon + back btn -->
            <div class="header-right" style="display:flex;align-items:center;">

                <!-- Live cart icon -->
                <div class="header-cart-icon"
                     onclick="window.location.href='/dheergayu/app/Views/Patient/cart.php'"
                     style="position:relative;cursor:pointer;margin-right:20px;
                            display:inline-flex;align-items:center;">
                    <span style="font-size:26px;color:white;line-height:1;">🛒</span>
                    <span id="cartBadgeHeader"
                          style="display:none;position:absolute;top:-8px;right:-10px;
                                 background:#dc3545;color:white;font-size:11px;font-weight:700;
                                 min-width:18px;height:18px;border-radius:9px;
                                 align-items:center;justify-content:center;padding:0 4px;">
                        0
                    </span>
                </div>

                <a href="home.php" class="back-btn">← Back to Home</a>
            </div>
        </div>
    </header>

    <!-- ── Page body ────────────────────────────────────────────────────────── -->
    <div class="container">
        <div class="page-header">
            <h1 class="main-title">Our Products</h1>
            <div class="page-description">
                <p>Discover our comprehensive range of authentic Ayurvedic medicines and herbal products.</p>
            </div>
        </div>

        <div class="products-grid">
            <?php if ($productsError): ?>
                <div class="product-card" style="grid-column:1/-1;text-align:center;">
                    <p><?= htmlspecialchars($productsError) ?></p>
                </div>
            <?php elseif (empty($patientProducts) && empty($adminProducts)): ?>
                <div class="product-card" style="grid-column:1/-1;text-align:center;">
                    <h3 class="product-name">No products available</h3>
                    <p class="product-use">Please check back soon.</p>
                </div>
            <?php else: ?>
                <?php foreach (array_merge($patientProducts, $adminProducts) as $product): ?>
                    <div class="product-card"
                         data-product-id="<?= $product['id'] ?>"
                         data-product-type="<?= $product['type'] ?>">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image']) ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price">Rs. <?= htmlspecialchars($product['price']) ?></div>
                            <p class="product-use"><?= htmlspecialchars($product['description']) ?></p>
                            <button class="add-to-cart-btn"
                                    onclick="addToCart(
                                        <?= $product['id'] ?>,
                                        '<?= htmlspecialchars(addslashes($product['name'])) ?>',
                                        <?= str_replace(',', '', $product['price']) ?>,
                                        '<?= $product['type'] ?>',
                                        '<?= htmlspecialchars(addslashes($product['image'])) ?>',
                                        event
                                    )">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="footer-note">
            <h3>Visit Our Pharmacy</h3>
            <p>All products are available in our pharmacy. Our experienced staff are ready to assist you.</p>
        </div>
    </div>

    <!-- Cart toast notification -->
    <div class="cart-notification" id="cartNotification">
        <span id="notificationText">Added to cart!</span>
        <span class="close-notif" onclick="closeNotification()">✕</span>
    </div>

    <!-- ── Footer ───────────────────────────────────────────────────────────── -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best Ayurvedic wellness centers in your area!</p>
            </div>
            <div class="footer-column">
                <h3>OFFICE</h3>
                <p>Sri Lanka — 123 Wellness Street, Colombo LK 00100</p>
                <p><a href="mailto:info@dheergayu.com" class="footer-link">info@dheergayu.com</a></p>
                <p>+94 11 234 5678</p>
            </div>
            <div class="footer-column">
                <h3>LINKS</h3>
                <ul>
                    <li><a href="home.php"       class="footer-link">Home</a></li>
                    <li><a href="treatment.php"  class="footer-link">Treatments</a></li>
                    <li><a href="learn_more.php" class="footer-link">About Us</a></li>
                    <li><a href="channeling.php" class="footer-link">Booking</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="social-link">Facebook</a></li>
                    <li><a href="#" class="social-link">Instagram</a></li>
                    <li><a href="#" class="social-link">X</a></li>
                    <li><a href="#" class="social-link">LinkedIn</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- ── Scripts ──────────────────────────────────────────────────────────── -->
    <script>
    // ── Live cart badge ────────────────────────────────────────────────────────
    async function refreshCartBadge() {
        try {
            const res  = await fetch('/dheergayu/public/api/cart-api.php?action=get');
            const data = await res.json();
            const badge = document.getElementById('cartBadgeHeader');
            if (!badge) return;
            if (data.success) {
                const total = (data.items || []).reduce((s, i) => s + i.quantity, 0);
                badge.textContent   = total;
                badge.style.display = total > 0 ? 'flex' : 'none';
            } else {
                badge.style.display = 'none';
            }
        } catch (e) {
            const badge = document.getElementById('cartBadgeHeader');
            if (badge) badge.style.display = 'none';
        }
    }

    // ── Add to cart ────────────────────────────────────────────────────────────
    async function addToCart(productId, productName, price, productType, image, event) {
        const btn = event ? event.target : null;
        if (btn) { btn.disabled = true; btn.textContent = 'Adding…'; }

        try {
            const formData = new FormData();
            formData.append('action',       'add');
            formData.append('product_id',   productId);
            formData.append('product_name', productName);
            formData.append('price',        price);
            formData.append('product_type', productType);
            formData.append('image',        image);
            formData.append('quantity',     1);

            const res  = await fetch('/dheergayu/public/api/cart-api.php', { method:'POST', body:formData });
            const data = await res.json();

            if (data.success) {
                // Update badge
                await refreshCartBadge();

                // Brief button feedback
                if (btn) {
                    btn.textContent = '✓ Added!';
                    btn.style.backgroundColor = '#4CAF50';
                    setTimeout(() => {
                        btn.textContent = 'Add to Cart';
                        btn.style.backgroundColor = '';
                        btn.disabled = false;
                    }, 1500);
                }

                showNotification(productName);
            } else {
                throw new Error(data.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Add to cart error:', error);
            alert('Failed to add item to cart: ' + error.message);
            if (btn) { btn.textContent = 'Add to Cart'; btn.disabled = false; }
        }
    }

    // ── Toast notification ─────────────────────────────────────────────────────
    let _notifTimer;
    function showNotification(productName) {
        const el = document.getElementById('cartNotification');
        document.getElementById('notificationText').textContent = `"${productName}" added to cart!`;
        el.classList.add('show');
        clearTimeout(_notifTimer);
        _notifTimer = setTimeout(() => el.classList.remove('show'), 3000);
    }
    function closeNotification() {
        document.getElementById('cartNotification').classList.remove('show');
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', refreshCartBadge);
    </script>
</body>
</html>
