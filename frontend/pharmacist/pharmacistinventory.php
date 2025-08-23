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

            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Search Bar -->
        <div class="search-container">
            <div class="search-box">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
                </svg>
                <input type="text" placeholder="Search" class="search-input">
            </div>
        </div>

        <!-- Product Grid -->
        <div class="product-grid">
            <!-- Product Card 1 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/paspanguwa.jpeg" alt="Paspanguwa Pack" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Paspanguwa Pack</h3>
                    <p class="product-price">Price: Rs. 850</p>
                    <p class="product-stock">Quantity in Stock: 12</p>
                    <p class="product-manufacture">Mfg date: Jul 15, 2024</p>
                    <p class="product-expiry">Exp date: Sep 2025</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/asamodagam.jpg" alt="Asamodagam Spirit" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Asamodagam Spirit</h3>
                    <p class="product-price">Price: Rs. 650</p>
                    <p class="product-stock">Quantity in Stock: 8</p>
                    <p class="product-manufacture">Mfg date: Jun 20, 2024</p>
                    <p class="product-expiry">Exp date: Oct 2025</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/siddhalepa.png" alt="Siddhalepa Balm" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Siddhalepa Balm</h3>
                    <p class="product-price">Price: Rs. 450</p>
                    <p class="product-stock low-stock">Quantity in Stock: 3 (Low!)</p>
                    <p class="product-manufacture">Mfg date: May 12, 2024</p>
                    <p class="product-expiry">Exp date: Nov 2025</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Dashamoolarishta.jpeg" alt="Dashamoolarishta" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Dashamoolarishta</h3>
                    <p class="product-price">Price: Rs. 750</p>
                    <p class="product-stock low-stock">Quantity in Stock: 2 (Low!)</p>
                    <p class="product-manufacture">Mfg date: Apr 8, 2024</p>
                    <p class="product-expiry">Exp date: Dec 2025</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 5 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Kothalahimbutu Capsules.jpeg" alt="Kothalahimbutu Capsules" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Kothalahimbutu Capsules</h3>
                    <p class="product-price">Price: Rs. 1200</p>
                    <p class="product-stock low-stock">Quantity in Stock: 1 (Low!)</p>
                    <p class="product-manufacture">Mfg date: Mar 25, 2024</p>
                    <p class="product-expiry">Exp date: Jan 2026</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 6 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Neem Oil.jpg" alt="Neem Oil" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Neem Oil</h3>
                    <p class="product-price">Price: Rs. 380</p>
                    <p class="product-stock">Quantity in Stock: 15</p>
                    <p class="product-manufacture">Mfg date: Feb 18, 2024</p>
                    <p class="product-expiry">Exp date: Mar 2026</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 7 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Pinda Thailaya.jpeg" alt="Pinda Thailaya" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Pinda Thailaya</h3>
                    <p class="product-price">Price: Rs. 550</p>
                    <p class="product-stock low-stock">Quantity in Stock: 3 (Low!)</p>
                    <p class="product-manufacture">Mfg date: Jan 30, 2024</p>
                    <p class="product-expiry">Exp date: May 2026</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 8 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="images/Nirgundi Oil.jpg" alt="Nirgundi Oil" class="product-img">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Nirgundi Oil</h3>
                    <p class="product-price">Price: Rs. 480</p>
                    <p class="product-stock">Quantity in Stock: 7</p>
                    <p class="product-manufacture">Mfg date: Aug 5, 2024</p>
                    <p class="product-expiry">Exp date: Jul 2026</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>
        </div>

        <!-- Add New Product Button -->
        <div class="add-product-container">
            <a href="add-product.php" class="btn-add-product">+ Add New Product</a>
        </div>
    </main>
    
</body>
</html>