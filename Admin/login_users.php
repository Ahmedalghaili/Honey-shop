<?php
session_start();
require 'db.php'; // Connection to the database

// Check if a return URL is provided in the query string, otherwise default to index.php
$return_url = isset($_GET['return_url']) ? $_GET['return_url'] : 'index.php';

if (isset($_POST['login'])) {
    // Get the user input
    $username = $_POST['form3Example3'];  // Fetch the email input
    $password = $_POST['form3Example4'];  // Fetch the password input

    // Fetch user information from the database
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password using password_verify (if using hashed passwords)
        if (password_verify($password, $user['password'])) {
            // Set session variables after successful login
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            // Redirect to the return URL after successful login
            header("Location: " . $return_url);
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "User not found!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../login_style.css">
    <!-- fontawesome cdn -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- bootstrap cdn -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>

<body>
    <section class="vh-100">
        <div class="container-fluid h-custom">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-md-9 col-lg-6 col-xl-5">
                    <img src="../IMG-20240921-WA0075.jpg" class="img-fluid" alt="Sample image">
                </div>
                <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                    <form action="./login_users.php?return_url=<?php echo urlencode($return_url); ?>" method="POST">
                        <!-- Email input -->
                        <div class="form-outline mb-4">
                            <input type="email" id="form3Example3" name="form3Example3" class="form-control form-control-lg" placeholder="Enter a valid email address" required />
                            <label class="form-label" for="form3Example3">Email address</label>
                        </div>

                        <!-- Password input -->
                        <div class="form-outline mb-3">
                            <input type="password" id="form3Example4" name="form3Example4" class="form-control form-control-lg" placeholder="Enter password" required />
                            <label class="form-label" for="form3Example4">Password</label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check mb-0">
                                <input class="form-check-input me-2" type="checkbox" value="" id="form2Example3" />
                                <label class="form-check-label" for="form2Example3">Remember me</label>
                            </div>
                            <a href="#!" class="text-body">Forgot password?</a>
                        </div>

                        <div class="text-center text-lg-start mt-4 pt-2">
                            <button type="submit" name="login" class="btn btn-dark btn-lg" style="padding-left: 2.5rem; padding-right: 2.5rem;">Login</button>
                            <p class="small fw-bold mt-2 pt-1 mb-0">Don't have an account? <a href="create_user.php" class="link-danger">Register</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>

</html>
