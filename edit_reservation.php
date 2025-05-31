<?php
require 'config.php';

if (!isset($_GET['id'])) {
    echo "Reservation ID is missing.";
    exit();
}


$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'];
    $phone = $_POST['phone_number'];
    $guests = $_POST['guests'];
    $date = $_POST['reservation_date'];
    $time = $_POST['reservation_time'];
    $notes = $_POST['notes'];


    $reservation_date = strtotime($fetched_reservation['reservation_date']);
$now = time();
$diff = $reservation_date - $now;

if ($diff <= 86400) {
    $_SESSION['page_error_message'] = "You can't edit this reservation because it's less than 1 day away.";
    header("Location: user_dashboard.php"); // Adjust to your actual dashboard page
    exit();
}


    // Update query
    $stmt = $conn->prepare("UPDATE reservations SET name=?, phone_number=?, guests=?, reservation_date=?, reservation_time=?, notes=? WHERE id=?");
    $stmt->bind_param("ssisssi", $name, $phone, $guests, $date, $time, $notes, $id);
    $stmt->execute();

    header("Location: admindashboard.php");
    exit();
}

// Fetch existing data
$result = $conn->query("SELECT * FROM reservations WHERE id = $id");
$reservation = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Reservation</title>
    <style>
        body {
            font-family: Arial; padding: 30px; background: #f5f5f5;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Edit Reservation</h2>
    <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?= $reservation['name'] ?>" required>

        <label>Phone Number</label>
        <input type="text" name="phone_number" value="<?= $reservation['phone_number'] ?>" required>

        <label>Guests</label>
        <input type="number" name="guests" value="<?= $reservation['guests'] ?>" required>

        <label>Reservation Date</label>
        <input type="date" name="reservation_date" value="<?= $reservation['reservation_date'] ?>" required>

        <label>Reservation Time</label>
        <input type="time" name="reservation_time" value="<?= $reservation['reservation_time'] ?>" required>

        <label>Notes</label>
        <textarea name="notes"><?= $reservation['notes'] ?></textarea>

        <button type="submit">Update Reservation</button>
    </form>
</body>
</html>
