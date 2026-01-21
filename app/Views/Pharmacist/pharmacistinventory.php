<?php
require_once __DIR__ . '/../../../core/bootloader.php';

use App\Models\BatchModel;

$model = new BatchModel();

// Database connection
$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

// Function to get product image
function get_product_image($image_path, $name, $type = 'admin') {
    if (!empty($image_path)) {
        $basePath = $type === 'admin' ? '/dheergayu/public/assets/images/Admin/' : '/dheergayu/public/assets/images/Admin/';
        return $basePath . str_replace('images/', '', $image_path);
    }
    return '/dheergayu/public/assets/images/Pharmacist/dheergayu.png';
}

// Get admin products from products table
$adminProductsQuery = "SELECT product_id, name, price, description, image 
                       FROM products 
                       WHERE COALESCE(product_type, 'admin') = 'admin' 
                       ORDER BY name";
$adminProductsResult = $db->query($adminProductsQuery);

$adminProducts = [];
$adminProductNameToId = [];
$allProductRows = []; // For the add batch form

if ($adminProductsResult && $adminProductsResult->num_rows > 0) {
    while ($row = $adminProductsResult->fetch_assoc()) {
        $productId = (int)$row['product_id'];
        $adminProductNameToId[$row['name']] = $productId;
        $allProductRows[] = ['id' => $productId, 'name' => $row['name']];
        
        // Get batch information for this product (admin products)
        $batches = $model->getBatchesByProductId($productId, 'admin');
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
            'image' => get_product_image($row['image'], $row['name'], 'admin'),
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
        $allProductRows[] = ['id' => $productId, 'name' => $row['name']];
        
        // Get batch information for this product (patient products)
        $batches = $model->getBatchesByProductId($productId, 'patient');
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
            'image' => get_product_image($row['image'], $row['name'], 'patient'),
            'total_quantity' => $totalQuantity,
            'earliest_exp' => $earliestExp,
            'batches_count' => $batchesCount
        ];
    }
}

// Get suppliers from database
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/SupplierModel.php';
$supplierModel = new SupplierModel($conn);
$suppliers = $supplierModel->getAllSuppliers();

// Calculate statistics from database
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
    <title>Inventory - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacistinventory.css">
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/addbatch.css">
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
<header class="header">
    <div class="header-top">
        <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
        <h1 class="header-title">Dheergayu</h1>
    </div>
    
    <nav class="navigation">
        <a href="pharmacisthome.php" class="nav-btn">Home</a>
        <button class="nav-btn active">Inventory</button>
        <a href="pharmacistorders.php" class="nav-btn">Orders</a>
        <a href="pharmacistreports.php" class="nav-btn">Reports</a>
        <a href="pharmacistrequest.php" class="nav-btn">Request</a>
    </nav>
    
    <div class="user-section">
        <div class="user-icon" id="user-icon">👤</div>
        <span class="user-role">Pharmacist</span>
        <div class="user-dropdown" id="user-dropdown">
            <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
        <h2 class="section-title">Stock Management</h2>

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
                    <th>Medicine</th>
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
                        <tr data-product="<?= htmlspecialchars($item['name']) ?>" data-type="admin" data-product-id="<?= $item['id'] ?>">
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
                                <button class="btn-add-batch" data-product-name="<?= htmlspecialchars($item['name']) ?>" data-product-id="<?= $item['id'] ?>" data-product-type="admin">Add Batch</button>
                                <button class="btn-batches" data-product-name="<?= htmlspecialchars($item['name']) ?>" data-product-id="<?= $item['id'] ?>" data-product-type="admin">View Batches</button>
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
                <th>Medicine</th>
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
                        <tr data-product="<?= htmlspecialchars($item['name']) ?>" data-type="patient" data-product-id="<?= $item['id'] ?>">
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
                                <button class="btn-add-batch" data-product-name="<?= htmlspecialchars($item['name']) ?>" data-product-id="<?= $item['id'] ?>" data-product-type="patient">Add Batch</button>
                                <button class="btn-batches" data-product-name="<?= htmlspecialchars($item['name']) ?>" data-product-id="<?= $item['id'] ?>" data-product-type="patient">View Batches</button>
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

    <!-- Edit Batch Modal -->
    <div id="editBatchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Batch</h3>
                <span class="close" onclick="closeEditBatchModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="add-batch-form">
                <form id="editBatchForm">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <input type="hidden" id="edit_product_name_hidden">
                    <div class="form-group">
                        <label for="edit_product_name">Product</label>
                        <input type="text" id="edit_product_name" class="form-input" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_batch_number">Batch Number</label>
                        <input type="text" name="batch_number" id="edit_batch_number" class="form-input" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_quantity">Quantity *</label>
                        <input type="number" name="quantity" id="edit_quantity" class="form-input" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_mfd">Manufacturing Date *</label>
                        <input type="date" name="mfd" id="edit_mfd" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_exp">Expiry Date *</label>
                        <input type="date" name="exp" id="edit_exp" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_supplier">Supplier *</label>
                        <select name="supplier" id="edit_supplier" class="form-input" required>
                            <option value="">Select Supplier</option>
                            <?php foreach($suppliers as $supplier): ?>
                            <option value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>">
                                <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <input type="text" id="edit_status" class="form-input" readonly style="background-color: #f8f9fa; color: #6c757d;">
                        <small class="form-help">Status is automatically calculated based on expiry date</small>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Save Changes</button>
                        <button type="button" class="btn-cancel" onclick="closeEditBatchModal()">Cancel</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Batch Modal -->
    <div id="addBatchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Batch</h3>
                <span class="close" onclick="closeAddBatchModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="add-batch-form">
                <form id="addBatchForm">
                    <div class="form-group">
                        <label for="product">Product *</label>
                        <select name="product" id="product" class="form-input" required>
                            <option value="">Select Product</option>
                        <?php foreach($allProductRows as $p): ?>
                        <option value="<?= htmlspecialchars($p['name']) ?>" data-product-id="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="batch_number">Batch Number *</label>
                        <input type="text" name="batch_number" id="batch_number" class="form-input" required placeholder="e.g., ASM001, BLT002">
                        <small class="form-help">Unique identifier for this batch</small>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" name="quantity" id="quantity" class="form-input" min="1" required placeholder="Enter quantity">
                        <small class="form-help">Number of units in this batch</small>
                    </div>

                    <div class="form-group">
                        <label for="mfd">Manufacturing Date *</label>
                        <input type="date" name="mfd" id="mfd" class="form-input" required max="">
                        <small class="form-help">Date when this batch was manufactured (cannot be in the future)</small>
                    </div>

                    <div class="form-group">
                        <label for="exp">Expiry Date *</label>
                        <input type="date" name="exp" id="exp" class="form-input" required min="">
                        <small class="form-help">Date when this batch expires (cannot be in the past)</small>
                    </div>

                    <div class="form-group">
                        <label for="supplier">Supplier *</label>
                        <select name="supplier" id="supplier" class="form-input" required>
                            <option value="">Select Supplier</option>
                            <?php foreach($suppliers as $supplier): ?>
                            <option value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>">
                                <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-help">Select the supplier for this batch</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Add Batch</button>
                        <button type="button" class="btn-cancel" onclick="closeAddBatchModal()">Cancel</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

<script>
    const adminProductNameToId = <?= json_encode($adminProductNameToId) ?>;
    const patientProductNameToId = <?= json_encode($patientProductNameToId) ?>;
    const allProductNameToId = { ...adminProductNameToId, ...patientProductNameToId };

    // Make functions globally accessible
    window.addBatch = function(productName, productId) {
        console.log('addBatch called with:', productName, productId);
        if (typeof productName === 'undefined' || typeof productId === 'undefined') {
            console.error('Invalid parameters:', productName, productId);
            alert('Error: Invalid product information');
            return;
        }
        if (typeof window.openAddBatchModal === 'function') {
            window.openAddBatchModal(productName, productId);
        } else {
            console.error('openAddBatchModal function not found');
            alert('Error: Add batch function not available');
        }
    };

    window.openAddBatchModal = function(productName, productId) {
        console.log('openAddBatchModal called with:', productName, productId);
        const modal = document.getElementById('addBatchModal');
        if (!modal) {
            console.error('Add batch modal not found');
            alert('Error: Add batch modal not found');
            return;
        }
        const productSelect = document.getElementById('product');
        const mfdInput = document.getElementById('mfd');
        const expInput = document.getElementById('exp');
        
        if (!productSelect || !mfdInput || !expInput) {
            console.error('Form elements not found', {productSelect, mfdInput, expInput});
            alert('Error: Form elements not found');
            return;
        }
        
        // Set date constraints
        const today = new Date().toISOString().split('T')[0];
        
        // Manufacturing date: cannot be in the future (max = today)
        mfdInput.max = today;
        mfdInput.value = '';
        
        // Expiry date: cannot be in the past (min = today)
        expInput.min = today;
        expInput.value = '';
        
        if (productName) {
            for (const option of productSelect.options) {
                if (option.value === productName) {
                    option.selected = true;
                    break;
                }
            }
        }
        // Suggest next batch number (if function exists)
        if (typeof suggestNextBatchNumber === 'function') {
            try {
                suggestNextBatchNumber();
            } catch (e) {
                console.error('Error suggesting batch number:', e);
            }
        }
        modal.style.display = 'block';
    };

    window.closeAddBatchModal = function() {
        document.getElementById('addBatchModal').style.display = 'none';
    };

    window.viewBatches = async function(productName, productId) {
        console.log('viewBatches called with:', productName, productId);
        if (typeof productName === 'undefined' || typeof productId === 'undefined') {
            console.error('Invalid parameters:', productName, productId);
            alert('Error: Invalid product information');
            return;
        }
        
        const modal = document.getElementById('batchModal');
        const modalTitle = document.getElementById('modalTitle');
        const batchDetails = document.getElementById('batchDetails');
        
        if (!modal || !modalTitle || !batchDetails) {
            console.error('Modal elements not found', {modal, modalTitle, batchDetails});
            alert('Error: Modal elements not found');
            return;
        }
        
        modalTitle.textContent = `Batch Details - ${productName}`;
        batchDetails.innerHTML = '<p>Loading...</p>';
        modal.style.display = 'block';
        
        let rows = [];
        if (productId) {
            try {
                // Determine product_source from the row
                const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                const productSource = row && row.getAttribute('data-type') === 'patient' ? 'patient' : 'admin';
                const url = `/dheergayu/public/api/batches/by-product?product_id=${productId}&product_source=${productSource}`;
                console.log('Fetching batches from:', url);
                const res = await fetch(url);
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                const data = await res.json();
                console.log('Batch data received:', data);
                rows = data.data || [];
            } catch (e) { 
                console.error('Error fetching batches:', e); 
                batchDetails.innerHTML = `<p style="color: red;">Error loading batches: ${e.message}</p>`;
                return;
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
                                    <th>Actions</th>
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
                    
                    // Escape values for use in HTML attributes
                    const escapedProductName = String(productName || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const escapedBatchNumber = String(batch.batch_number || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    
                    // Ensure productId is a valid number
                    const productIdNum = parseInt(productId, 10);
                    if (isNaN(productIdNum) || productIdNum <= 0) {
                        console.error('Invalid productId in viewBatches:', productId);
                        return; // Skip this batch if productId is invalid
                    }
                    
                    html += `
                        <tr>
                            <td>${batch.batch_number}</td>
                            <td>${batch.quantity}</td>
                            <td>${batch.mfd}</td>
                            <td>${batch.exp}</td>
                            <td>${batch.supplier}</td>
                            <td><span class="status-badge ${statusClass}">${status}</span></td>
                            <td>
                            <button class="btn-edit-batch" data-product-name="${escapedProductName}" data-product-id="${productIdNum}" data-batch-number="${escapedBatchNumber}">Edit</button>
                            <button class="btn-delete-batch" data-product-name="${escapedProductName}" data-product-id="${productIdNum}" data-batch-number="${escapedBatchNumber}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                batchDetails.innerHTML = html;
                
                // Attach event listeners to edit and delete buttons
                batchDetails.querySelectorAll('.btn-edit-batch').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const productName = this.getAttribute('data-product-name');
                        const productId = this.getAttribute('data-product-id');
                        const batchNumber = this.getAttribute('data-batch-number');
                        if (window.openEditBatchModal) {
                            window.openEditBatchModal(productName, productId, batchNumber);
                        } else {
                            console.error('openEditBatchModal function not found');
                        }
                    });
                });
                
                batchDetails.querySelectorAll('.btn-delete-batch').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const productName = this.getAttribute('data-product-name');
                        const productId = this.getAttribute('data-product-id');
                        const batchNumber = this.getAttribute('data-batch-number');
                        if (window.deleteBatch) {
                            window.deleteBatch(productName, productId, batchNumber);
                        } else {
                            console.error('deleteBatch function not found');
                        }
                    });
                });
            } else {
                batchDetails.innerHTML = '<p>No batch data available for this product.</p>';
            }
    };

    window.closeModal = function() {
        document.getElementById('batchModal').style.display = 'none';
    };

    window.openEditBatchModal = async function(productName, productId, batchNumber) {
            let batch = null;
            try {
                // Determine product_source from the row
                const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                const productSource = row && row.getAttribute('data-type') === 'patient' ? 'patient' : 'admin';
                const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}&product_source=${productSource}`);
                const data = await res.json();
                const rows = data.data || [];
                batch = rows.find(b => String(b.batch_number) === String(batchNumber));
            } catch (e) { console.error(e); }
            if (!batch) { alert('Batch not found'); return; }
            document.getElementById('edit_product_id').value = productId;
            document.getElementById('edit_product_name').value = productName;
            document.getElementById('edit_product_name_hidden').value = productName;
            document.getElementById('edit_batch_number').value = batch.batch_number;
            document.getElementById('edit_quantity').value = batch.quantity;
            document.getElementById('edit_mfd').value = batch.mfd;
            document.getElementById('edit_exp').value = batch.exp;
            
            // Set supplier - trim and find matching option
            const supplierSelect = document.getElementById('edit_supplier');
            const supplierValue = (batch.supplier || '').trim();
            supplierSelect.value = ''; // Reset first
            
            // Try exact match first
            for (let option of supplierSelect.options) {
                if (option.value.trim() === supplierValue) {
                    option.selected = true;
                    break;
                }
            }
            
            // If no exact match found, try case-insensitive match
            if (supplierSelect.value === '') {
                for (let option of supplierSelect.options) {
                    if (option.value.trim().toLowerCase() === supplierValue.toLowerCase()) {
                        option.selected = true;
                        break;
                    }
                }
            }
            
            // If still no match, set the value directly (might add new supplier)
            if (supplierSelect.value === '' && supplierValue) {
                supplierSelect.value = supplierValue;
            }
            
            // Auto-calculate status based on expiry date
            const autoStatus = calculateStatus(batch.exp);
            document.getElementById('edit_status').value = autoStatus;
            
            document.getElementById('editBatchModal').style.display = 'block';
        }

    window.closeEditBatchModal = function() {
        document.getElementById('editBatchModal').style.display = 'none';
    };

    window.deleteBatch = async function(productName, productId, batchNumber) {
            // Validate parameters
            if (!productId || !batchNumber) {
                console.error('Invalid parameters:', { productName, productId, batchNumber });
                alert('❌ Error: Missing product ID or batch number');
                return;
            }
            
            // Convert productId to integer
            const productIdInt = parseInt(productId, 10);
            if (isNaN(productIdInt) || productIdInt <= 0) {
                console.error('Invalid product ID:', productId);
                alert('❌ Error: Invalid product ID');
                return;
            }
            
            // Ensure batchNumber is a string and not empty
            const batchNumberStr = String(batchNumber || '').trim();
            if (!batchNumberStr) {
                console.error('Invalid batch number:', batchNumber);
                alert('❌ Error: Invalid batch number');
                return;
            }
            
            if (!confirm(`Delete Batch: ${batchNumberStr}\n\nProduct: ${productName}\n\nThis action cannot be undone. Proceed?`)) return;
            
            console.log('Deleting batch:', { productName, productId: productIdInt, batchNumber: batchNumberStr });
            
            const form = new FormData();
            form.append('product_id', productIdInt);
            form.append('batch_number', batchNumberStr);
            
            // Debug: Log FormData contents
            console.log('FormData contents:');
            for (let [key, value] of form.entries()) {
                console.log(key, '=', value, '(type:', typeof value, ')');
            }
            
            try {
                const res = await fetch('/dheergayu/public/api/batches/delete', { method: 'POST', body: form });
                
                if (!res.ok) {
                    const errorText = await res.text();
                    console.error('API error response:', errorText);
                    alert(`❌ Delete failed: ${res.status} ${res.statusText}`);
                    return;
                }
                
                const data = await res.json();
                console.log('Delete API response:', data);
                
                if (data.success) {
                    alert('✅ Batch deleted successfully');
                    viewBatches(productName, productIdInt);
                    await updateMainTableQuantity(productName, productIdInt);
                } else {
                    const errorMsg = data.error || data.message || 'Unknown error';
                    alert(`❌ Delete failed: ${errorMsg}`);
                }
            } catch (error) {
                console.error('Error deleting batch:', error);
                alert(`❌ Error: ${error.message}`);
            }
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('batchModal');
            const addModal = document.getElementById('addBatchModal');
        const editModal = document.getElementById('editBatchModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
}

        // Calculate status based on expiry date
        function calculateStatus(expDate) {
            const today = new Date();
            const thirtyDaysFromNow = new Date();
            thirtyDaysFromNow.setDate(today.getDate() + 30);
            
            const expDateObj = new Date(expDate);
            
            if (expDateObj < today) {
                return 'Expired';
            } else if (expDateObj <= thirtyDaysFromNow) {
                return 'Expiring Soon';
            }
            return 'Good';
        }

        // Update main table quantity after edit/delete
    window.updateMainTableQuantity = async function(productName, productId) {
            try {
                // Determine product_source from the row
                const rowById = document.querySelector(`tr[data-product-id="${productId}"]`);
                const productSource = rowById && rowById.getAttribute('data-type') === 'patient' ? 'patient' : 'admin';
                const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}&product_source=${productSource}`);
                const data = await res.json();
                const rows = data.data || [];
                
                let totalQty = 0;
                let earliestExp = null;
                
                rows.forEach(b => {
                    totalQty += Number(b.quantity || 0);
                    if (b.exp) {
                        if (!earliestExp || new Date(b.exp) < new Date(earliestExp)) {
                            earliestExp = b.exp;
                        }
                    }
                });
                
                // Update the main table row
                const rowByName = document.querySelector(`tr[data-product="${productName}"]`);
                if (rowByName) {
                    rowByName.querySelector('.total-quantity').textContent = totalQty;
                    rowByName.querySelector('.earliest-expiry').textContent = earliestExp || '-';
                    rowByName.querySelector('.batches-count').textContent = `${rows.length} batch${rows.length > 1 ? 'es' : ''}`;
                    
                    // Update stock warning
                    const quantityCell = rowByName.querySelector('.quantity-cell');
                    const warningSpan = quantityCell.querySelector('.stock-warning');
                    if (warningSpan) warningSpan.remove();
                    
                    if (totalQty <= 5) {
                        quantityCell.innerHTML += '<span class="stock-warning critical">Critical</span>';
                    } else if (totalQty <= 15) {
                        quantityCell.innerHTML += '<span class="stock-warning low">Low</span>';
                    }
                }
            } catch (e) { console.error(e); }
        }

        // Handle add-batch form submit via API
        function attachAddBatchFormHandler() {
            const addBatchForm = document.getElementById('addBatchForm');
            if (addBatchForm) {
                console.log('Add batch form found, attaching submit handler');
                addBatchForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                console.log('Add batch form submitted');
                
                const formEl = e.target;
                const productSelect = document.getElementById('product');
                if (!productSelect) {
                    alert('❌ Product select not found');
                    return;
                }
                
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const productName = selectedOption ? selectedOption.value : '';
                const productId = selectedOption ? selectedOption.getAttribute('data-product-id') : allProductNameToId[productName];
            
                if (!productId) { 
                    alert('❌ Invalid product. Please select a product.'); 
                    return; 
                }
                
                // Validate required fields
                if (!formEl.batch_number.value.trim()) {
                    alert('❌ Batch number is required');
                    return;
                }
                if (!formEl.quantity.value || parseInt(formEl.quantity.value) <= 0) {
                    alert('❌ Valid quantity is required');
                    return;
                }
                if (!formEl.mfd.value) {
                    alert('❌ Manufacturing date is required');
                    return;
                }
                if (!formEl.exp.value) {
                    alert('❌ Expiry date is required');
                    return;
                }
                if (!formEl.supplier.value) {
                    alert('❌ Supplier is required');
                    return;
                }
                
                const expDate = formEl.exp.value;
                const autoStatus = calculateStatus(expDate);
                // Determine product_source from selected product
                const isPatientProduct = patientProductNameToId.hasOwnProperty(productName);
                const productSource = isPatientProduct ? 'patient' : 'admin';
                
                const batchNumberValue = formEl.batch_number.value.trim();
                console.log('Batch number from form:', batchNumberValue, 'Type:', typeof batchNumberValue, 'Length:', batchNumberValue.length);
                
                if (!batchNumberValue) {
                    alert('❌ Batch number is required');
                    return;
                }
                
                const payload = new FormData();
                payload.append('product_id', productId);
                payload.append('product_source', productSource);
                payload.append('batch_number', batchNumberValue);
                payload.append('quantity', formEl.quantity.value);
                payload.append('mfd', formEl.mfd.value);
                payload.append('exp', expDate);
                payload.append('supplier', formEl.supplier.value);
                payload.append('status', autoStatus);
                
                // Debug: Log FormData contents
                console.log('Submitting batch FormData:');
                for (let [key, value] of payload.entries()) {
                    console.log(key, '=', value, '(type:', typeof value, ')');
                }
                
                try {
                    const res = await fetch('/dheergayu/public/api/batches/create', { method: 'POST', body: payload });
                    
                    // Get response as text first to check if it's JSON
                    const responseText = await res.text();
                    console.log('Raw API response:', responseText);
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('Failed to parse JSON response:', parseError);
                        console.error('Response was:', responseText);
                        alert('❌ Server returned invalid response. Check console for details.');
                        return;
                    }
                    
                    console.log('Parsed API response:', data);
                    
                    if (data.success) {
                        alert('✅ Batch added successfully');
                        closeAddBatchModal();
                        await updateMainTableQuantity(productName, productId);
                        // Reload page to refresh all data
                        location.reload();
                    } else {
                        const errorMsg = data.error || data.message || 'Unknown error';
                        alert(`❌ Failed to add batch: ${errorMsg}`);
                    }
                } catch (error) {
                    console.error('Error submitting batch:', error);
                    alert(`❌ Error: ${error.message}`);
                }
                });
            } else {
                console.error('Add batch form not found');
            }
        }
        
        // Try to attach immediately (if DOM is ready) or wait for DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attachAddBatchFormHandler);
        } else {
            attachAddBatchFormHandler();
        }

        // Batch number auto-suggest
        function getPrefixForProduct(name) {
            const n = name.toLowerCase();
            if (n.includes('asamodagam')) return 'ASM';
            if (n.includes('paspanguwa') || n.includes('pasapanguwa') || n.includes('pasanguwa')) return 'PSP';
            if (n.includes('siddhalepa') || n.includes('sidhalepa') || n.includes('siddalepa') || n.includes('siddphalepa')) return 'SDP';
            if (n.includes('dashamoolarishta')) return 'DMR';
            if (n.includes('kothalahimbutu')) return 'KHC';
            if (n.includes('neem') && n.includes('oil')) return 'NEO';
            if (n.includes('nirgundi') && n.includes('oil')) return 'NRO';
            if (n.includes('pinda') && n.includes('thailaya')) return 'PTL';
            if (n.includes('bala') && n.includes('thailaya')) return 'BLT';
        if (n.includes('ashwagandha')) return 'ASH';
        if (n.includes('arawindasawaya')) return 'ARS';
        if (n.includes('chandanasawaya')) return 'CDS';
        if (n.includes('kanakasawaya')) return 'KNS';
        if (n.includes('abayarishtaya')) return 'ABY';
        if (n.includes('amurtharishtaya')) return 'AMR';
        if (n.includes('arjunarishtaya')) return 'ARJ';
        if (n.includes('samahan')) return 'SMH';
            return (name.replace(/[^A-Za-z]/g, '').toUpperCase().slice(0,3) || 'BAT');
        }

        async function suggestNextBatchNumber() {
            const productName = document.getElementById('product').value;
            if (!productName) return;
        const selectedOption = document.getElementById('product').options[document.getElementById('product').selectedIndex];
        const productId = selectedOption ? selectedOption.getAttribute('data-product-id') : allProductNameToId[productName];
        if (!productId) return;
        
            const prefix = getPrefixForProduct(productName);
            let maxNum = 0;
            try {
                // Determine product_source
                const isPatientProduct = patientProductNameToId.hasOwnProperty(productName);
                const productSource = isPatientProduct ? 'patient' : 'admin';
                const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}&product_source=${productSource}`);
                if (!res.ok) {
                    console.error('Failed to fetch batches:', res.status, res.statusText);
                    return;
                }
                const data = await res.json();
                const rows = data.data || [];
                rows.forEach(b => {
                    const m = String(b.batch_number || '').match(/^(\D+)(\d{1,})$/);
                    if (m) {
                        const existingPrefix = m[1].toUpperCase();
                        const num = parseInt(m[2], 10) || 0;
                        if (existingPrefix === prefix && num > maxNum) maxNum = num;
                    }
                });
            } catch (e) { console.error('Error in suggestNextBatchNumber:', e); }
            const nextNum = String(maxNum + 1).padStart(3, '0');
            document.getElementById('batch_number').value = `${prefix}${nextNum}`;
        }

        // Recompute suggestion when product changes in add form
        document.getElementById('product').addEventListener('change', suggestNextBatchNumber);
        
        // Add date validation
        document.getElementById('mfd').addEventListener('change', function() {
            const mfdDate = this.value;
            const expInput = document.getElementById('exp');
            if (mfdDate) {
                expInput.min = mfdDate;
                if (expInput.value && expInput.value < mfdDate) {
                    expInput.value = '';
                }
            }
        });
        
        document.getElementById('exp').addEventListener('change', function() {
            const expDate = this.value;
            const mfdInput = document.getElementById('mfd');
            if (expDate && mfdInput.value && expDate < mfdInput.value) {
                alert('❌ Expiry date cannot be before manufacturing date');
                this.value = '';
            }
        });
        
        // Quantity validation
        document.getElementById('quantity').addEventListener('input', function() {
            const quantity = parseInt(this.value);
            if (quantity < 1) {
                this.setCustomValidity('Quantity must be at least 1');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Auto-update status when expiry date changes in edit form
        document.getElementById('edit_exp').addEventListener('change', function() {
            const expDate = this.value;
            const autoStatus = calculateStatus(expDate);
            document.getElementById('edit_status').value = autoStatus;
        });

        document.getElementById('editBatchForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const productName = document.getElementById('edit_product_name_hidden').value;
        const productId = document.getElementById('edit_product_id').value;
            const expDate = document.getElementById('edit_exp').value;
            const autoStatus = calculateStatus(expDate);
            
            const payload = new FormData();
        payload.append('product_id', productId);
            payload.append('batch_number', document.getElementById('edit_batch_number').value);
            payload.append('quantity', document.getElementById('edit_quantity').value);
            payload.append('mfd', document.getElementById('edit_mfd').value);
            payload.append('exp', expDate);
            payload.append('supplier', document.getElementById('edit_supplier').value);
            payload.append('status', autoStatus);
            const res = await fetch('/dheergayu/public/api/batches/update', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                alert('✅ Batch updated');
                closeEditBatchModal();
            viewBatches(productName, productId);
            await updateMainTableQuantity(productName, productId);
            } else {
                alert('❌ Update failed');
            }
        });

    // Add event listeners to buttons using data attributes
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, attaching event listeners...');
        
        // Add Batch buttons
        document.querySelectorAll('.btn-add-batch').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const productName = this.getAttribute('data-product-name');
                const productId = parseInt(this.getAttribute('data-product-id'));
                console.log('Add Batch button clicked:', productName, productId);
                if (window.addBatch && typeof window.addBatch === 'function') {
                    window.addBatch(productName, productId);
                } else {
                    console.error('window.addBatch is not a function');
                    alert('Error: Add batch function not available');
                }
            });
        });
        
        // View Batches buttons
        document.querySelectorAll('.btn-batches').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const productName = this.getAttribute('data-product-name');
                const productId = parseInt(this.getAttribute('data-product-id'));
                console.log('View Batches button clicked:', productName, productId);
                if (window.viewBatches && typeof window.viewBatches === 'function') {
                    window.viewBatches(productName, productId);
                } else {
                    console.error('window.viewBatches is not a function');
                    alert('Error: View batches function not available');
                }
            });
        });
        
        console.log('Event listeners attached. Found', document.querySelectorAll('.btn-add-batch').length, 'Add Batch buttons and', document.querySelectorAll('.btn-batches').length, 'View Batches buttons');
    });
</script>
</body>
</html>
