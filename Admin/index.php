<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session to track login status
}
require 'db.php'; // Database connection

// Fetch all products from the 'product' table first
$productQuery = "SELECT * FROM product";
$productResult = $conn->query($productQuery); // Save the result for products

// Determine if the user is logged in based on session
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

$cartItemCount = 0; // Initialize cart item count

if ($isLoggedIn) {
    // Fetch the user's cart items (or orders) from the database
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

    // Use backticks to escape the 'order' table name
    $orderQuery = "SELECT COUNT(*) as itemCount FROM `order` WHERE user_id = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $cartResult = $stmt->get_result();  // Separate cart result
    if ($cartResult && $cartResult->num_rows > 0) {
        $row = $cartResult->fetch_assoc();
        $cartItemCount = $row['itemCount']; // Set the cart item count
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Honey Haven</title>
    <!-- fontawesome cdn -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- bootstrap css -->
    <link rel="stylesheet" href="../bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <!-- custom css -->
    <link rel="stylesheet" href="../css/main.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light text-white fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex justify-content-between align-items-center order-lg-0" href="index.php">
                <img src="../logo-bg1723844848-removebg-preview.png" alt="Honey Haven Logo"
                    style="width: 75px; height: 55px;">
                <span class="neww text-uppercase fw-bold ms-3">Honey Haven</span>
            </a>

            <div class="order-lg-2 nav-btns">
                <!-- Shopping Cart Button -->
                <?php if ($isLoggedIn): ?>
                    <a href="./my_orders.php" class="btn position-relative text-decoration-none text-dark">
                        <i class="fa fa-shopping-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge bg-primary">
                            <?php echo $cartItemCount; ?>
                        </span>
                    </a>
                <?php else: ?>
                    <a href="./login_users.php" class="btn position-relative text-decoration-none text-dark">
                        <i class="fa fa-shopping-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge bg-primary">0</span>
                    </a>
                <?php endif; ?>

                <!-- User Dropdown -->
                <div class="btn-group">
                    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user"></i>
                    </button>

                    <?php if ($isLoggedIn): ?>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="./logout.php">
                                    <i class="fa fa-sign-out"></i> Logout
                                </a>
                            </li>
                        </ul>
                    <?php else: ?>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="login_users.php">
                                    <i class="fa fa-sign-in"></i> Login
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="create_user.php">
                                    <i class="fa fa-user-plus"></i> Register
                                </a>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse order-lg-1" id="navMenu">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item px-3 py-2">
                        <a class="nav-link text-uppercase text-black" href="#header">Home</a>
                    </li>
                    <li class="nav-item px-3 py-2">
                        <a class="nav-link text-uppercase text-black" href="#special">Specials</a>
                    </li>
                    <li class="nav-item px-3 py-2">
                        <a class="nav-link text-uppercase text-black" href="#about">About Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- End of Navbar -->

    <!-- Header / Hero Section -->
    <header id="header" class="vh-100 carousel slide" data-bs-ride="carousel" style="padding-top: 104px;">
        <div class="container h-100 d-flex align-items-center carousel-inner">
            <div class="text-center carousel-item active">
                <h2 class="text-capitalize text-white">pure organic honey</h2>
                <h1 class="text-uppercase py-2 fw-bold text-white">new arrivals</h1>
                <a href="#" class="btn mt-3 text-uppercase">shop now</a>
            </div>
            <div class="text-center carousel-item">
                <h2 class="text-capitalize text-white">best price & offer</h2>
                <h1 class="text-uppercase py-2 fw-bold text-white">natural honey products</h1>
                <a href="#" class="btn mt-3 text-uppercase">buy now</a>
            </div>
        </div>
    </header>
    <!-- End of Header -->

    <!-- Special Section -->
    <div class="title text-center py-5 bg-white">
        <h2 class="d-inline-block">Special Honey Selection</h2>
    </div>
    <?php include 'product.php'; ?>

    <!-- End of Special Section -->

    <!-- Blogs -->
    <section id="offers" class="py-5">
        <div class="container">
            <div
                class="row d-flex align-items-center justify-content-center text-center justify-content-lg-start text-lg-start">
                <div class="offers-content">
                    <span class="text-white">Exclusive Discounts Up To 40% on Yemeni Sumur Honey & Royal Sidr
                        Honey</span>
                    <h2 class="mt-2 mb-4 text-white">Grand Sale on Premium Honeys!</h2>
                    <a href="#" class="btn">Shop Now</a>
                </div>
            </div>
        </div>
    </section>
    <?php include 'blog.php'; ?>
    <!-- End of Blogs -->

    <!-- About Us Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row gy-lg-5 align-items-center custom-gutters">
                <div class="col-lg-6 order-lg-1 text-center text-lg-start">
                    <div class="title pt-3 pb-5">
                        <h2 class="position-relative d-inline-block ms-4">About Honey Haven</h2>
                    </div>
                    <p class="lead text-muted">At Honey Haven, we specialize in delivering the finest Yemeni Sumur and
                        Royal Sidr honeys, sourced directly from trusted beekeepers. Our commitment to quality ensures
                        every jar is filled with pure, organic honey that enhances both flavor and health.</p>
                </div>
                <div class="col-lg-6 order-lg-0">
                    <img src="../imag/IMG-20240921-WA0065.jpg" alt="About Honey Haven"
                        class="image-about img-thumbnail rounded-4">
                </div>
            </div>
        </div>
    </section>
    <!-- End of About Us Section -->

    <!-- Newsletter Section -->
    <section id="newsletter" class="py-5 bg-white">
        <div class="container">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="title text-center pt-3 pb-5">
                    <h2 class="d-inline-block ms-4">Newsletter Subscription</h2>
                </div>

                <p class="text-center text-muted">Subscribe to our newsletter for the latest updates on our organic
                    honey collection and special offers.</p>
                <div class="input-group mb-3 mt-3">
                    <input type="text" class="form-control" placeholder="Enter Your Email ...">
                    <button class="btn btn-primary" type="submit">Subscribe</button>
                </div>
            </div>
        </div>
    </section>
    <!-- End of Newsletter Section -->

    <!-- Footer Section -->
    <footer class="bg-dark py-5">
        <div class="container">
            <div class="row text-white g-4">
                <div class="col-md-6 col-lg-3">
                    <a class="text-uppercase text-decoration-none brand text-white" href="index.php">Honey Haven</a>
                    <p class="text-white text-muted mt-3">Honey Haven is your trusted source for premium organic honey.
                    </p>
                </div>

                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-light">Links</h5>
                    <ul class="list-unstyled">
                        <li class="my-3"><a href="#" class="text-white text-decoration-none text-muted"><i
                                    class="fas fa-chevron-right me-1"></i> Home</a></li>
                        <li class="my-3"><a href="#" class="text-white text-decoration-none text-muted"><i
                                    class="fas fa-chevron-right me-1"></i> Honey Selection</a></li>
                        <li class="my-3"><a href="#" class="text-white text-decoration-none text-muted"><i
                                    class="fas fa-chevron-right me-1"></i> Blog</a></li>
                        <li class="my-3"><a href="#" class="text-white text-decoration-none text-muted"><i
                                    class="fas fa-chevron-right me-1"></i> About Us</a></li>
                    </ul>
                </div>

                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-light mb-3">Contact Us</h5>
                    <div class="d-flex my-2 text-muted">
                        <span class="me-3"><i class="fas fa-map-marked-alt"></i></span>
                        <span class="fw-light">Honey Street, Bee City, HC 123, Country</span>
                    </div>
                    <div class="d-flex my-2 text-muted">
                        <span class="me-3"><i class="fas fa-envelope"></i></span>
                        <span class="fw-light">contact@honeyhaven.com</span>
                    </div>
                    <div class="d-flex my-2 text-muted">
                        <span class="me-3"><i class="fas fa-phone-alt"></i></span>
                        <span class="fw-light">+1234 567 890</span>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-light mb-3">Follow Us</h5>
                    <ul class="list-unstyled d-flex">
                        <li>
                            <a href="https://wa.me/+61449747762"
                                class="text-white text-decoration-none text-muted fs-4 me-4" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.instagram.com/yemen_apiaries"
                                class="text-white text-decoration-none text-muted fs-4 me-4" target="_blank">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.6.0.js"></script>
    <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.js"></script>
    <script src="js/script.js"></script>
</body>

</html>