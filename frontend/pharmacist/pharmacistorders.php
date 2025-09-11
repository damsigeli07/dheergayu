<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Orders</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/pharmacistorders.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="pharmacisthome.php" class="nav-btn">Home</a>
                <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
                <button class="nav-btn active">Orders</button>
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
                        <th>View Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>101</td>
                        <td>John Doe</td>
                        <td>
                            <div class="medicine-card">
                                <span class="medicine-name">Paspanguwa Pack</span>
                                <span class="medicine-qty">x2</span>
                            </div>
                            <div class="medicine-card">
                                <span class="medicine-name">Asamodagam Spirit</span>
                                <span class="medicine-qty">x1</span>
                            </div>
                        </td>
                        <td><button class="btn-action" onclick="calculateTotal('101')">Calculate</button></td>
                        <td><input type="checkbox" class="dispense-status"> Dispatched</td>
                    </tr>
                    <tr>
                        <td>102</td>
                        <td>Jane Smith</td>
                        <td>
                            <div class="medicine-card">
                                <span class="medicine-name">Siddhalepa Balm</span>
                                <span class="medicine-qty">x1</span>
                            </div>
                            <div class="medicine-card">
                                <span class="medicine-name">Dashamoolarishta</span>
                                <span class="medicine-qty">x2</span>
                            </div>
                        </td>
                        <td><button class="btn-action" onclick="calculateTotal('102')">Calculate</button></td>
                        <td><input type="checkbox" class="dispense-status"> Dispatched</td>
                    </tr>
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
        // Sample prices
        const productPrices = {
            "Paspanguwa Pack": 850,
            "Asamodagam Spirit": 650,
            "Siddhalepa Balm": 450,
            "Dashamoolarishta": 750
        };

        const prescriptions = {
            "101": [
                {name: "Paspanguwa Pack", qty: 2},
                {name: "Asamodagam Spirit", qty: 1}
            ],
            "102": [
                {name: "Siddhalepa Balm", qty: 1},
                {name: "Dashamoolarishta", qty: 2}
            ]
        };

        function calculateTotal(id) {
            const items = prescriptions[id];
            const tbody = document.querySelector("#receiptTable tbody");
            tbody.innerHTML = "";
            let total = 0;

            items.forEach(item => {
                const price = productPrices[item.name] || 0;
                const amount = item.qty * price;
                total += amount;
                const row = `<tr>
                                <td>${item.name}</td>
                                <td>${item.qty}</td>
                                <td>Rs. ${price.toFixed(2)}</td>
                                <td>Rs. ${amount.toFixed(2)}</td>
                             </tr>`;
                tbody.innerHTML += row;
            });

            document.getElementById("consultationId").innerText = id;
            document.getElementById("totalAmount").innerText = total.toFixed(2);
            document.getElementById("receiptModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("receiptModal").style.display = "none";
        }
    </script>
</body>
</html>
