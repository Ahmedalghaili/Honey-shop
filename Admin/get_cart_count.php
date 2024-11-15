<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$response = ['success' => false, 'count' => 0];

// Determine if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if ($isLoggedIn) {
    $user_id = $_SESSION['user_id'];
    $orderQuery = "SELECT COUNT(*) as itemCount FROM `order` 
                   JOIN `order_product` ON `order`.order_id = `order_product`.order_id 
                   WHERE `order`.user_id = ? AND `order`.order_status='pending'";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param('i', $user_id);
} else {
    if (isset($_SESSION['session_id'])) {
        $session_id = $_SESSION['session_id'];
        $orderQuery = "SELECT COUNT(*) as itemCount FROM `order` 
                       JOIN `order_product` ON `order`.order_id = `order_product`.order_id 
                       WHERE `order`.session_id = ? AND `order`.order_status='pending'";
        $stmt = $conn->prepare($orderQuery);
        $stmt->bind_param('s', $session_id);
    } else {
        // No cart exists
        echo json_encode($response);
        exit;
    }
}

$stmt->execute();
$cartResult = $stmt->get_result();
if ($cartResult && $cartResult->num_rows > 0) {
    $row = $cartResult->fetch_assoc();
    $response['count'] = $row['itemCount'];
    $response['success'] = true;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
