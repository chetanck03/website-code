<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

// Verify order belongs to user
$order_query = "SELECT o.*, u.email, u.phone FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id AND o.user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    header('location: index.php');
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Cashfree API Configuration
$api_key = "YOUR_CASHFREE_API_KEY"; // Replace with your Cashfree API key
$api_secret = "YOUR_CASHFREE_API_SECRET"; // Replace with your Cashfree API secret
$is_production = false; // Set to true for production environment

$api_endpoint = $is_production 
    ? "https://api.cashfree.com/pg/orders" 
    : "https://sandbox.cashfree.com/pg/orders";

// Create order payload for Cashfree
$payload = array(
    "order_id" => "ORDER_" . $order_id,
    "order_amount" => $order['total_amount'],
    "order_currency" => "INR",
    "order_note" => "Payment for Order #" . $order_id,
    "customer_details" => array(
        "customer_id" => "CUST_" . $user_id,
        "customer_email" => $order['email'],
        "customer_phone" => $order['phone']
    ),
    "order_meta" => array(
        "return_url" => "https://yourwebsite.com/payment_callback.php?order_id=" . $order_id
    )
);

// Initialize cURL session
$ch = curl_init($api_endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'x-api-version: 2022-09-01',
    'x-client-id: ' . $api_key,
    'x-client-secret: ' . $api_secret
));

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    $_SESSION['error'] = "Payment initialization failed. Please try again.";
    header("location: checkout.php");
    exit();
}

$result = json_decode($response, true);

if (isset($result['payment_link'])) {
    // Redirect to Cashfree payment page
    header("location: " . $result['payment_link']);
    exit();
} else {
    $_SESSION['error'] = "Payment initialization failed. Please try again.";
    header("location: checkout.php");
    exit();
}
?> 