<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $update_query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    mysqli_query($conn, $update_query);
    $_SESSION['success'] = "Order status updated successfully";
    header('location: orders.php');
    exit();
}

// Get all orders with user details
$orders_query = "SELECT o.*, u.full_name, u.email, u.phone 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 ORDER BY o.created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Set page title
$page_title = "Manage Orders";

// Start output buffering
ob_start();
?>

<div class="card animate__animated animate__fadeIn">
    <div class="card-header">
        <h5>All Orders</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders_result)) { ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $order['full_name']; ?></td>
                        <td>
                            <small>
                                <div><?php echo $order['email']; ?></div>
                                <div><?php echo $order['phone']; ?></div>
                            </small>
                        </td>
                        <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <form action="orders.php" method="POST" class="d-flex">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="form-select form-select-sm me-2" 
                                        onchange="this.form.submit()">
                                    <?php
                                    $statuses = ['pending', 'confirmed', 'preparing', 'on_delivery', 'delivered', 'cancelled'];
                                    foreach($statuses as $status) {
                                        $selected = $status == $order['status'] ? 'selected' : '';
                                        echo "<option value='$status' $selected>" . ucfirst($status) . "</option>";
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="view_order.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
include 'admin_layout.php';
?>
