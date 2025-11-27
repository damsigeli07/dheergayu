<?php
$products = [];
$productsError = '';

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    $productsError = 'Failed to load products. Please try again later.';
} else {
    $query = "SELECT product_id, name, price, description, image FROM products ORDER BY name ASC";
    if ($result = $db->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = trim((string)($row['image'] ?? ''));
            if ($imagePath !== '') {
                $imagePath = '/dheergayu/public/assets/images/Admin/' . ltrim(str_replace('images/', '', $imagePath), '/');
            } else {
                $imagePath = '/dheergayu/public/assets/images/dheergayu.png';
            }

            $products[] = [
                'id' => (int)$row['product_id'],
                'name' => $row['name'] ?? 'Unnamed Product',
                'price' => number_format((float)($row['price'] ?? 0), 2),
                'description' => $row['description'] ?? 'No description available.',
                'image' => $imagePath
            ];
        }
        $result->free();
    } else {
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
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="/dheergayu/public/assets/images/Patient/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div>
            <a href="home.php" class="back-btn">← Back to Home</a>
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
            <?php elseif (empty($products)): ?>
                <div class="product-card" style="grid-column: 1 / -1; text-align:center;">
                    <h3 class="product-name">No products available</h3>
                    <p class="product-use">Please check back soon. Our pharmacy team is adding new wellness products.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
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
</body>
</html>
