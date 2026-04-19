<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/auth_admin.php';
// Fetch products from database
$db = $conn;

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Check if product_type column exists, if not add it
$checkColumn = $db->query("SHOW COLUMNS FROM products LIKE 'product_type'");
if ($checkColumn->num_rows == 0) {
    $db->query("ALTER TABLE products ADD COLUMN product_type VARCHAR(20) DEFAULT 'admin' AFTER image");
}

// Fix images for admin products
// Update Asamodagam Spirit image - check if asamodagam.jpg exists in Admin folder, if not copy from Pharmacist
$asamodagamAdminPath = __DIR__ . '/../../../public/assets/images/Admin/asamodagam.jpg';
$asamodagamPharmacistPath = __DIR__ . '/../../../public/assets/images/Pharmacist/asamodagam.jpg';
if (!file_exists($asamodagamAdminPath) && file_exists($asamodagamPharmacistPath)) {
    copy($asamodagamPharmacistPath, $asamodagamAdminPath);
}
$db->query("UPDATE products SET image = 'images/asamodagam.jpg' WHERE name = 'Asamodagam Spirit' AND (image != 'images/asamodagam.jpg' OR image IS NULL OR image = '')");

// Update Dashamoolarishta image if it exists and doesn't have the correct image
$db->query("UPDATE products SET image = 'images/Dashamoolarishta.jpeg' WHERE name LIKE '%Dashamoolarishta%' AND (image != 'images/Dashamoolarishta.jpeg' OR image IS NULL OR image = '')");

// Fix images for patient products
$db->query("UPDATE products SET image = 'images/Samhan.jpg' WHERE product_type = 'patient' AND name LIKE '%Samahan%' AND image != 'images/Samhan.jpg'");

if (!file_exists($asamodagamAdminPath) && file_exists($asamodagamPharmacistPath)) {
    copy($asamodagamPharmacistPath, $asamodagamAdminPath);
}
$db->query("UPDATE products SET image = 'images/asamodagam.jpg' WHERE product_type = 'patient' AND name LIKE '%Asamodagam%' AND image != 'images/asamodagam.jpg'");

// Fetch all products
$query = "SELECT product_id, name, price, description, image, COALESCE(product_type,'admin') AS product_type
          FROM products ORDER BY name";
$result = $db->query($query);

$allProducts = [];
$regularProducts = [];
$treatmentProducts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $image_db = $row['image'] ?? '';
        $image_path = empty($image_db)
            ? '/dheergayu/public/assets/images/dheergayu.png'
            : '/dheergayu/public/assets/images/Admin/' . ltrim(str_replace('images/', '', $image_db), '/');
        $entry = [
            'id' => $row['product_id'],
            'name' => $row['name'],
            'price' => number_format($row['price'], 2, '.', ','),
            'description' => $row['description'],
            'image' => $image_path,
            'product_type' => $row['product_type']
        ];
        $allProducts[] = $entry;
        if ($row['product_type'] === 'treatment') {
            $treatmentProducts[] = $entry;
        } else {
            $regularProducts[] = $entry;
        }
    }
}

$db->close();
$products = $allProducts;

// Sample inventory batches (normally fetched from DB) - Multiple batches per product
$inventoryBatches = [
    ["product"=>"Asamodagam", "quantity"=>12, "mfd"=>"2024-01-01", "exp"=>"2026-01-01", "batch_number"=>"ASM001"],
    ["product"=>"Bala Thailaya", "quantity"=>8, "mfd"=>"2024-06-15", "exp"=>"2026-06-15", "batch_number"=>"BLT001"],
    ["product"=>"Dashamoolarishta", "quantity"=>18, "mfd"=>"2024-02-10", "exp"=>"2026-02-10", "batch_number"=>"DMR001"],
    ["product"=>"Kothalahimbutu Capsules", "quantity"=>6, "mfd"=>"2024-01-20", "exp"=>"2026-01-20", "batch_number"=>"KHC001"],
    ["product"=>"Neem Oil", "quantity"=>15, "mfd"=>"2024-02-20", "exp"=>"2026-02-20", "batch_number"=>"NEO001"],
    ["product"=>"Nirgundi Oil", "quantity"=>22, "mfd"=>"2024-03-05", "exp"=>"2026-03-05", "batch_number"=>"NRO001"],
    ["product"=>"Paspanguwa", "quantity"=>20, "mfd"=>"2024-03-10", "exp"=>"2026-03-10", "batch_number"=>"PSP001"],
    ["product"=>"Pinda Thailaya", "quantity"=>14, "mfd"=>"2024-01-25", "exp"=>"2026-01-25", "batch_number"=>"PTL001"],
    ["product"=>"Siddhalepa", "quantity"=>25, "mfd"=>"2024-01-15", "exp"=>"2026-01-15", "batch_number"=>"SDP001"],
    // Additional batches to show total quantities
    ["product"=>"Siddhalepa", "quantity"=>15, "mfd"=>"2024-02-20", "exp"=>"2026-02-20", "batch_number"=>"SDP002"],
    ["product"=>"Asamodagam", "quantity"=>8, "mfd"=>"2024-03-01", "exp"=>"2026-03-01", "batch_number"=>"ASM002"],
    ["product"=>"Neem Oil", "quantity"=>10, "mfd"=>"2024-01-10", "exp"=>"2026-01-10", "batch_number"=>"NEO002"]
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
            <a href="adminpatients.php" class="nav-btn">Patients</a>
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <a href="adminpayments.php" class="nav-btn">Payments</a>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
            <a href="admincontact.php" class="nav-btn">Contact Submissions</a>
                <a href="adminreports.php" class="nav-btn">Reports</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Tab Navigation -->
        <div class="tab-nav">
            <button class="tab-btn active" onclick="switchTab('products')">Products <span class="tab-count"><?= count($regularProducts) ?></span></button>
            <button class="tab-btn" onclick="switchTab('treatment')">Treatment Products <span class="tab-count"><?= count($treatmentProducts) ?></span></button>
        </div>

        <!-- ===== PRODUCTS TAB ===== -->
        <div id="tab-products" class="tab-section">
            <div class="search-container">
                <div class="search-box">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <input type="text" placeholder="Search products..." class="search-input" id="productSearch">
                </div>
            </div>
            <div class="product-grid" id="productGrid">
                <?php if (empty($regularProducts)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">
                        <p>No products found. <a href="add-product.php?product_type=admin" style="color: #E6A85A;">Add a new product</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($regularProducts as $product): ?>
                        <div class="product-card" data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-price">Price: Rs. <?= htmlspecialchars($product['price']) ?></p>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-edit" onclick="editProduct(<?= $product['id'] ?>, '<?= $product['product_type'] ?>')">Edit</button>
                                <button class="btn btn-delete" onclick="deleteProduct(<?= $product['id'] ?>, '<?= $product['product_type'] ?>')">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="add-product-container">
                <a href="add-product.php?product_type=admin" class="btn-add-product">+ Add New Product</a>
            </div>
        </div>

        <!-- ===== TREATMENT PRODUCTS TAB ===== -->
        <div id="tab-treatment" class="tab-section" style="display:none;">
            <div class="search-container">
                <div class="search-box">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <input type="text" placeholder="Search treatment products..." class="search-input" id="treatmentSearch">
                </div>
            </div>
            <div class="product-grid" id="treatmentGrid">
                <?php if (empty($treatmentProducts)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">
                        <p>No treatment products found. <a href="add-product.php?product_type=treatment" style="color: #E6A85A;">Add a treatment product</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($treatmentProducts as $product): ?>
                        <div class="product-card treatment-card" data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-price">Price: Rs. <?= htmlspecialchars($product['price']) ?></p>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                <span class="treatment-badge">Treatment Oil</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-edit" onclick="editProduct(<?= $product['id'] ?>, '<?= $product['product_type'] ?>')">Edit</button>
                                <button class="btn btn-delete" onclick="deleteProduct(<?= $product['id'] ?>, '<?= $product['product_type'] ?>')">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="add-product-container">
                <a href="add-product.php?product_type=treatment" class="btn-add-product" style="background-color: #5b8a6e;">+ Add Treatment Product</a>
            </div>
        </div>
    </main>

    <style>
        .tab-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 0;
        }
        .tab-btn {
            padding: 0.6rem 1.4rem;
            border: none;
            border-radius: 8px 8px 0 0;
            background: #f0f0f0;
            color: #555;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            bottom: -2px;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: white;
            color: #8B7355;
            border-bottom: 2px solid white;
            border-top: 2px solid #E6A85A;
        }
        .tab-btn:hover:not(.active) { background: #e5e5e5; }
        .tab-count {
            background: #E6A85A;
            color: white;
            font-size: 0.7rem;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 4px;
        }
        .treatment-badge {
            display: inline-block;
            background: #5b8a6e;
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
            margin-top: 4px;
        }
        .treatment-card { border-top: 3px solid #5b8a6e; }
    </style>
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-section').forEach(s => s.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + tab).style.display = '';
            event.currentTarget.classList.add('active');
        }

        document.getElementById('productSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('#productGrid .product-card').forEach(card => {
                card.style.display = (card.getAttribute('data-name') || '').includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('treatmentSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('#treatmentGrid .product-card').forEach(card => {
                card.style.display = (card.getAttribute('data-name') || '').includes(searchTerm) ? '' : 'none';
            });
        });


        async function editProduct(id, productType) {
            // Fetch product data from server
            try {
                const formData = new FormData();
                formData.append('product_id', id);
                formData.append('product_type', productType);
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
                        product_image: p.image || '',
                        product_type: productType
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

        async function deleteProduct(id, productType) {
            if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) return;
            
            try {
                const formData = new FormData();
                formData.append('product_id', id);
                formData.append('product_type', productType);
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
