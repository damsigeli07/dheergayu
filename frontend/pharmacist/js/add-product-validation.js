const form = document.querySelector('form');
const manuDateInput = document.getElementById('product-manufacture');
const expiryDateInput = document.getElementById('product-expiry');

form.addEventListener('submit', function(e) {
    const today = new Date();
    today.setHours(0,0,0,0);

    const manuDate = new Date(manuDateInput.value);
    const expiryDate = new Date(expiryDateInput.value);

    if (manuDate > today) {
        alert("Manufacturing date cannot be in the future.");
        e.preventDefault();
        return;
    }

    if (expiryDate < today) {
        alert("Expiry date cannot be before today.");
        e.preventDefault();
        return;
    }

    if (expiryDate < manuDate) {
        alert("Expiry date cannot be before manufacturing date.");
        e.preventDefault();
        return;
    }
});
