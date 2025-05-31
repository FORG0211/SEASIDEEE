<?php
session_start();
require_once 'config.php'; // your DB connection file

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Delete query
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Reservation deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete reservation.";
    }

    $stmt->close();
}

header("Location: admindashboard.php"); // replace with your actual dashboard filename
exit();
