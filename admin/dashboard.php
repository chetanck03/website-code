<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()) as today_orders,
    (SELECT COUNT(*) FROM orders WHERE status = 'pending') as pending_orders,
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(*) FROM users WHERE user_type = 'user') as total_customers";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent orders
$orders_query = "SELECT o.*, u.full_name 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 ORDER BY o.created_at DESC 
                 LIMIT 5";
$orders_result = mysqli_query($conn, $orders_query);

// Set page title
$page_title = "Dashboard";

// Start output buffering
ob_start();
?>

<!-- Welcome Section -->
<div class="welcome-section animate__animated animate__fadeIn">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h3>Welcome, Admin!</h3>
            <p class="text-muted">Here's what's happening with your store today.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <p class="current-date mb-0"><?php echo date('l, F j, Y'); ?></p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card animate__animated animate__fadeInUp" style="background: var(--stat-card-1); animation-delay: 0.1s;">
            <div class="stat-card-body">
                <div class="stat-card-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-card-info">
                    <h3><?php echo $stats['today_orders']; ?></h3>
                    <p>Today's Orders</p>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="orders.php" class="text-white">View Details <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card animate__animated animate__fadeInUp" style="background: var(--stat-card-2); animation-delay: 0.2s;">
            <div class="stat-card-body">
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-info">
                    <h3><?php echo $stats['pending_orders']; ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="orders.php" class="text-white">View Details <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card animate__animated animate__fadeInUp" style="background: var(--stat-card-3); animation-delay: 0.3s;">
            <div class="stat-card-body">
                <div class="stat-card-icon">
                    <i class="fas fa-paint-brush"></i>
                </div>
                <div class="stat-card-info">
                    <h3><?php echo $stats['total_products']; ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="products.php" class="text-white">View Details <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card animate__animated animate__fadeInUp" style="background: var(--stat-card-4); animation-delay: 0.4s;">
            <div class="stat-card-body">
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-info">
                    <h3><?php echo $stats['total_customers']; ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="customers.php" class="text-white">View Details <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-8 mb-4">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-shopping-bag me-2"></i>Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($orders_result) > 0) {
                                while($order = mysqli_fetch_assoc($orders_result)) { 
                            ?>
                            <tr>
                                <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $order['full_name']; ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($order['status']) {
                                            case 'pending': echo 'warning'; break;
                                            case 'confirmed': echo 'info'; break;
                                            case 'preparing': echo 'primary'; break;
                                            case 'on_delivery': echo 'info'; break;
                                            case 'delivered': echo 'success'; break;
                                            case 'cancelled': echo 'danger'; break;
                                        }
                                    ?>"><?php echo ucfirst($order['status']); ?></span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No recent orders found</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="add_product.php" class="quick-action-item">
                        <div class="quick-action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="quick-action-text">
                            <h6>Add Product</h6>
                            <p>Add a new product to your store</p>
                        </div>
                    </a>
                    <a href="orders.php?status=pending" class="quick-action-item">
                        <div class="quick-action-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="quick-action-text">
                            <h6>Pending Orders</h6>
                            <p>Manage pending orders</p>
                        </div>
                    </a>
                    <a href="categories.php" class="quick-action-item">
                        <div class="quick-action-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="quick-action-text">
                            <h6>Categories</h6>
                            <p>Manage product categories</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.welcome-section {
    margin-bottom: 20px;
}

.welcome-section h3 {
    font-weight: 700;
    color: var(--primary-color);
}

.current-date {
    color: var(--primary-color);
    font-weight: 500;
}

.stat-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.stat-card-body {
    padding: 20px;
    display: flex;
    align-items: center;
}

.stat-card-icon {
    font-size: 2.5rem;
    margin-right: 15px;
    opacity: 0.8;
}

.stat-card-info h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: white;
}

.stat-card-info p {
    margin-bottom: 0;
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.8);
}

.stat-card-footer {
    padding: 10px 20px;
    background-color: rgba(0, 0, 0, 0.1);
    text-align: right;
}

.stat-card-footer a {
    color: white !important;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.stat-card-footer a:hover {
    opacity: 0.8;
    color: white !important;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.quick-action-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-radius: 10px;
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    text-decoration: none;
    color: var(--text-color);
}

.quick-action-item:hover {
    transform: translateX(5px);
    border-color: var(--primary-color);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.quick-action-item:hover .quick-action-text h6,
.quick-action-item:hover .quick-action-text p {
    color: var(--text-color);
}

.quick-action-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-right: 15px;
}

.quick-action-text h6 {
    margin-bottom: 2px;
    font-weight: 600;
}

.quick-action-text p {
    margin-bottom: 0;
    font-size: 0.85rem;
    opacity: 0.7;
}

@media (max-width: 768px) {
    .stat-card-body {
        padding: 15px;
    }
    
    .stat-card-icon {
        font-size: 2rem;
        margin-right: 10px;
    }
    
    .stat-card-info h3 {
        font-size: 1.5rem;
    }
}
</style>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
include 'admin_layout.php';
?>
