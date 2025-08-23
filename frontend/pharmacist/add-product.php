<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="css/inventory-styles.css">
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
            padding: 1rem;
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
            box-shadow: 0 0 0 3px rgba(122,155,87,0.1);
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

        .btn-submit, .btn-cancel {
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none; /* removes underline from <a> */
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .btn-submit, .btn-cancel {
                width: 100%;
            }
        }
    </style>
</head>
<body>
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
                        <input type="number" id="product-price" name="product_price" class="form-input" required min="0" step="0.01" placeholder="Enter price">
                    </div>

                    <div class="form-group">
                        <label for="product-quantity" class="form-label">Quantity in Stock</label>
                        <input type="number" id="product-quantity" name="product_quantity" class="form-input" required min="0" placeholder="Enter quantity">
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

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add Product</button>
                    <a href="pharmacistinventory.php" class="btn-cancel">Cancel</a>
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
