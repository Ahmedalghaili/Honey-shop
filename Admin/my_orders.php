<?php
session_start();
require 'db.php'; // Include your database connection

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_users.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Handle Delete Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
    $order_id = $_POST['delete_order_id'];

    // Corrected table name to 'order' (singular)
    $delete_query = "DELETE FROM `order` WHERE order_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    if ($delete_stmt) {
        $delete_stmt->bind_param("ii", $order_id, $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    } else {
        // Handle prepare statement error
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
}

// Handle Proceed to Payment
$total_price = 0; // Initialize total price
$product_details = []; // Array to store product names and prices
$error_message = ''; // To store any error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_payment'])) {
    // Retrieve and sanitize input data
    $payer_name = trim($_POST['payer_name']);
    $payer_email = trim($_POST['payer_email']);
    $shipping_address = trim($_POST['shipping_address']);
    $selected_orders = $_POST['orders'] ?? []; // Get selected orders

    if (empty($payer_name) || empty($payer_email) || empty($shipping_address)) {
        $error_message = "Please fill in all the required payer and shipping information.";
    } elseif (!filter_var($payer_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (empty($selected_orders)) {
        $error_message = "No orders selected for payment.";
    } else {
        // Proceed with payment processing
        // For simplicity, we'll handle one order at a time
        $order_id = $selected_orders[0];
        $_SESSION['order_id'] = $order_id; // Save the order ID in session for later use

        // Update the order with payer information
        $update_order_query = "
            UPDATE `order` 
            SET payer_name = ?, payer_email = ?, shipping_address = ? 
            WHERE order_id = ? AND user_id = ?
        ";
        $update_stmt = $conn->prepare($update_order_query);
        if ($update_stmt) {
            $update_stmt->bind_param("sssii", $payer_name, $payer_email, $shipping_address, $order_id, $user_id);
            $update_success = $update_stmt->execute();
            $update_stmt->close();

            if ($update_success) {
                // Retrieve product details and calculate total price
                $order_query = "
                    SELECT p.name AS product_name, op.price_at_order, op.quantity
                    FROM `order_product` op 
                    JOIN `product` p ON op.product_id = p.product_id 
                    WHERE op.order_id = ?
                ";

                $order_stmt = $conn->prepare($order_query);
                if ($order_stmt) {
                    $order_stmt->bind_param("i", $order_id);
                    $order_stmt->execute();
                    $order_result = $order_stmt->get_result();

                    if ($order_result->num_rows > 0) {
                        while ($row = $order_result->fetch_assoc()) {
                            $total_price += $row['price_at_order'] * $row['quantity']; // Aggregate the price
                            $product_details[] = [
                                'name' => $row['product_name'],
                                'price' => $row['price_at_order'],
                                'quantity' => $row['quantity']
                            ];
                        }
                    } else {
                        $error_message = "No products found for the selected order.";
                    }

                    $order_stmt->close();
                } else {
                    // Handle prepare statement error
                    error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                    $error_message = "An error occurred while retrieving product details.";
                }
            } else {
                $error_message = "Failed to update order with payer information. Please try again.";
            }
        } else {
            // Handle prepare statement error
            error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $error_message = "An error occurred while saving your information.";
        }
    }
}

// Function to fetch orders based on status
function fetchOrders($conn, $user_id, $status) {
    $query = "SELECT o.order_id, o.order_date, o.total_price, o.order_status 
              FROM `order` o 
              WHERE o.user_id = ? AND o.order_status = ?
              ORDER BY o.order_date DESC";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $status);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        $stmt->close();
        return $orders;
    } else {
        // Handle prepare statement error
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return [];
    }
}

$pendingOrders = fetchOrders($conn, $user_id, 'pending');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
<?php include('./Header.php') ?>

<div class="container my-5">
    <h2 class="mb-4">Your Orders</h2>
    <div class="row">
        <!-- Pending Orders Section -->
        <div class="col-md-6">
            <h3>Pending Orders</h3>
            <?php if (empty($pendingOrders)): ?>
                <p>You have no pending orders at the moment.</p>
            <?php else: ?>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form method="POST" action="" id="paymentForm">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Order Date</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Select</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingOrders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                <td>$<?php echo number_format($order['total_price'], 2); ?> AUD</td>
                                <td><?php echo htmlspecialchars(ucfirst($order['order_status'])); ?></td>
                                <td>
                                    <input type="checkbox" class="order-checkbox" name="orders[]" value="<?php echo $order['order_id']; ?>">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteOrder(<?php echo $order['order_id']; ?>)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Payer and Shipping Information -->
                    <div class="mb-3">
                        <label for="payerName" class="form-label">Payer Name</label>
                        <input type="text" class="form-control" id="payerName" name="payer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="payerEmail" class="form-label">Payer Email</label>
                        <input type="email" class="form-control" id="payerEmail" name="payer_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="shippingAddress" class="form-label">Shipping Address</label>
                        <textarea class="form-control" id="shippingAddress" name="shipping_address" rows="3" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" name="proceed_payment" id="proceedPaymentButton">Proceed to Payment</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Payment Summary Section -->
        <div class="col-md-6">
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_payment']) && empty($error_message)): ?>
                <h3>Payment Details</h3>
                <h4>Payer Information</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($payer_name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($payer_email); ?></p>
                <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($shipping_address)); ?></p>
                
                <h4>Order Summary</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price (AUD)</th>
                            <th>Subtotal (AUD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($product_details)): ?>
                            <tr>
                                <td colspan="4">No products selected.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($product_details as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td>$<?php echo number_format($product['price'] * $product['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <h5>Total Price: $<?php echo number_format($total_price, 2); ?> AUD</h5>
                
                <!-- PayPal Button -->
                <div id="paypal-button-container"></div>
                <script src="https://www.paypal.com/sdk/js?client-id=AbqeHxNx5LstfXcRv9bHgrunv3Pwj2YYlu1PXmq6SPRBWxjvoWID6LMunANldepgJe5zOcef4t1Xq1ha&currency=AUD"></script>

                <!-- PayPal SDK -->
                <script>
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
                                window.location.href = 'process_paypal_payment.php?orderID=' + '<?php echo $order_id; ?>' + '&paypalOrderID=' + data.orderID;
                            });
                        },
                        onError: function (err) {
                            console.error(err);
                            alert('An error occurred during the payment process. Please try again.');
                        }
                    }).render('#paypal-button-container'); // Renders PayPal button
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function deleteOrder(orderId) {
        if (confirm('Are you sure you want to delete this order?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = 'delete_order_id';
            hiddenField.value = orderId;
            form.appendChild(hiddenField);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include('./footer.php') ?>
</body>
</html>
