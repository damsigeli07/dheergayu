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
    <title>Inventory - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/pharmacistinventory.css">
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
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
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
                    <th>Reduce</th>
                    <th>Earliest Expiry</th>
                    <th>Batches</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach($inventoryData as $item): ?>
                <tr>
                    <?php 
                        $prod = array_filter($products, fn($p)=>$p['name']==$item['product']);
                $prod = array_values($prod)[0];
            ?>
                <td><img src="<?= $prod['image'] ?>" alt="<?= $prod['name'] ?>" class="prod-img"></td>
                    <td><?= $item['product'] ?></td>
                    <td class="quantity-cell">
                        <span class="total-quantity"><?= $item['total_quantity'] ?></span>
                        <?php if($item['total_quantity'] <= 5): ?>
                            <span class="stock-warning critical">Critical</span>
                        <?php elseif($item['total_quantity'] <= 15): ?>
                            <span class="stock-warning low">Low</span>
                        <?php endif; ?>
                    </td>
                    <td class="reduce-cell">
                        <button class="btn-reduce-stock" onclick="reduceStock('<?= $item['product'] ?>')" title="Reduce Stock for Order">−</button>
                    </td>
                    <td><?= $item['earliest_exp'] ?></td>
                    <td><?= count($item['batches']) ?> batch<?= count($item['batches']) > 1 ? 'es' : '' ?></td>
                    <td>
                        <button class="btn-add-batch" onclick="addBatch('<?= $item['product'] ?>')">Add Batch</button>
                        <button class="btn-batches" onclick="viewBatches('<?= $item['product'] ?>')">View Batches</button>
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
        // Sample batch data (in real app, this would come from database)
        const batchData = {
            <?php foreach($inventoryData as $item): ?>
            "<?= $item['product'] ?>": [
                <?php foreach($item['batches'] as $batch): ?>
                {
                    quantity: <?= $batch['quantity'] ?>,
                    mfd: "<?= $batch['mfd'] ?>",
                    exp: "<?= $batch['exp'] ?>",
                    batch_number: "<?= isset($batch['batch_number']) ? $batch['batch_number'] : 'N/A' ?>",
                    supplier: "<?= isset($batch['supplier']) ? $batch['supplier'] : 'N/A' ?>"
                },
                <?php endforeach; ?>
            ],
            <?php endforeach; ?>
        };

        function addBatch(productName) {
            // Redirect to add batch page with pre-selected product
            window.location.href = `../admin/adminaddbatch.php?product=${encodeURIComponent(productName)}`;
        }

        function reduceStock(productName) {
            const currentQuantity = document.querySelector(`tr:has(td:contains('${productName}')) .total-quantity`).textContent;
            const reduceAmount = prompt(
                `Reduce Stock for Order\n\nProduct: ${productName}\nCurrent Quantity: ${currentQuantity}\n\nEnter quantity to reduce:`,
                '1'
            );
            
            if (reduceAmount !== null && reduceAmount !== '') {
                const amount = parseInt(reduceAmount);
                if (isNaN(amount) || amount < 0) {
                    alert('❌ Please enter a valid quantity (0 or higher)');
                    return;
                }
                
                if (amount > parseInt(currentQuantity)) {
                    alert('❌ Cannot reduce more than available stock!');
                    return;
                }
                
                if (confirm(`Reduce ${amount} units from ${productName}?\n\nCurrent: ${currentQuantity}\nAfter reduction: ${parseInt(currentQuantity) - amount}`)) {
                    // Update the display
                    const quantityElement = document.querySelector(`tr:has(td:contains('${productName}')) .total-quantity`);
                    const newQuantity = parseInt(currentQuantity) - amount;
                    quantityElement.textContent = newQuantity;
                    
                    // Update stock warnings
                    const row = quantityElement.closest('tr');
                    const warningSpan = row.querySelector('.stock-warning');
                    if (warningSpan) {
                        warningSpan.remove();
                    }
                    
                    if (newQuantity <= 5) {
                        quantityElement.insertAdjacentHTML('afterend', '<span class="stock-warning critical">Critical</span>');
                    } else if (newQuantity <= 15) {
                        quantityElement.insertAdjacentHTML('afterend', '<span class="stock-warning low">Low</span>');
                    }
                    
                    alert(`✅ Stock reduced successfully!\n${productName}: ${currentQuantity} → ${newQuantity}`);
                    console.log(`Stock reduced: ${productName} - ${amount} units (${currentQuantity} → ${newQuantity})`);
                }
            }
        }

        function viewBatches(productName) {
            const modal = document.getElementById('batchModal');
            const modalTitle = document.getElementById('modalTitle');
            const batchDetails = document.getElementById('batchDetails');
            
            modalTitle.textContent = `Batch Details - ${productName}`;
            
            if (batchData[productName]) {
                let html = `
                    <div class="batch-summary">
                        <div class="summary-card">
                            <h4>Total Quantity</h4>
                            <span class="total-qty">${batchData[productName].reduce((sum, batch) => sum + batch.quantity, 0)}</span>
                        </div>
                        <div class="summary-card">
                            <h4>Total Batches</h4>
                            <span class="total-batches">${batchData[productName].length}</span>
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
                
                batchData[productName].forEach((batch, index) => {
                    const expDate = new Date(batch.exp);
                    const today = new Date();
                    const thirtyDaysFromNow = new Date();
                    thirtyDaysFromNow.setDate(today.getDate() + 30);
                    
                    let status = 'Good';
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
                                <button class="btn-edit-batch" onclick="editBatch('${productName}', '${batch.batch_number}')">Edit</button>
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

        function editBatch(productName, batchNumber) {
            // Find the batch data
            const batch = batchData[productName].find(b => b.batch_number === batchNumber);
            
            if (batch) {
                const newQuantity = prompt(
                    `Edit Batch: ${batchNumber}\n\nProduct: ${productName}\nCurrent quantity: ${batch.quantity}\n\nEnter new quantity:`,
                    batch.quantity
                );
                
                if (newQuantity !== null && newQuantity !== '') {
                    const qty = parseInt(newQuantity);
                    if (isNaN(qty) || qty < 0) {
                        alert('❌ Please enter a valid quantity (0 or higher)');
                        return;
                    }
                    
                    if (confirm(`Update batch ${batchNumber}?\n\nProduct: ${productName}\nOld quantity: ${batch.quantity}\nNew quantity: ${qty}`)) {
                        // Update the batch data
                        batch.quantity = qty;
                        
                        // Refresh the modal to show updated data
                        viewBatches(productName);
                        
                        alert(`✅ Batch ${batchNumber} updated successfully!\nNew quantity: ${qty}`);
                        console.log(`Batch ${batchNumber} updated: ${productName} - ${qty} units`);
                    }
                }
            }
        }

        function deleteBatch(productName, batchNumber) {
            // Find the batch data
            const batch = batchData[productName].find(b => b.batch_number === batchNumber);
            
            if (batch) {
                const confirmMessage = `Delete Batch: ${batchNumber}\n\nProduct: ${productName}\nQuantity: ${batch.quantity}\nManufacturing Date: ${batch.mfd}\nExpiry Date: ${batch.exp}\nSupplier: ${batch.supplier}\n\n⚠️ This action cannot be undone!\n\nAre you sure you want to delete this batch?`;
                
                if (confirm(confirmMessage)) {
                    // Remove the batch from the data
                    const batchIndex = batchData[productName].findIndex(b => b.batch_number === batchNumber);
                    if (batchIndex > -1) {
                        batchData[productName].splice(batchIndex, 1);
                        
                        // If no batches left for this product, remove the product
                        if (batchData[productName].length === 0) {
                            delete batchData[productName];
                        }
                        
                        // Refresh the modal to show updated data
                        if (batchData[productName] && batchData[productName].length > 0) {
                            viewBatches(productName);
                        } else {
                            closeModal();
                        }
                        
                        alert(`✅ Batch ${batchNumber} deleted successfully!`);
                        console.log(`Batch ${batchNumber} deleted: ${productName}`);
                    }
                }
            }
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