<?php
session_start();
require 'db.php'; // Make sure to include your database connection

// Ensure that order IDs are provided in the URL
if (!isset($_GET['order_ids'])) {
    header("Location: some_error_page.php"); // Redirect if order IDs are not provided
    exit;
}

// Retrieve the order IDs from the URL
$order_ids = explode(',', $_GET['order_ids']);
$payer_name = htmlspecialchars($_GET['payer_name']);
$payer_email = htmlspecialchars($_GET['payer_email']);
$shipping_address = htmlspecialchars($_GET['shipping_address']);

$total_price = 0;
$product_details = [];

// Prepare a SQL query to fetch product details and prices for the selected orders
$order_query = "SELECT p.name AS product_name, op.price_at_order 
                FROM `order_product` op 
                JOIN `product` p ON op.product_id = p.product_id 
                WHERE op.order_id IN (" . implode(',', array_fill(0, count($order_ids), '?')) . ")"; // Prepare placeholders

$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param(str_repeat('i', count($order_ids)), ...$order_ids); // Dynamically bind order IDs
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows > 0) {
    while ($row = $order_result->fetch_assoc()) {
        $total_price += $row['price_at_order']; // Aggregate the price
        $product_details[] = [
            'name' => $row['product_name'],
            'price' => $row['price_at_order']
        ];
    }
}
$order_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container my-5">
    <h1>Payment Details</h1>
    
    <h3>Payer Information</h3>
    <p><strong>Name:</strong> <?php echo $payer_name; ?></p>
    <p><strong>Email:</strong> <?php echo $payer_email; ?></p>
    <p><strong>Shipping Address:</strong> <?php echo $shipping_address; ?></p>
    
    <h3>Order Summary</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Price (AUD)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($product_details)): ?>
                <tr>
                    <td colspan="2">No products selected.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($product_details as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <h4>Total Price: $<?php echo number_format($total_price, 2); ?> AUD</h4>
    
    <!-- PayPal Button -->
    <div id="paypal-button-container"></div>
</div>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=AUD"></script>
<script>
    // Create PayPal button and handle payment
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?php echo $total_price; ?>'
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Redirect to PHP script for further processing or display success
                window.location.href = 'process_paypal_payment.php?orderID=' + data.orderID;
            });
        },
        onError: function (err) {
            console.error(err);
            alert('An error occurred during the payment process. Please try again.');
        }
    }).render('#paypal-button-container'); // Renders PayPal button
</script>
</body>
</html>
