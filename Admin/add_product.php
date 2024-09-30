<!-- add_product.php -->
<?php
require './db.php'; // Connection to the database

if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];

    $query = "INSERT INTO product (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdis", $name, $description, $price, $stock, $category);

    if ($stmt->execute()) {
        header("Location: dashboard.php"); // Redirect to dashboard after adding the product
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
</head>
<body>
    <form action="add_product.php" method="POST">
        <h2>Add New Product</h2>
        <input type="text" name="name" placeholder="Product Name" required>
        <textarea name="description" placeholder="Product Description" required></textarea>
        <input type="number" name="price" placeholder="Price" required>
        <input type="number" name="stock" placeholder="Stock" required>
        <input type="text" name="category" placeholder="Category" required>
        <button type="submit" name="add_product">Add Product</button>
    </form>
</body>
</html>
