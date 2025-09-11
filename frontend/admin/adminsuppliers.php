<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Info - Admin Dashboard</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/adminsuppliers.css">
    <style>
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #8B7355;
            margin-bottom: 1rem;
            text-align: center;
        }

        .add-supplier-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .btn-add-supplier {
            background-color: #8B7355;
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-add-supplier:hover {
            background-color: #6F5B42;
            transform: translateY(-2px);
        }

        .supplier-table-container {
            overflow-x: auto;
        }

        .supplier-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .supplier-table th, .supplier-table td {
            padding: 0.8rem 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .supplier-table th {
            background-color: #f8f8f8;
            font-weight: 600;
            color: #555;
        }

        .supplier-table tr:hover {
            background-color: #f0f0f0;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit, .btn-delete {
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-edit {
            background-color: #7a9b57;
        }

        .btn-edit:hover {
            background-color: #6B8E23;
        }

        .btn-delete {
            background-color: #DC143C;
        }

        .btn-delete:hover {
            background-color: #B91C3C;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="admindashboard.php" class="nav-btn">Home</a>
                <a href="admininventory.php" class="nav-btn">Products</a>
                <a href="adminappointment.php" class="nav-btn">Appointments</a>
                <a href="adminusers.php" class="nav-btn">Users</a>
                <a href="admintreatment.php" class="nav-btn">Treatments</a>
                <button class="nav-btn active">Supplier Info</button>
            </nav>
        </div>
        <div class="header-right">
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>
                <!-- Dropdown -->
                <div class="user-dropdown" id="user-dropdown">
                    <a href="adminprofile.php" class="profile-btn">Profile</a>
                    <a href="../patient/login.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <h2 class="section-title">Supplier Information</h2>

        <!-- Add Supplier Button -->
        <div class="add-supplier-container">
            <a href="addsupplier.php" class="btn-add-supplier">+ Add New Supplier</a>
        </div>

        <!-- Supplier Table -->
        <div class="supplier-table-container">
            <table class="supplier-table">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Herbal Supplies Co.</td>
                        <td>Mr. Sunil Perera</td>
                        <td>071 123 4567</td>
                        <td>sunil@herbalsupplies.lk</td>
                        <td class="action-buttons">
                            <button class="btn-edit">Edit</button>
                            <button class="btn-delete">Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Ayurvedic Traders</td>
                        <td>Mrs. Nadeesha Silva</td>
                        <td>077 987 6543</td>
                        <td>nadeesha@ayutraders.lk</td>
                        <td class="action-buttons">
                            <button class="btn-edit">Edit</button>
                            <button class="btn-delete">Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Natural Extracts Ltd.</td>
                        <td>Mr. Amal Fernando</td>
                        <td>076 555 8899</td>
                        <td>amal@naturalextracts.lk</td>
                        <td class="action-buttons">
                            <button class="btn-edit">Edit</button>
                            <button class="btn-delete">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
