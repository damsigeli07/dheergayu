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

// Stock statistics
$criticalStockCount = 0;
$lowStockCount = 0;
$expiringSoonCount = 0;
$today = new DateTime();
$ninetyDaysFromNow = (new DateTime())->add(new DateInterval('P90D'));
foreach ($overview as $row) {
    $qty = (int)$row['total_quantity'];
    if ($qty <= 5) { $criticalStockCount++; }
    elseif ($qty <= 15) { $lowStockCount++; }
    if (!empty($row['earliest_exp'])) {
        $expDate = new DateTime($row['earliest_exp']);
        if ($expDate <= $ninetyDaysFromNow) { $expiringSoonCount++; }
    }
}
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
</head>
<body>
<header class="header">
    <div class="header-left">
        <nav class="navigation">
            <a href="pharmacisthome.php" class="nav-btn">Home</a>
            <button class="nav-btn active">Inventory</button>
            <a href="pharmacistorders.php" class="nav-btn">Orders</a>
            <a href="pharmacistreports.php" class="nav-btn">Reports</a>
        </nav>
    </div>
    <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
        <h1 class="header-title">Dheergayu</h1>
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Pharmacist</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>
</header>

<main class="main-content">
        <h2 class="section-title">Stock Management</h2>

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
                <?php foreach($overview as $item): ?>
                <tr>
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
                    
                    <td><?= $item['earliest_exp'] ? htmlspecialchars($item['earliest_exp']) : '-' ?></td>
                    <td><?= (int)$item['batches_count'] ?> batch<?= (int)$item['batches_count'] > 1 ? 'es' : '' ?></td>
                    <td>
                        <button class="btn-add-batch" onclick="addBatch('<?= htmlspecialchars($item['product']) ?>')">Add Batch</button>
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
                            <option value="Herbal Supplies Co.">Herbal Supplies Co.</option>
                            <option value="Ayurvedic Traders">Ayurvedic Traders</option>
                            <option value="Natural Extracts Ltd.">Natural Extracts Ltd.</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select name="status" id="edit_status" class="form-input">
                            <option value="Good">Good</option>
                            <option value="Expiring Soon">Expiring Soon</option>
                            <option value="Expired">Expired</option>
                        </select>
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
                            <?php foreach($productRows as $p): ?>
                            <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
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
                        <input type="date" name="mfd" id="mfd" class="form-input" required>
                        <small class="form-help">Date when this batch was manufactured</small>
                    </div>

                    <div class="form-group">
                        <label for="exp">Expiry Date *</label>
                        <input type="date" name="exp" id="exp" class="form-input" required>
                        <small class="form-help">Date when this batch expires</small>
                    </div>

                    <div class="form-group">
                        <label for="supplier">Supplier *</label>
                        <select name="supplier" id="supplier" class="form-input" required>
                            <option value="">Select supplier</option>
                            <option value="Herbal Supplies Co.">Herbal Supplies Co.</option>
                            <option value="Ayurvedic Traders">Ayurvedic Traders</option>
                            <option value="Natural Extracts Ltd.">Natural Extracts Ltd.</option>
                        </select>
                        <small class="form-help">Select the supplier for this batch</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-input">
                            <option value="Good">Good</option>
                            <option value="Expiring Soon">Expiring Soon</option>
                            <option value="Expired">Expired</option>
                        </select>
                        <small class="form-help">Current status of this batch (auto-calculated based on dates)</small>
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
        const productNameToId = <?= json_encode($productNameToId) ?>;

        function addBatch(productName) {
            openAddBatchModal(productName);
        }

        function openAddBatchModal(productName) {
            const modal = document.getElementById('addBatchModal');
            const productSelect = document.getElementById('product');
            // Set default dates
            const mfdInput = document.getElementById('mfd');
            mfdInput.valueAsDate = new Date();
            if (productName) {
                for (const option of productSelect.options) {
                    option.selected = option.value === productName;
                }
            }
            // Suggest next batch number
            suggestNextBatchNumber();
            modal.style.display = 'block';
        }

        function closeAddBatchModal() {
            document.getElementById('addBatchModal').style.display = 'none';
        }

        

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
                    
                    html += `
                        <tr>
                            <td>${batch.batch_number}</td>
                            <td>${batch.quantity}</td>
                            <td>${batch.mfd}</td>
                            <td>${batch.exp}</td>
                            <td>${batch.supplier}</td>
                            <td><span class="status-badge ${statusClass}">${status}</span></td>
                            <td>
                                <button class="btn-edit-batch" onclick="openEditBatchModal('${productName}', '${batch.batch_number}')">Edit</button>
                                <button class="btn-delete-batch" onclick="deleteBatch('${productName}', '${batch.batch_number}')">Delete</button>
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
            } else {
                batchDetails.innerHTML = '<p>No batch data available for this product.</p>';
            }
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('batchModal').style.display = 'none';
        }

        async function openEditBatchModal(productName, batchNumber) {
            const productId = productNameToId[productName];
            let batch = null;
            try {
                const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}`);
                const data = await res.json();
                const rows = data.data || [];
                batch = rows.find(b => String(b.batch_number) === String(batchNumber));
            } catch (e) { console.error(e); }
            if (!batch) { alert('Batch not found'); return; }
            document.getElementById('edit_product_id').value = productId;
            document.getElementById('edit_product_name').value = productName;
            document.getElementById('edit_batch_number').value = batch.batch_number;
            document.getElementById('edit_quantity').value = batch.quantity;
            document.getElementById('edit_mfd').value = batch.mfd;
            document.getElementById('edit_exp').value = batch.exp;
            document.getElementById('edit_supplier').value = batch.supplier;
            document.getElementById('edit_status').value = batch.status || 'Good';
            document.getElementById('editBatchModal').style.display = 'block';
        }

        function closeEditBatchModal() {
            document.getElementById('editBatchModal').style.display = 'none';
        }

        

        async function deleteBatch(productName, batchNumber) {
            const productId = productNameToId[productName];
            if (!confirm(`Delete Batch: ${batchNumber}\n\nProduct: ${productName}\n\nThis action cannot be undone. Proceed?`)) return;
            const form = new FormData();
            form.append('product_id', productId);
            form.append('batch_number', batchNumber);
            const res = await fetch('/dheergayu/public/api/batches/delete', { method: 'POST', body: form });
            const data = await res.json();
            if (data.success) {
                alert('✅ Batch deleted');
                            viewBatches(productName);
                        } else {
                alert('❌ Delete failed');
            }
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('batchModal');
            const addModal = document.getElementById('addBatchModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            const editModal = document.getElementById('editBatchModal');
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
}

        // Handle add-batch form submit via API
        document.getElementById('addBatchForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const productName = document.getElementById('product').value;
            const productId = productNameToId[productName];
            if (!productId) { alert('❌ Invalid product'); return; }
            const formEl = e.target;
            const payload = new FormData();
            payload.append('product_id', productId);
            payload.append('batch_number', formEl.batch_number.value);
            payload.append('quantity', formEl.quantity.value);
            payload.append('mfd', formEl.mfd.value);
            payload.append('exp', formEl.exp.value);
            payload.append('supplier', formEl.supplier.value);
            payload.append('status', formEl.status.value);
            const res = await fetch('/dheergayu/public/api/batches/create', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                alert('✅ Batch added');
                closeAddBatchModal();
                location.reload();
            } else {
                alert('❌ Failed to add batch');
            }
        });

        // =============================
        // Batch number auto-suggest
        // =============================
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
            // fallback: first 3 consonants/letters
            return (name.replace(/[^A-Za-z]/g, '').toUpperCase().slice(0,3) || 'BAT');
        }

        async function suggestNextBatchNumber() {
            const productName = document.getElementById('product').value;
            if (!productName) return;
            const productId = productNameToId[productName];
            const prefix = getPrefixForProduct(productName);
            let maxNum = 0;
            try {
                const res = await fetch(`/dheergayu/public/api/batches/by-product?product_id=${productId}`);
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
            } catch (e) { console.error(e); }
            const nextNum = String(maxNum + 1).padStart(3, '0');
            document.getElementById('batch_number').value = `${prefix}${nextNum}`;
        }

        // Recompute suggestion when product changes in add form
        document.getElementById('product').addEventListener('change', suggestNextBatchNumber);

        document.getElementById('editBatchForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const payload = new FormData();
            payload.append('product_id', document.getElementById('edit_product_id').value);
            payload.append('batch_number', document.getElementById('edit_batch_number').value);
            payload.append('quantity', document.getElementById('edit_quantity').value);
            payload.append('mfd', document.getElementById('edit_mfd').value);
            payload.append('exp', document.getElementById('edit_exp').value);
            payload.append('supplier', document.getElementById('edit_supplier').value);
            payload.append('status', document.getElementById('edit_status').value);
            const res = await fetch('/dheergayu/public/api/batches/update', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                alert('✅ Batch updated');
                closeEditBatchModal();
                const name = document.getElementById('edit_product_name').value;
                viewBatches(name);
            } else {
                alert('❌ Update failed');
            }
        });
</script>
</body>
</html>