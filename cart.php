<?php 
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$current_time = date('Y-m-d H:i:s');

// Initialize variables for coupon and totals
$coupon_discount = 0;
$coupon_message = '';
$applied_coupon = null;
$total = 0;

// Fetch cart items
$query = "SELECT c.*, p.name, p.price, p.image 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = $user_id";
$cart_items = [];
$result = mysqli_query($conn, $query);
while ($item = mysqli_fetch_assoc($result)) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}

// Handle coupon removal
if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['applied_coupon']);
    $_SESSION['success'] = 'Coupon removed successfully.';
    header('Location: cart.php');
    exit();
}

// Handle coupon application
if (isset($_POST['apply_coupon']) && !empty($_POST['coupon_code'])) {
    $coupon_code = mysqli_real_escape_string($conn, $_POST['coupon_code']);
    
    // Check if coupon exists and is valid
    $coupon_query = "SELECT * FROM coupons WHERE code = '$coupon_code'";
    $coupon_result = mysqli_query($conn, $coupon_query);
    
    if ($coupon = mysqli_fetch_assoc($coupon_result)) {
        // Separate validation checks with specific messages
        if (!$coupon['is_active']) {
            $_SESSION['error'] = 'This coupon is not active.';
        }
        elseif (strtotime($coupon['valid_from']) > strtotime($current_time)) {
            $_SESSION['error'] = 'This coupon is not valid yet.';
        }
        elseif (strtotime($coupon['valid_until']) < strtotime($current_time)) {
            $_SESSION['error'] = 'This coupon has expired.';
        }
        elseif ($coupon['usage_limit'] !== null && $coupon['times_used'] >= $coupon['usage_limit']) {
            $_SESSION['error'] = 'This coupon has reached its usage limit.';
        }
        elseif ($total < $coupon['min_order_amount']) {
            $_SESSION['error'] = 'Minimum order amount of ₹' . number_format($coupon['min_order_amount'], 2) . ' required.';
        }
        else {
            // All validation passed
            $_SESSION['applied_coupon'] = $coupon;
            $_SESSION['success'] = 'Coupon applied successfully!';
        }
    } else {
        $_SESSION['error'] = 'Invalid coupon code.';
    }
    
    header('Location: cart.php');
    exit();
}

// Now include the header after all potential redirects
include 'includes/header.php';

// Retrieve applied coupon from session if exists
if (!$applied_coupon && isset($_SESSION['applied_coupon'])) {
    $applied_coupon = $_SESSION['applied_coupon'];
}

// Calculate discount if coupon is applied
if ($applied_coupon) {
    if ($applied_coupon['discount_type'] === 'percentage') {
        $coupon_discount = $total * ($applied_coupon['discount_value'] / 100);
        if ($applied_coupon['max_discount_amount'] && $coupon_discount > $applied_coupon['max_discount_amount']) {
            $coupon_discount = $applied_coupon['max_discount_amount'];
        }
    } else {
        $coupon_discount = $applied_coupon['discount_value'];
    }
    
    // Verify minimum order amount again
    if ($total < $applied_coupon['min_order_amount']) {
        $coupon_message = 'Minimum order amount of ₹' . number_format($applied_coupon['min_order_amount'], 2) . ' required.';
        $coupon_discount = 0;
    }
}

$final_total = $total - $coupon_discount + 5; // Adding delivery fee of ₹5

// Debug information for admin users
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    echo "<!-- Debug Info:
    Current Time: {$current_time}
    Cart Total: ₹{$total}
    Coupon Discount: ₹{$coupon_discount}
    Final Total: ₹{$final_total}
    -->";
}
?>

<div class="container my-5">
    <h2 class="mb-4 text-center animate__animated animate__fadeIn">Shopping Cart</h2>
    
    <?php if(isset($_SESSION['success'])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <?php if(!empty($cart_items)) { ?>
        <div class="row">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php foreach($cart_items as $item) { ?>
                        <div class="row mb-4 cart-item animate__animated animate__fadeIn">
                            <div class="col-md-3 col-sm-4 mb-3 mb-md-0">
                                <img src="<?php echo $item['image'] ? $item['image'] : 'https://via.placeholder.com/150'; ?>" 
                                     class="img-fluid rounded shadow-sm" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="col-md-9 col-sm-8">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <h5 class="mb-2"><?php echo $item['name']; ?></h5>
                                    <form action="cart_actions.php" method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-link text-danger p-0">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <p class="text-muted mb-2">Price: ₹<?php echo number_format($item['price'], 2); ?></p>
                                <div class="d-flex align-items-center flex-wrap">
                                    <form action="cart_actions.php" method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <div class="quantity-control d-flex align-items-center">
                                            <button type="button" class="btn btn-sm quantity-btn" onclick="decrementQuantity(this)">-</button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                min="1" max="10" class="form-control form-control-sm mx-2" style="width: 60px;"
                                                onchange="this.form.submit()">
                                            <button type="button" class="btn btn-sm quantity-btn" onclick="incrementQuantity(this)">+</button>
                                        </div>
                                    </form>
                                    <p class="mb-0 ms-3 mt-2 mt-sm-0">
                                        Subtotal: <strong>₹<?php echo number_format($item['subtotal'], 2); ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php if(next($cart_items) !== false) { ?>
                            <hr class="my-3" style="border-color: var(--border-color);">
                        <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <hr style="border-color: var(--border-color);">
                        
                        <!-- Coupon Code Form -->
                        <form method="POST" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="coupon_code" class="form-control" placeholder="Enter coupon code" 
                                       value="<?php echo $applied_coupon ? $applied_coupon['code'] : ''; ?>"
                                       <?php echo $applied_coupon ? 'readonly' : ''; ?>>
                                <?php if ($applied_coupon) { ?>
                                    <button type="submit" name="remove_coupon" class="btn btn-outline-danger">Remove</button>
                                <?php } else { ?>
                                    <button type="submit" name="apply_coupon" class="btn btn-outline-primary">Apply</button>
                                <?php } ?>
                            </div>
                            <?php if ($coupon_message) { ?>
                                <div class="<?php echo strpos($coupon_message, 'successfully') !== false ? 'text-success' : 'text-danger'; ?> small mt-1">
                                    <?php echo $coupon_message; ?>
                                </div>
                            <?php } ?>
                            <?php if ($applied_coupon && $coupon_discount > 0) { ?>
                                <div class="text-success small mt-1">
                                    Coupon applied: <?php echo $applied_coupon['discount_type'] === 'percentage' ? 
                                        $applied_coupon['discount_value'] . '% off' : 
                                        '₹' . number_format($applied_coupon['discount_value'], 2) . ' off'; ?>
                                </div>
                            <?php } ?>
                        </form>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <?php if ($coupon_discount > 0) { ?>
                            <div class="d-flex justify-content-between mb-3 text-success">
                                <span>Discount</span>
                                <span>-₹<?php echo number_format($coupon_discount, 2); ?></span>
                            </div>
                        <?php } ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Delivery Fee</span>
                            <span>₹5.00</span>
                        </div>
                        <hr style="border-color: var(--border-color);">
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong>₹<?php echo number_format($final_total, 2); ?></strong>
                        </div>
                        <a href="checkout.php" class="btn btn-primary w-100 mb-2 shadow-sm">Proceed to Checkout</a>
                        <form action="cart_actions.php" method="POST" class="mt-2">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-primary w-100">Clear Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="text-center py-5 animate__animated animate__fadeIn">
            <div class="empty-cart-icon mb-4">
                <i class="fas fa-shopping-cart fa-4x" style="color: var(--primary-color); opacity: 0.7;"></i>
            </div>
            <h3>Your cart is empty</h3>
            <p class="text-muted mb-4">Browse our menu and add some art!</p>
            <a href="menu.php" class="btn btn-primary px-4 py-2 shadow-sm">View Art</a>
        </div>
    <?php } ?>
</div>

<style>
.cart-item {
    transition: all 0.3s ease;
    border-radius: 8px;
    padding: 15px 10px;
}

.cart-item:hover {
    background-color: var(--bg-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}

/* Order Summary Input Field Styles */
.card .input-group .form-control {
    background-color: var(--input-bg);
    color: var(--input-text);
    border: 1px solid var(--input-border);
    transition: all 0.3s ease;
}

.card .input-group .form-control:focus {
    background-color: var(--input-bg);
    color: var(--input-text);
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem var(--input-focus-shadow);
}

.card .input-group .form-control::placeholder {
    color: var(--input-text);
    opacity: 0.6;
}

.card .input-group .form-control:read-only {
    background-color: var(--card-bg);
    opacity: 0.8;
}

.card .input-group .btn {
    border: 1px solid var(--primary-color);
}

.card .input-group .btn-outline-danger {
    border-color: var(--danger-color, #dc3545);
    color: var(--danger-color, #dc3545);
}

.card .input-group .btn-outline-danger:hover {
    background-color: var(--danger-color, #dc3545);
    color: #fff;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(142, 68, 173, 0.25);
}

.quantity-btn {
    background-color: var(--primary-color);
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    padding: 0;
}

.quantity-btn:hover {
    background-color: var(--hover-color);
}

.empty-cart-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

@media (max-width: 576px) {
    .cart-item {
        padding: 10px 5px;
    }
}
</style>

<script>
function decrementQuantity(btn) {
    const input = btn.nextElementSibling;
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
        input.form.submit();
    }
}

function incrementQuantity(btn) {
    const input = btn.previousElementSibling;
    const currentValue = parseInt(input.value);
    if (currentValue < 10) {
        input.value = currentValue + 1;
        input.form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
