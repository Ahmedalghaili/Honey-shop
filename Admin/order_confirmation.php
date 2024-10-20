<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h2>Thank You for Your Purchase!</h2>
        <p>Your payment was successful.</p>

        <!-- Display order summary -->
        <div class="card">
            <div class="card-header">Order Summary</div>
            <div class="card-body">
                <p><strong>Order ID:</strong> 12345</p>
                <p><strong>Total Paid:</strong> $150.00 AUD</p>
                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($_GET['transaction_id']); ?></p>
                <p><strong>Shipping Address:</strong> 123 Example St, City, Country</p>
            </div>
        </div>

        <!-- Button to download invoice -->
        <a href="generate_invoice.php?order_id=12345" class="btn btn-primary mt-3">Download Invoice</a>

        <!-- CTA buttons -->
        <a href="track_order.php?order_id=12345" class="btn btn-success mt-3">Track Order</a>
        <a href="shop.php" class="btn btn-secondary mt-3">Continue Shopping</a>
    </div>
</body>
</html>
