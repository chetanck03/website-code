<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('location: orders.php');
    exit();
}

$order_id = $_GET['id'];

// Get order details with user information
$order_query = "SELECT o.*, u.full_name, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('location: orders.php');
    exit();
}

// Get order items
$items_query = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

// Set page title
$page_title = "Order Details #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT);

// Start output buffering
ob_start();
?>

<div class="print-only" style="display: none;">
    <div class="print-header">
        <h2>Art Delivery - Order Invoice</h2>
        <p>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?> | <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Order Details</h3>
        <p class="text-muted">
            Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?> | 
            <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
        </p>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-outline-primary btn-print me-2">
            <i class="fas fa-print me-2"></i>Print
        </button>
        <a href="orders.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<div class="row">
    <!-- Order Information -->
    <div class="col-lg-4 mb-4">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2"></i>Order Information</h5>
            </div>
            <div class="card-body">
                <div class="order-status mb-4">
                    <span class="status-label">Status:</span>
                    <span class="badge bg-<?php 
                        switch($order['status']) {
                            case 'pending': echo 'warning'; break;
                            case 'confirmed': echo 'info'; break;
                            case 'preparing': echo 'primary'; break;
                            case 'on_delivery': echo 'info'; break;
                            case 'delivered': echo 'success'; break;
                            case 'cancelled': echo 'danger'; break;
                        }
                    ?> status-badge"><?php echo ucfirst($order['status']); ?></span>
                    
                    <form action="orders.php" method="POST" class="mt-3">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="input-group">
                            <select name="status" class="form-select">
                                <?php
                                $statuses = ['pending', 'confirmed', 'preparing', 'on_delivery', 'delivered', 'cancelled'];
                                foreach($statuses as $status) {
                                    $selected = $status == $order['status'] ? 'selected' : '';
                                    echo "<option value='$status' $selected>" . ucfirst($status) . "</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
                
                <div class="order-details">
                    <div class="detail-item">
                        <span class="detail-label">Payment Method:</span>
                        <span class="detail-value"><?php echo ucfirst($order['payment_method']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Payment Status:</span>
                        <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['payment_status']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer Information -->
    <div class="col-lg-4 mb-4">
        <div class="card animate__animated animate__fadeIn" style="animation-delay: 0.1s;">
            <div class="card-header">
                <h5><i class="fas fa-user me-2"></i>Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="customer-avatar mb-3">
                    <div class="avatar-placeholder"><?php echo substr($order['full_name'], 0, 1); ?></div>
                    <h6 class="customer-name"><?php echo $order['full_name']; ?></h6>
                </div>
                
                <div class="customer-details">
                    <div class="detail-item">
                        <i class="fas fa-envelope detail-icon"></i>
                        <span class="detail-value"><?php echo $order['email']; ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-phone detail-icon"></i>
                        <span class="detail-value"><?php echo $order['phone']; ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt detail-icon"></i>
                        <span class="detail-value"><?php echo $order['delivery_address']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Summary -->
    <div class="col-lg-4 mb-4">
        <div class="card animate__animated animate__fadeIn" style="animation-delay: 0.2s;">
            <div class="card-header">
                <h5><i class="fas fa-dollar-sign me-2"></i>Order Summary</h5>
            </div>
            <div class="card-body">
                <?php 
                $subtotal = 0;
                $items_count = 0;
                mysqli_data_seek($items_result, 0);
                while($item = mysqli_fetch_assoc($items_result)) {
                    $subtotal += $item['price'] * $item['quantity'];
                    $items_count += $item['quantity'];
                }
                ?>
                <div class="summary-item">
                    <span class="summary-label">Items:</span>
                    <span class="summary-value"><?php echo $items_count; ?> items</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value">₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Delivery Fee:</span>
                    <span class="summary-value">₹5.00</span>
                </div>
                <hr>
                <div class="summary-item total">
                    <span class="summary-label">Total:</span>
                    <span class="summary-value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="card animate__animated animate__fadeIn" style="animation-delay: 0.3s;">
    <div class="card-header">
        <h5><i class="fas fa-shopping-cart me-2"></i>Order Items</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 order-items-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Item</th>
                        <th style="width: 100px;">Price</th>
                        <th style="width: 100px;">Quantity</th>
                        <th style="width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($items_result, 0);
                    $subtotal = 0;
                    while($item = mysqli_fetch_assoc($items_result)) { 
                        $item_total = $item['price'] * $item['quantity'];
                        $subtotal += $item_total;
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo $item['image']; ?>" 
                                 class="product-image rounded" 
                                 alt="<?php echo $item['name']; ?>">
                        </td>
                        <td><?php echo $item['name']; ?></td>
                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₹<?php echo number_format($item_total, 2); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot class="print-visible">
                    <tr>
                        <td colspan="4" class="text-end"><strong>Subtotal</strong></td>
                        <td>₹<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Delivery Fee</strong></td>
                        <td>₹5.00</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total</strong></td>
                        <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php if($order['order_notes']) { ?>
<div class="card animate__animated animate__fadeIn mt-4" style="animation-delay: 0.4s;">
    <div class="card-header">
        <h5><i class="fas fa-sticky-note me-2"></i>Order Notes</h5>
    </div>
    <div class="card-body">
        <p class="mb-0"><?php echo $order['order_notes']; ?></p>
    </div>
</div>
<?php } ?>

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

.status-badge {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.detail-item {
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
}

.detail-label {
    font-weight: 600;
    margin-right: 10px;
    min-width: 120px;
}

.detail-icon {
    color: var(--primary-color);
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.summary-label {
    font-weight: 500;
}

.summary-item.total {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary-color);
}

.avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 10px;
}

.customer-avatar {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.customer-name {
    margin-top: 10px;
    font-weight: 600;
}

.order-items-table th, 
.order-items-table td {
    vertical-align: middle;
}

.text-end {
    text-align: right !important;
}

.print-visible {
    display: table-footer-group;
}

@media print {
    .btn-print, .no-print, .theme-switch-wrapper, .navbar-toggler, .sidebar {
        display: none !important;
    }
    
    body {
        background-color: white !important;
        color: black !important;
        font-size: 12pt;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .container-fluid, .main-content, .row, .col-lg-4, .col-lg-8 {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
        page-break-inside: avoid !important;
        width: 100% !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: black !important;
        border-bottom: 1px solid #ddd !important;
        padding: 10px 15px !important;
    }
    
    .card-body {
        padding: 15px !important;
    }
    
    .row {
        display: flex !important;
        flex-wrap: wrap !important;
    }
    
    .col-lg-4 {
        width: 33% !important;
        float: left !important;
    }
    
    h3, h5 {
        color: black !important;
        margin-top: 0 !important;
    }
    
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    
    .table th, .table td {
        border: 1px solid #ddd !important;
        padding: 8px !important;
        text-align: left !important;
    }
    
    .table th {
        background-color: #f8f9fa !important;
        font-weight: bold !important;
    }
    
    .badge {
        border: 1px solid #ddd !important;
        padding: 3px 8px !important;
        font-weight: normal !important;
        color: black !important;
        background-color: transparent !important;
    }
    
    .badge.bg-success {
        border-color: #28a745 !important;
        background-color: rgba(40, 167, 69, 0.1) !important;
    }
    
    .badge.bg-warning {
        border-color: #ffc107 !important;
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .badge.bg-danger {
        border-color: #dc3545 !important;
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    .badge.bg-info, .badge.bg-primary {
        border-color: #17a2b8 !important;
        background-color: rgba(23, 162, 184, 0.1) !important;
    }
    
    .product-image {
        max-width: 50px !important;
        max-height: 50px !important;
    }
    
    .avatar-placeholder {
        border: 1px solid #ddd !important;
        background-color: #f8f9fa !important;
        color: black !important;
    }
    
    /* Add a print header with logo and title */
    @page {
        margin: 1cm;
    }
    
    .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 10px;
    }
    
    .print-header h2 {
        margin: 0;
        font-size: 18pt;
    }
    
    .print-header p {
        margin: 5px 0 0 0;
        font-size: 10pt;
    }
    
    /* Hide elements not needed in print */
    .admin-header, .mobile-toggle {
        display: none !important;
    }
    
    /* Show print-only elements */
    .print-only {
        display: block !important;
    }
    
    /* Order summary section for print */
    .order-items-table tfoot {
        display: table-footer-group !important;
        border-top: 2px solid #ddd !important;
    }
    
    .order-items-table tfoot td {
        border-top: 2px solid #ddd !important;
    }
    
    /* Print footer */
    .print-footer {
        display: block !important;
        text-align: center;
        margin-top: 30px;
        border-top: 1px solid #ddd;
        padding-top: 10px;
        font-size: 10pt;
    }
    
    /* Force page breaks */
    .page-break-before {
        page-break-before: always !important;
    }
    
    .page-break-after {
        page-break-after: always !important;
    }
    
    /* Ensure text alignment in print */
    .text-end {
        text-align: right !important;
    }
}
</style>

<!-- Print footer -->
<div class="print-only" style="display: none;">
    <div class="print-footer">
        <p>Thank you for your order! For any questions, please contact us at info@artdelivery.com</p>
        <p>&copy; <?php echo date('Y'); ?> Art Delivery. All rights reserved.</p>
    </div>
</div>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
include 'admin_layout.php';
?>
