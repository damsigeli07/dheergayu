<?php
require_once __DIR__ . '/../../../core/bootloader.php';

// Pre-fill when editing via query params
$treatmentId = isset($_GET['treatment_id']) ? (int)$_GET['treatment_id'] : 0;
$treatmentName = $_GET['treatment_name'] ?? '';
$description = $_GET['description'] ?? '';
$duration = $_GET['duration'] ?? '';
$price = isset($_GET['price']) ? (float)$_GET['price'] : '';
$status = $_GET['status'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $treatmentId ? 'Edit Treatment' : 'Add New Treatment' ?></title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/addnewtreatment.css">
</head>
<body>
    <div class="main-container">
        <h2><?= $treatmentId ? 'Edit Treatment' : 'Add New Treatment' ?></h2>

        <form method="POST" id="addTreatmentForm">
            <?php if ($treatmentId): ?>
            <input type="hidden" name="treatment_id" value="<?= $treatmentId ?>">
            <?php endif; ?>
            <label for="treatment-name">Treatment Name <span>*</span></label>
            <input type="text" id="treatment-name" name="treatment_name" required placeholder="Enter treatment name" value="<?= htmlspecialchars($treatmentName) ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Enter treatment description"><?= htmlspecialchars($description) ?></textarea>

            <label for="duration">Duration (e.g., 30 mins, 1 hour) <span>*</span></label>
            <input type="text" id="duration" name="duration" required placeholder="Enter duration" value="<?= htmlspecialchars($duration) ?>">

            <label for="price">Price (Rs.) <span>*</span></label>
            <input type="number" id="price" name="price" required min="0" step="0.01" placeholder="Enter price" value="<?= htmlspecialchars($price) ?>">

            <label for="status">Status <span>*</span></label>
            <select id="status" name="status" required>
                <option value="" disabled <?= $status === '' ? 'selected' : '' ?>>-- Select Status --</option>
                <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>

            <div class="form-buttons">
                <button type="button" onclick="window.history.back();" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn"><?= $treatmentId ? 'Save Changes' : 'Add Treatment' ?></button>
            </div>
        </form>
    </div>
    <script>
    document.getElementById('addTreatmentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);
        const isEdit = data.get('treatment_id');
        const url = isEdit ? '/dheergayu/public/api/treatments/update' : '/dheergayu/public/api/treatments/create';
        try {
            const res = await fetch(url, { method: 'POST', body: data });
            let ok = false;
            if (res.ok) {
                try {
                    const json = await res.json();
                    ok = !!json.success;
                } catch (_) {
                    ok = true; // non-JSON but 200 OK
                }
            }
            if (ok) {
                alert(isEdit ? '✅ Treatment updated' : '✅ New treatment added');
                window.location.href = 'admintreatment.php';
            } else {
                alert('Save failed');
            }
        } catch (err) {
            alert('Save failed');
        }
    });
    </script>
</body>
</html>
