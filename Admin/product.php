<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session to track login status
}
require 'db.php'; // Database connection

// Determine if the user is logged in
$loggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// Fetch all products from the 'product' table first
$productQuery = "SELECT * FROM product";
$productResult = $conn->query($productQuery); // Save the result for products
?>
<section id="special" class="py-5 bg-white">
    <div class="container">
        <div class="special-list row g-0 justify-content-center">
            <?php
            // Check if product query is successful
            if ($productResult && $productResult->num_rows > 0):
                // Loop through each product in the result set
                while ($row = $productResult->fetch_assoc()):
                    $product_id = $row['product_id'];
                    $name = $row['name'];
                    $price = $row['price'];
                    $image_url = $row['image_url'];
                    ?>
                    <div class="col-md-6 col-lg-4 col-xl-3 p-2">
                        <div class="special-img position-relative overflow-hidden">
                            <!-- Clicking on image moves to buy1.php for detailed view -->
                            <a href="./buy1.php?product_id=<?php echo $product_id; ?>">
                                <img src="<?php echo $image_url; ?>" class="w-100 image-fixed-size" alt="<?php echo $name; ?>">
                            </a>
                        </div>
                        <div class="text-center">
                            <p class="text-capitalize mt-3 mb-1"><?php echo $name; ?></p>
                            <span class="fw-bold d-block"><?php echo number_format($price, 2); ?> USD</span>
                            <!-- Add to Cart Button -->
                            <button class="btn btn-primary mt-3 add-to-cart-btn" data-product-id="<?php echo $product_id; ?>">Add to Cart</button>
                        </div>
                    </div>
                    <?php
                endwhile;
            else:
                echo "<p>No products available at the moment.</p>";
            endif;
            ?>
        </div>
    </div>
</section>

<!-- Pass the login status to JavaScript -->
<script>
    var loggedIn = <?php echo json_encode($loggedIn); ?>;
</script>
<script>
    // Add to Cart functionality using AJAX
    document.querySelectorAll('.add-to-cart-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var productId = this.getAttribute('data-product-id');

            // Create AJAX request to add product to cart
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_to_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                alert('Product added to cart successfully!');
                            } else {
                                alert(response.message || 'Failed to add product to cart.');
                            }
                        } catch (e) {
                            console.error('Invalid JSON response');
                            alert('An unexpected error occurred.');
                        }
                    } else {
                        console.error('Error adding product to cart');
                        alert('Failed to add product to cart.');
                    }
                }
            };
            xhr.send('product_id=' + encodeURIComponent(productId) + '&quantity=1'); // Quantity is set to 1 by default
        });
    });
</script>
