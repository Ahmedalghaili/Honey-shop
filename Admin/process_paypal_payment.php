<?php
require 'db.php'; // Include your database connection

// Start session to access the session variables
session_start();

$payment_success = false; // Variable to track if payment is successful
$success_message = '';
$error_message = '';

// Ensure that PayPal sent an order ID via the GET request
if (isset($_GET['orderID']) && isset($_GET['paypalOrderID'])) {
    $order_id = intval($_GET['orderID']);
    $paypal_order_id = $_GET['paypalOrderID'];

    // Verify that the order ID exists and belongs to the logged-in user
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // 1. Update the order status to 'shipped' and save the PayPal transaction ID
        $update_order_query = "
            UPDATE `order` 
            SET order_status = 'shipped', transaction_id = ?, payer_name = IFNULL(payer_name, ''), payer_email = IFNULL(payer_email, ''), shipping_address = IFNULL(shipping_address, '') 
            WHERE order_id = ? AND user_id = ?
        ";
        $stmt = $conn->prepare($update_order_query);
        if ($stmt) {
            $stmt->bind_param('sii', $paypal_order_id, $order_id, $user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    // 2. Update the status of the products to 'shipped' in the `order_product` table
                    $update_product_query = "UPDATE `order_product` SET product_status = 'shipped' WHERE order_id = ?";
                    $product_stmt = $conn->prepare($update_product_query);
                    if ($product_stmt) {
                        $product_stmt->bind_param('i', $order_id);
                        if ($product_stmt->execute()) {
                            $payment_success = true;
                            $success_message = "Payment successful! Your order has been shipped.";
                        } else {
                            $error_message = "Failed to update product status.";
                        }
                        $product_stmt->close();
                    } else {
                        // Handle prepare statement error
                        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                        $error_message = "An error occurred while updating product status.";
                    }
                } else {
                    $error_message = "No matching order found or order already processed.";
                }
            } else {
                $error_message = "Failed to update order status. Please contact support.";
            }
            $stmt->close();
        } else {
            // Handle prepare statement error
            error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $error_message = "An error occurred while processing your payment.";
        }
    } else {
        $error_message = "User session not found. Please log in again.";
    }
} else {
    $error_message = "Invalid payment. No order ID provided.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container my-5">
        <?php if ($payment_success): ?>
            <!-- Payment success message -->
            <div class="alert alert-success">
                <h2>Thank you for your payment!</h2>
                <p><?php echo htmlspecialchars($success_message); ?></p>
                <a href="my_orders.php" class="btn btn-primary">View Your Orders</a>
            </div>
        <?php else: ?>
            <!-- Payment failure message -->
            <div class="alert alert-danger">
                <h2>Payment Failed</h2>
                <p><?php echo htmlspecialchars($error_message); ?></p>
                <a href="my_orders.php" class="btn btn-danger">Back to Orders</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
