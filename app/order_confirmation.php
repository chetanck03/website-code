<?php 
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

// Get order details
$order_query = "SELECT o.*, u.full_name, u.email, u.phone, c.code as coupon_code, c.discount_type, c.discount_value 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                LEFT JOIN coupons c ON o.coupon_id = c.id 
                WHERE o.id = $order_id AND o.user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    header('location: index.php');
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, p.name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
?>

<div class="container my-5">
    <?php if(isset($_SESSION['success'])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <div class="card order-confirmation-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="success-checkmark">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="confirmation-title">Order Confirmed!</h2>
                <p class="order-id">Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-section">
                        <h5 class="section-title">Order Details</h5>
                        <div class="info-item">
                            <span class="info-label">Order Date:</span>
                            <span class="info-value"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Method:</span>
                            <span class="info-value"><?php echo ucfirst($order['payment_method']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Status:</span>
                            <span class="badge <?php echo $order['payment_status'] == 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Order Status:</span>
                            <span class="badge bg-primary"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-section">
                        <h5 class="section-title">Delivery Information</h5>
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?php echo $order['full_name']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo $order['email']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo $order['phone']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo $order['delivery_address']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Order Summary</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                while ($item = mysqli_fetch_assoc($items_result)) {
                                    $subtotal += $item['price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $item['name']; ?> × <?php echo $item['quantity']; ?></td>
                                    <td class="text-end">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end">₹<?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                                <?php if ($order['discount_amount'] > 0) { ?>
                                    <tr class="text-success">
                                        <td>
                                            Discount
                                            <?php if ($order['coupon_code']) { ?>
                                                <br>
                                                <small class="text-muted">
                                                    Coupon: <?php echo $order['coupon_code']; ?>
                                                    (<?php echo $order['discount_type'] === 'percentage' ? 
                                                        $order['discount_value'] . '%' : 
                                                        '₹' . $order['discount_value']; ?> off)
                                                </small>
                                            <?php } ?>
                                        </td>
                                        <td class="text-end text-success">-₹<?php echo number_format($order['discount_amount'], 2); ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td>Delivery Fee</td>
                                    <td class="text-end">₹5.00</td>
                                </tr>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($order['order_notes']) { ?>
            <div class="order-notes mt-4">
                <h5 class="section-title">Order Notes</h5>
                <p class="notes-text"><?php echo $order['order_notes']; ?></p>
            </div>
            <?php } ?>

            <div class="text-center mt-4">
                <a href="menu.php" class="btn btn-outline-primary me-2">Continue Shopping</a>
                <a href="orders.php" class="btn btn-primary">View All Orders</a>
            </div>
        </div>
    </div>
</div>

<style>
.order-confirmation-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    background-color: var(--card-bg);
}

.success-checkmark {
    color: var(--primary-color);
    font-size: 4rem;
    margin-bottom: 1rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.confirmation-title {
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.order-id {
    color: var(--text-color);
    opacity: 0.8;
    font-size: 1.1rem;
}

.section-title {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 1.2rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color);
}

.info-section {
    background-color: var(--card-bg);
    padding: 1.5rem;
    border-radius: 10px;
    height: 100%;
    border: 1px solid var(--border-color);
}

.info-item {
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
}

.info-label {
    font-weight: 600;
    color: var(--text-color);
    opacity: 0.8;
    margin-bottom: 0.3rem;
}

.info-value {
    color: var(--text-color);
}

.order-items-section {
    background-color: var(--card-bg);
    padding: 1.5rem;
    border-radius: 10px;
    border: 1px solid var(--border-color);
}

.table {
    color: var(--text-color);
}

.table thead th {
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    padding: 12px;
}

.table tbody td {
    border-color: var(--border-color);
    padding: 12px;
}

.table tfoot td {
    border-color: var(--border-color);
    padding: 12px;
}

.total-row {
    background-color: var(--primary-color);
    color: #fff;
}

.total-row td {
    border-color: var(--primary-color) !important;
}

.badge {
    padding: 8px 12px;
    font-weight: 500;
}

.order-notes {
    background-color: var(--card-bg);
    padding: 1.5rem;
    border-radius: 10px;
    border: 1px solid var(--border-color);
}

.notes-text {
    color: var(--text-color);
    opacity: 0.9;
    margin-bottom: 0;
}

@media print {
    .navbar, .btn {
        display: none;
    }
    
    .container {
        width: 100%;
        max-width: 100%;
    }
    
    .card {
        border: none;
        box-shadow: none;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
