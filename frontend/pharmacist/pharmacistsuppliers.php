<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Info - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/pharmacistsuppliers.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="pharmacisthome.php" class="nav-btn">Home</a>
                <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
                <a href="pharmacistorders.php" class="nav-btn">Orders</a>
                <a href="pharmacistreports.php" class="nav-btn">Reports</a>
                <button class="nav-btn active">Supplier Info</button>
            </nav>
        </div>
        <div class="header-right">
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Pharmacist</span>
                <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <h2 class="section-title">Supplier Information</h2>

        <div class="supplier-table-container">
            <table class="supplier-table">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Products Supplied</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Herbal Supplies Co.</td>
                        <td>Mr. Sunil Perera</td>
                        <td>071 123 4567</td>
                        <td>sunil@herbalsupplies.lk</td>
                        <td>Paspanguwa Pack, Asamodagam Spirit</td>
                        <td>Colombo 03</td>
                    </tr>
                    <tr>
                        <td>Ayurvedic Traders</td>
                        <td>Mrs. Nadeesha Silva</td>
                        <td>077 987 6543</td>
                        <td>nadeesha@ayutraders.lk</td>
                        <td>Siddhalepa Balm, Dashamoolarishta</td>
                        <td>Kandy</td>
                    </tr>
                    <tr>
                        <td>Natural Extracts Ltd.</td>
                        <td>Mr. Amal Fernando</td>
                        <td>076 555 8899</td>
                        <td>amal@naturalextracts.lk</td>
                        <td>Kothalahimbutu Capsules, Neem Oil</td>
                        <td>Galle</td>
                    </tr>
                    <tr>
                        <td>Herbal Oils Pvt Ltd.</td>
                        <td>Ms. Ruwani Jayawardena</td>
                        <td>071 234 6789</td>
                        <td>ruwani@herbaloils.lk</td>
                        <td>Pinda Thailaya, Nirgundi Oil</td>
                        <td>Matara</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
