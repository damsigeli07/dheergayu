<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Dheergayu</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: #4CAF50;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease;
        }

        .success-icon::before {
            content: '✓';
            font-size: 60px;
            color: white;
            font-weight: bold;
        }

        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .order-id {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            margin: 25px 0;
            font-family: monospace;
            font-size: 1.1rem;
            color: #666;
        }

        .order-id strong {
            color: #333;
        }

        .message {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #8B7355, #A0916B);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            margin: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(139, 115, 85, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .info-box {
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            border-radius: 5px;
            margin: 25px 0;
            text-align: left;
        }

        .info-box h3 {
            color: #2e7d32;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .info-box p {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon"></div>
        
        <h1>Payment Successful!</h1>
        
        <div class="order-id">
            <strong>Order ID:</strong> 
            <?php echo htmlspecialchars($_GET['order_id'] ?? 'N/A'); ?>
        </div>

        <p class="message">
            Thank you for your purchase! Your order has been confirmed and will be processed shortly.
        </p>

        <div class="info-box">
            <h3>📧 Confirmation Email Sent</h3>
            <p>We've sent an order confirmation email to your registered email address. Please check your inbox (and spam folder).</p>
        </div>

        <div class="info-box">
            <h3>📦 Delivery Information</h3>
            <p>Your order will be delivered within 3-5 business days. You'll receive a tracking number via SMS once your order is dispatched.</p>
        </div>

        <div style="margin-top: 40px;">
            <a href="/dheergayu/app/Views/Patient/home.php" class="btn">Back to Home</a>
            <a href="/dheergayu/app/Views/Patient/products.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
    <script>
        (async function finalizeSandboxPayment() {
            const params = new URLSearchParams(window.location.search);
            const orderId = params.get('order_id');
            if (!orderId) return;

            try {
                const formData = new FormData();
                formData.append('order_id', orderId);

                const response = await fetch('/dheergayu/public/api/finalize-payment-local.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (!data.success) {
                    console.warn('Local finalize skipped:', data.error || 'unknown error');
                }
            } catch (error) {
                console.warn('Local finalize failed:', error);
            }
        })();
    </script>
</body>
</html>