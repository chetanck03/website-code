<?php
include 'includes/header.php';

if (!isset($_GET['order_id']) || !isset($_POST['orderId'])) {
    header('location: index.php');
    exit();
}

$order_id = $_GET['order_id'];
$cashfree_order_id = $_POST['orderId'];
$order_status = $_POST['orderStatus'];
$transaction_id = $_POST['referenceId'];

// Verify the payment status with Cashfree
$api_key = "YOUR_CASHFREE_API_KEY"; // Replace with your Cashfree API key
$api_secret = "YOUR_CASHFREE_API_SECRET"; // Replace with your Cashfree API secret
$is_production = false; // Set to true for production environment

$api_endpoint = $is_production 
    ? "https://api.cashfree.com/pg/orders/" . $cashfree_order_id 
    : "https://sandbox.cashfree.com/pg/orders/" . $cashfree_order_id;

// Initialize cURL session to verify payment status
$ch = curl_init($api_endpoint);
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

if (!$err) {
    $result = json_decode($response, true);
    
    if ($result['order_status'] === 'PAID') {
        // Update order status in database
        $update_query = "UPDATE orders SET 
                        payment_status = 'completed',
                        transaction_id = '" . mysqli_real_escape_string($conn, $transaction_id) . "'
                        WHERE id = " . (int)$order_id;
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = "Payment processed successfully!";
            header("location: order_confirmation.php?order_id=" . $order_id);
            exit();
        }
    }
}

$_SESSION['error'] = "Payment verification failed. Please contact support.";
header("location: orders.php");
exit();
?> 