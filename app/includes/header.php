<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db_connect.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCartCount() {
    global $conn;
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['count'] ? $row['count'] : 0;
    }
    return 0;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            /* Light Theme Variables */
            --primary-color: #8e44ad;
            --secondary-color: #9b59b6;
            --text-color: #333;
            --bg-color: #fff;
            --card-bg: #fff;
            --nav-bg: #f8f9fa;
            --footer-bg: #343a40;
            --footer-text: #fff;
            --border-color: #dee2e6;
            --hover-color: #7d3c98;
            --input-bg: #fff;
            --input-text: #333;
            --input-border: #ced4da;
            --input-focus-border: #9b59b6;
            --input-focus-shadow: rgba(155, 89, 182, 0.25);
            --card-header-bg: #9b59b6;
            --card-header-text: #fff;
            --list-group-active-bg: #8e44ad;
            --list-group-active-text: #fff;
            --list-group-hover-bg: #f8f9fa;
            --list-group-hover-text: #8e44ad;
            --why-choose-bg: #f8f9fa;
            --alert-success-bg: #d4edda;
            --alert-success-text: #155724;
            --alert-success-border: #c3e6cb;
            --btn-close-filter: invert(0%);
        }
        
        [data-theme="dark"] {
            /* Dark Theme Variables */
            --primary-color: #9b59b6;
            --secondary-color: #8e44ad;
            --text-color: #f8f9fa;
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --nav-bg: #1e1e1e;
            --footer-bg: #121212;
            --footer-text: #f8f9fa;
            --border-color: #2d2d2d;
            --hover-color: #a569bd;
            --input-bg: #2d2d2d;
            --input-text: #f8f9fa;
            --input-border: #444;
            --input-focus-border: #9b59b6;
            --input-focus-shadow: rgba(155, 89, 182, 0.5);
            --card-header-bg: #8e44ad;
            --card-header-text: #f8f9fa;
            --list-group-active-bg: #9b59b6;
            --list-group-active-text: #f8f9fa;
            --list-group-hover-bg: #2d2d2d;
            --list-group-hover-text: #9b59b6;
            --why-choose-bg: #1e1e1e;
            --alert-success-bg: #1e3a2d;
            --alert-success-text: #75b798;
            --alert-success-border: #265e3f;
            --btn-close-filter: invert(100%);
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .navbar {
            background-color: var(--nav-bg) !important;
            transition: all 0.3s ease;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: bold;
            font-size: 24px;
            transition: all 0.3s ease;
        }
        
        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-primary, .btn-danger {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            transition: all 0.3s ease;
            color: #fff !important;
        }
        
        .btn-primary:hover, .btn-danger:hover {
            background-color: var(--hover-color) !important;
            border-color: var(--hover-color) !important;
        }
        
        .btn-outline-primary, .btn-outline-danger {
            color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover, .btn-outline-danger:hover {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: #fff !important;
        }
        
        /* Input fields styling */
        .form-control {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--input-border);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 0.25rem var(--input-focus-shadow);
            background-color: var(--input-bg);
            color: var(--input-text);
        }
        
        /* Card header styling */
        .card-header {
            background-color: var(--card-header-bg) !important;
            color: var(--card-header-text) !important;
            border-color: var(--border-color);
        }
        
        /* List group styling */
        .list-group-item {
            background-color: var(--card-bg);
            color: var(--text-color);
            border-color: var(--border-color);
            transition: all 0.3s ease;
        }
        
        .list-group-item.active {
            background-color: var(--list-group-active-bg) !important;
            border-color: var(--list-group-active-bg) !important;
            color: var(--list-group-active-text) !important;
        }
        
        .list-group-item:hover:not(.active) {
            background-color: var(--list-group-hover-bg);
            color: var(--list-group-hover-text);
        }
        
        /* Why Choose Us section */
        .bg-light {
            background-color: var(--why-choose-bg) !important;
        }
        
        /* Alert styling */
        .alert-success {
            background-color: var(--alert-success-bg) !important;
            color: var(--alert-success-text) !important;
            border-color: var(--alert-success-border) !important;
        }
        
        .btn-close {
            filter: var(--btn-close-filter);
        }
        
        /* Footer styling */
        footer {
            background-color: var(--footer-bg) !important;
            color: var(--footer-text) !important;
        }
        
        footer a {
            color: var(--footer-text) !important;
            transition: all 0.3s ease;
        }
        
        footer a:hover {
            color: var(--primary-color) !important;
            text-decoration: none;
        }
        
        /* Theme toggle switch */
        .theme-switch-wrapper {
            display: flex;
            align-items: center;
            margin-left: 15px;
        }
        
        .theme-switch {
            display: inline-block;
            position: relative;
            width: 60px;
            height: 30px;
        }
        
        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(30px);
        }
        
        .slider-icons {
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
            align-items: center;
            height: 100%;
            color: white;
            font-size: 14px;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 20px;
            }
            
            .theme-switch-wrapper {
                margin: 10px 0;
            }
        }
        
        /* Text muted color */
        .text-muted {
            color: var(--text-color) !important;
            opacity: 0.7;
        }
        
        /* Card footer */
        .card-footer {
            background-color: var(--card-bg) !important;
            border-color: var(--border-color);
        }
        
        /* Carousel controls */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: drop-shadow(0px 0px 3px rgba(0, 0, 0, 0.7));
        }
        
        /* Hero section overlay */
        .hero-section::before {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5));
        }

        /* Navbar styling */
        .navbar {
            background-color: var(--nav-bg) !important;
            transition: all 0.3s ease;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Card styling */
        .card {
            background-color: var(--card-bg);
            border-color: var(--border-color);
            transition: all 0.3s ease;
            color: var(--text-color);
        }
        
        /* Navbar toggler (hamburger) icon for dark mode */
        [data-theme="dark"] .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        [data-theme="dark"] .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        
        .navbar-toggler {
            padding: 0.25rem 0.5rem;
            font-size: 1.25rem;
            line-height: 1;
            background-color: transparent;
            border: 1px solid var(--border-color);
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(142, 68, 173, 0.25) !important;
            outline: none;
        }
        
        .navbar-toggler:hover {
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-paint-brush"></i> Art Delivery
            </a>
            <div class="d-flex align-items-center">
                <div class="theme-switch-wrapper d-flex align-items-center">
                    <label class="theme-switch" for="checkbox">
                        <input type="checkbox" id="checkbox" />
                        <div class="slider">
                            <div class="slider-icons">
                                <i class="fas fa-sun"></i>
                                <i class="fas fa-moon"></i>
                            </div>
                        </div>
                    </label>
                </div>
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">Menu</a>
                    </li>
                    <?php if(isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">My Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count"><?php echo getCartCount(); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
