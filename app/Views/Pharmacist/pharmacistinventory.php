<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
require_once __DIR__ . '/../../includes/auth_pharmacist.php';
require_once __DIR__ . '/../../../core/bootloader.php';

use App\Models\BatchModel;

$model = new BatchModel();

// Database connection
$db = $conn;

// Function to get product image
function get_product_image($image_path) {
    if (!empty($image_path)) {
        return '/dheergayu/public/assets/images/Admin/' . str_replace('images/', '', $image_path);
    }
    return '/dheergayu/public/assets/images/Pharmacist/dheergayu.png';
}

$todayStr = date('Y-m-d');

// Get all products from unified products table
$allProductsQuery = "SELECT product_id, name, price, description, image, product_type
                     FROM products ORDER BY name";
$allProductsResult = $db->query($allProductsQuery);

$regularProducts = [];
$treatmentProducts = [];

if ($allProductsResult && $allProductsResult->num_rows > 0) {
    while ($row = $allProductsResult->fetch_assoc()) {
        $productId = (int)$row['product_id'];
        $productType = $row['product_type'];

        $sourceFilter = ($productType === 'treatment') ? 'treatment' : 'patient';
        $batches = $model->getBatchesByProductId($productId, $sourceFilter);
        $totalQuantity = 0;
        $expiredQuantity = 0;
        $earliestExp = null;
        $batchesCount = count($batches);

        foreach ($batches as $batch) {
            if (empty($batch['exp']) || $batch['exp'] >= $todayStr) {
                $totalQuantity += (int)$batch['quantity'];
            } else {
                $expiredQuantity += (int)$batch['quantity'];
            }
            if ($batch['exp']) {
                if (!$earliestExp || $batch['exp'] < $earliestExp) {
                    $earliestExp = $batch['exp'];
                }
            }
        }

        $entry = [
            'id' => $productId,
            'name' => $row['name'],
            'price' => $row['price'],
            'description' => $row['description'],
            'image' => get_product_image($row['image']),
            'product_type' => $productType,
            'total_quantity' => $totalQuantity,
            'expired_quantity' => $expiredQuantity,
            'earliest_exp' => $earliestExp,
            'batches_count' => $batchesCount
        ];
        if ($productType === 'treatment') {
            $treatmentProducts[] = $entry;
        } else {
            $regularProducts[] = $entry;
        }
    }
}

// Calculate statistics from database
$criticalStockCount = 0;
$lowStockCount = 0;
$expiringSoonCount = 0;
$today = new DateTime();
$thirtyDaysFromNow = (new DateTime())->add(new DateInterval('P30D'));

$allProductsForStats = array_merge($regularProducts, $treatmentProducts);
foreach ($allProductsForStats as $item) {
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

$totalProducts = count($allProductsForStats);
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
    <style>
        .inv-tab-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e0e0e0;
        }
        .inv-tab-btn {
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
        .inv-tab-btn.active {
            background: white;
            color: #5b8a6e;
            border-bottom: 2px solid white;
            border-top: 2px solid #5b8a6e;
        }
        .inv-tab-btn:hover:not(.active) { background: #e5e5e5; }
        .inv-tab-count {
            background: #5b8a6e;
            color: white;
            font-size: 0.7rem;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 4px;
        }
        .expired-qty-hint {
            font-size: 0.75rem;
            color: #dc3545;
        }
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
        .inventory-search-wrap {
            margin-bottom: 1rem;
        }
        .inventory-search-input {
            width: 100%;
            max-width: 420px;
            padding: 0.65rem 0.9rem;
            border: 1px solid #d8d8d8;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        .inventory-search-input:focus {
            outline: none;
            border-color: #5b8a6e;
            box-shadow: 0 0 0 3px rgba(91, 138, 110, 0.15);
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
            <a href="pharmacistshoporders.php" class="nav-btn">Shop Orders</a>
        <a href="pharmacistrequest.php" class="nav-btn">Request</a>
        <a href="pharmacisttreatmentprep.php" class="nav-btn">Treatment Prep</a>
    </nav>
    
    <div class="user-section">
        <div class="user-icon" id="user-icon">👤</div>
        <span class="user-role">Pharmacist</span>
        <div class="user-dropdown" id="user-dropdown">
            <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
            <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
    <h2 class="section-title">Stock Management</h2>

    <div class="inventory-search-wrap">
        <input
            type="text"
            id="inventorySearchInput"
            class="inventory-search-input"
            placeholder="Search medicines and treatment oils by name..."
            aria-label="Search inventory products"
        >
    </div>

    <!-- Tab Navigation -->
    <div class="inv-tab-nav">
        <button class="inv-tab-btn active" onclick="switchInvTab('medicines', this)">Medicines <span class="inv-tab-count"><?= count($regularProducts) ?></span></button>
        <button class="inv-tab-btn" onclick="switchInvTab('treatment-oils', this)">Treatment Oils <span class="inv-tab-count"><?= count($treatmentProducts) ?></span></button>
    </div>

    <!-- Medicines Tab -->
    <div id="inv-tab-medicines" class="inv-tab-section">
        <div class="inventory-section">
            <?= renderInventoryTable($regularProducts) ?>
        </div>
    </div>

    <!-- Treatment Oils Tab -->
    <div id="inv-tab-treatment-oils" class="inv-tab-section" style="display:none;">
        <div class="inventory-section">
            <?= renderInventoryTable($treatmentProducts) ?>
        </div>
    </div>

</main>

<?php
function renderInventoryTable(array $items): string {
    $todayStr = date('Y-m-d');
    $html = '<table class="inventory-table"><thead><tr>
        <th>Image</th><th>Product</th><th>Total Qty (Bottles)</th><th>Earliest Expiry</th><th>Batches</th><th>Actions</th>
    </tr></thead><tbody>';
    if (empty($items)) {
        $html .= '<tr><td colspan="6" style="text-align:center;padding:2rem;color:#666;">No products found.</td></tr>';
    } else {
        foreach ($items as $item) {
            $badge = '';
            $expiredHint = '';
            $hasExpired = $item['expired_quantity'] > 0 || ($item['earliest_exp'] && $item['earliest_exp'] < $todayStr);
            $allExpired  = $item['total_quantity'] == 0 && $hasExpired;

            if ($allExpired) {
                $badge = '<span class="stock-warning critical">All Expired</span>';
            } else {
                if ($hasExpired) {
                    $expiredHint = ' <span class="stock-warning critical" style="font-size:0.72rem;padding:2px 7px;">HAS EXPIRED</span>';
                }
                if ($item['total_quantity'] <= 5) {
                    $badge = '<span class="stock-warning critical">Critical</span>';
                } elseif ($item['total_quantity'] <= 15) {
                    $badge = '<span class="stock-warning low">Low</span>';
                }
            }
            $exp = $item['earliest_exp'] ? htmlspecialchars($item['earliest_exp']) : '-';
            $batchLabel = $item['batches_count'] . ' batch' . ($item['batches_count'] != 1 ? 'es' : '');
            $name = htmlspecialchars($item['name']);
            $img = htmlspecialchars($item['image']);
            $id = $item['id'];
            $type = $item['product_type'];
            $html .= "
            <tr data-product=\"{$name}\" data-type=\"{$type}\" data-product-id=\"{$id}\">
                <td><img src=\"{$img}\" alt=\"{$name}\" class=\"prod-img\"></td>
                <td>{$name}</td>
                <td class=\"quantity-cell\">
                    <span class=\"total-quantity\">{$item['total_quantity']}</span>
                    {$badge}{$expiredHint}
                </td>
                <td class=\"earliest-expiry\">{$exp}</td>
                <td class=\"batches-count\">{$batchLabel}</td>
                <td>
                    <button class=\"btn-batches\" data-product-name=\"{$name}\" data-product-id=\"{$id}\" data-product-type=\"{$type}\">View Batches</button>
                </td>
            </tr>";
        }
    }
    $html .= '</tbody></table>';
    return $html;
}
?>

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

<script>
    function switchInvTab(tab, btn) {
        document.querySelectorAll('.inv-tab-section').forEach(s => s.style.display = 'none');
        document.querySelectorAll('.inv-tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('inv-tab-' + tab).style.display = '';
        btn.classList.add('active');
        applyInventorySearch();
    }

    function applyInventorySearch() {
        const searchInput = document.getElementById('inventorySearchInput');
        const term = (searchInput?.value || '').trim().toLowerCase();
        const rows = document.querySelectorAll('.inventory-table tbody tr');

        rows.forEach((row) => {
            const productName = (row.getAttribute('data-product') || '').toLowerCase();

            // Keep placeholder rows (no data rows) always visible.
            if (!productName) {
                row.style.display = '';
                return;
            }

            row.style.display = productName.includes(term) ? '' : 'none';
        });
    }

    window.viewBatches = async function(productName, productId) {
        if (typeof productName === 'undefined' || typeof productId === 'undefined') {
            alert('Error: Invalid product information');
            return;
        }

        const modal = document.getElementById('batchModal');
        const modalTitle = document.getElementById('modalTitle');
        const batchDetails = document.getElementById('batchDetails');

        if (!modal || !modalTitle || !batchDetails) {
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
                const dataType = row ? row.getAttribute('data-type') : 'admin';
                const productSource = dataType === 'patient' ? 'patient' : dataType === 'treatment' ? 'treatment' : 'admin';
                const url = `/dheergayu/public/api/batches/by-product?product_id=${productId}&product_source=${productSource}`;
                const res = await fetch(url);
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                const data = await res.json();
                rows = data.data || [];
            } catch (e) {
                batchDetails.innerHTML = `<p style="color: red;">Error loading batches: ${e.message}</p>`;
                return;
            }
        }

            if (rows.length > 0) {
                const _t = new Date();
                const todayStr = _t.getFullYear() + '-' + String(_t.getMonth()+1).padStart(2,'0') + '-' + String(_t.getDate()).padStart(2,'0');
                const availableQty = rows.filter(b => !b.exp || b.exp >= todayStr).reduce((sum, b) => sum + Number(b.quantity || 0), 0);
                const expiredBatches = rows.filter(b => b.exp && b.exp < todayStr);
                const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                const productSource = row && row.getAttribute('data-type') === 'treatment' ? 'treatment' : 'patient';

                let html = `
                    <div class="batch-summary">
                        <div class="summary-card">
                            <h4>Available Qty</h4>
                            <span class="total-qty">${availableQty}</span>
                        </div>
                        <div class="summary-card">
                            <h4>Total Batches</h4>
                            <span class="total-batches">${rows.length}</span>
                        </div>
                        ${expiredBatches.length > 0 ? `
                        <div class="summary-card">
                            <h4>Expired Batches</h4>
                            <span style="color:#dc3545;font-weight:700;font-size:2rem;">${expiredBatches.length}</span>
                        </div>` : ''}
                    </div>
                    ${expiredBatches.length > 0 ? `
                    <div style="margin-bottom:1rem;">
                        <button id="btn-remove-expired" style="background:#dc3545;color:#fff;border:none;padding:0.5rem 1.2rem;border-radius:4px;cursor:pointer;font-weight:600;">
                            Remove All Expired (${expiredBatches.length})
                        </button>
                    </div>` : ''}
                    
                    <div class="batches-table-container">
                        <table class="batches-table">
                            <thead>
                                <tr>
                                    <th>Batch #</th>
                                    <th>Quantity</th>
                                    <th>Manufacturing Date</th>
                                    <th>Expiry Date</th>
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
                    
                    const productIdNum = parseInt(productId, 10);
                    if (isNaN(productIdNum) || productIdNum <= 0) {
                        return;
                    }
                    
                    html += `
                        <tr>
                            <td>${batch.batch_number}</td>
                            <td>${batch.quantity}</td>
                            <td>${batch.mfd}</td>
                            <td>${batch.exp}</td>
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

                // Remove Expired button
                const removeExpiredBtn = document.getElementById('btn-remove-expired');
                if (removeExpiredBtn) {
                    removeExpiredBtn.addEventListener('click', async function() {
                        if (!confirm(`Remove all ${expiredBatches.length} expired batch(es) for ${productName}?\n\nThey will be archived to the expired batches record.`)) return;
                        this.disabled = true;
                        this.textContent = 'Removing...';
                        try {
                            const form = new FormData();
                            form.append('product_id', productId);
                            form.append('product_source', productSource);
                            const res = await fetch('/dheergayu/public/api/batches/remove-expired', { method: 'POST', body: form });
                            const data = await res.json();
                            if (data.success) {
                                alert(`✅ Removed ${data.removed} expired batch(es). They have been archived.`);
                                viewBatches(productName, productId);
                                await updateMainTableQuantity(productName, productId);
                            } else {
                                alert('❌ Failed to remove expired batches');
                                this.disabled = false;
                                this.textContent = `Remove All Expired (${expiredBatches.length})`;
                            }
                        } catch (e) {
                            alert('❌ Error: ' + e.message);
                            this.disabled = false;
                            this.textContent = `Remove All Expired (${expiredBatches.length})`;
                        }
                    });
                }

                // Attach event listeners to edit and delete buttons
                batchDetails.querySelectorAll('.btn-edit-batch').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const productName = this.getAttribute('data-product-name');
                        const productId = this.getAttribute('data-product-id');
                        const batchNumber = this.getAttribute('data-batch-number');
                        if (window.openEditBatchModal) {
                            window.openEditBatchModal(productName, productId, batchNumber);
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
                const dataType2 = row ? row.getAttribute('data-type') : 'admin';
                const productSource = dataType2 === 'patient' ? 'patient' : dataType2 === 'treatment' ? 'treatment' : 'admin';
                const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}&product_source=${productSource}`);
                const data = await res.json();
                const rows = data.data || [];
                batch = rows.find(b => String(b.batch_number) === String(batchNumber));
            } catch (e) { }
            if (!batch) { alert('Batch not found'); return; }
            document.getElementById('edit_product_id').value = productId;
            document.getElementById('edit_product_name').value = productName;
            document.getElementById('edit_product_name_hidden').value = productName;
            document.getElementById('edit_batch_number').value = batch.batch_number;
            document.getElementById('edit_quantity').value = batch.quantity;
            document.getElementById('edit_mfd').value = batch.mfd;
            document.getElementById('edit_exp').value = batch.exp;
            
            // Auto-calculate status based on expiry date
            const autoStatus = calculateStatus(batch.exp);
            document.getElementById('edit_status').value = autoStatus;
            
            document.getElementById('editBatchModal').style.display = 'block';
        }

    window.closeEditBatchModal = function() {
        document.getElementById('editBatchModal').style.display = 'none';
    };

    window.deleteBatch = async function(productName, productId, batchNumber) {
            if (!productId || !batchNumber) {
                alert('❌ Error: Missing product ID or batch number');
                return;
            }

            const productIdInt = parseInt(productId, 10);
            if (isNaN(productIdInt) || productIdInt <= 0) {
                alert('❌ Error: Invalid product ID');
                return;
            }

            const batchNumberStr = String(batchNumber || '').trim();
            if (!batchNumberStr) {
                alert('❌ Error: Invalid batch number');
                return;
            }

            if (!confirm(`Delete Batch: ${batchNumberStr}\n\nProduct: ${productName}\n\nThis action cannot be undone. Proceed?`)) return;

            const form = new FormData();
            form.append('product_id', productIdInt);
            form.append('batch_number', batchNumberStr);

            try {
                const res = await fetch('/dheergayu/public/api/batches/delete', { method: 'POST', body: form });

                if (!res.ok) {
                    alert(`❌ Delete failed: ${res.status} ${res.statusText}`);
                    return;
                }

                const data = await res.json();

                if (data.success) {
                    alert('✅ Batch deleted successfully');
                    viewBatches(productName, productIdInt);
                    await updateMainTableQuantity(productName, productIdInt);
                } else {
                    const errorMsg = data.error || data.message || 'Unknown error';
                    alert(`❌ Delete failed: ${errorMsg}`);
                }
            } catch (error) {
                alert(`❌ Error: ${error.message}`);
            }
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('batchModal');
        const editModal = document.getElementById('editBatchModal');
            if (event.target == modal) {
                modal.style.display = 'none';
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
                const dt4 = rowById ? rowById.getAttribute('data-type') : 'admin';
                const productSource = dt4 === 'patient' ? 'patient' : dt4 === 'treatment' ? 'treatment' : 'admin';
                const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}&product_source=${productSource}`);
                const data = await res.json();
                const rows = data.data || [];
                
                let totalQty = 0;
                let expiredQty = 0;
                let earliestExp = null;
                const _td = new Date();
                const todayStr = _td.getFullYear() + '-' + String(_td.getMonth()+1).padStart(2,'0') + '-' + String(_td.getDate()).padStart(2,'0');

                rows.forEach(b => {
                    if (!b.exp || b.exp >= todayStr) {
                        totalQty += Number(b.quantity || 0);
                    } else {
                        expiredQty += Number(b.quantity || 0);
                    }
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
                    
                    // Update expired hint
                    const quantityCell = rowByName.querySelector('.quantity-cell');
                    const existingHint = quantityCell.querySelector('.expired-qty-hint');
                    if (existingHint) existingHint.remove();
                    if (expiredQty > 0) {
                        rowByName.querySelector('.total-quantity').insertAdjacentHTML('afterend', `<span class="expired-qty-hint">(${expiredQty} expired)</span>`);
                    }

                    // Update stock warning
                    const warningSpan = quantityCell.querySelector('.stock-warning');
                    if (warningSpan) warningSpan.remove();

                    if (totalQty === 0 && expiredQty > 0) {
                        quantityCell.innerHTML += '<span class="stock-warning critical">All Expired</span>';
                    } else if (totalQty <= 5) {
                        quantityCell.innerHTML += '<span class="stock-warning critical">Critical</span>';
                    } else if (totalQty <= 15) {
                        quantityCell.innerHTML += '<span class="stock-warning low">Low</span>';
                    }
                }
            } catch (e) { }
        }

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
        const inventorySearchInput = document.getElementById('inventorySearchInput');
        if (inventorySearchInput) {
            inventorySearchInput.addEventListener('input', applyInventorySearch);
        }

        document.querySelectorAll('.btn-batches').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const productName = this.getAttribute('data-product-name');
                const productId = parseInt(this.getAttribute('data-product-id'));
                if (window.viewBatches && typeof window.viewBatches === 'function') {
                    window.viewBatches(productName, productId);
                } else {
                    alert('Error: View batches function not available');
                }
            });
        });

        applyInventorySearch();
    });
</script>
</body>
</html>
