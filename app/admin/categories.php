<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

// Handle category deletion
if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    $delete_query = "DELETE FROM categories WHERE id = $category_id";
    mysqli_query($conn, $delete_query);
    $_SESSION['success'] = "Category deleted successfully";
    header('location: categories.php');
    exit();
}

// Handle category addition/update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    
    if (isset($_POST['category_id'])) {
        // Update existing category
        $category_id = $_POST['category_id'];
        $query = "UPDATE categories SET name = '$name', description = '$description', image = '$image' 
                  WHERE id = $category_id";
        $message = "Category updated successfully";
    } else {
        // Add new category
        $query = "INSERT INTO categories (name, description, image) VALUES ('$name', '$description', '$image')";
        $message = "Category added successfully";
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = $message;
        header('location: categories.php');
        exit();
    }
}

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

// Get category for editing if ID is provided
$edit_category = null;
if (isset($_GET['edit'])) {
    $category_id = $_GET['edit'];
    $edit_query = "SELECT * FROM categories WHERE id = $category_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_category = mysqli_fetch_assoc($edit_result);
}

// Set page title
$page_title = "Manage Categories";

// Start output buffering
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Categories</h3>
        <p class="text-muted">Manage product categories</p>
    </div>
</div>

<div class="row">
    <!-- Category Form -->
    <div class="col-lg-4 mb-4">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h5><i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?> me-2"></i><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h5>
            </div>
            <div class="card-body">
                <form action="categories.php" method="POST">
                    <?php if($edit_category) { ?>
                        <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                    <?php } ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?php echo $edit_category ? $edit_category['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required><?php 
                            echo $edit_category ? $edit_category['description'] : ''; 
                        ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" class="form-control" name="image" 
                               value="<?php echo $edit_category ? $edit_category['image'] : ''; ?>" required>
                        <?php if($edit_category && $edit_category['image']) { ?>
                            <div class="mt-2">
                                <img src="<?php echo $edit_category['image']; ?>" class="img-thumbnail" style="height: 100px;">
                            </div>
                        <?php } ?>
                    </div>
                    
                    <div class="d-flex">
                        <button type="submit" name="save_category" class="btn btn-primary">
                            <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
                        </button>
                        
                        <?php if($edit_category) { ?>
                            <a href="categories.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="col-lg-8">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i>All Categories</h5>
                <input type="text" id="categorySearch" class="form-control form-control-sm" style="width: 200px;" placeholder="Search categories...">
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="categoriesTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($categories_result) > 0) {
                                while($category = mysqli_fetch_assoc($categories_result)) { 
                            ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $category['image']; ?>" 
                                         class="category-image rounded" 
                                         alt="<?php echo $category['name']; ?>">
                                </td>
                                <td><?php echo $category['name']; ?></td>
                                <td><?php echo substr($category['description'], 0, 100); ?><?php echo strlen($category['description']) > 100 ? '...' : ''; ?></td>
                                <td>
                                    <a href="categories.php?edit=<?php echo $category['id']; ?>" 
                                       class="btn btn-sm btn-primary me-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="categories.php" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="category_id" 
                                               value="<?php echo $category['id']; ?>">
                                        <button type="submit" name="delete_category" 
                                                class="btn btn-sm btn-danger">
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
                                <td colspan="4" class="text-center py-4">No categories found</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.category-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.category-image:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.img-thumbnail {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}
</style>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('categorySearch');
    const table = document.getElementById('categoriesTable');
    const rows = table.querySelectorAll('tbody tr');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const nameCell = row.querySelector('td:nth-child(2)');
            const descCell = row.querySelector('td:nth-child(3)');
            
            if (!nameCell || !descCell) return;
            
            const name = nameCell.textContent.toLowerCase();
            const desc = descCell.textContent.toLowerCase();
            
            if (name.includes(searchTerm) || desc.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
include 'admin_layout.php';
?>
