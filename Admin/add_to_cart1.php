<?php
session_start();
header('Content-Type: application/json'); // Set response type to JSON
require 'db.php'; // Include the database connection

$response = ['success' => false, 'message' => ''];

// Determine if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// Get user_id or generate a session_id for guests
if ($isLoggedIn) {
    $user_id = $_SESSION['user_id'];
    $session_id = null;
} else {
    // Generate a unique session ID if not already set
    if (!isset($_SESSION['session_id'])) {
        $_SESSION['session_id'] = session_id();
    }
    $session_id = $_SESSION['session_id'];
    $user_id = null;
}

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

        // Check if the cart exists
        if ($isLoggedIn) {
            // For logged-in users
            $cartQuery = "SELECT * FROM `order` WHERE user_id = ? AND order_status = 'pending' LIMIT 1";
            $cartStmt = $conn->prepare($cartQuery);
            $cartStmt->bind_param("i", $user_id);
        } else {
            // For guests
            $cartQuery = "SELECT * FROM `order` WHERE session_id = ? AND order_status = 'pending' LIMIT 1";
            $cartStmt = $conn->prepare($cartQuery);
            $cartStmt->bind_param("s", $session_id);
        }

        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();

        if ($cartResult->num_rows > 0) {
            // Cart exists, get the order ID
            $cart = $cartResult->fetch_assoc();
            $order_id = $cart['order_id'];
        } else {
            // Create a new cart
            if ($isLoggedIn) {
                $insert_order_query = "INSERT INTO `order` (user_id, total_price, order_status, shipping_address)
                                       VALUES (?, 0, 'pending', '')";
                $insert_order_stmt = $conn->prepare($insert_order_query);
                $insert_order_stmt->bind_param("i", $user_id);
            } else {
                $insert_order_query = "INSERT INTO `order` (session_id, total_price, order_status, shipping_address)
                                       VALUES (?, 0, 'pending', '')";
                $insert_order_stmt = $conn->prepare($insert_order_query);
                $insert_order_stmt->bind_param("s", $session_id);
            }

            if ($insert_order_stmt->execute()) {
                $order_id = $conn->insert_id;
            } else {
                $response['message'] = 'Error creating cart: ' . $insert_order_stmt->error;
                echo json_encode($response);
                exit;
            }

            $insert_order_stmt->close();
        }

        // Check if the product is already in the order_product table
        $check_product_query = "SELECT * FROM `order_product` WHERE order_id = ? AND product_id = ?";
        $check_product_stmt = $conn->prepare($check_product_query);
        $check_product_stmt->bind_param("ii", $order_id, $product_id);
        $check_product_stmt->execute();
        $product_result = $check_product_stmt->get_result();

        if ($product_result->num_rows > 0) {
            // Update the quantity and total price
            $existing_product = $product_result->fetch_assoc();
            $new_quantity = $existing_product['quantity'] + $quantity;
            $update_product_stmt = $conn->prepare("UPDATE `order_product` SET quantity = ?, price_at_order = ? WHERE order_id = ? AND product_id = ?");
            $update_product_stmt->bind_param("diii", $new_quantity, $price, $order_id, $product_id);

            if ($update_product_stmt->execute()) {
                // Update the total price in the order table
                $update_order_stmt = $conn->prepare("UPDATE `order` SET total_price = total_price + ? WHERE order_id = ?");
                $update_order_stmt->bind_param("di", $total_price, $order_id);
                $update_order_stmt->execute();
                $response['success'] = true;
                $response['message'] = 'Product quantity updated in cart.';
            } else {
                $response['message'] = 'Error updating product in cart: ' . $update_product_stmt->error;
            }

            $update_product_stmt->close();
        } else {
            // Insert the new product into the order_product table
            $insert_order_product_query = "INSERT INTO `order_product` (order_id, product_id, quantity, price_at_order)
                                           VALUES (?, ?, ?, ?)";
            $insert_order_product_stmt = $conn->prepare($insert_order_product_query);
            $insert_order_product_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);

            if ($insert_order_product_stmt->execute()) {
                // Update the total price in the order table
                $update_order_stmt = $conn->prepare("UPDATE `order` SET total_price = total_price + ? WHERE order_id = ?");
                $update_order_stmt->bind_param("di", $total_price, $order_id);
                $update_order_stmt->execute();
                $response['success'] = true;
                $response['message'] = 'Product added to cart.';
            } else {
                $response['message'] = 'Error adding product to cart: ' . $insert_order_product_stmt->error;
            }

            $insert_order_product_stmt->close();
        }

        $check_product_stmt->close();
        $cartStmt->close();
    } else {
        $response['message'] = 'Product not found.';
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request.';
}

$conn->close();

echo json_encode($response);
?>
