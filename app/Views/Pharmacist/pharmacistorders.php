<?php
// Fetch consultation forms from database
require_once __DIR__ . '/../../Models/ConsultationFormModel.php';

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');
$consultationModel = new ConsultationFormModel($db);

// Get all consultation forms
$consultations = $consultationModel->getAllConsultationForms();

// Get product prices
$productPrices = [];
$productsQuery = $db->query("SELECT product_id, name, price FROM products");
while ($product = $productsQuery->fetch_assoc()) {
    $productPrices[$product['name']] = [
        'id' => $product['product_id'],
        'price' => (float)$product['price']
    ];
}

// Fetch dispatch statuses
$dispatchStatuses = [];
$dispatchQuery = $db->query("SELECT consultation_id, status FROM consultation_dispatches");
if ($dispatchQuery) {
    while ($row = $dispatchQuery->fetch_assoc()) {
        $dispatchStatuses[$row['consultation_id']] = $row['status'];
    }
}
$db->close();
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

        <div class="orders-container">
            <?php if (!empty($consultations)): ?>
                <?php foreach ($consultations as $consultation): ?>
                    <?php 
                    // Parse personal_products JSON
                    $personalProducts = json_decode($consultation['personal_products'] ?? '[]', true);
                    if (!is_array($personalProducts)) {
                        $personalProducts = [];
                    }
                    ?>
                    <?php $isDispatched = isset($dispatchStatuses[$consultation['id']]) && $dispatchStatuses[$consultation['id']] === 'Dispatched'; ?>
                    <div class="order-card <?= $isDispatched ? 'row-dispatched' : '' ?>">
                        <div class="order-header">
                            <div class="order-id-section">
                                <span class="order-id-label">Consultation ID</span>
                                <span class="order-id-value">#<?= htmlspecialchars($consultation['id']) ?></span>
                                <span class="patient-name"><?= htmlspecialchars($consultation['first_name'] . ' ' . $consultation['last_name']) ?></span>
                            </div>
                            <span class="order-status-badge <?= $isDispatched ? 'dispatched' : 'pending' ?>">
                                <?= $isDispatched ? 'Dispatched' : 'Pending' ?>
                            </span>
                        </div>
                        
                        <div class="order-body">
                            <div class="medicines-section">
                                <div class="medicines-label">Medicines Prescribed</div>
                                <div class="medicines-list">
                                    <?php if (!empty($personalProducts)): ?>
                                        <?php foreach ($personalProducts as $product): ?>
                                            <div class="medicine-card">
                                                <span class="medicine-name"><?= htmlspecialchars($product['product'] ?? '') ?></span>
                                                <span class="medicine-qty">x<?= htmlspecialchars($product['qty'] ?? '0') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-medicines">No medicines prescribed</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="order-actions-section">
                                <div class="total-section">
                                    <div class="total-label">Total Amount</div>
                                    <button class="total-button" onclick="calculateTotal('<?= $consultation['id'] ?>')">
                                        View Total
                                    </button>
                                </div>
                                
                                <div class="dispatch-section">
                                    <label class="dispatch-label">
                                        <input type="checkbox"
                                               class="dispense-status"
                                               <?= $isDispatched ? 'checked' : '' ?>
                                               onchange="toggleDispatch('<?= $consultation['id'] ?>', this.checked)">
                                        <span>Mark as Dispatched</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-text">No consultation orders found.</div>
                </div>
            <?php endif; ?>
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
        
        // Consultation data from database
        const consultations = <?= json_encode($consultations) ?>;

        function calculateTotal(consultationId) {
            // Find the consultation by ID
            const consultation = consultations.find(c => c.id == consultationId);
            if (!consultation) {
                alert('Consultation not found');
                return;
            }

            // Parse personal products
            let personalProducts = [];
            try {
                personalProducts = JSON.parse(consultation.personal_products || '[]');
            } catch (e) {
                console.error('Error parsing personal products:', e);
                personalProducts = [];
            }

            const tbody = document.querySelector("#receiptTable tbody");
            tbody.innerHTML = "";
            let total = 0;

            personalProducts.forEach(product => {
                const productName = product.product || '';
                const quantity = parseInt(product.qty) || 0;
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
            try {
                const formData = new FormData();
                formData.append('consultation_id', consultationId);
                formData.append('dispatched', isDispatched ? '1' : '0');

                const response = await fetch('/dheergayu/app/Controllers/pharmacist_dispatch.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (!result.success) {
                    alert(result.message || 'Failed to update dispatch status');
                }
            } catch (error) {
                console.error('Dispatch update failed', error);
                alert('Failed to update dispatch status');
            }
        }
    </script>
</body>
</html>
