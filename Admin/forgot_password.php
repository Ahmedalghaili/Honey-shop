<?php
require 'db.php'; // Database connection

if (isset($_POST['submit'])) {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50)); // Generate a secure token
        $expiry_time = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expiration time

        // Store the token and expiration time in the database
        $update = "UPDATE user SET reset_token = ?, reset_expires = ? WHERE email = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("sss", $token, $expiry_time, $email);
        $stmt->execute();

        // Send reset link to user email
        $reset_link = "http://yourdomain.com/reset_password.php?token=" . $token;
        mail($email, "Password Reset", "Click on this link to reset your password: $reset_link");

        echo "Password reset link has been sent to your email.";
    } else {
        echo "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <form action="" method="POST">
        <label>Enter your email address:</label>
        <input type="email" name="email" required>
        <button type="submit" name="submit">Submit</button>
    </form>
</body>
</html>
