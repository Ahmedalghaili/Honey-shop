<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php'; // Database connection

// Get product ID and quantity from the POST request
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Validate product ID and quantity
if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity.']);
    exit;
}

// Check if the user is logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // User is logged in, add to cart in database
    $user_id = $_SESSION['user_id']; // Retrieve user ID from session

    // Check if the product exists in the cart
    $cartCheckQuery = "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($cartCheckQuery);
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if product already in cart
        $cartUpdateQuery = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($cartUpdateQuery);
        $stmt->bind_param('iii', $quantity, $user_id, $product_id);
    } else {
        // Insert new item in the cart
        $cartInsertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($cartInsertQuery);
        $stmt->bind_param('iii', $user_id, $product_id, $quantity);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product added to cart successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product to cart.']);
    }

    $stmt->close();
    $conn->close();
} else {
    // User is not logged in, add to session-based cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = []; // Initialize cart if not set
    }

    // Check if the product already exists in the session cart
    if (isset($_SESSION['cart'][$product_id])) {
        // If it exists, increase the quantity
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        // If it doesn't exist, add it to the cart
        $_SESSION['cart'][$product_id] = $quantity;
    }

    echo json_encode(['success' => true, 'message' => 'Product added to cart successfully!']);
}
?>
