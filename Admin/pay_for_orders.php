<?php 
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_users.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $payer_name = $_POST['payer_name'];
    $payer_email = $_POST['payer_email'];
    $shipping_address = $_POST['shipping_address'];
    $orders = $_POST['orders']; // Array of selected orders

    // Process each order
    foreach ($orders as $order_id) {
        // Update the order with payer details and shipping info
        $update_query = "UPDATE `order` 
                         SET payer_name = ?, payer_email = ?, shipping_address = ?
                         WHERE order_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssii", $payer_name, $payer_email, $shipping_address, $order_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to PayPal payment or another payment processing page
    echo "<script>alert('Order details updated. Proceeding to payment.'); window.location.href='payment_page.php';</script>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay for Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        .pay-section {
            max-width: 600px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .order-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 15px;
            padding: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }
        .pay-button {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include('./Header.php'); ?>

    <div class="container my-5">
        <h2>Pay for Orders</h2>
        <div class="row">
            <!-- Shipping and Payer Information Section -->
            <div class="col-md-7">
                <h3>Shipping and Payment Information</h3>
                <form method="POST" action="pay_for_orders.php">
                    <!-- Payer Name -->
                    <div class="mb-3">
                        <label for="payerName" class="form-label">Payer Name</label>
                        <input type="text" class="form-control" id="payerName" name="payer_name" required>
                    </div>
                    <!-- Payer Email -->
                    <div class="mb-3">
                        <label for="payerEmail" class="form-label">Payer Email</label>
                        <input type="email" class="form-control" id="payerEmail" name="payer_email" required>
                    </div>
                    <!-- Shipping Address -->
                    <div class="mb-3">
                        <label for="shippingAddress" class="form-label">Shipping Address</label>
                        <input type="text" class="form-control" id="shippingAddress" name="shipping_address" required>
                    </div>

                    <!-- Selected Orders Section -->
                    <div class="pay-section">
                        <h3>Your Selected Orders</h3>
                        <ul>
                            <?php
                            // Display the selected orders
                            foreach ($_POST['orders'] as $order_id) {
                                echo "<li>Order ID: $order_id</li>";
                            }
                            ?>
                        </ul>
                    </div>

                    <!-- Hidden input to carry forward selected orders -->
                    <?php foreach ($_POST['orders'] as $order_id): ?>
                        <input type="hidden" name="orders[]" value="<?php echo $order_id; ?>">
                    <?php endforeach; ?>

                    <!-- Submit form -->
                    <button type="submit" class="btn btn-primary">Pay Now</button>
                </form>
            </div>

            <!-- PayPal Payment Section -->
            <div class="col-md-5">
                <div class="pay-section">
                    <h3>Payment via PayPal</h3>
                    <p>After entering your details, click the Pay Now button to proceed with PayPal payment.</p>
                </div>
            </div>
        </div>
    </div>

    <?php include('./footer.php'); ?>
</body>
</html>
