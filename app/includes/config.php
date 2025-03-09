<?php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Default XAMPP MySQL username
define('DB_PASS', '');      // Default XAMPP MySQL password is empty
define('DB_NAME', 'art_delivery');  // Your database name from database.sql

// Cashfree Configuration
define('CASHFREE_API_KEY', 'YOUR_CASHFREE_API_KEY');
define('CASHFREE_API_SECRET', 'YOUR_CASHFREE_API_SECRET');
define('CASHFREE_IS_PRODUCTION', false);

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'http://localhost/app/google_callback.php');

// Base URL Configuration
define('BASE_URL', 'http://localhost/app'); // Local development URL

// Connect to database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// PDO Connection for prepared statements
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?> 