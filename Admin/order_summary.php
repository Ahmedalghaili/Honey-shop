<?php
session_start();
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_users.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the latest order for the logged-in user
$orderQuery = "SELECT * FROM `order` WHERE `user_id` = ? ORDER BY `order_date` DESC LIMIT 1";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();

if (!$order) {
    echo "No order found.";
    exit;
}

$order_id = $order['order_id'];
$order_date = $order['order_date'];
$total_price = $order['total_price'];
$order_status = $order['order_status'];

// Optionally, fetch order items if you have an order items table
// Example: $itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
// $itemsStmt = $conn->prepare($itemsQuery);
// $itemsStmt->bind_param("i", $order_id);
// $itemsStmt->execute();
// $itemsResult = $itemsStmt->get_result();
// $items = $itemsResult->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center">Order Summary</h2>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Order ID: <?php echo $order_id; ?></h5>
                <p class="card-text">Order Date: <?php echo $order_date; ?></p>
                <p class="card-text">Total Price: $<?php echo number_format($total_price, 2); ?></p>
                <p class="card-text">Order Status: <?php echo htmlspecialchars($order_status); ?></p>
                
                <!-- Optionally, display order items if fetched -->
                <!-- <h6>Order Items:</h6>
                <ul>
                    <?php foreach ($items as $item): ?>
                        <li><?php echo htmlspecialchars($item['product_name']); ?> (Quantity: <?php echo $item['quantity']; ?>)</li>
                    <?php endforeach; ?>
                </ul> -->

                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                <a href="orders.php" class="btn btn-secondary">View All Orders</a>
            </div>
        </div>
    </div>
</body>
</html>
