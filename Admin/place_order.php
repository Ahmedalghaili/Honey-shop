<?php
require 'db.php';  // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $shipping_address = $_POST['shipping_address'];
    $order_date = date('Y-m-d H:i:s');  // Set order date as current date-time
    $products = $_POST['products'];  // Array of products with id, quantity, price

    // Step 1: Insert into orders table
    $insertOrderQuery = "INSERT INTO orders (user_id, order_date, total_price, order_status, shipping_address) VALUES (?, ?, ?, 'pending', ?)";
    $stmt = $conn->prepare($insertOrderQuery);

    $total_price = array_reduce($products, function ($sum, $product) {
        return $sum + ($product['price'] * $product['quantity']);
    }, 0);

    $stmt->bind_param('isds', $user_id, $order_date, $total_price, $shipping_address);
    $stmt->execute();

    // Get the last inserted order ID
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Step 2: Insert each product into order_product table
    $insertOrderProductQuery = "INSERT INTO order_product (order_id, product_id, quantity, price_at_order) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertOrderProductQuery);

    foreach ($products as $product) {
        $stmt->bind_param('iiid', $order_id, $product['product_id'], $product['quantity'], $product['price']);
        $stmt->execute();
    }

    $stmt->close();

    // Step 3: Redirect to order confirmation page
    header('Location: order_confirmation.php?order_id=' . $order_id);
    exit;
} else {
    echo "Invalid request method.";
}
?>
<form action="place_order.php" method="POST">
    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">  <!-- Assume user_id is retrieved from session -->

    <label for="shipping_address">Shipping Address:</label>
    <textarea name="shipping_address" id="shipping_address" required></textarea>

    <h3>Products:</h3>
    <div id="product-list">
        <!-- Assuming this is dynamically generated based on the selected products -->
        <input type="hidden" name="products[0][product_id]" value="<?php echo $product['product_id']; ?>">
        <input type="hidden" name="products[0][price]" value="<?php echo $product['price']; ?>">
        <label for="quantity">Quantity:</label>
        <input type="number" name="products[0][quantity]" value="1" min="1">
    </div>

    <button type="submit">Place Order</button>
</form>
