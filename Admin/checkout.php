<?php
session_start();
require 'db.php'; // Database connection

// Determine if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if (!$isLoggedIn) {
    // Redirect to login page with a message to proceed to checkout after login
    $_SESSION['redirect_to'] = 'checkout.php';
    header("Location: login_users.php?message=Please log in to proceed to checkout.");
    exit;
}

// Fetch cart items similar to cart.php
$user_id = $_SESSION['user_id'];
$cartItems = [];
$totalPrice = 0;

$cartQuery = "SELECT `order_product`.*, `product`.`name`, `product`.`price` 
              FROM `order_product` 
              JOIN `order` ON `order_product`.`order_id` = `order`.`order_id` 
              JOIN `product` ON `order_product`.`product_id` = `product`.`product_id` 
              WHERE `order`.`user_id` = ? AND `order`.`order_status` = 'pending'";
$stmt = $conn->prepare($cartQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $totalPrice += $row['price_at_order'] * $row['quantity'];
}
$stmt->close();

if (empty($cartItems)) {
    header("Location: cart.php?error=Your cart is empty.");
    exit;
}

// Handle form submission for payment (implement your payment logic here)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process payment
    // ...

    // Update order status to 'shipped' or 'completed'
    $updateOrderStmt = $conn->prepare("UPDATE `order` SET order_status = 'shipped', transaction_id = ?, payer_name = ?, payer_email = ? WHERE user_id = ? AND order_status = 'pending'");
    // Assume you have obtained these values from the payment gateway
    $transaction_id = 'TRANS123456';
    $payer_name = $_POST['payer_name'];
    $payer_email = $_POST['payer_email'];
    $updateOrderStmt->bind_param("sssi", $transaction_id, $payer_name, $payer_email, $user_id);

    if ($updateOrderStmt->execute()) {
        // Optionally, send a confirmation email
        // Redirect to a success page
        header("Location: checkout_success.php");
        exit;
    } else {
        $error = "Failed to process your order. Please try again.";
    }

    $updateOrderStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta and Title -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Honey Haven</title>
    <!-- Include Bootstrap and other necessary CSS/JS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <!-- Navbar (reuse your existing navbar code) -->
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4">Checkout</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="checkout.php" method="POST">
            <h4>Order Summary</h4>
            <ul class="list-group mb-4">
                <?php foreach ($cartItems as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($item['name']) . " x " . $item['quantity']; ?>
                        <span><?php echo number_format($item['price_at_order'] * $item['quantity'], 2); ?> USD</span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong>Total</strong>
                    <strong><?php echo number_format($totalPrice, 2); ?> USD</strong>
                </li>
            </ul>

            <h4>Payment Details</h4>
            <div class="form-group">
                <label for="payer_name">Name</label>
                <input type="text" class="form-control" id="payer_name" name="payer_name" required>
            </div>
            <div class="form-group">
                <label for="payer_email">Email</label>
                <input type="email" class="form-control" id="payer_email" name="payer_email" required>
            </div>
            <!-- Add more payment fields as needed -->

            <button type="submit" class="btn btn-primary mt-3">Pay Now</button>
        </form>
    </div>

    <!-- Footer (reuse your existing footer code) -->
    <?php include 'footer.php'; ?>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
