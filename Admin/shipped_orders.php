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

// Fetch shipped orders for the logged-in user
function fetchShippedOrders($conn, $user_id)
{
    $query = "SELECT order_id, order_date, total_price, order_status, shipping_address, tracking_number
              FROM `order`
              WHERE user_id = ? AND order_status = 'shipped'
              ORDER BY order_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $shippedOrders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $shippedOrders[] = $row;
        }
    }
    $stmt->close();
    return $shippedOrders;
}

$shippedOrders = fetchShippedOrders($conn, $user_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipped Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- bootstrap cdn -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">

    <!-- custom css -->
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .order-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 15px;
            padding: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .order-details {
            padding: 10px;
        }

        .tracking-link {
            color: #007bff;
            text-decoration: underline;
        }

        .tracking-link:hover {
            color: #0056b3;
            text-decoration: none;
        }
    </style>
</head>

<body>
<?php include('./Header.php') ?>

    <div class="container my-5">
        <h2 class="mb-4">Shipped Orders</h2>
        <div class="row">
            <div class="col-md-12">
                <?php if (empty($shippedOrders)): ?>
                    <p>You have no shipped orders at the moment.</p>
                <?php else: ?>
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Order Date</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Shipping Address</th>
                                <th>Tracking Number</th>
                                <th>Track Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shippedOrders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                <td>$<?php echo number_format($order['total_price'], 2); ?> AUD</td>
                                <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                                <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                                <td><?php echo htmlspecialchars($order['tracking_number']); ?></td>
                                <td>
                                    <a href="https://trackmyshipment.com/<?php echo htmlspecialchars($order['tracking_number']); ?>" class="tracking-link" target="_blank">Track Order</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('./footer.php') ?> 
</body>

</html>
