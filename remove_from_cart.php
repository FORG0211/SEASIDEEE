<?php
session_start();
include('db.php');

if (isset($_POST['cart_item_id'])) {
    $cart_item_id = $_POST['cart_item_id'];

    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->execute([$cart_item_id]);

    header("Location: cart.php");
    exit();
}
?>
