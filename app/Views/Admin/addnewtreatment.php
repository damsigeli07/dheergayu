<?php
// You can add database connection here if needed
// Example: include('db_connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Treatment</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/addnewtreatment.css">
</head>
<body>
    <div class="main-container">
        <h2>Add New Treatment</h2>

        <form action="process-add-treatment.php" method="POST" id="addTreatmentForm">
            <label for="treatment-name">Treatment Name <span>*</span></label>
            <input type="text" id="treatment-name" name="treatment_name" required placeholder="Enter treatment name">

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Enter treatment description"></textarea>

            <label for="duration">Duration (e.g., 30 mins, 1 hour) <span>*</span></label>
            <input type="text" id="duration" name="duration" required placeholder="Enter duration">

            <label for="price">Price (Rs.) <span>*</span></label>
            <input type="number" id="price" name="price" required min="0" step="0.01" placeholder="Enter price">

            <label for="status">Status <span>*</span></label>
            <select id="status" name="status" required>
                <option value="" disabled selected>-- Select Status --</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <div class="form-buttons">
                <button type="button" onclick="window.history.back();" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn">Add Treatment</button>
            </div>
        </form>
    </div>
</body>
</html>
