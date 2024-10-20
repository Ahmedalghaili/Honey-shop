<?php
session_start();
require 'db.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo 'Please log in to add items to your cart.';
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Check if product ID and quantity are sent via POST
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    // Ensure the product exists
    $stmt = $conn->prepare("SELECT * FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $price = $product['price'];
        $total_price = $price * $quantity;

        // Step 1: Insert the order into the 'order' table
        $insert_order_query = "INSERT INTO `order` (user_id, total_price, order_status, shipping_address)
                               VALUES (?, ?, 'pending', '')";
        $insert_order_stmt = $conn->prepare($insert_order_query);
        $insert_order_stmt->bind_param("id", $user_id, $total_price);

        if ($insert_order_stmt->execute()) {
            // Get the newly inserted order ID
            $order_id = $conn->insert_id;

            // Step 2: Insert into the 'order_product' table to link products to the order
            $insert_order_product_query = "INSERT INTO `order_product` (order_id, product_id, quantity, price_at_order)
                                           VALUES (?, ?, ?, ?)";
            $insert_order_product_stmt = $conn->prepare($insert_order_product_query);
            $insert_order_product_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);

            if ($insert_order_product_stmt->execute()) {
                echo 'Product added to cart.';
            } else {
                echo 'Error adding product to order_product: ' . $insert_order_product_stmt->error;
            }

            $insert_order_product_stmt->close();
        } else {
            echo 'Error adding order: ' . $insert_order_stmt->error;
        }

        $insert_order_stmt->close();
    } else {
        echo 'Product not found.';
    }

    $stmt->close();
} else {
    echo 'Invalid request.';
}

$conn->close();
?>
