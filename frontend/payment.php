<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Confirm and Pay</title>
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <div class="header">
        CONFIRM AND PAY
        <div class="user-icon" onclick="showUserMenu()" title="User Menu">👤</div>
    </div>

    <div class="container">
        <!-- Booking Summary Card -->
        <div class="card">
            <h2 class="card-title">Booking Summary</h2>
            
            <div class="booking-summary">
                <div class="summary-row">
                    <span class="summary-label">Consultation Fee</span>
                    <span class="summary-value fee-highlight">Rs 560</span>
                </div>
            </div>

            <h3 style="color: #8B4513; margin-bottom: 15px; font-size: 1.1em;">Patient Details</h3>
            <div class="summary-row">
                <span class="summary-label">Patient name:</span>
                <span class="summary-value">John Doe</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Date:</span>
                <span class="summary-value">9th May 2025</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Time Slot:</span>
                <span class="summary-value">9am - 1pm</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Doctor:</span>
                <span class="summary-value">Dr. Smith</span>
            </div>

            <div class="total-section">
                <div class="total-row">
                    <span>Total Amount</span>
                    <span class="total-amount">Rs 560</span>
                </div>
            </div>
        </div>

        <!-- Payment Information Card -->
        <div class="card">
            <h2 class="card-title">Payment Information</h2>
            
            <form id="paymentForm">
                <div class="form-group">
                    <label for="cardNumber">Card Number</label>
                    <div class="card-input">
                        <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" required>
                        <div class="card-icon">VISA</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="expiryDate">Expiry Date</label>
                        <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label for="cvc">CVC</label>
                        <input type="text" id="cvc" name="cvc" placeholder="123" maxlength="3" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cardholderName">Cardholder Name</label>
                    <input type="text" id="cardholderName" name="cardholderName" placeholder="Enter name on card" required>
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <select id="country" name="country" required>
                        <option value="">Select country</option>
                        <option value="LK">Sri Lanka</option>
                        <option value="IN">India</option>
                        <option value="US">United States</option>
                        <option value="UK">United Kingdom</option>
                        <option value="AU">Australia</option>
                    </select>
                </div>

                <div class="checkbox-group">
                    <div class="checkbox-container">
                        <input type="checkbox" id="saveCard" name="saveCard">
                        <label for="saveCard" class="checkbox-label">Save payment information safely and securely</label>
                    </div>
                </div>

                <div class="payment-buttons">
                    <button type="submit" class="pay-btn">Pay Now</button>
                    <button type="button" class="cancel-btn" onclick="cancelPayment()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showUserMenu() {
            alert('User menu options:\n• Profile\n• My Appointments\n• Settings\n• Logout');
        }

        function cancelPayment() {
            if (confirm('Are you sure you want to cancel this payment?')) {
                alert('Payment cancelled. Returning to booking page...');
            }
        }

        // Format card number input
        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            let formattedValue = value.replace(/(.{4})/g, '$1 ').trim();
            
            if (formattedValue.length <= 19) {
                e.target.value = formattedValue;
            }

            // Update card icon based on card number
            const cardIcon = document.querySelector('.card-icon');
            if (value.startsWith('4')) {
                cardIcon.textContent = 'VISA';
                cardIcon.style.background = '#1a73e8';
            } else if (value.startsWith('5')) {
                cardIcon.textContent = 'MC';
                cardIcon.style.background = '#eb001b';
            } else {
                cardIcon.textContent = 'CARD';
                cardIcon.style.background = '#666';
            }
        });

        // Format expiry date input
        document.getElementById('expiryDate').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.replace(/(\d{2})(\d+)/, '$1/$2');
            }
            e.target.value = value;
        });

        // Format CVC input
        document.getElementById('cvc').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
            const expiryDate = document.getElementById('expiryDate').value;
            const cvc = document.getElementById('cvc').value;
            const cardholderName = document.getElementById('cardholderName').value;
            const country = document.getElementById('country').value;
            
            // Basic validation
            if (cardNumber.length < 13 || cardNumber.length > 19) {
                alert('Please enter a valid card number!');
                return;
            }
            
            if (expiryDate.length !== 5) {
                alert('Please enter a valid expiry date (MM/YY)!');
                return;
            }
            
            if (cvc.length < 3) {
                alert('Please enter a valid CVC!');
                return;
            }
            
            if (!cardholderName.trim()) {
                alert('Please enter the cardholder name!');
                return;
            }
            
            if (!country) {
                alert('Please select your country!');
                return;
            }
            
            // Simulate payment process
            const payBtn = document.querySelector('.pay-btn');
            payBtn.textContent = 'PROCESSING...';
            payBtn.disabled = true;
            
            setTimeout(() => {
                alert('Payment successful!\n\nTransaction Details:\nAmount: Rs 560\nTransaction ID: TXN' + Math.random().toString(36).substr(2, 9).toUpperCase() + '\n\nYour appointment is confirmed. You will receive a confirmation email shortly.');
                payBtn.textContent = 'PAYMENT SUCCESSFUL ✓';
                payBtn.style.background = '#5CB85C';
                
                setTimeout(() => {
                    alert('Redirecting to appointment details...');
                }, 2000);
            }, 3000);
        });

        // Real-time validation
        const inputs = document.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#5CB85C';
                } else {
                    this.style.borderColor = '#e1e5e9';
                }
            });
        });

        // Expiry date validation
        document.getElementById('expiryDate').addEventListener('blur', function() {
            const value = this.value;
            if (value.length === 5) {
                const [month, year] = value.split('/');
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear() % 100;
                const currentMonth = currentDate.getMonth() + 1;
                
                const inputMonth = parseInt(month);
                const inputYear = parseInt(year);
                
                if (inputMonth < 1 || inputMonth > 12) {
                    this.style.borderColor = '#dc3545';
                    alert('Please enter a valid month (01-12)');
                } else if (inputYear < currentYear || (inputYear === currentYear && inputMonth < currentMonth)) {
                    this.style.borderColor = '#dc3545';
                    alert('Card has expired. Please enter a valid expiry date.');
                } else {
                    this.style.borderColor = '#5CB85C';
                }
            }
        });

        // Auto-uppercase cardholder name
        document.getElementById('cardholderName').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>