<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

$product_id = $_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    $query = "UPDATE products 
              SET category_id = $category_id,
                  name = '$name',
                  description = '$description',
                  price = $price,
                  image = '$image',
                  is_available = $is_available
              WHERE id = $product_id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Product updated successfully";
        header('location: products.php');
        exit();
    } else {
        $error = "Error updating product: " . mysqli_error($conn);
    }
}

// Get product details
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = mysqli_query($conn, $product_query);
$product = mysqli_fetch_assoc($product_result);

// Get categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Art Delivery Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css" rel="stylesheet">
    <style>
        :root {
            /* Light Theme Variables */
            --primary-color:#8e44ad;
            --text-color: #333333;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --sidebar-bg: #343a40;
            --sidebar-text: #ffffff;
            --hover-bg: #495057;
            --input-bg: #ffffff;
            --input-text: #333333;
        }

        [data-theme="dark"] {
            /* Dark Theme Variables */
            --text-color: #e4e6eb;
            --bg-color: #18191a;
            --card-bg: #242526;
            --border-color: #3e4042;
            --sidebar-bg: #242526;
            --sidebar-text: #e4e6eb;
            --hover-bg: #3a3b3c;
            --input-bg: #3a3b3c;
            --input-text: #e4e6eb;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .sidebar {
            min-height: 100vh;
            background-color: var(--sidebar-bg);
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 15px 20px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--hover-bg);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .main-content {
            padding: 20px;
            background-color: var(--bg-color);
        }

        .card {
            background-color: var(--card-bg);
            border-color: var(--border-color);
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .product-preview {
            max-width: 100%;
            height: 300px;
            object-fit: contain;
            border-radius: 10px;
            margin-top: 10px;
            border: 2px dashed var(--border-color);
            padding: 10px;
            background-color: var(--bg-color);
        }

        .form-control, .form-select {
            background-color: var(--input-bg);
            border-color: var(--border-color);
            color: var(--input-text);
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--input-bg);
            border-color: var(--primary-color);
            color: var(--input-text);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .dropzone {
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            background-color: var(--bg-color);
            min-height: 200px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dropzone:hover {
            border-color: var(--primary-color);
        }

        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            margin-right: 10px;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .input-group-text {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .category-preview {
            display: inline-block;
            padding: 5px 15px;
            background-color: var(--bg-color);
            border-radius: 20px;
            margin: 5px;
            font-size: 0.9em;
        }

        .category-preview.selected {
            background-color: var(--primary-color);
            color: white;
        }

        .theme-switch {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .theme-switch:hover {
            transform: scale(1.1);
        }

        .image-preview-container {
            background-color: var(--card-bg);
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 10px;
            text-align: center;
        }

        .image-preview {
            max-width: 100%;
            height: 300px;
            object-fit: contain;
        }

        .image-input-group {
            position: relative;
        }

        .image-input-group .form-control {
            padding-right: 100px;
        }

        .image-input-group .btn-check-image {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Breadcrumb styling */
        .breadcrumb {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--text-color);
        }

        /* Form validation styles */
        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: var(--primary-color);
            background-color: var(--input-bg);
        }

        .invalid-feedback {
            color: var(--primary-color);
        }

        /* Responsive Styles */
        .image-preview-container {
            background-color: var(--card-bg);
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .image-preview-container img {
            max-height: 300px;
            width: auto;
            object-fit: contain;
        }

        @media (max-width: 768px) {
            .image-preview-container {
                padding: 10px;
            }
            
            .image-preview-container img {
                max-height: 200px;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .admin-header > div:not(:last-child) {
                margin-bottom: 1rem;
            }
            
            .breadcrumb {
                margin-top: 0.5rem;
            }
        }

        /* Touch-friendly Form Controls */
        @media (max-width: 768px) {
            .form-control,
            .form-select,
            .btn {
                min-height: 44px;
                font-size: 16px;
            }
            
            .form-check-input {
                width: 1.5em;
                height: 1.5em;
                margin-top: 0.25em;
            }
            
            .form-check-label {
                padding-left: 0.5rem;
                font-size: 16px;
            }
        }

        /* Improved Card Styles */
        .card {
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        /* Breadcrumb Improvements */
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--text-color);
        }
    </style>
</head>
<body>
    <!-- Theme Toggle Button -->
    <div class="theme-switch" id="themeSwitch">
        <i class="fas fa-sun"></i>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="text-center py-4">
                    <i class="fas fa-paint-brush fa-2x text-white"></i>
                    <h5 class="text-white mt-2">Admin Panel</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-bag me-2"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">
                            <i class="fas fa-paint-brush me-2"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-list me-2"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">
                            <i class="fas fa-users me-2"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="admin-header mb-4">
                    <span class="mobile-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </span>
                    <div>
                        <h2 class="mb-1">Edit Product</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                                <li class="breadcrumb-item active">Edit Product</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-none d-md-block">
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                    </div>
                </div>

                <?php if(isset($error)) { ?>
                    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>

                <div class="row g-4">
                    <!-- Product Form -->
                    <div class="col-12 col-lg-8">
                        <div class="card animate__animated animate__fadeIn">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Product Information</h5>
                            </div>
                            <div class="card-body">
                                <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" id="editProductForm" class="needs-validation" novalidate>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Product Name</label>
                                            <input type="text" class="form-control" name="name" value="<?php echo $product['name']; ?>" required>
                                            <div class="invalid-feedback">Please enter a product name.</div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Category</label>
                                            <select class="form-select" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php while($category = mysqli_fetch_assoc($categories_result)) { ?>
                                                    <option value="<?php echo $category['id']; ?>" 
                                                            <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $category['name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a category.</div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="4" required><?php echo $product['description']; ?></textarea>
                                            <div class="invalid-feedback">Please enter a description.</div>
                                            <small class="text-muted">Provide a detailed description of your product</small>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" class="form-control" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                                                <div class="invalid-feedback">Please enter a valid price.</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Image URL</label>
                                            <input type="url" class="form-control" name="image" id="imageUrl" value="<?php echo $product['image']; ?>" required>
                                            <div class="invalid-feedback">Please enter a valid image URL.</div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_available" id="isAvailable" <?php echo $product['is_available'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="isAvailable">Available for Order</label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Preview and Actions -->
                    <div class="col-12 col-lg-4">
                        <div class="card animate__animated animate__fadeIn">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-image me-2"></i>Image Preview</h5>
                            </div>
                            <div class="card-body">
                                <div class="image-preview-container mb-3">
                                    <img id="previewImage" src="<?php echo $product['image'] ? $product['image'] : 'https://via.placeholder.com/300x300?text=Product+Image'; ?>" 
                                         class="img-fluid rounded" alt="Product Preview">
                                </div>
                                <p class="text-muted small text-center mb-0">Preview of your product image</p>
                            </div>
                        </div>

                        <div class="card mt-4 animate__animated animate__fadeIn">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-save me-2"></i>Save Changes</h5>
                            </div>
                            <div class="card-body">
                                <button type="submit" form="editProductForm" class="btn btn-primary w-100 mb-3">
                                    <i class="fas fa-save me-2"></i>Update Product
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times-circle me-2"></i>Cancel
                                </a>
                            </div>
                        </div>

                        <!-- Mobile Back Button -->
                        <div class="d-block d-md-none mt-4">
                            <a href="products.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-arrow-left me-2"></i>Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme Toggle Functionality
            const themeSwitch = document.getElementById('themeSwitch');
            const html = document.documentElement;
            const themeSwitchIcon = themeSwitch.querySelector('i');

            // Load saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);

            themeSwitch.addEventListener('click', function() {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
            });

            function updateThemeIcon(theme) {
                themeSwitchIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }

            // Image URL Handling
            const imageUrlInput = document.getElementById('imageUrl');
            const previewImage = document.getElementById('previewImage');
            
            imageUrlInput.addEventListener('input', function() {
                const url = this.value.trim();
                if (url) {
                    previewImage.src = url;
                    previewImage.onerror = function() {
                        previewImage.src = 'https://via.placeholder.com/300x300?text=Invalid+Image+URL';
                    };
                } else {
                    previewImage.src = 'https://via.placeholder.com/300x300?text=Product+Image';
                }
            });

            // Form Validation
            const form = document.getElementById('editProductForm');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    </script>
</body>
</html>
