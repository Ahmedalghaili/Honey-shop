<?php
session_start();
require 'db.php';  // Assuming this contains your database connection

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_users.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Get product_id and quantity from query string
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

// Fetch product details
$query = "SELECT name, price, image_url FROM product WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $product_name = $product['name'];
    $price = $product['price'];
    $image_url = $product['image_url'];
    $total_price = $price * $quantity;
} else {
    echo "Product not found.";
    exit;
}

$stmt->close();

// Handle form submission when the user enters shipping details and confirms order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    
    // Insert order into the database
    $insertQuery = "INSERT INTO `order` (user_id, order_date, total_price, order_status, shipping_address)
                    VALUES (?, NOW(), ?, 'Pending', ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ids", $user_id, $total_price, $shipping_address);
    
    if ($insertStmt->execute()) {
        // Redirect to user's order page after placing the order
        header("Location: my_orders.php");
        exit();
    } else {
        echo "Error placing order.";
    }
    
    $insertStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Your Order - Honey Haven</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  
</head>
<body>

<?php include("./header.php"); ?>

<div class="container my-5">
    <h2 class="mb-4">Confirm Your Order</h2>

    <!-- Display Product Details with Image -->
    <div class="card mb-4">
        <div class="row g-0">
            <div class="col-md-4 text-center">
                <!-- Product Image -->
                <img src="<?php echo htmlspecialchars($image_url); ?>" class="img-fluid rounded-start p-3" alt="<?php echo htmlspecialchars($product_name); ?>">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h4 class="card-title">Product: <?php echo htmlspecialchars($product_name); ?></h4>
                    <p class="card-text">Price: $<?php echo number_format($price, 2); ?> AUD</p>
                    <p class="card-text">Quantity: <?php echo htmlspecialchars($quantity); ?></p>
                    <p class="card-text total-price">Total Price: $<?php echo number_format($total_price, 2); ?> AUD</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping Form -->
    <form method="POST">
        <div class="mb-3">
            <label for="shipping_address" class="form-label">Shipping Address</label>
            <textarea name="shipping_address" id="shipping_address" class="form-control" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Place Order</button>
    </form>
</div>

<?php include("./footer.php"); ?>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
