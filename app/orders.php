<?php 
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<div class="container my-5">
    <h2 class="mb-4">My Orders</h2>

    <?php if(mysqli_num_rows($orders_result) > 0) { ?>
        <div class="row">
            <?php while($order = mysqli_fetch_assoc($orders_result)) { 
                $items_query = "SELECT oi.*, p.name 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = {$order['id']}";
                $items_result = mysqli_query($conn, $items_query);
            ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
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
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Ordered on <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></small>
                        </div>
                        
                        <h6>Items:</h6>
                        <ul class="list-unstyled">
                            <?php while($item = mysqli_fetch_assoc($items_result)) { ?>
                            <li class="mb-2">
                                <?php echo $item['name']; ?> × <?php echo $item['quantity']; ?>
                                <span class="float-end">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </li>
                            <?php } ?>
                        </ul>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <span>Total Amount:</span>
                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> 
                                Delivery Address: <?php echo $order['delivery_address']; ?>
                            </small>
                        </div>
                        
                        <?php if($order['payment_method'] == 'card') { ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-credit-card"></i>
                                Payment Status: <?php echo ucfirst($order['payment_status']); ?>
                            </small>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-danger btn-sm">
                            View Details
                        </a>
                        <?php if($order['status'] == 'delivered') { ?>
                        <a href="#" class="btn btn-outline-primary btn-sm float-end">
                            Reorder
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
            <h3>No orders yet</h3>
            <p class="text-muted">You haven't placed any orders yet.</p>
            <a href="menu.php" class="btn btn-danger">Start Shopping</a>
        </div>
    <?php } ?>
</div>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.9em;
    padding: 0.5em 0.8em;
}
</style>

<?php include 'includes/footer.php'; ?>
