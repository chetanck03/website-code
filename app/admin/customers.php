<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

// Handle customer deletion
if (isset($_POST['delete_customer'])) {
    $user_id = $_POST['user_id'];
    $delete_query = "DELETE FROM users WHERE id = $user_id AND user_type = 'user'";
    mysqli_query($conn, $delete_query);
    $_SESSION['success'] = "Customer deleted successfully";
    header('location: customers.php');
    exit();
}

// Get all customers (excluding admins)
$customers_query = "SELECT * FROM users WHERE user_type = 'user' ORDER BY created_at DESC";
$customers_result = mysqli_query($conn, $customers_query);

// Get customer statistics
$stats_query = "SELECT 
    COUNT(*) as total_customers,
    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_today,
    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_this_week
    FROM users WHERE user_type = 'user'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Set page title
$page_title = "Manage Customers";

// Start output buffering
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Customers</h3>
        <p class="text-muted">Manage your customer accounts</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="stat-card animate__animated animate__fadeInUp" style="background: var(--stat-card-1); animation-delay: 0.1s;">
            <div class="stat-card-body">
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-info">
                    <h3><?php echo $stats['total_customers']; ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="stat-card animate__animated animate__fadeInUp" style="background: var(--stat-card-2); animation-delay: 0.2s;">
            <div class="stat-card-body">
                <div class="stat-card-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-card-info">
                    <h3><?php echo $stats['new_today']; ?></h3>
                    <p>New Today</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="stat-card animate__animated animate__fadeInUp" style="background: var(--stat-card-3); animation-delay: 0.3s;">
            <div class="stat-card-body">
                <div class="stat-card-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-card-info">
                    <h3><?php echo $stats['new_this_week']; ?></h3>
                    <p>New This Week</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card animate__animated animate__fadeIn">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-users me-2"></i>Customer List</h5>
        <input type="text" id="customerSearch" class="form-control form-control-sm" style="width: 200px;" placeholder="Search customers...">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="customersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($customers_result) > 0) {
                        while($customer = mysqli_fetch_assoc($customers_result)) { 
                            // Get customer's order count
                            $customer_id = $customer['id'];
                            $orders_query = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = $customer_id";
                            $orders_result = mysqli_query($conn, $orders_query);
                            $order_count = mysqli_fetch_assoc($orders_result)['order_count'];
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder me-2"><?php echo substr($customer['full_name'], 0, 1); ?></div>
                                <div><?php echo $customer['full_name']; ?></div>
                            </div>
                        </td>
                        <td><?php echo $customer['email']; ?></td>
                        <td><?php echo $customer['phone']; ?></td>
                        <td>
                            <small><?php echo substr($customer['address'], 0, 50); ?><?php echo strlen($customer['address']) > 50 ? '...' : ''; ?></small>
                        </td>
                        <td>
                            <?php echo date('M j, Y', strtotime($customer['created_at'])); ?>
                            <br>
                            <span class="badge bg-<?php echo $order_count > 0 ? 'success' : 'secondary'; ?>">
                                <?php echo $order_count; ?> orders
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $customer['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $customer['id']; ?>">
                                    <li>
                                        <a class="dropdown-item" href="orders.php?user_id=<?php echo $customer['id']; ?>">
                                            <i class="fas fa-shopping-bag me-2"></i>View Orders
                                        </a>
                                    </li>
                                    <li>
                                        <form action="customers.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                            <input type="hidden" name="user_id" value="<?php echo $customer['id']; ?>">
                                            <button type="submit" name="delete_customer" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">No customers found</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
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

.dropdown-menu {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

.dropdown-item {
    color: var(--text-color);
}

.dropdown-item:hover {
    background-color: var(--list-group-hover-bg);
    color: var(--primary-color);
}

.dropdown-item.text-danger:hover {
    color: #dc3545 !important;
}
</style>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customerSearch');
    const table = document.getElementById('customersTable');
    const rows = table.querySelectorAll('tbody tr');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const nameCell = row.querySelector('td:nth-child(1)');
            const emailCell = row.querySelector('td:nth-child(2)');
            const phoneCell = row.querySelector('td:nth-child(3)');
            
            if (!nameCell || !emailCell || !phoneCell) return;
            
            const name = nameCell.textContent.toLowerCase();
            const email = emailCell.textContent.toLowerCase();
            const phone = phoneCell.textContent.toLowerCase();
            
            if (name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm)) {
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
