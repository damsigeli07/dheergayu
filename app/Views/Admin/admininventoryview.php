<?php
require_once __DIR__ . '/../../../core/bootloader.php';

use App\Models\BatchModel;

$model = new BatchModel();

// Products and image mapping (by product name)
$productRows = $model->getProducts();
$productNameToId = [];
foreach ($productRows as $row) {
    $productNameToId[$row['name']] = (int)$row['id'];
}

function product_image_for(string $name): string {
    $n = strtolower($name);
    if (strpos($n, 'asamodagam') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/asamodagam.jpg';
    }
    if (strpos($n, 'paspanguwa') !== false || strpos($n, 'pasapanguwa') !== false || strpos($n, 'pasanguwa') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/paspanguwa.jpeg';
    }
    if (strpos($n, 'siddhalepa') !== false || strpos($n, 'sidhalepa') !== false || strpos($n, 'siddalepa') !== false || strpos($n, 'siddphalepa') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/siddhalepa.png';
    }
    if (strpos($n, 'bala') !== false && strpos($n, 'thailaya') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/Bala Thailaya.png';
    }
    if (strpos($n, 'dashamoolarishta') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/Dashamoolarishta.jpeg';
    }
    if (strpos($n, 'kothalahimbutu') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/Kothalahimbutu Capsules.jpeg';
    }
    if (strpos($n, 'neem') !== false && strpos($n, 'oil') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/Neem Oil.jpg';
    }
    if (strpos($n, 'nirgundi') !== false && strpos($n, 'oil') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/Nirgundi Oil.jpg';
    }
    if (strpos($n, 'pinda') !== false && strpos($n, 'thailaya') !== false) {
        return '/dheergayu/public/assets/images/Pharmacist/Pinda Thailaya.jpeg';
    }
    return '/dheergayu/public/assets/images/Pharmacist/dheergayu.png';
}

// Inventory overview from DB
$overview = $model->getInventoryOverview();

// Calculate statistics from database
$criticalStockCount = 0;
$lowStockCount = 0;
$expiringSoonCount = 0;

foreach ($overview as $row) {
    $qty = (int)$row['total_quantity'];
    if ($qty <= 5) { 
        $criticalStockCount++; 
    } elseif ($qty <= 15) { 
        $lowStockCount++; 
    }
}

// Check for products with batches that have "Expiring Soon" status
$expiringProducts = [];
foreach ($overview as $row) {
    $productId = $row['product_id'];
    $batches = $model->getBatchesByProductId($productId);
    
    foreach ($batches as $batch) {
        if ($batch['status'] === 'Expiring Soon') {
            if (!in_array($productId, $expiringProducts)) {
                $expiringProducts[] = $productId;
                $expiringSoonCount++;
            }
            break; // Only count the product once
        }
    }
}

$totalProducts = count($overview);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory View - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacistinventory.css">
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
        <a href="admininventory.php" class="nav-btn">Products</a>
        <button class="nav-btn active">Inventory</button>
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
        <h2 class="section-title">Inventory Overview</h2>

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
                    <p class="overview-desc">Within 30 days</p>
                </div>
            </div>
            
            <div class="overview-card total">
                <div class="overview-icon">📦</div>
                <div class="overview-content">
                    <h3>Total Products</h3>
                    <p class="overview-number"><?= $totalProducts ?></p>
                    <p class="overview-desc">In inventory</p>
                </div>
            </div>
        </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Medicine</th>
                <th>Total Quantity</th>
                <th>Earliest Expiry</th>
                <th>Batches</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="inventoryTableBody">
                <?php foreach($overview as $item): ?>
                <tr data-product="<?= htmlspecialchars($item['product']) ?>">
                <?php $img = product_image_for($item['product']); ?>
                <td><img src="<?= $img ?>" alt="<?= htmlspecialchars($item['product']) ?>" class="prod-img"></td>
                    <td><?= htmlspecialchars($item['product']) ?></td>
                    <td class="quantity-cell">
                        <span class="total-quantity"><?= (int)$item['total_quantity'] ?></span>
                        <?php if((int)$item['total_quantity'] <= 5): ?>
                            <span class="stock-warning critical">Critical</span>
                        <?php elseif((int)$item['total_quantity'] <= 15): ?>
                            <span class="stock-warning low">Low</span>
                        <?php endif; ?>
                    </td>
                    
                    <td class="earliest-expiry"><?= $item['earliest_exp'] ? htmlspecialchars($item['earliest_exp']) : '-' ?></td>
                    <td class="batches-count"><?= (int)$item['batches_count'] ?> batch<?= (int)$item['batches_count'] > 1 ? 'es' : '' ?></td>
                    <td>
                        <button class="btn-batches" onclick="viewBatches('<?= htmlspecialchars($item['product']) ?>')">View Batches</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</main>

    <!-- Batch Details Modal -->
    <div id="batchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Batch Details</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="batchDetails"></div>
            </div>
        </div>
    </div>

<script>
        const productNameToId = <?= json_encode($productNameToId) ?>;

        async function viewBatches(productName) {
            const modal = document.getElementById('batchModal');
            const modalTitle = document.getElementById('modalTitle');
            const batchDetails = document.getElementById('batchDetails');
            
            modalTitle.textContent = `Batch Details - ${productName}`;
            const productId = productNameToId[productName];
            let rows = [];
            if (productId) {
                try {
                    const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}`);
                    const data = await res.json();
                    rows = data.data || [];
                } catch (e) { console.error(e); }
            }

            if (rows.length > 0) {
                let html = `
                    <div class="batch-summary">
                        <div class="summary-card">
                            <h4>Total Quantity</h4>
                            <span class="total-qty">${rows.reduce((sum, b) => sum + Number(b.quantity || 0), 0)}</span>
                        </div>
                        <div class="summary-card">
                            <h4>Total Batches</h4>
                            <span class="total-batches">${rows.length}</span>
                        </div>
                    </div>
                    
                    <div class="batches-table-container">
                        <table class="batches-table">
                            <thead>
                                <tr>
                                    <th>Batch #</th>
                                    <th>Quantity</th>
                                    <th>Manufacturing Date</th>
                                    <th>Expiry Date</th>
                                    <th>Supplier</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                rows.forEach((batch, index) => {
                    const expDate = new Date(batch.exp);
                    const today = new Date();
                    const thirtyDaysFromNow = new Date();
                    thirtyDaysFromNow.setDate(today.getDate() + 30);
                    
                    let status = batch.status || 'Good';
                    let statusClass = 'status-good';
                    
                    if (expDate < today) {
                        status = 'Expired';
                        statusClass = 'status-expired';
                    } else if (expDate <= thirtyDaysFromNow) {
                        status = 'Expiring Soon';
                        statusClass = 'status-warning';
                    }
                    
                    html += `
                        <tr>
                            <td>${batch.batch_number}</td>
                            <td>${batch.quantity}</td>
                            <td>${batch.mfd}</td>
                            <td>${batch.exp}</td>
                            <td>${batch.supplier}</td>
                            <td><span class="status-badge ${statusClass}">${status}</span></td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                batchDetails.innerHTML = html;
            } else {
                batchDetails.innerHTML = '<p>No batch data available for this product.</p>';
            }
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('batchModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('batchModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
</script>
</body>
</html>
