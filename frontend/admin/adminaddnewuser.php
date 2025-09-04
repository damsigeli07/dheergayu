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

        <form id="addUserForm">
            <label for="name">Name <span>*</span></label>
            <input type="text" id="name" name="name" required placeholder="Enter full name">

            <label for="password">Password <span>*</span></label>
            <input type="password" id="password" name="password" required placeholder="Enter password">

            <label for="email">Email <span>*</span></label>
            <input type="email" id="email" name="email" required placeholder="example@email.com">

            <label for="phone">Phone <span>*</span></label>
            <input type="text" id="phone" name="phone" required placeholder="Enter 10-digit phone number">

            <label for="role">Role <span>*</span></label>
            <select id="role" name="role" required>
                <option value="" disabled selected>-- Select Role --</option>
                <option value="Pharmacist">Pharmacist</option>
                <option value="Doctor">Doctor</option>
                <option value="Staff">Staff</option>
            </select>

            <label class="checkbox-label">
                <input type="checkbox" id="verified" name="verified">
                Certification is verified
            </label>

            <div class="form-buttons">
                <button type="button" onclick="window.history.back();" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn">Submit</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById("addUserForm").addEventListener("submit", function(e) {
            const phone = document.getElementById("phone").value.trim();
            const verified = document.getElementById("verified").checked;

            // Phone validation: must be 10 digits (only numbers)
            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(phone)) {
                alert("Phone number must be exactly 10 digits.");
                e.preventDefault();
                return;
            }

            // Certification checkbox validation
            if (!verified) {
                alert("Certification must be verified before submission.");
                e.preventDefault();
                return;
            }

            // If all validations pass
            alert("Form submitted successfully! (No backend)");
        });
    </script>
</body>
</html>
