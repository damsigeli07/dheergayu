<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/inventory-styles.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
        }

        /* Form Container */
        .main-content {
            max-width: 700px;
            margin: 2rem auto;
            margin-left: calc(180px + 2rem);
            padding: 1rem;
            width: calc(100vw - 180px - 4rem);
            box-sizing: border-box;
        }

        .add-product-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .form-title {
            text-align: center;
            color: #8B7355;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #7a9b57;
            box-shadow: 0 0 0 3px rgba(122,155,87,0.1);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-submit, .btn-cancel {
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .btn-submit {
            background-color: #7a9b57;
            color: white;
        }

        .btn-submit:hover {
            background-color: #6B8E23;
        }

        .btn-cancel {
            background-color: #DC143C;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #B91C3C;
        }

        .image-upload {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background-color: #f8f8f8;
            cursor: pointer;
            transition: border-color 0.3s ease;
            position: relative;
        }

        .image-upload:hover {
            border-color: #7a9b57;
        }

        .image-upload input[type="file"] {
            display: none;
        }

        .upload-text {
            color: #666;
            font-size: 0.9rem;
        }

        .image-preview {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
        }

        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            object-fit: contain;
        }

        @media (max-width: 480px) {
            .btn-submit, .btn-cancel {
                width: 100%;
            }
        }
    </style>
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="admindashboard.php" class="nav-btn">Home</a>
            <a href="admininventory.php" class="nav-btn active">Products</a>
            <a href="admininventoryview.php" class="nav-btn">Inventory</a>
            <a href="adminappointment.php" class="nav-btn">Appointments</a>
            <a href="adminusers.php" class="nav-btn">Users</a>
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="add-product-form">
            <h2 class="form-title">Add New Product</h2>
            
            <form action="process-add-product.php" method="POST" enctype="multipart/form-data">
                <!-- Product Name -->
                <div class="form-group">
                    <label for="product-name" class="form-label">Product Name</label>
                    <input type="text" id="product-name" name="product_name" class="form-input" required placeholder="Enter product name">
                </div>

                <!-- Product Price -->
                <div class="form-group">
                    <label for="product-price" class="form-label">Price (Rs.)</label>
                    <input type="number" id="product-price" name="product_price" class="form-input" required min="0" step="0.01" placeholder="Enter price">
                </div>

                <!-- Product Description -->
                <div class="form-group">
                    <label for="product-description" class="form-label">Description</label>
                    <textarea id="product-description" name="product_description" class="form-textarea" required placeholder="Enter product description"></textarea>
                </div>

                <!-- Product Image -->
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <div class="image-upload" onclick="document.getElementById('product-image').click()">
                        <input type="file" id="product-image" name="product_image" accept="image/*">
                        <div class="upload-text">
                            <p>Click to upload product image</p>
                            <p style="font-size: 0.8rem; margin-top: 0.5rem;">Supports: JPG, PNG, JPEG</p>
                        </div>
                        <div class="image-preview"></div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add Product</button>
                    <a href="admininventory.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        const inputFile = document.getElementById('product-image');
        const previewDiv = document.querySelector('.image-preview');

        inputFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewDiv.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
