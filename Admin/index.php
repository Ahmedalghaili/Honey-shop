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
    $orderQuery = "SELECT COUNT(*) as itemCount FROM `order` WHERE user_id = ? AND order_status='pending'";
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
    <!-- Meta and Title -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Honey Haven</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/main.css">
    <!-- Additional CSS from your code -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kaisei+Tokumin:wght@400;500;700&family=Poppins:wght@300;400;500&display=swap');

        :root {
            --lg-font: 'Kaisei Tokumin', serif;
            --sm-font: 'Poppins', sans-serif;
            --pink: #e5345b;
        }

        .custom-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .image-fixed-size {
            height: 500px;
            object-fit: cover;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f4f4;
        }

        .colorSS {
            color: #270c00;
        }

        .navbar-brand span {
            color: #270c00;
        }

        .btn-primary {
            background-color: #ffab40;
            border: none;
        }

        .btn-primary:hover {
            background-color: #ff6f00;
        }

        .carousel {
            background-image: url('../imag/IMG-20240921-WA0073.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            z-index: 1;
            height: 100vh;
            /* Adjusts height for full coverage */
        }

        .carousel-caption {
            color: #fff;
            position: absolute;
            bottom: 20%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            text-align: center;
        }

        .special-img img {
            height: 250px;
            object-fit: cover;
            border-radius: 15px;
        }

        .special-list .col-md-6 {
            padding: 20px;
        }

        footer {
            background-color: #3e2723;
        }

        footer a {
            color: #ffab40;
        }

        footer a:hover {
            color: #fff;
        }

        @media (max-width: 768px) {
            .carousel {
                height: 100vh;
                /* Set to 100% of the viewport height on mobile */
            }
        }

        .navbar-nav .nav-link {
            color: #270c00;
        }

        .honey-heading {
            color: black !important;
        }

        .navbar-nav .nav-link:hover {
            color: #e65100;
        }

        body {
            font-family: var(--sm-font);
        }

        .honey-bg-light {
            background-color: #fff5e1 !important;
        }

        .honey-bg-warm {
            background-color: #ffe5b4 !important;
        }

        .honey-text {
            color: black !important;
        }

        .honey-heading {
            color: #030303 !important;
        }

        .btn-honey {
            background-color: #f4a460 !important;
            border-color: #f4a460 !important;
        }

        .btn-honey:hover {
            background-color: #d68f4e !important;
        }

        .bg-primary {
            background-color: var(--pink) !important;
        }

        .btn:not(.nav-btns button) {
            background-color: #fff;
            color: rgb(85, 85, 85);
            padding: 10px 28px;
            border-radius: 25px;
            border: 1px solid rgb(85, 85, 85);
        }

        .btn:not(.nav-btns button):hover {
            background-color: var(--pink);
            color: #fff;
            border-color: var(--pink);
        }

        .text-primary {
            color: var(--pink) !important;
        }

        .navbar {
            box-shadow: 0 3px 9px 3px rgba(0, 0, 0, 0.1);
            /* Adjust or remove as desired */
        }


        .navbar-brand img {
            width: 30px;
        }

        .navbar-brand span {
            letter-spacing: 2px;
            font-family: var(--lg-font);
        }

        .nav-link:hover {
            color: var(--pink) !important;
        }

        .nav-item {
            border-bottom: 0.5px solid rgba(0, 0, 0, 0.05);
        }

        /* Header */
        #header {
            position: relative;
            width: 100%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #header img {
            width: 100%;
            height: auto;
            display: block;
        }

        @media (max-width: 768px) {
            #header {
                height: auto;
            }
        }

        .image-about {
            border-radius: 2.5rem;
        }

        .carousel-inner h1 {
            font-size: 60px;
            font-family: var(--lg-font);
        }

        .carousel-item .btn {
            border-color: #fff !important;
        }

        .carousel-item .btn:hover {
            border-color: var(--pink) !important;
        }

        .title h2::before {
            position: absolute;
            content: "";
            width: 4px;
            height: 50px;
            background-color: var(--pink);
            left: -20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .active-filter-btn {
            background-color: var(--pink) !important;
            color: #fff !important;
            border-color: var(--pink) !important;
        }

        .filter-button-group .btn:hover {
            color: #fff !important;
        }

        .collection-img span {
            top: 20px;
            right: 20px;
            width: 46px;
            height: 46px;
            border-radius: 50%;
        }

        .special-img span {
            top: 20px;
            right: 20px;
        }

        .special-list .btn {
            padding: 8px 20px !important;
        }

        .special-img img {
            transition: all 0.3s ease;
        }

        .special-img:hover img {
            transform: scale(1.2);
        }

        #offers {
            background: url(../IMG.jpg) center/cover no-repeat;
        }

        #offers .row {
            min-height: 60vh;
        }

        offers-content span {
            font-size: 28px;
        }

        offers-content h2 {
            font-size: 60px;
            font-family: var(--lg-font);
        }

        offers-content .btn {
            border-color: transparent !important;
        }

        #about {
            background-color: rgba(179, 179, 179, 0.05);
        }

        #newsletter p {
            max-width: 600px;
        }

        #newsletter .input-group {
            max-width: 500px;
        }

        #newsletter .form-control {
            border-top-left-radius: 25px;
            border-bottom-left-radius: 25px;
        }

        #newsletter .btn {
            background-color: var(--pink);
            color: #fff;
            border-color: var(--pink);
        }

        #newsletter .btn:hover {
            background-color: #000;
            border-color: #000;
        }

        footer .brand {
            font-family: var(--lg-font);
            letter-spacing: 2px;
        }

        footer a {
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: var(--pink) !important;
        }

        @media (min-width: 992px) {
            .nav-item {
                border-bottom: none;
            }
        }
    </style>
</head>

<body>

   <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-md">
  <div class="container d-flex justify-content-between align-items-center">
    <!-- Brand -->
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../logo-bg1723844848-removebg-preview.png" style="width: 75px; height: 55px;" alt="Yemen Apiaries Logo" class="h-12 w-auto">
      <span class="ms-2 text-xl font-bold text-gray-800">Yemen Apiaries</span>
    </a>

    <!-- Toggler Button for Mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Content: Navbar Links and Right-Aligned Icons -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Centered Navbar Links -->
      <ul class="navbar-nav mx-auto text-center mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link text-gray-700 hover:text-amber-600" href="#header">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-gray-700 hover:text-amber-600" href="#special">Specials</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-gray-700 hover:text-amber-600" href="#offers">Newsletter</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-gray-700 hover:text-amber-600" href="#blogs">Blog</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-gray-700 hover:text-amber-600" href="#about">About Us</a>
        </li>
      </ul>

      <!-- Cart and User Dropdown Aligned to the Right -->
      <div class="d-flex align-items-center justify-content-center mt-3 mt-lg-0">
        <!-- Cart Icon -->
        <a href="<?php echo $isLoggedIn ? './my_orders.php' : './login_users.php'; ?>" class="btn btn-link position-relative me-2">
          <i class="fas fa-shopping-cart"></i>
          <?php if ($cartItemCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-amber-500">
              <?php echo $cartItemCount; ?>
              <span class="visually-hidden">cart items</span>
            </span>
          <?php endif; ?>
        </a>

        <!-- User Dropdown -->
        <div class="dropdown">
          <button class="btn btn-link" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <?php if ($isLoggedIn): ?>
              <li><a class="dropdown-item" href="./logout.php">Logout</a></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="login_users.php">Login</a></li>
              <li><a class="dropdown-item" href="create_user.php">Register</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
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
                <a href="#special" class="btn mt-3 text-uppercase">shop now</a>
            </div>
            <div class="text-center carousel-item">
                <h2 class="text-capitalize text-white">best price & offer</h2>
                <h1 class="text-uppercase py-2 fw-bold text-white">natural honey products</h1>
                <a href="#special" class="btn mt-3 text-uppercase">buy now</a>
            </div>
        </div>
    </header>
    <!-- End of Header -->

    <!-- Special Section -->
    <div class="title text-center py-5 bg-white" id="special">
        <h2 class="d-inline-block">Special Honey Selection</h2>
    </div>
    <?php include 'product.php'; ?>
    <!-- End of Special Section -->

    <!-- Offers Section -->
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
    <!-- End of Offers Section -->

    <!-- Blog Section -->
    <?php include 'blog.php'; ?>
    <!-- End of Blog Section -->

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
    <!-- Custom Script for Navbar Background Change -->

    <!-- Other Scripts -->
    <script src="js/jquery-3.6.0.js"></script>
    <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.js"></script>
    <script src="js/script.js"></script>
</body>

</html>