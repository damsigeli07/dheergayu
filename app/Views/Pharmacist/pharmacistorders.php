<?php
// Fetch consultation forms from database
require_once __DIR__ . '/../../Models/ConsultationFormModel.php';

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');
if ($db->connect_error) {
    $consultations = [];
} else {
    $consultationModel = new ConsultationFormModel($db);
    $consultations = $consultationModel->getAllConsultationForms();
    if (!is_array($consultations)) $consultations = [];
}

// Get product prices and list of admin products
$productPrices = [];
$adminProducts = [];
$productsQuery = @$db->query("SELECT product_id, name, price FROM products WHERE COALESCE(product_type, 'admin') = 'admin' ORDER BY name");
if ($productsQuery) {
    while ($product = $productsQuery->fetch_assoc()) {
        $productPrices[$product['name']] = [
            'id' => $product['product_id'],
            'price' => (float)$product['price']
        ];
        $adminProducts[] = [
            'id' => $product['product_id'],
            'name' => $product['name'],
            'price' => (float)$product['price']
        ];
    }
    $productsQuery->free();
}

// Fetch dispatch statuses (consultation_dispatches table)
$dispatchStatuses = [];
$dispatchQuery = @$db->query("SELECT consultation_id, status FROM consultation_dispatches");
if ($dispatchQuery && $dispatchQuery->num_rows >= 0) {
    while ($row = $dispatchQuery->fetch_assoc()) {
        $dispatchStatuses[(int)$row['consultation_id']] = $row['status'];
    }
    if (is_object($dispatchQuery)) $dispatchQuery->free();
}
if (!$db->connect_error) $db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Orders</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacistorders.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="pharmacisthome.php" class="nav-btn">Home</a>
            <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
            <button class="nav-btn active">Orders</button>
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

    <!-- Main Content -->
    <main class="main-content">
        <h2 class="section-title">Consultation Orders</h2>

        <?php
        $pendingConsultations = [];
        $dispatchedConsultations = [];
        foreach ($consultations as $c) {
            $isD = isset($dispatchStatuses[$c['id']]) && $dispatchStatuses[$c['id']] === 'Dispatched';
            if ($isD) $dispatchedConsultations[] = $c;
            else $pendingConsultations[] = $c;
        }
        ?>

        <div class="orders-tabs">
            <button type="button" class="orders-tab active" data-tab="pending">Pending Orders (<?= count($pendingConsultations) ?>)</button>
            <button type="button" class="orders-tab" data-tab="dispatched">Dispatched Orders (<?= count($dispatchedConsultations) ?>)</button>
        </div>

        <div id="pending-orders-section" class="orders-section">
            <h3 class="orders-subtitle" style="font-size: 1.1rem; color: #555; margin-bottom: 1rem;">Pending — to be dispensed</h3>
            <div id="pending-orders-container" class="orders-container">
                <?php if (!empty($pendingConsultations)): ?>
                    <?php foreach ($pendingConsultations as $consultation): ?>
                        <?php include __DIR__ . '/_order_card.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <div class="empty-state-text">No pending orders.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="dispatched-orders-section" class="orders-section" style="display: none;">
            <h3 class="orders-subtitle" style="font-size: 1.1rem; color: #555; margin-bottom: 1rem;">Dispatched — stock already deducted</h3>
            <div id="dispatched-orders-container" class="orders-container">
                <?php if (!empty($dispatchedConsultations)): ?>
                    <?php foreach ($dispatchedConsultations as $consultation): ?>
                        <?php include __DIR__ . '/_order_card.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">✅</div>
                        <div class="empty-state-text">No dispatched orders yet.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal for Receipt -->
    <div id="receiptModal" class="modal">
        <div class="modal-content">
            <div class="receipt-actions">
                <button class="print-receipt-btn" onclick="printReceipt()">🖨️ Print Receipt</button>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="receiptContent" class="receipt-content">
                <div class="receipt-header">
                    <div class="receipt-logo">DHEERGAYU</div>
                    <div class="receipt-subtitle">AYURVEDIC MANAGEMENT CENTER</div>
                    <div class="receipt-divider"></div>
                </div>
                <div class="receipt-info">
                    <div class="receipt-line">
                        <span class="receipt-label">Consultation ID:</span>
                        <span class="receipt-value" id="receiptConsultationId"></span>
                    </div>
                    <div class="receipt-line">
                        <span class="receipt-label">Date:</span>
                        <span class="receipt-value" id="receiptDate"></span>
                    </div>
                </div>
                <div class="receipt-divider"></div>
                <table id="receiptTable" class="receipt-table">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="receipt-divider"></div>
                <div class="receipt-total">
                    <div class="receipt-total-line">
                        <span class="receipt-total-label">TOTAL:</span>
                        <span class="receipt-total-amount">Rs. <span id="totalAmount">0.00</span></span>
                    </div>
                </div>
                <div class="receipt-footer">
                    <div class="receipt-thankyou">Thank you for your visit!</div>
                    <div class="receipt-divider"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Product prices from database
        const productPrices = <?= json_encode($productPrices) ?>;
        
        // Admin products from database (hardcoded for receipts)
        const adminProducts = <?= json_encode($adminProducts) ?>;
        
        // Consultation data from database
        const consultations = <?= json_encode($consultations) ?>;

        function calculateTotal(consultationId) {
            // Find the consultation by ID (from consultationforms table)
            const consultation = consultations.find(c => c.id == consultationId);
            if (!consultation) {
                alert('Consultation not found');
                return;
            }

            // Use prescribed products from consultationforms.personal_products
            let prescribedItems = [];
            try {
                prescribedItems = typeof consultation.personal_products === 'string'
                    ? JSON.parse(consultation.personal_products || '[]')
                    : (consultation.personal_products || []);
            } catch (e) {
                prescribedItems = [];
            }
            if (!Array.isArray(prescribedItems)) prescribedItems = [];

            const tbody = document.querySelector("#receiptTable tbody");
            tbody.innerHTML = "";
            let total = 0;

            prescribedItems.forEach((item) => {
                const productName = (item.product || item.name || '').trim();
                const quantity = parseInt(item.qty, 10) || 1;
                if (!productName) return;
                const priceInfo = productPrices[productName];
                const unitPrice = priceInfo ? priceInfo.price : 0;
                const amount = quantity * unitPrice;
                total += amount;
                
                const row = `<tr>
                                <td>${productName}</td>
                                <td>${quantity}</td>
                                <td>Rs. ${unitPrice.toFixed(2)}</td>
                                <td>Rs. ${amount.toFixed(2)}</td>
                             </tr>`;
                tbody.innerHTML += row;
            });

            document.getElementById("receiptConsultationId").innerText = "#" + consultationId;
            document.getElementById("totalAmount").innerText = total.toFixed(2);
            
            // Set current date
            const today = new Date();
            const dateStr = today.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById("receiptDate").innerText = dateStr;
            
            document.getElementById("receiptModal").style.display = "block";
        }
        
        function printReceipt() {
            const receiptContent = document.getElementById("receiptContent").innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Receipt - Consultation ${document.getElementById("receiptConsultationId").innerText}</title>
                        <style>
                            @media print {
                                body { margin: 0; padding: 20px; }
                                .print-receipt-btn, .close { display: none !important; }
                            }
                            body {
                                font-family: 'Courier New', monospace;
                                max-width: 300px;
                                margin: 0 auto;
                                padding: 20px;
                                background: white;
                                color: #000;
                            }
                            .receipt-header {
                                text-align: center;
                                margin-bottom: 20px;
                            }
                            .receipt-logo {
                                font-size: 24px;
                                font-weight: bold;
                                letter-spacing: 2px;
                                margin-bottom: 5px;
                            }
                            .receipt-subtitle {
                                font-size: 12px;
                                color: #333;
                                margin-bottom: 15px;
                            }
                            .receipt-divider {
                                border-top: 1px dashed #333;
                                margin: 15px 0;
                            }
                            .receipt-info {
                                margin-bottom: 15px;
                            }
                            .receipt-line {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 5px;
                                font-size: 12px;
                            }
                            .receipt-label {
                                font-weight: bold;
                            }
                            .receipt-value {
                                color: #333;
                            }
                            .receipt-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 15px 0;
                                font-size: 11px;
                            }
                            .receipt-table thead {
                                border-bottom: 2px solid #000;
                            }
                            .receipt-table th {
                                text-align: left;
                                padding: 8px 4px;
                                font-weight: bold;
                                background: #f5f5f5;
                            }
                            .receipt-table td {
                                padding: 6px 4px;
                                border-bottom: 1px dotted #ccc;
                            }
                            .receipt-total {
                                margin-top: 15px;
                            }
                            .receipt-total-line {
                                display: flex;
                                justify-content: space-between;
                                font-size: 14px;
                                font-weight: bold;
                                padding: 10px 0;
                                border-top: 2px solid #000;
                            }
                            .receipt-footer {
                                margin-top: 20px;
                                text-align: center;
                            }
                            .receipt-thankyou {
                                font-size: 12px;
                                font-style: italic;
                                margin-bottom: 10px;
                            }
                        </style>
                    </head>
                    <body>
                        ${receiptContent}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
            }, 250);
        }

        function closeModal() {
            document.getElementById("receiptModal").style.display = "none";
        }

        async function toggleDispatch(consultationId, isDispatched) {
            var card = document.querySelector('.order-card[data-consultation-id="' + consultationId + '"]');
            if (!card) return;
            var checkbox = card.querySelector('.dispense-status');
            checkbox.disabled = true;
            try {
                var formData = new FormData();
                formData.append('consultation_id', consultationId);
                formData.append('dispatched', isDispatched ? '1' : '0');

                var response = await fetch('/dheergayu/app/Controllers/pharmacist_dispatch.php', { method: 'POST', body: formData });
                var result = await response.json();

                if (!result.success) {
                    alert(result.message || 'Failed to update dispatch status');
                    checkbox.checked = !isDispatched;
                } else {
                    var pendingContainer = document.getElementById('pending-orders-container');
                    var dispatchedContainer = document.getElementById('dispatched-orders-container');
                    var pendingEmpty = pendingContainer.querySelector('.empty-state');
                    var dispatchedEmpty = dispatchedContainer.querySelector('.empty-state');

                    if (isDispatched) {
                        alert('Order marked as dispatched. Stock has been reduced from inventory.');
                        if (dispatchedEmpty) dispatchedEmpty.remove();
                        card.classList.add('row-dispatched');
                        card.querySelector('.order-status-badge').className = 'order-status-badge dispatched';
                        card.querySelector('.order-status-badge').textContent = 'Dispatched';
                        if (!card.querySelector('.dispatch-note')) {
                            var note = document.createElement('div');
                            note.className = 'dispatch-note';
                            note.textContent = 'Stock deducted from inventory.';
                            card.querySelector('.dispatch-section').appendChild(note);
                        }
                        checkbox.checked = true;
                        card.parentNode.removeChild(card);
                        dispatchedContainer.appendChild(card);
                    } else {
                        if (pendingEmpty) pendingEmpty.remove();
                        card.classList.remove('row-dispatched');
                        card.querySelector('.order-status-badge').className = 'order-status-badge pending';
                        card.querySelector('.order-status-badge').textContent = 'Pending';
                        var note = card.querySelector('.dispatch-note');
                        if (note) note.remove();
                        checkbox.checked = false;
                        dispatchedContainer.removeChild(card);
                        pendingContainer.appendChild(card);
                    }

                    if (pendingContainer.querySelectorAll('.order-card').length === 0) {
                        var empty = document.createElement('div');
                        empty.className = 'empty-state';
                        empty.innerHTML = '<div class="empty-state-icon">📋</div><div class="empty-state-text">No pending orders.</div>';
                        pendingContainer.appendChild(empty);
                    }
                    if (dispatchedContainer.querySelectorAll('.order-card').length === 0) {
                        var empty2 = document.createElement('div');
                        empty2.className = 'empty-state';
                        empty2.innerHTML = '<div class="empty-state-icon">✅</div><div class="empty-state-text">No dispatched orders yet.</div>';
                        dispatchedContainer.appendChild(empty2);
                    }

                    var pendingCount = pendingContainer.querySelectorAll('.order-card').length;
                    var dispatchedCount = dispatchedContainer.querySelectorAll('.order-card').length;
                    document.querySelector('.orders-tab[data-tab="pending"]').textContent = 'Pending Orders (' + pendingCount + ')';
                    document.querySelector('.orders-tab[data-tab="dispatched"]').textContent = 'Dispatched Orders (' + dispatchedCount + ')';
                }
            } catch (error) {
                console.error('Dispatch update failed', error);
                alert('Failed to update dispatch status');
                checkbox.checked = !isDispatched;
            }
            checkbox.disabled = false;
        }

        document.querySelectorAll('.orders-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var tab = this.getAttribute('data-tab');
                document.querySelectorAll('.orders-tab').forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                document.getElementById('pending-orders-section').style.display = tab === 'pending' ? 'block' : 'none';
                document.getElementById('dispatched-orders-section').style.display = tab === 'dispatched' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
