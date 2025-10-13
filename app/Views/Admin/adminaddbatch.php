<?php
// Sample data for demonstration
$products = [
    ["name"=>"Asamodagam", "image"=>"images/asamodagam.jpg"],
    ["name"=>"Bala Thailaya", "image"=>"images/Bala Thailaya.png"],
    ["name"=>"Dashamoolarishta", "image"=>"images/Dashamoolarishta.jpeg"],
    ["name"=>"Kothalahimbutu Capsules", "image"=>"images/Kothalahimbutu Capsules.jpeg"],
    ["name"=>"Neem Oil", "image"=>"images/Neem Oil.jpg"],
    ["name"=>"Nirgundi Oil", "image"=>"images/Nirgundi Oil.jpg"],
    ["name"=>"Paspanguwa", "image"=>"images/paspanguwa.jpeg"],
    ["name"=>"Pinda Thailaya", "image"=>"images/Pinda Thailaya.jpeg"],
    ["name"=>"Siddhalepa", "image"=>"images/siddhalepa.png"],
];

// Get pre-selected product from URL parameter
$selectedProduct = isset($_GET['product']) ? $_GET['product'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Batch - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/admininventory.css">
    <style>
        .add-batch-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 2rem auto;
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

        .form-group label {
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

        .form-help {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.8rem;
            color: #666;
            font-style: italic;
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

        .status-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .status-info h4 {
            margin: 0 0 0.5rem 0;
            color: #0066cc;
            font-size: 1rem;
        }

        .status-info p {
            margin: 0;
            color: #333;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .btn-submit, .btn-cancel {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="admindashboard.php" class="nav-btn">Home</a>
                <a href="admininventory.php" class="nav-btn">Products</a>
                <a href="adminappointment.php" class="nav-btn">Appointments</a>
                <a href="adminusers.php" class="nav-btn">Users</a>
                <a href="admintreatment.php" class="nav-btn">Treatments</a>
                <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>
                <div class="user-dropdown" id="user-dropdown">
                    <a href="adminprofile.php" class="profile-btn">Profile</a>
                    <a href="../patient/login.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="add-batch-form">
            <h2 class="form-title">Add Batch</h2>
            
            <div class="status-info">
                <h4>📋 Batch Information</h4>
                <p>Fill in the details for the new batch. All fields marked with * are required.</p>
            </div>
            
            <form action="process_add_batch.php" method="POST">
                <div class="form-group">
                    <label for="product">Product *</label>
                    <select name="product" id="product" class="form-input" required>
                        <option value="">Select Product</option>
                        <?php foreach($products as $p): ?>
                        <option value="<?= $p['name'] ?>" <?= $selectedProduct === $p['name'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="batch_number">Batch Number *</label>
                    <input type="text" name="batch_number" id="batch_number" class="form-input" required placeholder="e.g., ASM001, BLT002">
                    <small class="form-help">Unique identifier for this batch</small>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity *</label>
                    <input type="number" name="quantity" id="quantity" class="form-input" min="1" required placeholder="Enter quantity">
                    <small class="form-help">Number of units in this batch</small>
                </div>

                <div class="form-group">
                    <label for="mfd">Manufacturing Date *</label>
                    <input type="date" name="mfd" id="mfd" class="form-input" required>
                    <small class="form-help">Date when this batch was manufactured</small>
                </div>

                <div class="form-group">
                    <label for="exp">Expiry Date *</label>
                    <input type="date" name="exp" id="exp" class="form-input" required>
                    <small class="form-help">Date when this batch expires</small>
                </div>

                <div class="form-group">
                    <label for="supplier">Supplier *</label>
                    <input type="text" name="supplier" id="supplier" class="form-input" required placeholder="Enter supplier name">
                    <small class="form-help">Name of the supplier for this batch</small>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-input">
                        <option value="Good">Good</option>
                        <option value="Expiring Soon">Expiring Soon</option>
                        <option value="Expired">Expired</option>
                    </select>
                    <small class="form-help">Current status of this batch (auto-calculated based on dates)</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add Batch</button>
                    <a href="../pharmacist/pharmacistinventory.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const mfd = document.getElementById('mfd').value;
            const exp = document.getElementById('exp').value;
            const quantity = document.getElementById('quantity').value;
            const batchNumber = document.getElementById('batch_number').value;
            const supplier = document.getElementById('supplier').value;
            
            if (mfd && exp) {
                const mfdDate = new Date(mfd);
                const expDate = new Date(exp);
                const today = new Date();
                
                // Check if expiry date is after manufacturing date
                if (expDate <= mfdDate) {
                    alert('❌ Expiry date must be after manufacturing date!');
                    e.preventDefault();
                    return false;
                }
                
                // Check if manufacturing date is not in the future
                if (mfdDate > today) {
                    alert('❌ Manufacturing date cannot be in the future!');
                    e.preventDefault();
                    return false;
                }
                
                // Check if expiry date is not too far in the past
                const oneYearAgo = new Date();
                oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
                if (expDate < oneYearAgo) {
                    alert('⚠️ Warning: This batch expired over a year ago. Please verify the dates.');
                }
                
                // Check if expiry date is within 30 days (expiring soon)
                const thirtyDaysFromNow = new Date();
                thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30);
                if (expDate <= thirtyDaysFromNow) {
                    alert('⚠️ Warning: This batch expires within 30 days. Please verify the expiry date.');
                }
            }
            
            // Validate quantity
            if (quantity <= 0) {
                alert('❌ Quantity must be greater than 0!');
                e.preventDefault();
                return false;
            }

            // Validate batch number
            if (batchNumber.trim() === '') {
                alert('❌ Batch number is required!');
                e.preventDefault();
                return false;
            }

            // Validate supplier
            if (supplier.trim() === '') {
                alert('❌ Supplier is required!');
                e.preventDefault();
                return false;
            }
        });

        // Set default manufacturing date to today
        document.getElementById('mfd').valueAsDate = new Date();
        
        // Auto-calculate expiry date based on product type
        document.getElementById('product').addEventListener('change', function() {
            const product = this.value;
            const mfdInput = document.getElementById('mfd');
            const expInput = document.getElementById('exp');
            
            if (mfdInput.value) {
                const mfdDate = new Date(mfdInput.value);
                let expiryDate = new Date(mfdDate);
                
                // Set default expiry based on product type (2 years for most ayurvedic products)
                expiryDate.setFullYear(expiryDate.getFullYear() + 2);
                
                expInput.valueAsDate = expiryDate;
            }
        });
        
        // Update expiry date when manufacturing date changes
        document.getElementById('mfd').addEventListener('change', function() {
            const product = document.getElementById('product').value;
            const expInput = document.getElementById('exp');
            
            if (this.value && product) {
                const mfdDate = new Date(this.value);
                let expiryDate = new Date(mfdDate);
                
                // Set default expiry based on product type (2 years for most ayurvedic products)
                expiryDate.setFullYear(expiryDate.getFullYear() + 2);
                
                expInput.valueAsDate = expiryDate;
            }
        });

        // Auto-update status based on expiry date
        document.getElementById('exp').addEventListener('change', function() {
            const expDate = new Date(this.value);
            const today = new Date();
            const thirtyDaysFromNow = new Date();
            thirtyDaysFromNow.setDate(today.getDate() + 30);
            
            const statusSelect = document.getElementById('status');
            
            if (expDate < today) {
                statusSelect.value = 'Expired';
            } else if (expDate <= thirtyDaysFromNow) {
                statusSelect.value = 'Expiring Soon';
            } else {
                statusSelect.value = 'Good';
            }
        });
    </script>
</body>
</html>
