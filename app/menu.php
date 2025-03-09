<?php 
include 'includes/header.php';

$category_id = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_clause = "WHERE 1=1";
if ($category_id) {
    $where_clause .= " AND category_id = $category_id";
}
if ($search) {
    $where_clause .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          $where_clause";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="animate__animated animate__fadeIn">Our Products</h2>
        </div>
        <div class="col-md-6">
            <form class="d-flex" action="menu.php" method="GET">
                <input class="form-control me-2" type="search" name="search" placeholder="Search menu..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="menu.php" class="list-group-item list-group-item-action <?php echo !$category_id ? 'active' : ''; ?>">
                        All Categories
                    </a>
                    <?php
                    $cat_query = "SELECT * FROM categories";
                    $cat_result = mysqli_query($conn, $cat_query);
                    while ($category = mysqli_fetch_assoc($cat_result)) {
                        $active = $category_id == $category['id'] ? 'active' : '';
                        echo "<a href='menu.php?category={$category['id']}' class='list-group-item list-group-item-action {$active}'>";
                        echo $category['name'];
                        echo "</a>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="row">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($product = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm product-card">
                        <div class="product-img-container">
                            <img src="<?php echo $product['image'] ? $product['image'] : 'https://via.placeholder.com/300x200'; ?>" 
                                 class="card-img-top" alt="<?php echo $product['name']; ?>" 
                                 style="height: 200px; object-fit: cover;">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="card-text category-badge"><?php echo $product['category_name']; ?></p>
                            <p class="card-text description"><?php echo $product['description']; ?></p>
                            <h6 class="card-subtitle mb-2 price">₹<?php echo number_format($product['price'], 2); ?></h6>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <?php if ($product['is_available']) { ?>
                                <form action="cart_actions.php" method="POST" class="d-flex">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="10" class="form-control me-2">
                                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                                </form>
                            <?php } else { ?>
                                <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<div class='col'><p class='text-center'>No products found.</p></div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
    border-color: var(--border-color);
}

.product-card {
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.product-img-container {
    overflow: hidden;
}

.product-img-container img {
    transition: transform 0.5s ease;
}

.product-card:hover .product-img-container img {
    transform: scale(1.05);
}

.category-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background-color: var(--primary-color);
    color: white;
    border-radius: 20px;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
}

.description {
    color: var(--text-color);
    opacity: 0.8;
    font-size: 0.9rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.price {
    color: var(--primary-color);
    font-weight: bold;
    font-size: 1.1rem;
}

.list-group-item.active {
    background-color: var(--list-group-active-bg);
    border-color: var(--list-group-active-bg);
}

.list-group-item:hover:not(.active) {
    background-color: var(--list-group-hover-bg);
    color: var(--list-group-hover-text);
}

@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
