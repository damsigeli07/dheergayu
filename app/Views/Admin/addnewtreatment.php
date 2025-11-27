<?php
require_once __DIR__ . '/../../../core/bootloader.php';

// Fetch treatment data from database if editing
$treatmentId = isset($_GET['treatment_id']) ? (int)$_GET['treatment_id'] : 0;
$treatmentName = '';
$description = '';
$duration = '';
$price = '';
$status = '';
$currentImage = '';

if ($treatmentId > 0) {
    // Fetch treatment data from database
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    if (!$db->connect_error) {
        $stmt = $db->prepare("SELECT treatment_id, treatment_name, description, duration, price, image, status FROM treatment_list WHERE treatment_id = ?");
        $stmt->bind_param('i', $treatmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $treatment = $result->fetch_assoc();
        $stmt->close();
        $db->close();
        
        if ($treatment) {
            $treatmentName = $treatment['treatment_name'] ?? '';
            $description = $treatment['description'] ?? '';
            $duration = $treatment['duration'] ?? '';
            $price = $treatment['price'] ?? '';
            $status = $treatment['status'] ?? '';
            $currentImage = $treatment['image'] ?? '';
        }
    }
}
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

        <form method="POST" id="addTreatmentForm" enctype="multipart/form-data">
            <?php if ($treatmentId): ?>
            <input type="hidden" name="treatment_id" value="<?= $treatmentId ?>">
            <?php endif; ?>
            <label for="treatment-name">Treatment Name <span>*</span></label>
            <input type="text" id="treatment-name" name="treatment_name" required placeholder="Enter treatment name" value="<?= htmlspecialchars($treatmentName) ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Enter treatment description"><?= htmlspecialchars($description) ?></textarea>

            <label for="duration">Duration (e.g., 30 minutes, 60 minutes) <span>*</span></label>
            <input type="text" id="duration" name="duration" required placeholder="Enter duration" value="<?= htmlspecialchars($duration) ?>">

            <label for="price">Price (Rs.) <span>*</span></label>
            <input type="number" id="price" name="price" required min="0" step="0.01" placeholder="Enter price" value="<?= htmlspecialchars($price) ?>">

            <label for="treatment-image">Treatment Image</label>
            <input type="file" id="treatment-image" name="treatment_image" accept="image/*">
            <small style="display: block; margin-top: 5px; color: #666;">Upload an image for this treatment (JPG, PNG, JPEG). Leave empty to keep current image when editing.</small>
            <?php if ($treatmentId > 0 && $currentImage): ?>
                <div style="margin-top: 10px;">
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">Current Image:</p>
                    <img src="<?= htmlspecialchars($currentImage) ?>" alt="Current treatment image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>
            <div id="image-preview" style="margin-top: 10px; display: none;">
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">New Image Preview:</p>
                <img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
            </div>

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
    // Image preview functionality
    document.getElementById('treatment-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewDiv = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewDiv.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewDiv.style.display = 'none';
        }
    });

    // Form submission
    document.getElementById('addTreatmentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);
        const isEdit = data.get('treatment_id');
        const url = '/dheergayu/app/Controllers/TreatmentController.php';
        try {
            const res = await fetch(url, { method: 'POST', body: data });
            let ok = false;
            if (res.ok) {
                try {
                    const json = await res.json();
                    ok = !!json.success;
                    if (!ok && json.message) {
                        alert('Error: ' + json.message);
                    }
                } catch (_) {
                    ok = true; // non-JSON but 200 OK
                }
            }
            if (ok) {
                alert(isEdit ? '✅ Treatment updated' : '✅ New treatment added');
                window.location.href = 'admintreatment.php';
            } else {
                alert('Save failed. Please check the console for details.');
            }
        } catch (err) {
            console.error('Error:', err);
            alert('Save failed: ' + err.message);
        }
    });
    </script>
</body>
</html>
