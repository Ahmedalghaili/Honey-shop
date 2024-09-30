<!-- dashboard.php -->
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

require 'db.php'; // Database connection

// Fetch products
$productQuery = "SELECT * FROM product";
$productResult = $conn->query($productQuery);

// Fetch users
$userQuery = "SELECT * FROM user";
$userResult = $conn->query($userQuery);

// Fetch orders
$orderQuery = "SELECT * FROM `order`";
$orderResult = $conn->query($orderQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <!-- Products Section -->
    <h2>Manage Products</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $productResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo $row['price']; ?></td>
                <td><?php echo $row['stock']; ?></td>
                <td>
                    <a href="edit_product.php?id=<?php echo $row['product_id']; ?>">Edit</a>
                    <a href="delete_product.php?id=<?php echo $row['product_id']; ?>">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Users Section -->
    <h2>Manage Users</h2>
    <table border="1">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $userResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td>
                    <a href="edit_user.php?id=<?php echo $row['user_id']; ?>">Edit</a>
                    <a href="delete_user.php?id=<?php echo $row['user_id']; ?>">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Orders Section -->
    <h2>Manage Orders</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User ID</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $orderResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['order_id']; ?></td>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo $row['total_price']; ?></td>
                <td><?php echo $row['order_status']; ?></td>
                <td>
                    <a href="edit_order.php?id=<?php echo $row['order_id']; ?>">Edit</a>
                    <a href="delete_order.php?id=<?php echo $row['order_id']; ?>">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
