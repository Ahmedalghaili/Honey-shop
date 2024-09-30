<!-- add_admin.php -->
 
<?php
require 'db.php'; // Database connection

if (isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashing the password
    $email = $_POST['email'];

    // Prepare and execute the insert query
    $query = "INSERT INTO admin (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $password, $email);

    if ($stmt->execute()) {
        echo "New admin added successfully!";
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
    <title>Add Admin</title>
</head>
<body>
    <form action="add_admin.php" method="POST">
        <h2>Add New Admin</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit" name="add_admin">Add Admin</button>
    </form>
</body>
</html>
