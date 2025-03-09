<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

// Set the active page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Art Delivery Admin</title>
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
            --sidebar-bg: #f8f9fa;
            --sidebar-text: #333;
            --sidebar-hover: #e9ecef;
            --sidebar-active: #8e44ad;
            --sidebar-active-text: #fff;
            --input-bg: #fff;
            --input-text: #333;
            --input-border: #ced4da;
            --input-focus-border: #9b59b6;
            --input-focus-shadow: rgba(155, 89, 182, 0.25);
            --btn-text: #fff;
            --link-color: #8e44ad;
            --border-color: #dee2e6;
            --hover-color: #7d3c98;
            --stat-card-1: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            --stat-card-2: linear-gradient(135deg, #a569bd 0%, #9b59b6 100%);
            --stat-card-3: linear-gradient(135deg, #bb8fce 0%, #a569bd 100%);
            --stat-card-4: linear-gradient(135deg, #d2b4de 0%, #bb8fce 100%);
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
            --sidebar-bg: #1a1a1a;
            --sidebar-text: #f8f9fa;
            --sidebar-hover: #2d2d2d;
            --sidebar-active: #9b59b6;
            --sidebar-active-text: #fff;
            --input-bg: #2d2d2d;
            --input-text: #f8f9fa;
            --input-border: #444;
            --input-focus-border: #9b59b6;
            --input-focus-shadow: rgba(155, 89, 182, 0.5);
            --btn-text: #fff;
            --link-color: #bb8fce;
            --border-color: #2d2d2d;
            --hover-color: #a569bd;
            --stat-card-1: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            --stat-card-2: linear-gradient(135deg, #a569bd 0%, #9b59b6 100%);
            --stat-card-3: linear-gradient(135deg, #bb8fce 0%, #a569bd 100%);
            --stat-card-4: linear-gradient(135deg, #d2b4de 0%, #bb8fce 100%);
            --alert-success-bg: #1e3a2d;
            --alert-success-text: #75b798;
            --alert-success-border: #265e3f;
            --btn-close-filter: invert(100%);
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .sidebar {
            min-height: 100vh;
            background-color: var(--sidebar-bg);
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 15px 20px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--sidebar-active);
            color: var(--sidebar-active-text);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .main-content {
            padding: 30px;
            transition: all 0.3s ease;
        }
        
        .card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }
        
        .card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 25px;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 25px;
        }
        
        .table {
            color: var(--text-color);
        }
        
        .table thead th {
            border-bottom: 2px solid var(--border-color);
            color: var(--primary-color);
            font-weight: 600;
            padding: 15px 10px;
        }
        
        .table td {
            padding: 15px 10px;
            vertical-align: middle;
            border-color: var(--border-color);
            color: var(--text-color);
            transition: color 0.3s ease;
        }
        
        .table tr:hover {
            background-color: rgba(155, 89, 182, 0.1);
        }
        
        .table tr:hover td {
            color: var(--text-color);
        }
        
        [data-theme="dark"] .table tr:hover {
            background-color: rgba(155, 89, 182, 0.2);
            color: var(--text-color) !important;
        }
        
        [data-theme="dark"] .table tr:hover td {
            color: var(--text-color) !important;
        }
        
        [data-theme="dark"] .card:hover {
            color: var(--text-color) !important;
        }
        
        [data-theme="dark"] a:hover {
            color: var(--primary-color) !important;
        }
        
        [data-theme="dark"] .btn-outline-primary:hover {
            color: white !important;
        }
        
        [data-theme="dark"] .quick-action-item:hover .quick-action-text h6,
        [data-theme="dark"] .quick-action-item:hover .quick-action-text p {
            color: var(--text-color) !important;
        }
        
        .badge {
            padding: 7px 12px;
            font-weight: 500;
            border-radius: 30px;
        }
        
        .btn-primary, .btn-danger, .bg-primary, .bg-danger {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        
        .btn-primary:hover, .btn-danger:hover {
            background-color: var(--hover-color) !important;
            border-color: var(--hover-color) !important;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .admin-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h2 {
            margin-bottom: 0;
            font-weight: 700;
        }
        
        .admin-header .theme-switch-wrapper {
            margin-left: auto;
            display: flex;
            align-items: center;
        }
        
        .theme-switch {
            display: inline-block;
            position: relative;
            width: 60px;
            height: 30px;
            margin: 0 10px;
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
        
        .theme-text {
            font-size: 14px;
            color: var(--text-color);
        }
        
        .logo-wrapper {
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .logo-wrapper h5 {
            margin-top: 15px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .logo-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }
        
        .nav-container {
            padding: 0 15px;
        }
        
        .form-control, .form-select {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--input-border);
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 0.25rem var(--input-focus-shadow);
            background-color: var(--input-bg);
            color: var(--input-text);
        }
        
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%239b59b6' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }
        
        .alert-success {
            background-color: var(--alert-success-bg) !important;
            color: var(--alert-success-text) !important;
            border-color: var(--alert-success-border) !important;
        }
        
        .btn-close {
            filter: var(--btn-close-filter);
        }
        
        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                left: -250px;
                width: 250px !important;
                transition: all 0.3s ease;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .main-content {
                width: 100% !important;
                padding: 20px;
            }
            
            .mobile-toggle {
                display: block !important;
                margin-right: 15px;
                font-size: 1.5rem;
                color: var(--primary-color);
                cursor: pointer;
            }
        }
        
        .mobile-toggle {
            display: none;
        }
        
        /* Responsive table */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .admin-header .theme-switch-wrapper {
                margin-left: 0;
                margin-top: 15px;
            }
        }
        
        /* Global dark mode text color fixes */
        [data-theme="dark"] * {
            color-scheme: dark;
        }
        
        [data-theme="dark"] .text-muted {
            color: rgba(248, 249, 250, 0.7) !important;
        }
        
        [data-theme="dark"] h1, 
        [data-theme="dark"] h2, 
        [data-theme="dark"] h3, 
        [data-theme="dark"] h4, 
        [data-theme="dark"] h5, 
        [data-theme="dark"] h6, 
        [data-theme="dark"] p, 
        [data-theme="dark"] span, 
        [data-theme="dark"] td, 
        [data-theme="dark"] th, 
        [data-theme="dark"] div {
            color: var(--text-color);
        }
        
        [data-theme="dark"] .form-select option {
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        
        /* Fix for hover text color in dark mode */
        [data-theme="dark"] .table tr:hover {
            background-color: rgba(155, 89, 182, 0.2);
            color: var(--text-color) !important;
        }
        
        [data-theme="dark"] .table tr:hover td {
            color: var(--text-color) !important;
        }
        
        [data-theme="dark"] .card:hover {
            color: var(--text-color) !important;
        }
        
        [data-theme="dark"] a:hover {
            color: var(--primary-color) !important;
        }
        
        [data-theme="dark"] .btn-outline-primary:hover {
            color: white !important;
        }
        
        [data-theme="dark"] .quick-action-item:hover .quick-action-text h6,
        [data-theme="dark"] .quick-action-item:hover .quick-action-text p {
            color: var(--text-color) !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 px-0 sidebar" id="sidebar">
                <div class="logo-wrapper">
                    <i class="fas fa-paint-brush logo-icon"></i>
                    <h5>Admin Panel</h5>
                </div>
                <div class="nav-container">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'orders.php' || $current_page == 'view_order.php' ? 'active' : ''; ?>" href="orders.php">
                                <i class="fas fa-shopping-bag"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'products.php' || $current_page == 'add_product.php' || $current_page == 'edit_product.php' ? 'active' : ''; ?>" href="products.php">
                                <i class="fas fa-paint-brush"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                                <i class="fas fa-list"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                                <i class="fas fa-users"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'coupons.php' ? 'active' : ''; ?>" href="coupons.php">
                                <i class="fas fa-ticket-alt"></i> Coupons
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 col-md-9 main-content">
                <div class="admin-header">
                    <span class="mobile-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </span>
                    <h2><?php echo $page_title; ?></h2>
                    <div class="theme-switch-wrapper">
                        <span class="theme-text">Light</span>
                        <label class="theme-switch" for="checkbox">
                            <input type="checkbox" id="checkbox" checked />
                            <div class="slider">
                                <div class="slider-icons">
                                    <i class="fas fa-sun"></i>
                                    <i class="fas fa-moon"></i>
                                </div>
                            </div>
                        </label>
                        <span class="theme-text">Dark</span>
                    </div>
                </div>

                <?php if(isset($_SESSION['success'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>

                <!-- Main content will be here -->
                <?php echo $content; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for saved theme preference or use default dark theme
            const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : 'dark';
            
            // Apply the saved theme on page load
            document.documentElement.setAttribute('data-theme', currentTheme);
            
            // Update checkbox state based on current theme
            const themeCheckbox = document.getElementById('checkbox');
            if (themeCheckbox) {
                themeCheckbox.checked = currentTheme === 'dark';
                
                // Theme toggle functionality
                themeCheckbox.addEventListener('change', function() {
                    const newTheme = this.checked ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    
                    // Dispatch a custom event for other scripts to listen to
                    document.dispatchEvent(new CustomEvent('themeChanged', { 
                        detail: { theme: newTheme } 
                    }));
                });
            }
            
            // Mobile sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.getElementById('sidebar').classList.toggle('show');
                });
            }
            
            // Apply theme to any dynamically loaded content
            applyThemeToElements();
        });
        
        // Function to apply theme to specific elements that might need special handling
        function applyThemeToElements() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            
            // Apply theme to status badges
            document.querySelectorAll('.badge').forEach(badge => {
                if (currentTheme === 'dark') {
                    // Ensure badges are visible in dark mode
                    if (badge.classList.contains('bg-light')) {
                        badge.classList.remove('bg-light');
                        badge.classList.add('bg-secondary');
                    }
                }
            });
            
            // Apply theme to tables
            document.querySelectorAll('table').forEach(table => {
                if (currentTheme === 'dark') {
                    table.classList.add('table-dark');
                } else {
                    table.classList.remove('table-dark');
                }
            });
        }
        
        // Listen for theme changes
        document.addEventListener('themeChanged', function(e) {
            applyThemeToElements();
        });
    </script>
</body>
</html> 