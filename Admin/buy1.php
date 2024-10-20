<?php
session_start();
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_users.php");
    exit;
}

// Get the product_id from the URL query string
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1; // Default quantity is 1

// Ensure a valid product ID is provided
if ($product_id > 0) {
    // Fetch product details from the product table
    $stmt = $conn->prepare("SELECT * FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $name = $product['name'];
        $price = $product['price'];
        $description = $product['description'];
        $image_url = $product['image_url'];
    } else {
        echo 'Product not found.';
        exit;
    }
    $stmt->close();
} else {
    echo 'Invalid product ID.';
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($name); ?> - Honey Haven</title>

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/main.css">

    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f9f9f9;
        }

        .navbar {
            background-color: white;
        }

        .navbar-brand span {
            color: #e65100;
        }

        .btn-primary {
            background-color: #ffab40;
            border: none;
        }

        .btn-primary:hover {
            background-color: #ff6f00;
        }

        .product-image {
            max-width: 100%;
            height: auto;
            border-radius: 15px;
        }

        .product-title {
            font-size: 2rem;
            color: #333;
        }

        .product-price {
            font-size: 1.5rem;
            color: #e65100;
        }

        .price-sale {
            color: #d32f2f;
            text-decoration: line-through;
        }

        .shop-pay-btn {
            background-color: #5a31f4;
            color: white;
            border: none;
        }

        .shop-pay-btn:hover {
            background-color: #4a28d9;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex justify-content-between align-items-center order-lg-0" href="index.php">
                <img src="../logo-bg1723844848-removebg-preview.png" alt="Honey Haven Logo" style="width: 75px; height: 55px;">
                <span class="text-uppercase fw-bold ms-3">Honey Haven</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link text-uppercase text-dark" href="index.php">Home</a>
                    </li>
                </ul>

                <!-- User Dropdown -->
                <div class="btn-group">
                    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user"></i> <!-- User Icon -->
                    </button>

                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="./logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    <?php else: ?>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="login_users.php"><i class="fa fa-sign-in"></i> Login</a></li>
                            <li><a class="dropdown-item" href="create_user.php"><i class="fa fa-user-plus"></i> Register</a></li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Product Details Section -->
    <div class="container my-5 pt-5">
        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6 text-center">
                <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($name); ?>" class="img-fluid product-image">
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <h1 class="product-title"><?php echo htmlspecialchars($name); ?></h1>
                <div class="product-price my-3">
                    <span class="price-discount">$<?php echo number_format($price, 2); ?> AUD</span>
                </div>
                <p class="text-muted">Tax included. Shipping calculated at checkout.</p>

                <div class="my-4">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" id="quantity" class="form-control" value="1" min="1">
                </div>

                <!-- Add to Cart Button -->
                <button type="button" class="btn btn-primary mb-3 w-100" id="addToCartBtn">Add to cart</button>

                <!-- Shop Pay Button -->
                <button class="btn shop-pay-btn w-100">Buy with Shop Pay</button>

                <!-- Product Description -->
                <div class="product-description mt-4">
                    <h2>Product Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($description)); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('addToCartBtn').addEventListener('click', function() {
            var productId = <?php echo $product_id; ?>;
            var quantity = document.getElementById('quantity').value;

            // Create an AJAX request to send product and quantity to the server
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_to_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText); // Show response from add_to_cart.php
                }
            };
            xhr.send('product_id=' + productId + '&quantity=' + quantity);
        });
    </script>
 
 <?php include 'blog.php'; ?>
    <div class="title text-center py-5 bg-white">
            <h2 class="d-inline-block">You may also like</h2>
        </div>
    <?php include 'product.php'; ?>
    <!-- Footer -->
    <!-- Footer Section -->
    <footer class="bg-dark py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <a class="text-uppercase text-decoration-none brand text-white" href="index.php">Honey Haven</a>
                    <p class="text-light mt-3">Your trusted source for organic honey.</p>
                </div>

                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-light text-white">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="my-3"><a href="./index.php" class="text-light text-decoration-none">Home</a></li>
                    </ul>
                </div>

                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-light text-white">Contact Us</h5>
                    <p class="text-light">6/30 Philip Street, Roselands, NSW, Australia</p>
                    <p class="text-light">contact@yemenapiaries.com.au</p>
                    <p class="text-light">+61 0421867781</p>
                </div>

                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-light text-white">Follow Us</h5>
                    <ul class="list-unstyled d-flex">
                        <li><a href="#" class="text-light fs-4 me-4"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#" class="text-light fs-4 me-4"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="#" class="text-light fs-4 me-4"><i class="fab fa-whatsapp"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
