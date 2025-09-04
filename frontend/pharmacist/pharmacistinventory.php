<?php
// Sample data for demonstration
$products = [
    ["name"=>"Asamodagam", "image"=>"images/asamodagam.jpg"],
    ["name"=>"Bala Thailaya", "image"=>"images/Bala Thailaya.png"],
    ["name"=>"Dashamoolarishta", "image"=>"images/dashamoolarishta.jpg"],
    ["name"=>"Kothalahimbutu", "image"=>"images/kothalahimbutu.jpg"],
    ["name"=>"Neem Oil", "image"=>"images/neem_oil.jpg"],
    ["name"=>"Nirugandi Oil", "image"=>"images/nirugandi_oil.jpg"],
    ["name"=>"Paspanguwa", "image"=>"images/paspanguwa.jpeg"],
    ["name"=>"Pinda Thaliya", "image"=>"images/Pinda Thailaya.jpeg"],
    ["name"=>"Siddhalepa", "image"=>"images/siddhalepa.png"],
];

// Sample inventory batches (normally fetched from DB)
$inventory = [
    ["product"=>"Asamodagam", "quantity"=>12, "mfd"=>"2024-01-01", "exp"=>"2025-01-01"],
    ["product"=>"Bala Thailaya", "quantity"=>8, "mfd"=>"2023-08-15", "exp"=>"2024-08-15"],
    ["product"=>"Paspanguwa", "quantity"=>20, "mfd"=>"2024-03-10", "exp"=>"2026-03-10"]
];
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
        <h2 class="section-title">Inventory</h2>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>MFD</th>
                    <th>EXP</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($inventory as $batch): ?>
                <tr>
                    <?php 
                        $prod = array_filter($products, fn($p)=>$p['name']==$batch['product']);
                        $prod = array_values($prod)[0];
                    ?>
                    <td><img src="<?= $prod['image'] ?>" alt="<?= $prod['name'] ?>" class="prod-img"></td>
                    <td><?= $batch['product'] ?></td>
                    <td><?= $batch['quantity'] ?></td>
                    <td><?= $batch['mfd'] ?></td>
                    <td><?= $batch['exp'] ?></td>
                    <td>
                        <button class="btn-edit">Edit</button>
                        <button class="btn-delete">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Add Stock</h3>
        <form action="process_add_stock.php" method="POST" class="add-stock-form">
            <div class="form-group">
                <label for="product">Product</label>
                <select name="product" id="product" class="form-input" required>
                    <option value="">Select Product</option>
                    <?php foreach($products as $p): ?>
                    <option value="<?= $p['name'] ?>"><?= $p['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-input" min="1" required>
            </div>

            <div class="form-group">
                <label for="mfd">Manufacturing Date</label>
                <input type="date" name="mfd" id="mfd" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="exp">Expiry Date</label>
                <input type="date" name="exp" id="exp" class="form-input" required>
            </div>

            <button type="submit" class="btn-submit">Add Stock</button>
        </form>
    </main>
    <script src="js/pharmacistinventory.js"></script>
</body>
</html>
