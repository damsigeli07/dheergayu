<?php
// Fetch products from database
$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch all products from products table
$query = "SELECT product_id, name, price, description, image FROM products ORDER BY product_id";
$result = $db->query($query);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Construct full image path - database stores "images/filename" but we need full path
        $image_path = '/dheergayu/public/assets/images/Admin/' . str_replace('images/', '', $row['image']);
        $products[] = [
            'id' => $row['product_id'],
            'name' => $row['name'],
            'price' => number_format($row['price'], 2, '.', ','),
            'description' => $row['description'],
            'image' => $image_path
        ];
    }
}

$db->close();

// Sample inventory batches (normally fetched from DB) - Multiple batches per product
$inventoryBatches = [
    ["product"=>"Asamodagam", "quantity"=>12, "mfd"=>"2024-01-01", "exp"=>"2026-01-01", "batch_number"=>"ASM001", "supplier"=>"Herbal Supplies Co."],
    ["product"=>"Bala Thailaya", "quantity"=>8, "mfd"=>"2024-06-15", "exp"=>"2026-06-15", "batch_number"=>"BLT001", "supplier"=>"Ayurvedic Traders"],
    ["product"=>"Dashamoolarishta", "quantity"=>18, "mfd"=>"2024-02-10", "exp"=>"2026-02-10", "batch_number"=>"DMR001", "supplier"=>"Natural Extracts Ltd."],
    ["product"=>"Kothalahimbutu Capsules", "quantity"=>6, "mfd"=>"2024-01-20", "exp"=>"2026-01-20", "batch_number"=>"KHC001", "supplier"=>"Herbal Supplies Co."],
    ["product"=>"Neem Oil", "quantity"=>15, "mfd"=>"2024-02-20", "exp"=>"2026-02-20", "batch_number"=>"NEO001", "supplier"=>"Natural Extracts Ltd."],
    ["product"=>"Nirgundi Oil", "quantity"=>22, "mfd"=>"2024-03-05", "exp"=>"2026-03-05", "batch_number"=>"NRO001", "supplier"=>"Ayurvedic Traders"],
    ["product"=>"Paspanguwa", "quantity"=>20, "mfd"=>"2024-03-10", "exp"=>"2026-03-10", "batch_number"=>"PSP001", "supplier"=>"Herbal Supplies Co."],
    ["product"=>"Pinda Thailaya", "quantity"=>14, "mfd"=>"2024-01-25", "exp"=>"2026-01-25", "batch_number"=>"PTL001", "supplier"=>"Natural Extracts Ltd."],
    ["product"=>"Siddhalepa", "quantity"=>25, "mfd"=>"2024-01-15", "exp"=>"2026-01-15", "batch_number"=>"SDP001", "supplier"=>"Ayurvedic Traders"],
    // Additional batches to show total quantities
    ["product"=>"Siddhalepa", "quantity"=>15, "mfd"=>"2024-02-20", "exp"=>"2026-02-20", "batch_number"=>"SDP002", "supplier"=>"Herbal Supplies Co."],
    ["product"=>"Asamodagam", "quantity"=>8, "mfd"=>"2024-03-01", "exp"=>"2026-03-01", "batch_number"=>"ASM002", "supplier"=>"Natural Extracts Ltd."],
    ["product"=>"Neem Oil", "quantity"=>10, "mfd"=>"2024-01-10", "exp"=>"2026-01-10", "batch_number"=>"NEO002", "supplier"=>"Ayurvedic Traders"]
];

// Calculate total quantities per product
$inventoryData = [];
foreach($inventoryBatches as $batch) {
    $productName = $batch['product'];
    if(!isset($inventoryData[$productName])) {
        $inventoryData[$productName] = [
            'product' => $productName,
            'total_quantity' => 0,
            'batches' => [],
            'earliest_exp' => $batch['exp']
        ];
    }
    $inventoryData[$productName]['total_quantity'] += $batch['quantity'];
    $inventoryData[$productName]['batches'][] = $batch;
    
    // Keep track of earliest expiry date
    if($batch['exp'] < $inventoryData[$productName]['earliest_exp']) {
        $inventoryData[$productName]['earliest_exp'] = $batch['exp'];
    }
}

// Calculate stock statistics
$criticalStockCount = 0;
$lowStockCount = 0;
$expiringSoonCount = 0;
$today = new DateTime();
$ninetyDaysFromNow = (new DateTime())->add(new DateInterval('P90D'));

foreach($inventoryData as $item) {
    // Critical stock (total quantity <= 5)
    if($item['total_quantity'] <= 5) {
        $criticalStockCount++;
    }
    // Low stock (total quantity <= 15)
    elseif($item['total_quantity'] <= 15) {
        $lowStockCount++;
    }
    
    // Expiring soon (within 90 days) - check earliest expiry
    $expDate = new DateTime($item['earliest_exp']);
    if($expDate <= $ninetyDaysFromNow) {
        $expiringSoonCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/admininventory.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="admindashboard.php" class="nav-btn">Home</a>
            <button class="nav-btn active">Products</button>
            <a href="admininventoryview.php" class="nav-btn">Inventory</a>
            <a href="adminappointment.php" class="nav-btn">Appointments</a>
            <a href="adminusers.php" class="nav-btn">Users</a>
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">


        <!-- Search Bar -->
        <div class="search-container">
            <div class="search-box">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
                </svg>
                <input type="text" placeholder="Search" class="search-input">
            </div>
        </div>

        <!-- Product Grid -->
        <div class="product-grid">
            <?php if (empty($products)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">
                    <p>No products found. <a href="add-product.php" style="color: #E6A85A;">Add a new product</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-price">Price: Rs. <?= htmlspecialchars($product['price']) ?></p>
                            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        </div>
                        <div class="product-actions">
                            <button class="btn btn-edit" onclick="editProduct(<?= $product['id'] ?>)">Edit</button>
                            <button class="btn btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Add New Product Button -->
        <div class="add-product-container">
            <a href="add-product.php" class="btn-add-product">+ Add New Product</a>
        </div>
    </main>

    <script>
        async function editProduct(id) {
            // Fetch product data from server
            try {
                const formData = new FormData();
                formData.append('product_id', id);
                formData.append('action', 'get');
                
                const res = await fetch('/dheergayu/app/Controllers/ProductController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await res.json();
                if (result.success && result.product) {
                    const p = result.product;
                    // Build URL with query parameters
                    const params = new URLSearchParams({
                        product_id: p.product_id,
                        product_name: p.name || '',
                        product_price: p.price || '',
                        product_description: p.description || '',
                        product_image: p.image || ''
                    });
                    window.location.href = 'add-product.php?' + params.toString();
                } else {
                    alert('Error: Could not load product data');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Failed to load product data: ' + err.message);
            }
        }

        async function deleteProduct(id) {
            if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) return;
            
            try {
                const formData = new FormData();
                formData.append('product_id', id);
                formData.append('action', 'delete');
                
                const res = await fetch('/dheergayu/app/Controllers/ProductController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await res.json();
                if (result.success) {
                    alert('✅ Product deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to delete product'));
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Failed to delete product: ' + err.message);
            }
        }
    </script>
</body>
</html>
