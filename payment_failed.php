<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Failed</title>
    <style>
        body {
            background: linear-gradient(135deg, #ff6a6a, #8b0000);
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .error-box {
            background-color: #ffffff10;
            padding: 30px 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        h1 {
            color: #ffdede;
        }
        p {
            font-size: 18px;
        }
        a {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>Payment Failed</h1>
        <p>We were unable to process your payment.</p>
        <p>Please try again or use a different payment method.</p>
        <p><a href="payment.php">Return to Payment</a></p>
    </div>
</body>
</html>