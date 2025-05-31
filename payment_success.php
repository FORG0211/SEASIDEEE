<?php
// payment_success.php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    header("Location: reservation.php");
    exit();
}

$name = $_SESSION['reservation_name'];
$date = $_SESSION['reservation_date'];
$time = $_SESSION['reservation_time'];
$guests = $_SESSION['reservation_guests'];
$amount = $guests * 250;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
    <style>
        body {
            background: linear-gradient(135deg, #2e938e, #2e6193);
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .success-box {
            background-color: #ffffff10;
            padding: 30px 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        h1 {
            color: #a1ffce;
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
    <div class="success-box">
        <h1>Thank You for Your Payment!</h1>
        <p>Your reservation for <strong><?= htmlspecialchars($guests) ?></strong> guest(s) on <strong><?= htmlspecialchars($date) ?></strong> at <strong><?= htmlspecialchars($time) ?></strong> has been confirmed.</p>
        <p>Total Paid: <strong>PHP <?= number_format($amount, 2) ?></strong></p>
        <p>We look forward to serving you at Seaside Floating Restaurant!</p>
        <p><a href="user_page.php">Back to Home</a></p>
    </div>
</body>
</html>