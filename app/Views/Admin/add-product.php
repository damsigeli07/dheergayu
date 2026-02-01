<?php
// Fetch product data from database if editing
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$productType = isset($_GET['product_type']) ? trim($_GET['product_type']) : (isset($_GET['product_type']) ? $_GET['product_type'] : 'admin');
$productName = '';
$productPrice = '';
$productDescription = '';
$currentImage = '';

// Determine which table to use
$table_name = ($productType === 'patient') ? 'patient_products' : 'products';

if ($productId > 0) {
    // Fetch product data from database
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    if (!$db->connect_error) {
        $stmt = $db->prepare("SELECT product_id, name, price, description, image FROM $table_name WHERE product_id = ?");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        $db->close();
        
        if ($product) {
            $productName = $product['name'] ?? '';
            $productPrice = $product['price'] ?? '';
            $productDescription = $product['description'] ?? '';
            $currentImage = $product['image'] ?? '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $productId ? 'Edit ' . ucfirst($productType) . ' Product' : 'Add New ' . ucfirst($productType) . ' Product' ?> - Admin Dashboard</title>
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
            <h2 class="form-title"><?= $productId ? 'Edit ' . ucfirst($productType) . ' Product' : 'Add New ' . ucfirst($productType) . ' Product' ?></h2>
            
            <form id="addProductForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="hidden_product_id" value="<?= $productId ?>">
                <input type="hidden" name="product_type" id="hidden_product_type" value="<?= htmlspecialchars($productType) ?>">
                <?php if ($productId > 0): ?>
                <input type="hidden" name="is_edit" value="1">
                <?php endif; ?>
                
                <!-- Product Name -->
                <div class="form-group">
                    <label for="product-name" class="form-label">Product Name</label>
                    <input type="text" id="product-name" name="product_name" class="form-input" required placeholder="Enter product name" value="<?= htmlspecialchars($productName) ?>">
                </div>

                <!-- Product Price -->
                <div class="form-group">
                    <label for="product-price" class="form-label">Price (Rs.)</label>
                    <input type="number" id="product-price" name="product_price" class="form-input" required min="0" step="0.01" placeholder="Enter price" value="<?= htmlspecialchars($productPrice) ?>">
                </div>

                <!-- Product Description -->
                <div class="form-group">
                    <label for="product-description" class="form-label">Description</label>
                    <textarea id="product-description" name="product_description" class="form-textarea" required placeholder="Enter product description"><?= htmlspecialchars($productDescription) ?></textarea>
                </div>

                <!-- Product Image -->
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <div class="image-upload" onclick="document.getElementById('product-image').click()">
                        <input type="file" id="product-image" name="product_image" accept="image/*">
                        <div class="upload-text">
                            <p>Click to upload product image</p>
                            <p style="font-size: 0.8rem; margin-top: 0.5rem;">Supports: JPG, PNG, JPEG. Leave empty to keep current image when editing.</p>
                        </div>
                        <?php if ($productId > 0 && $currentImage): ?>
                            <div style="margin-top: 10px;">
                                <p style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">Current Image:</p>
                                <img src="/dheergayu/public/assets/images/Admin/<?= htmlspecialchars(str_replace('images/', '', $currentImage)) ?>" alt="Current product image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                        <?php endif; ?>
                        <div class="image-preview"></div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit"><?= $productId ? 'Save Changes' : 'Add ' . ucfirst($productType) . ' Product' ?></button>
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

        // Form submission
        document.getElementById('addProductForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);
            
            // Get product_id and product_type from hidden fields
            const hiddenProductId = document.getElementById('hidden_product_id');
            const hiddenProductType = document.getElementById('hidden_product_type');
            const productId = hiddenProductId ? parseInt(hiddenProductId.value) : 0;
            const productType = hiddenProductType ? hiddenProductType.value : 'admin';
            const isEdit = productId > 0;
            
            // Ensure product_id and product_type are always in the form data
            data.set('product_id', productId.toString());
            data.set('product_type', productType);
            
            console.log('Submitting form - product_id:', productId, 'isEdit:', isEdit);
            console.log('Form data product_id:', data.get('product_id'));
            
            // Double-check: if we're editing, product_id must be > 0
            if (isEdit && productId <= 0) {
                alert('❌ Error: Product ID is missing. Cannot update product.');
                console.error('Product ID missing for edit operation');
                return;
            }
            
            const url = '/dheergayu/app/Controllers/ProductController.php';
            try {
                const res = await fetch(url, { method: 'POST', body: data });
                const result = await res.json();
                
                console.log('Server response:', result);
                
                if (result.success) {
                    if (isEdit) {
                        alert('✅ Product updated successfully (ID: ' + productId + ')');
                    } else {
                        alert('✅ Product added successfully');
                    }
                    window.location.href = 'admininventory.php';
                } else {
                    alert('Error: ' + (result.message || 'Failed to save product'));
                    console.error('Server error:', result);
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Failed to save product: ' + err.message);
            }
        });
    </script>
</body>
</html>
