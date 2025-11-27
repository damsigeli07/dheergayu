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

        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Consultation ID</th>
                        <th>Patient Name</th>
                        <th>Medicines Prescribed</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
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
                            <tr class="<?= $isDispatched ? 'row-dispatched' : '' ?>">
                                <td><?= htmlspecialchars($consultation['id']) ?></td>
                                <td><?= htmlspecialchars($consultation['first_name'] . ' ' . $consultation['last_name']) ?></td>
                                <td>
                                    <?php if (!empty($personalProducts)): ?>
                                        <?php foreach ($personalProducts as $product): ?>
                                            <div class="medicine-card">
                                                <span class="medicine-name"><?= htmlspecialchars($product['product'] ?? '') ?></span>
                                                <span class="medicine-qty">x<?= htmlspecialchars($product['qty'] ?? '0') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="no-medicines">No medicines prescribed</span>
                                    <?php endif; ?>
                                </td>
                                <td><button class="btn-action" onclick="calculateTotal('<?= $consultation['id'] ?>')">View</button></td>
                                <td class="status-cell">
                                    <label class="dispatch-label">
                                        <input type="checkbox"
                                               class="dispense-status"
                                               <?= $isDispatched ? 'checked' : '' ?>
                                               onchange="toggleDispatch('<?= $consultation['id'] ?>', this.checked)">
                                        Dispatched
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">No consultation orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal for Receipt -->
    <div id="receiptModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Receipt for Consultation <span id="consultationId"></span></h3>
            <table id="receiptTable">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <h4>Total: Rs. <span id="totalAmount">0.00</span></h4>
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

            document.getElementById("consultationId").innerText = consultationId;
            document.getElementById("totalAmount").innerText = total.toFixed(2);
            document.getElementById("receiptModal").style.display = "block";
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
