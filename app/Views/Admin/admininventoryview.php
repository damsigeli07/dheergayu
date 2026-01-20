<?php
require_once __DIR__ . '/../../../core/bootloader.php';

use App\Models\BatchModel;

$model = new BatchModel();

// Database connection
$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

// Function to get product image
function get_product_image($image_path, $name) {
    if (!empty($image_path)) {
        return '/dheergayu/public/assets/images/Admin/' . str_replace('images/', '', $image_path);
    }
    return '/dheergayu/public/assets/images/Admin/dheergayu.png';
}

// Get admin products from products table
$adminProductsQuery = "SELECT product_id, name, price, description, image 
                       FROM products 
                       WHERE COALESCE(product_type, 'admin') = 'admin' 
                       ORDER BY name";
$adminProductsResult = $db->query($adminProductsQuery);

$adminProducts = [];
$adminProductNameToId = [];
if ($adminProductsResult && $adminProductsResult->num_rows > 0) {
    while ($row = $adminProductsResult->fetch_assoc()) {
        $productId = (int)$row['product_id'];
        $adminProductNameToId[$row['name']] = $productId;
        
        // Get batch information for this product
        $batches = $model->getBatchesByProductId($productId);
        $totalQuantity = 0;
        $earliestExp = null;
        $batchesCount = count($batches);
        
        foreach ($batches as $batch) {
            $totalQuantity += (int)$batch['quantity'];
            if ($batch['exp']) {
                if (!$earliestExp || $batch['exp'] < $earliestExp) {
                    $earliestExp = $batch['exp'];
                }
            }
        }
        
        $adminProducts[] = [
            'id' => $productId,
            'name' => $row['name'],
            'price' => $row['price'],
            'description' => $row['description'],
            'image' => get_product_image($row['image'], $row['name']),
            'total_quantity' => $totalQuantity,
            'earliest_exp' => $earliestExp,
            'batches_count' => $batchesCount
        ];
    }
}

// Get patient products from patient_products table
$patientProductsQuery = "SELECT product_id, name, price, description, image 
                          FROM patient_products 
                          ORDER BY name";
$patientProductsResult = $db->query($patientProductsQuery);

$patientProducts = [];
$patientProductNameToId = [];
if ($patientProductsResult && $patientProductsResult->num_rows > 0) {
    while ($row = $patientProductsResult->fetch_assoc()) {
        $productId = (int)$row['product_id'];
        $patientProductNameToId[$row['name']] = $productId;
        
        // Get batch information for this product (batches table uses product_id from products table)
        // For patient products, we might need to check if batches are linked differently
        // For now, we'll check if there are any batches with matching product names or IDs
        $batches = $model->getBatchesByProductId($productId);
        $totalQuantity = 0;
        $earliestExp = null;
        $batchesCount = count($batches);
        
        foreach ($batches as $batch) {
            $totalQuantity += (int)$batch['quantity'];
            if ($batch['exp']) {
                if (!$earliestExp || $batch['exp'] < $earliestExp) {
                    $earliestExp = $batch['exp'];
                }
            }
        }
        
        $patientProducts[] = [
            'id' => $productId,
            'name' => $row['name'],
            'price' => $row['price'],
            'description' => $row['description'],
            'image' => get_product_image($row['image'], $row['name']),
            'total_quantity' => $totalQuantity,
            'earliest_exp' => $earliestExp,
            'batches_count' => $batchesCount
        ];
    }
}

// Calculate statistics
$criticalStockCount = 0;
$lowStockCount = 0;
$expiringSoonCount = 0;
$today = new DateTime();
$thirtyDaysFromNow = (new DateTime())->add(new DateInterval('P30D'));

$allProducts = array_merge($adminProducts, $patientProducts);
foreach ($allProducts as $item) {
    $qty = (int)$item['total_quantity'];
    if ($qty <= 5) { 
        $criticalStockCount++; 
    } elseif ($qty <= 15) { 
        $lowStockCount++; 
    }
    
    if ($item['earliest_exp']) {
        $expDate = new DateTime($item['earliest_exp']);
        if ($expDate <= $thirtyDaysFromNow && $expDate >= $today) {
                $expiringSoonCount++;
        }
    }
}

$totalProducts = count($allProducts);
$db->close();
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
    <style>
        .inventory-section {
            margin-bottom: 3rem;
        }
        .section-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
        }
        .section-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d2d2d;
            margin-bottom: 0.5rem;
        }
        .section-header p {
            color: #666;
            font-size: 0.95rem;
        }
    </style>
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

    <!-- Admin Products Inventory Table -->
    <div class="inventory-section">
        <div class="section-header">
            <h3>Admin Products Inventory</h3>
            <p>Inventory management for admin products</p>
        </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th>Image</th>
                    <th>Product</th>
                <th>Total Quantity</th>
                <th>Earliest Expiry</th>
                <th>Batches</th>
                <th>Actions</th>
            </tr>
        </thead>
            <tbody>
                <?php if (empty($adminProducts)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #666;">No admin products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($adminProducts as $item): ?>
                        <tr data-product="<?= htmlspecialchars($item['name']) ?>" data-type="admin">
                            <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="prod-img"></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                    <td class="quantity-cell">
                                <span class="total-quantity"><?= $item['total_quantity'] ?></span>
                                <?php if($item['total_quantity'] <= 5): ?>
                            <span class="stock-warning critical">Critical</span>
                                <?php elseif($item['total_quantity'] <= 15): ?>
                            <span class="stock-warning low">Low</span>
                        <?php endif; ?>
                    </td>
                            <td class="earliest-expiry"><?= $item['earliest_exp'] ? htmlspecialchars($item['earliest_exp']) : '-' ?></td>
                            <td class="batches-count"><?= $item['batches_count'] ?> batch<?= $item['batches_count'] > 1 ? 'es' : '' ?></td>
                            <td>
                                <button class="btn-batches" onclick="viewBatches(<?= $item['id'] ?>, 'admin')">View Batches</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Patient Products Inventory Table -->
    <div class="inventory-section">
        <div class="section-header">
            <h3>Patient Products Inventory</h3>
            <p>Inventory management for patient products</p>
        </div>
        
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Total Quantity</th>
                    <th>Earliest Expiry</th>
                    <th>Batches</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patientProducts)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #666;">No patient products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($patientProducts as $item): ?>
                        <tr data-product="<?= htmlspecialchars($item['name']) ?>" data-type="patient">
                            <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="prod-img"></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td class="quantity-cell">
                                <span class="total-quantity"><?= $item['total_quantity'] ?></span>
                                <?php if($item['total_quantity'] <= 5): ?>
                                    <span class="stock-warning critical">Critical</span>
                                <?php elseif($item['total_quantity'] <= 15): ?>
                                    <span class="stock-warning low">Low</span>
                                <?php endif; ?>
                            </td>
                    <td class="earliest-expiry"><?= $item['earliest_exp'] ? htmlspecialchars($item['earliest_exp']) : '-' ?></td>
                            <td class="batches-count"><?= $item['batches_count'] ?> batch<?= $item['batches_count'] > 1 ? 'es' : '' ?></td>
                    <td>
                                <button class="btn-batches" onclick="viewBatches(<?= $item['id'] ?>, 'patient')">View Batches</button>
                </td>
            </tr>
            <?php endforeach; ?>
                <?php endif; ?>
        </tbody>
    </table>
    </div>

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
    const adminProductNameToId = <?= json_encode($adminProductNameToId) ?>;
    const patientProductNameToId = <?= json_encode($patientProductNameToId) ?>;

    async function viewBatches(productId, productType) {
            const modal = document.getElementById('batchModal');
            const modalTitle = document.getElementById('modalTitle');
            const batchDetails = document.getElementById('batchDetails');
        
        // Get product name
        let productName = '';
        if (productType === 'admin') {
            productName = Object.keys(adminProductNameToId).find(key => adminProductNameToId[key] === productId) || 'Product';
        } else {
            productName = Object.keys(patientProductNameToId).find(key => patientProductNameToId[key] === productId) || 'Product';
        }
            
            modalTitle.textContent = `Batch Details - ${productName}`;
        
            let rows = [];
            if (productId) {
                try {
                    const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}`);
                    const data = await res.json();
                    rows = data.data || [];
            } catch (e) { 
                console.error(e); 
            }
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
