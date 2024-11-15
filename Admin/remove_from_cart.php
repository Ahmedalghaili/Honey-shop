<?php
session_start();
require 'db.php'; // Database connection

// Determine if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_product_id'])) {
    $order_product_id = (int) $_POST['order_product_id'];

    if ($isLoggedIn) {
        $user_id = $_SESSION['user_id'];
        $query = "DELETE `order_product` FROM `order_product`
                  JOIN `order` ON `order_product`.`order_id` = `order`.`order_id`
                  WHERE `order_product`.`order_product_id` = ? AND `order`.`user_id` = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $order_product_id, $user_id);
    } else {
        if (isset($_SESSION['session_id'])) {
            $session_id = $_SESSION['session_id'];
            // Fetch the order_id associated with the session
            $orderStmt = $conn->prepare("SELECT `order_id` FROM `order` WHERE `session_id` = ? AND `order_status` = 'pending' LIMIT 1");
            $orderStmt->bind_param("s", $session_id);
            $orderStmt->execute();
            $orderResult = $orderStmt->get_result();
            if ($orderResult->num_rows > 0) {
                $order = $orderResult->fetch_assoc();
                $order_id = $order['order_id'];

                // Now delete the order_product
                $stmt = $conn->prepare("DELETE FROM `order_product` WHERE `order_product_id` = ? AND `order_id` = ?");
                $stmt->bind_param("ii", $order_product_id, $order_id);
            } else {
                // No cart found
                header("Location: cart.php?error=Cart not found.");
                exit;
            }
            $orderStmt->close();
        } else {
            // No session_id found
            header("Location: cart.php?error=Invalid session.");
            exit;
        }
    }

    if ($stmt->execute()) {
        // Optionally, update the total price
        // Redirect back to the cart page
        header("Location: cart.php?success=Item removed successfully.");
    } else {
        // Handle error
        header("Location: cart.php?error=Failed to remove item.");
    }

    $stmt->close();
} else {
    // Invalid request
    header("Location: cart.php?error=Invalid request.");
}

$conn->close();
?>
