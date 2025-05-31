<?php
session_start();
require_once 'config.php';

if (isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);

    // Get reservation details
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();

    if ($reservation) {
        // Insert into archived_reservations
        $archive_stmt = $conn->prepare("INSERT INTO archived_reservations (name, phone_number, guests, reservation_date, reservation_time, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $archive_stmt->bind_param("ssisss", 
            $reservation['name'],
            $reservation['phone_number'],
            $reservation['guests'],
            $reservation['reservation_date'],
            $reservation['reservation_time'],
            $reservation['notes']
        );
        $archive_stmt->execute();

        // Delete from reservations
        $delete_stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
        $delete_stmt->bind_param("i", $reservation_id);
        $delete_stmt->execute();

        // Redirect with success
        header("Location: admindashboard.php?archived=success");
        exit();
    } else {
        echo "Reservation not found.";
    }
} else {
    echo "Invalid request.";
}
?>
