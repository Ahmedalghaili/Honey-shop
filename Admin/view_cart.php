<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php'; // Database connection

$cartItems = [];

// If the user is logged in, fetch cart items from the database
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $user_id = $_SESSION['user_id'];
    $cartQuery = "SELECT product.*, cart.quantity FROM cart JOIN product ON cart.product_id = product.product_id WHERE cart.user_id = ?";
    $stmt = $conn->prepare($cartQuery);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
    }
    $stmt->close();
} else {
    // If not logged in, fetch cart items from session
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $productQuery = "SELECT * FROM product WHERE product_id = ?";
            $stmt = $conn->prepare($productQuery);
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $row['quantity'] = $quantity;
                $cartItems[] = $row;
            }
            $stmt->close();
        }
    }
}

// Display cart items
echo "<h1>Your Cart</h1>";
if (count($cartItems) > 0) {
    foreach ($cartItems as $item) {
        echo "<p>{$item['name']} - Quantity: {$item['quantity']} - Price: {$item['price']} USD</p>";
    }
} else {
    echo "<p>Your cart is empty.</p>";
}

$conn->close();
?>
