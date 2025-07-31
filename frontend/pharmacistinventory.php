<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="css/inventory-styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <h1 class="header-title">PHARMACIST DASHBOARD</h1>
        <div class="user-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="currentColor"/>
                <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="currentColor"/>
            </svg>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navigation">
        <a href="pharmacisthome.php" class="nav-btn active">Home</a>
        <button class="nav-btn active">Inventory</button>
        <button class="nav-btn">Orders</button>
    </nav>

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
                    <div class="placeholder-x">
                        <div class="x-line1"></div>
                        <div class="x-line2"></div>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name">Name: Herbal Pain Oil</h3>
                    <p class="product-price">Price: Rs. 350</p>
                    <p class="product-stock">Quantity in Stock: 12</p>
                    <p class="product-expiry">Exp date: Dec 2024</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="product-card">
                <div class="product-image">
                    <div class="placeholder-x">
                        <div class="x-line1"></div>
                        <div class="x-line2"></div>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name">Name: Neem Paste</h3>
                    <p class="product-price">Price: Rs. 180</p>
                    <p class="product-stock">Quantity in Stock: 8</p>
                    <p class="product-expiry">Exp date: Nov 2024</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="product-card">
                <div class="product-image">
                    <div class="placeholder-x">
                        <div class="x-line1"></div>
                        <div class="x-line2"></div>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name">Name: Steam Herbs</h3>
                    <p class="product-price">Price: Rs. 220</p>
                    <p class="product-stock low-stock">Quantity in Stock: 3 (Low!)</p>
                    <p class="product-expiry">Exp date: Jan 2025</p>
                </div>
                <div class="product-actions">
                    <button class="btn btn-edit">Edit</button>
                    <button class="btn btn-delete">Delete</button>
                </div>
            </div>
        </div>

        <!-- Add New Product Button -->
        <div class="add-product-container">
            <button class="btn-add-product">+ Add New Product</button>
        </div>
    </main>
</body>
</html>