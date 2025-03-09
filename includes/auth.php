<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

// Function to require admin access
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current user type
function getCurrentUserType() {
    return $_SESSION['user_type'] ?? null;
}
?> 