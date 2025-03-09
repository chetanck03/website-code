<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    $query = "INSERT INTO products (category_id, name, description, price, image, is_available) 
              VALUES ($category_id, '$name', '$description', $price, '$image', $is_available)";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Product added successfully";
        header('location: products.php');
        exit();
    } else {
        $error = "Error adding product: " . mysqli_error($conn);
    }
}

// Get categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

// Set page title
$page_title = "Add New Product";

// Start output buffering
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Add New Product</h3>
        <p class="text-muted">Create a new product in your inventory</p>
    </div>
    <a href="products.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Products
    </a>
</div>

<?php if(isset($error)) { ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php } ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h5><i class="fas fa-edit me-2"></i>Product Information</h5>
            </div>
            <div class="card-body">
                <form action="add_product.php" method="POST" id="productForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php while($category = mysqli_fetch_assoc($categories_result)) { ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                        <small class="text-muted">Provide a detailed description of the product</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image" id="imageUrl" required>
                            <small class="text-muted">Enter a valid image URL</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_available" id="isAvailable" checked>
                            <label class="form-check-label" for="isAvailable">
                                Available for Order
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card animate__animated animate__fadeIn" style="animation-delay: 0.1s;">
            <div class="card-header">
                <h5><i class="fas fa-image me-2"></i>Image Preview</h5>
            </div>
            <div class="card-body text-center">
                <div class="image-preview mb-3">
                    <img id="previewImage" src="https://via.placeholder.com/300x300?text=Product+Image" class="img-fluid rounded" alt="Product Preview">
                </div>
                <p class="text-muted small">Preview of your product image</p>
            </div>
        </div>
        
        <div class="card mt-4 animate__animated animate__fadeIn" style="animation-delay: 0.2s;">
            <div class="card-header">
                <h5><i class="fas fa-save me-2"></i>Save</h5>
            </div>
            <div class="card-body">
                <button type="submit" form="productForm" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-plus-circle me-2"></i>Add Product
                </button>
                <a href="products.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times-circle me-2"></i>Cancel
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.image-preview {
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 10px;
    background-color: var(--bg-color);
    transition: all 0.3s ease;
}

.image-preview img {
    max-height: 250px;
    object-fit: contain;
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.input-group-text {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageUrlInput = document.getElementById('imageUrl');
    const previewImage = document.getElementById('previewImage');
    
    // Update image preview when URL changes
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
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
include 'admin_layout.php';
?>
