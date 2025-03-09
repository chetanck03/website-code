<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    $delete_query = "DELETE FROM products WHERE id = $product_id";
    mysqli_query($conn, $delete_query);
    $_SESSION['success'] = "Product deleted successfully";
    header('location: products.php');
    exit();
}

// Get all products
$products_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.created_at DESC";
$products_result = mysqli_query($conn, $products_query);

// Set page title
$page_title = "Manage Products";

// Start output buffering
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">All Products</h3>
        <p class="text-muted">Manage your product inventory</p>
    </div>
    <a href="add_product.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Product
    </a>
</div>

<div class="card animate__animated animate__fadeIn">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-paint-brush me-2"></i>Product List</h5>
        <div class="d-flex">
            <input type="text" id="productSearch" class="form-control form-control-sm me-2" placeholder="Search products...">
            <select id="categoryFilter" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php
                $cat_query = "SELECT * FROM categories ORDER BY name";
                $cat_result = mysqli_query($conn, $cat_query);
                while ($category = mysqli_fetch_assoc($cat_result)) {
                    echo "<option value='{$category['name']}'>{$category['name']}</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="productsTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($products_result) > 0) {
                        while($product = mysqli_fetch_assoc($products_result)) { 
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo $product['image'] ? $product['image'] : 'https://via.placeholder.com/80'; ?>" 
                                 class="product-image rounded" alt="<?php echo $product['name']; ?>">
                        </td>
                        <td>
                            <h6 class="mb-0"><?php echo $product['name']; ?></h6>
                            <small class="text-muted"><?php echo substr($product['description'], 0, 50); ?>...</small>
                        </td>
                        <td data-category="<?php echo $product['category_name']; ?>"><?php echo $product['category_name']; ?></td>
                        <td>₹<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $product['is_available'] ? 'success' : 'danger'; ?>">
                                <?php echo $product['is_available'] ? 'Available' : 'Out of Stock'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-primary me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="products.php" method="POST" class="d-inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete_product" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">No products found</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.product-image:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<script>
// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const table = document.getElementById('productsTable');
    const rows = table.querySelectorAll('tbody tr');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        
        rows.forEach(row => {
            const nameCell = row.querySelector('td:nth-child(2)');
            const categoryCell = row.querySelector('td:nth-child(3)');
            
            if (!nameCell || !categoryCell) return;
            
            const name = nameCell.textContent.toLowerCase();
            const categoryText = categoryCell.getAttribute('data-category');
            
            const matchesSearch = name.includes(searchTerm);
            const matchesCategory = category === '' || categoryText === category;
            
            row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    categoryFilter.addEventListener('change', filterTable);
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
include 'admin_layout.php';
?>
