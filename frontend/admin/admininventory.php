<?php
// Sample data for demonstration
$products = [
    ["name"=>"Asamodagam", "image"=>"images/asamodagam.jpg"],
    ["name"=>"Bala Thailaya", "image"=>"images/Bala Thailaya.png"],
    ["name"=>"Dashamoolarishta", "image"=>"images/Dashamoolarishta.jpeg"],
    ["name"=>"Kothalahimbutu Capsules", "image"=>"images/Kothalahimbutu Capsules.jpeg"],
    ["name"=>"Neem Oil", "image"=>"images/Neem Oil.jpg"],
    ["name"=>"Nirgundi Oil", "image"=>"images/Nirgundi Oil.jpg"],
    ["name"=>"Paspanguwa", "image"=>"images/paspanguwa.jpeg"],
    ["name"=>"Pinda Thailaya", "image"=>"images/Pinda Thailaya.jpeg"],
    ["name"=>"Siddhalepa", "image"=>"images/siddhalepa.png"],
];

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
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/admininventory.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="admindashboard.php" class="nav-btn">Home</a>
                <button class="nav-btn active">Products</button>
                <a href="adminappointment.php" class="nav-btn">Appointments</a>
                <a href="adminusers.php" class="nav-btn">Users</a>
                <a href="admintreatment.php" class="nav-btn">Treatments</a>
                <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>

                <!-- Dropdown -->
                <div class="user-dropdown" id="user-dropdown">
                    <a href="adminprofile.php" class="profile-btn">Profile</a>
                    <a href="../patient/login.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">

        <!-- Inventory Overview Cards -->
        <div class="inventory-overview">
            <div class="overview-card critical">
                <div class="overview-icon">⚠️</div>
                <div class="overview-content">
                    <h3>Critical Stock</h3>
                    <p class="overview-number"><?= $criticalStockCount ?></p>
                    <p class="overview-desc">Items need immediate attention</p>
                </div>
            </div>
            
            <div class="overview-card warning">
                <div class="overview-icon">📉</div>
                <div class="overview-content">
                    <h3>Low Stock</h3>
                    <p class="overview-number"><?= $lowStockCount ?></p>
                    <p class="overview-desc">Items running low</p>
                </div>
            </div>
            
            <div class="overview-card alert">
                <div class="overview-icon">⏰</div>
                <div class="overview-content">
                    <h3>Expiring Soon</h3>
                    <p class="overview-number"><?= $expiringSoonCount ?></p>
                    <p class="overview-desc">Within 90 days</p>
                </div>
            </div>
            
            <div class="overview-card total">
                <div class="overview-icon">📦</div>
                <div class="overview-content">
                    <h3>Total Products</h3>
                    <p class="overview-number"><?= count($inventoryData) ?></p>
                    <p class="overview-desc">In inventory</p>
                </div>
            </div>
        </div>

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

            <!-- Product Card 1 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/paspanguwa.jpeg" alt="Paspanguwa Pack" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Paspanguwa Pack</h3>
                    <p class="product-price">Price: Rs. 850</p>
                    <p class="product-description">A traditional herbal remedy for digestive health and overall wellness.</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/asamodagam.jpg" alt="Asamodagam Spirit" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Asamodagam Spirit</h3>
                    <p class="product-price">Price: Rs. 650</p>
                    <p class="product-description">A potent herbal formulation to boost immunity and energy levels naturally.</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/siddhalepa.png" alt="Siddhalepa Balm" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Siddhalepa Balm</h3>
                    <p class="product-price">Price: Rs. 450</p>
                    <p class="product-description">Herbal balm for fast relief from headaches and muscle pain.</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Dashamoolarishta.jpeg" alt="Dashamoolarishta" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Dashamoolarishta</h3>
                    <p class="product-price">Price: Rs. 750</p>
                    <p class="product-description">A traditional tonic to support digestion and enhance immunity.</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 5 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Kothalahimbutu Capsules.jpeg" alt="Kothalahimbutu Capsules" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Kothalahimbutu Capsules</h3>
                    <p class="product-price">Price: Rs. 1200</p>
                    <p class="product-description">Capsules made from Kothalahimbutu herb to enhance stamina and vitality.</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 6 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Neem Oil.jpg" alt="Neem Oil" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Neem Oil</h3>
                    <p class="product-price">Price: Rs. 380</p>
                    <p class="product-description">Pure neem oil for skin care, hair care, and general wellness.</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 7 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Pinda Thailaya.jpeg" alt="Pinda Thailaya" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Pinda Thailaya</h3>
                    <p class="product-price">Price: Rs. 550</p>
                    <p class="product-description">Therapeutic oil for joint and muscle pain relief.</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 8 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Nirgundi Oil.jpg" alt="Nirgundi Oil" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Nirgundi Oil</h3>
                    <p class="product-price">Price: Rs. 480</p>
                    <p class="product-description">Nirgundi oil for effective pain relief and inflammation reduction.</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

        </div>

        <!-- Add New Product Button -->
        <div class="add-product-container">
            <a href="add-product.php" class="btn-add-product">+ Add New Product</a>
        </div>
    </main>
</body>
</html>
