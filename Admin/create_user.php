<?php
// Ensure no PHP errors are shown before the HTML output starts.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="./register_style.css">

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">

    <style>
        /* Ensure body takes up full height and uses flexbox */
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1;
        }

        .form-container {
            max-width: 320px;
        }

        .form-control {
            font-size: 0.85rem;
            padding: 0.4rem 0.6rem;
        }

        .btn-lg {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            font-size: 0.875rem;
        }

        .form-outline {
            margin-bottom: 0.9rem;
        }

        .image-column img {
            max-width: 250px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .image-column, .form-column {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .vh-100 {
            min-height: 80vh;
        }

        .footer {
            background-color: #333;
            color: white;
            padding: 10px;
        }
    </style>
</head>

<body>
    <!-- Main content -->
    <div class="content" style="margin: 31px;">
        <section class="vh-80">
            <div class="container-fluid h-custom">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <!-- Image Column -->
                    <div class="col-md-4 col-lg-4 col-xl-4 image-column">
                        <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                            class="img-fluid" alt="Sample image">
                    </div>

                    <!-- Form Column -->
                    <div class="col-md-6 col-lg-4 col-xl-3 form-column">
                        <form action="create_user.php" method="POST">
                            <!-- Username input -->
                            <div class="form-outline mb-3">
                                <input type="text" id="form3ExampleUsername" name="username" class="form-control"
                                    placeholder="Enter your username" required />
                                <label class="form-label" for="form3ExampleUsername">Username</label>
                            </div>

                            <!-- Email input -->
                            <div class="form-outline mb-3">
                                <input type="email" id="form3ExampleEmail" name="email" class="form-control"
                                    placeholder="Enter a valid email address" required />
                                <label class="form-label" for="form3ExampleEmail">Email address</label>
                            </div>

                            <!-- Password input -->
                            <div class="form-outline mb-3">
                                <input type="password" id="form3ExamplePassword" name="password" class="form-control"
                                    placeholder="Enter password" required />
                                <label class="form-label" for="form3ExamplePassword">Password</label>
                            </div>

                            <!-- Confirm Password input -->
                            <div class="form-outline mb-3">
                                <input type="password" id="form3ExampleConfirmPassword" name="confirm_password"
                                    class="form-control" placeholder="Confirm your password" required />
                                <label class="form-label" for="form3ExampleConfirmPassword">Confirm Password</label>
                            </div>

                            <!-- Phone number input (optional) -->
                            <div class="form-outline mb-3">
                                <input type="tel" id="form3ExamplePhone" name="phone" class="form-control"
                                    placeholder="Enter your phone number (optional)" />
                                <label class="form-label" for="form3ExamplePhone">Phone Number (optional)</label>
                            </div>

                            <!-- Checkbox for terms -->
                            <div class="form-check d-flex justify-content-start mb-4">
                                <input class="form-check-input me-2" type="checkbox" required />
                                <label class="form-check-label">
                                    I agree to the <a href="#!" class="text-body"><u>Terms of Service</u></a>
                                </label>
                            </div>

                            <!-- Register button -->
                            <div class="text-center text-lg-start mt-4 pt-2">
                                <button type="submit" class="btn btn-dark btn-lg">Register</button>
                                <p class="small fw-bold mt-2 pt-1 mb-0">Already have an account? <a href="#!"
                                        class="link-danger">Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <div class="footer d-flex flex-column flex-md-row text-center text-md-start justify-content-between py-4 px-4 px-xl-5 bg-dark">
        <div class="text-white mb-3 mb-md-0">
            Copyright © 2020. All rights reserved.
        </div>
        <div>
            <a href="#!" class="text-white me-4">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#!" class="text-white me-4">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="#!" class="text-white me-4">
                <i class="fab fa-google"></i>
            </a>
            <a href="#!" class="text-white">
                <i class="fab fa-linkedin-in"></i>
            </a>
        </div>
    </div>
</body>

</html>

<?php
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $_POST['phone'] ?? '';

    // Check for matching passwords
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
    } else {
        // Insert into the database
        require './db.php';

        $stmt = $conn->prepare("INSERT INTO user (username, email, password, phone_number) VALUES (?, ?, ?, ?)");
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $phone);

        if ($stmt->execute()) {
            echo "Registration successful!";
        } else {
            echo "Error: dfssdafsd" . $conn->error;
        }

        $stmt->close();
    }
}
?>