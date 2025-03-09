<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to add items to cart";
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'];

switch($action) {
    case 'add':
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        
        // Check if product already exists in cart
        $check_query = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update quantity
            $cart_item = mysqli_fetch_assoc($check_result);
            $new_quantity = $cart_item['quantity'] + $quantity;
            $update_query = "UPDATE cart SET quantity = $new_quantity WHERE user_id = $user_id AND product_id = $product_id";
            mysqli_query($conn, $update_query);
        } else {
            // Insert new item
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
            mysqli_query($conn, $insert_query);
        }
        
        $_SESSION['success'] = "Item added to cart successfully";
        break;
        
    case 'update':
        $cart_id = $_POST['cart_id'];
        $quantity = $_POST['quantity'];
        
        if ($quantity > 0) {
            $update_query = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id";
            mysqli_query($conn, $update_query);
            $_SESSION['success'] = "Cart updated successfully";
        } else {
            $delete_query = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
            mysqli_query($conn, $delete_query);
            $_SESSION['success'] = "Item removed from cart";
        }
        break;
        
    case 'remove':
        $cart_id = $_POST['cart_id'];
        $delete_query = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
        mysqli_query($conn, $delete_query);
        $_SESSION['success'] = "Item removed from cart";
        break;
        
    case 'clear':
        $clear_query = "DELETE FROM cart WHERE user_id = $user_id";
        mysqli_query($conn, $clear_query);
        $_SESSION['success'] = "Cart cleared successfully";
        break;
}

header('location: cart.php');
exit();
