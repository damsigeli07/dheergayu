<?php
// Sample inventory data - in real system this would come from database
$inventoryData = [
    [
        'id' => 1,
        'name' => 'Paspanguwa Pack',
        'price' => 850,
        'stock' => 12,
        'mfg_date' => '2024-07-15',
        'exp_date' => '2025-09-30',
        'image' => '../pharmacist/images/paspanguwa.jpeg',
        'category' => 'Herbal Medicine',
        'supplier' => 'Ayurvedic Suppliers Ltd',
        'status' => 'normal'
    ],
    [
        'id' => 2,
        'name' => 'Asamodagam Spirit',
        'price' => 650,
        'stock' => 8,
        'mfg_date' => '2024-06-20',
        'exp_date' => '2025-10-31',
        'image' => '../pharmacist/images/asamodagam.jpg',
        'category' => 'Herbal Medicine',
        'supplier' => 'Ayurvedic Suppliers Ltd',
        'status' => 'normal'
    ],
    [
        'id' => 3,
        'name' => 'Siddhalepa Balm',
        'price' => 450,
        'stock' => 3,
        'mfg_date' => '2024-05-12',
        'exp_date' => '2025-11-30',
        'image' => '../pharmacist/images/siddhalepa.png',
        'category' => 'External Medicine',
        'supplier' => 'Siddhalepa Ayurveda',
        'status' => 'low_stock'
    ],
    [
        'id' => 4,
        'name' => 'Dashamoolarishta',
        'price' => 750,
        'stock' => 2,
        'mfg_date' => '2024-04-08',
        'exp_date' => '2025-12-31',
        'image' => '../pharmacist/images/Dashamoolarishta.jpeg',
        'category' => 'Herbal Medicine',
        'supplier' => 'Ayurvedic Suppliers Ltd',
        'status' => 'low_stock'
    ],
    [
        'id' => 5,
        'name' => 'Kothalahimbutu Capsules',
        'price' => 1200,
        'stock' => 1,
        'mfg_date' => '2024-03-25',
        'exp_date' => '2026-01-31',
        'image' => '../pharmacist/images/Kothalahimbutu Capsules.jpeg',
        'category' => 'Capsules',
        'supplier' => 'Herbal Capsules Co',
        'status' => 'critical'
    ],
    [
        'id' => 6,
        'name' => 'Neem Oil',
        'price' => 380,
        'stock' => 15,
        'mfg_date' => '2024-02-18',
        'exp_date' => '2026-03-31',
        'image' => '../pharmacist/images/Neem Oil.jpg',
        'category' => 'Essential Oil',
        'supplier' => 'Pure Oils Ltd',
        'status' => 'normal'
    ],
    [
        'id' => 7,
        'name' => 'Pinda Thailaya',
        'price' => 550,
        'stock' => 3,
        'mfg_date' => '2024-01-30',
        'exp_date' => '2026-05-31',
        'image' => '../pharmacist/images/Pinda Thailaya.jpeg',
        'category' => 'Massage Oil',
        'supplier' => 'Ayurvedic Oils Co',
        'status' => 'low_stock'
    ],
    [
        'id' => 8,
        'name' => 'Nirgundi Oil',
        'price' => 480,
        'stock' => 7,
        'mfg_date' => '2024-08-05',
        'exp_date' => '2026-07-31',
        'image' => '../pharmacist/images/Nirgundi Oil.jpg',
        'category' => 'Essential Oil',
        'supplier' => 'Pure Oils Ltd',
        'status' => 'normal'
    ]
];

// Calculate alerts
$lowStockCount = 0;
$expiringSoonCount = 0;
$criticalStockCount = 0;

foreach ($inventoryData as $item) {
    if ($item['status'] === 'low_stock') $lowStockCount++;
    if ($item['status'] === 'critical') $criticalStockCount++;
    
    $expiryDate = new DateTime($item['exp_date']);
    $today = new DateTime();
    $daysUntilExpiry = $today->diff($expiryDate)->days;
    
    if ($daysUntilExpiry <= 90) $expiringSoonCount++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/admininventory.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="admindashboard.php" class="nav-btn">Home</a>
                <button class="nav-btn active">Inventory</button>
                <a href="adminappointment.php" class="nav-btn">Appointments</a>
                <a href="adminusers.php" class="nav-btn">Users</a>
                <a href="admintreatment.php" class="nav-btn">Treatment Schedule</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>
                <div class="user-dropdown" id="user-dropdown">
                    <a href="../patient/login.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
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

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="search-container">
                <div class="search-box">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <input type="text" placeholder="Search products..." class="search-input" id="searchInput">
                </div>
            </div>
            
            <div class="filter-container">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="normal">Normal Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="critical">Critical Stock</option>
                </select>
                
                <select class="filter-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="Herbal Medicine">Herbal Medicine</option>
                    <option value="External Medicine">External Medicine</option>
                    <option value="Capsules">Capsules</option>
                    <option value="Essential Oil">Essential Oil</option>
                    <option value="Massage Oil">Massage Oil</option>
                </select>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="inventory-table-container">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock Level</th>
                        <th>Price</th>
                        <th>Manufacture Date</th>
                        <th>Expiry Date</th>
                        <th>Supplier</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventoryData as $item): ?>
                        <?php
                        $expiryDate = new DateTime($item['exp_date']);
                        $today = new DateTime();
                        $daysUntilExpiry = $today->diff($expiryDate)->days;
                        $isExpiringSoon = $daysUntilExpiry <= 90;
                        ?>
                        <tr class="inventory-row <?= $item['status'] ?> <?= $isExpiringSoon ? 'expiring-soon' : '' ?>">
                            <td class="product-cell">
                                <div class="product-info">
                                    <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>" class="product-thumbnail">
                                    <div>
                                        <h4 class="product-name"><?= $item['name'] ?></h4>
                                        <span class="product-id">ID: <?= $item['id'] ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="category-cell"><?= $item['category'] ?></td>
                            <td class="stock-cell">
                                <span class="stock-number <?= $item['status'] ?>"><?= $item['stock'] ?></span>
                                <?php if ($item['status'] === 'critical'): ?>
                                    <span class="stock-alert critical">Critical!</span>
                                <?php elseif ($item['status'] === 'low_stock'): ?>
                                    <span class="stock-alert warning">Low!</span>
                                <?php endif; ?>
                            </td>
                            <td class="price-cell">Rs. <?= number_format($item['price']) ?></td>
                            <td class="date-cell"><?= date('M d, Y', strtotime($item['mfg_date'])) ?></td>
                            <td class="expiry-cell <?= $isExpiringSoon ? 'expiring-soon' : '' ?>">
                                <?= date('M d, Y', strtotime($item['exp_date'])) ?>
                                <?php if ($isExpiringSoon): ?>
                                    <span class="expiry-alert">⚠️ <?= $daysUntilExpiry ?> days left</span>
                                <?php endif; ?>
                            </td>
                            <td class="supplier-cell"><?= $item['supplier'] ?></td>
                            <td class="status-cell">
                                <span class="status-badge <?= $item['status'] ?>">
                                    <?php
                                    switch($item['status']) {
                                        case 'normal': echo 'Normal'; break;
                                        case 'low_stock': echo 'Low Stock'; break;
                                        case 'critical': echo 'Critical'; break;
                                    }
                                    ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-export" onclick="exportInventory()">📊 Export Report</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ Print Report</button>
        </div>
    </main>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.inventory-row');
            
            rows.forEach(row => {
                const productName = row.querySelector('.product-name').textContent.toLowerCase();
                const category = row.querySelector('.category-cell').textContent.toLowerCase();
                const supplier = row.querySelector('.supplier-cell').textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || category.includes(searchTerm) || supplier.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('categoryFilter').addEventListener('change', filterTable);

        function filterTable() {
            const statusFilter = document.getElementById('statusFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value;
            const rows = document.querySelectorAll('.inventory-row');
            
            rows.forEach(row => {
                const status = row.querySelector('.status-badge').textContent.toLowerCase();
                const category = row.querySelector('.category-cell').textContent;
                
                const statusMatch = !statusFilter || status.includes(statusFilter.replace('_', ' '));
                const categoryMatch = !categoryFilter || category === categoryFilter;
                
                if (statusMatch && categoryMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function exportInventory() {
            // In a real system, this would generate a CSV or PDF report
            alert('Export functionality would generate a detailed inventory report here.');
        }
    </script>
</body>
</html>
