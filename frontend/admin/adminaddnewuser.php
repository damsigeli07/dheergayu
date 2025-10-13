<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="css/adminaddnewuser.css">
</head>
<body>
    <div class="main-container">
        <h2>Add New User</h2>

        <form id="addUserForm" method="POST" action="../../backend/admin/add_user.php">
            <label for="firstName">First Name <span>*</span></label>
            <input type="text" id="firstName" name="first_name" required>

            <label for="lastName">Last Name <span>*</span></label>
            <input type="text" id="lastName" name="last_name" required>

            <label for="password">Password <span>*</span></label>
            <input type="password" id="password" name="password" required>

            <label for="email">Email <span>*</span></label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone <span>*</span></label>
            <input type="text" id="phone" name="phone" required>

            <label for="role">Role <span>*</span></label>
            <select id="role" name="role" required>
                <option value="" disabled selected>-- Select Role --</option>
                <option value="Pharmacist">Pharmacist</option>
                <option value="Doctor">Doctor</option>
                <option value="Staff">Staff</option>
                <option value="Admin">Admin</option>
            </select>

            <label class="checkbox-label">
                <input type="checkbox" id="verified" name="verified" required>
                Certification is verified
            </label>

            <div class="form-buttons">
                <button type="button" onclick="window.history.back();" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn">Submit</button>
            </div>
        </form>
    </div>
</body>
</html>
