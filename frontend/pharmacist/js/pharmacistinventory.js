document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.add-stock-form');
    const mfdInput = document.getElementById('mfd');
    const expInput = document.getElementById('exp');

    form.addEventListener('submit', (e) => {
        e.preventDefault(); // Prevent actual form submission

        const today = new Date();
        today.setHours(0,0,0,0); // normalize time
        const mfdDate = new Date(mfdInput.value);
        const expDate = new Date(expInput.value);

        if (mfdDate > today) {
            alert('Manufacturing date cannot be in the future.');
            mfdInput.focus();
            return;
        }

        if (expDate < today) {
            alert('Expiry date cannot be before today.');
            expInput.focus();
            return;
        }

        alert('Stock added successfully!');
        form.reset();
    });
});
