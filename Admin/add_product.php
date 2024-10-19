<?php

require 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $created_at = date('Y-m-d H:i:s');

    // Initialize image_url variable
    $image_url = '';

    // Check if the user uploaded an image or entered a URL
    if (isset($_POST['image_option']) && $_POST['image_option'] == 'upload') {
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/'; // Directory where images will be uploaded
            $uploadFile = $uploadDir . basename($_FILES['image']['name']);
            $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

            // Validate file type (optional)
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageFileType, $allowedTypes)) {
                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $image_url = $uploadFile; // Set the image URL to the uploaded file path
                } else {
                    echo "Error uploading the image.";
                    exit;
                }
            } else {
                echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
                exit;
            }
        } else {
            echo "Error with file upload.";
            exit;
        }
    } elseif (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
        // Use the provided URL
        $image_url = $_POST['image_url'];
    } else {
        echo "No image provided.";
        exit;
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO product (name, description, price, stock, category, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisss", $name, $description, $price, $stock, $category, $image_url, $created_at);

    // Execute the statement
    if ($stmt->execute()) {
        echo "New product added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function toggleImageInput() {
            const uploadSection = document.getElementById('upload-section');
            const urlSection = document.getElementById('url-section');
            if (document.getElementById('upload').checked) {
                uploadSection.style.display = 'block';
                urlSection.style.display = 'none';
            } else {
                uploadSection.style.display = 'none';
                urlSection.style.display = 'block';
            }
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2>Add New Product</h2>
        <form action="./add_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" class="form-control" id="category" name="category" required>
            </div>

            <!-- Image Input Options -->
            <label>Image Input Option:</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="image_option" id="upload" value="upload" onclick="toggleImageInput()" checked>
                <label class="form-check-label" for="upload">Upload Image</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="image_option" id="url" value="url" onclick="toggleImageInput()">
                <label class="form-check-label" for="url">Enter Image URL</label>
            </div>

            <!-- Image Upload Section -->
            <div id="upload-section">
                <label for="image">Choose an image to upload:</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>

            <!-- Image URL Section -->
            <div id="url-section" style="display: none;">
                <label for="image_url">Image URL</label>
                <input type="text" class="form-control" id="image_url" name="image_url">
            </div>

            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>

    <script>
        // Initialize the form state based on default selection
        document.addEventListener("DOMContentLoaded", function() {
    toggleImageInput();
});

    </script>
</body>
</html>
