<?php 
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

// Verify order belongs to user
$order_query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    header('location: index.php');
    exit();
}

$order = mysqli_fetch_assoc($order_result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // In a real application, you would integrate with a payment gateway here
    // For demonstration, we'll just simulate a successful payment
    
    $update_query = "UPDATE orders SET payment_status = 'completed' WHERE id = $order_id";
    mysqli_query($conn, $update_query);
    
    $_SESSION['success'] = "Payment processed successfully!";
    header("location: order_confirmation.php?order_id=$order_id");
    exit();
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Payment Details</h3>
                    <div class="alert alert-info">
                        <strong>Order Total: $<?php echo number_format($order['total_amount'], 2); ?></strong>
                    </div>
                    <form action="payment.php?order_id=<?php echo $order_id; ?>" method="POST" id="paymentForm">
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="1234 5678 9012 3456" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" placeholder="MM/YY" required>
                            </div>
                            <div class="col">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" placeholder="123" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Card Holder Name</label>
                            <input type="text" class="form-control" placeholder="John Doe" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Pay Now</button>
                    </form>
                    <div class="text-center mt-3">
                        <img src="https://www.pngitem.com/pimgs/m/291-2918799_stripe-payment-icon-png-transparent-png.png" 
                             alt="Secure Payment" style="height: 40px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

#paymentForm {
    max-width: 400px;
    margin: 0 auto;
}
</style>

<script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    // In a real application, you would handle payment processing here
    // For demonstration, we'll just submit the form
});
</script>

<?php include 'includes/footer.php'; ?>
