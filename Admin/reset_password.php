<?php
require 'db.php';

if (isset($_GET['token']) && isset($_POST['reset_password'])) {
    $token = $_GET['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        // Check if the token is valid and has not expired
        $query = "SELECT * FROM user WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Hash the new password and update it in the database
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update = "UPDATE user SET password = ?, reset_token = NULL, reset_expires = NULL WHERE user_id = ?";
            $stmt = $conn->prepare($update);
            $stmt->bind_param("si", $hashed_password, $user['user_id']);
            $stmt->execute();

            echo "Your password has been reset successfully.";
        } else {
            echo "Invalid or expired token.";
        }
    } else {
        echo "Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <form action="" method="POST">
        <label>Enter your new password:</label>
        <input type="password" name="new_password" required>
        <label>Confirm your new password:</label>
        <input type="password" name="confirm_password" required>
        <button type="submit" name="reset_password">Reset Password</button>
    </form>
</body>
</html>
