<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="css/inventory-styles.css">
    <style>
        .add-product-form {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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

        .form-input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #7a9b57;
            box-shadow: 0 0 0 3px rgba(122, 155, 87, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-submit {
            background-color: #7a9b57;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #6B8E23;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background-color: #DC143C;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background-color: #B91C3C;
            transform: translateY(-1px);
        }

        .image-upload {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background-color: #f8f8f8;
            cursor: pointer;
            transition: border-color 0.3s ease;
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .add-product-form {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
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
        <a href="pharmacisthome.php" class="nav-btn">Home</a>
        <a href="pharmacistinventory.php" class="nav-btn active">Inventory</a>
        <a href="pharmacistorders.php" class="nav-btn">Orders</a>
        <a href="pharmacistreports.php" class="nav-btn">Reports</a>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="add-product-form">
            <h2 class="form-title">Add New Product</h2>
            
            <form action="process-add-product.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product-name" class="form-label">Product Name</label>
                    <input type="text" id="product-name" name="product_name" class="form-input" required placeholder="Enter product name">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="product-price" class="form-label">Price (Rs.)</label>
                        <input type="number" id="product-price" name="product_price" class="form-input" required placeholder="Enter price" min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="product-quantity" class="form-label">Quantity in Stock</label>
                        <input type="number" id="product-quantity" name="product_quantity" class="form-input" required placeholder="Enter quantity" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="product-manufacture" class="form-label">Manufacturing Date</label>
                        <input type="date" id="product-manufacture" name="product_manufacture" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="product-expiry" class="form-label">Expiry Date</label>
                        <input type="date" id="product-expiry" name="product_expiry" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="product-image" class="form-label">Product Image</label>
                    <div class="image-upload" onclick="document.getElementById('product-image').click()">
                        <input type="file" id="product-image" name="product_image" accept="image/*">
                        <div class="upload-text">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 1rem;">
                                <path d="M21 19V5C21 3.9 20.1 3 19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19ZM8.5 13.5L11 16.51L14.5 12L19 18H5L8.5 13.5Z" fill="#999"/>
                            </svg>
                            <p>Click to upload product image</p>
                            <p style="font-size: 0.8rem; margin-top: 0.5rem;">Supports: JPG, PNG, JPEG</p>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add Product</button>
                    <a href="pharmacistinventory.php" class="btn-cancel" style="text-decoration: none; display: inline-block;">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Show selected image preview
        document.getElementById('product-image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const uploadDiv = document.querySelector('.image-upload');
                    uploadDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        <p style="margin-top: 1rem; color: #666;">Image selected: ${file.name}</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 