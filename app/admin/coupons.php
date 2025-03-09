<?php
$page_title = "Manage Coupons";
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Ensure only admin can access this page
if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $code = $_POST['code'];
            $discount_type = $_POST['discount_type'];
            $discount_value = floatval($_POST['discount_value']);
            $min_order_amount = floatval($_POST['min_order_amount']);
            $max_discount_amount = !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : NULL;
            $valid_from = $_POST['valid_from'];
            $valid_until = $_POST['valid_until'];
            $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : NULL;
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($_POST['action'] === 'add') {
                $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, max_discount_amount, valid_from, valid_until, usage_limit, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $discount_type, $discount_value, $min_order_amount, $max_discount_amount, $valid_from, $valid_until, $usage_limit, $is_active]);
                $_SESSION['success'] = "Coupon created successfully!";
            } else {
                $id = $_POST['coupon_id'];
                $stmt = $pdo->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, max_discount_amount = ?, valid_from = ?, valid_until = ?, usage_limit = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$code, $discount_type, $discount_value, $min_order_amount, $max_discount_amount, $valid_from, $valid_until, $usage_limit, $is_active, $id]);
                $_SESSION['success'] = "Coupon updated successfully!";
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['coupon_id'])) {
            $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->execute([$_POST['coupon_id']]);
            $_SESSION['success'] = "Coupon deleted successfully!";
        }
        header('Location: coupons.php');
        exit();
    }
}

// Fetch all coupons
$stmt = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC");
$coupons = $stmt->fetchAll();

// Start output buffering
ob_start();
?>

<style>
/* Custom styles for coupons page */
.table {
    --bs-table-bg: transparent;
    --bs-table-color: var(--text-color);
    border-color: var(--border-color);
}

[data-theme="dark"] .table {
    --bs-table-bg: var(--card-bg);
    --bs-table-color: var(--text-color);
    border-color: var(--border-color);
}

.table thead th {
    background-color: var(--card-bg);
    color: var(--primary-color);
    border-bottom: 2px solid var(--border-color);
    font-weight: 600;
    padding: 15px;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
    border-color: var(--border-color);
}

.table tr:hover {
    background-color: var(--sidebar-hover) !important;
}

/* Modal styles */
.modal-content {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
}

.modal-header {
    border-bottom: 1px solid var(--border-color);
    background-color: var(--card-bg);
}

.modal-footer {
    border-top: 1px solid var(--border-color);
    background-color: var(--card-bg);
}

.modal-title {
    color: var(--text-color);
}

/* Form styles */
.form-control, .form-select {
    background-color: var(--input-bg);
    border-color: var(--input-border);
    color: var(--input-text);
}

.form-control:focus, .form-select:focus {
    background-color: var(--input-bg);
    border-color: var(--input-focus-border);
    color: var(--input-text);
    box-shadow: 0 0 0 0.25rem var(--input-focus-shadow);
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.text-muted {
    color: var(--text-color) !important;
    opacity: 0.7;
}

/* Badge styles */
.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

.badge.bg-info {
    background-color: var(--primary-color) !important;
    color: white;
}

/* Button styles */
.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.875rem;
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: var(--hover-color);
    border-color: var(--hover-color);
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
}

/* Modal close button */
.btn-close {
    filter: var(--btn-close-filter);
}

/* Card styles */
.card {
    border: none;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    background-color: var(--card-bg);
}

.card-title {
    color: var(--primary-color);
    font-weight: 600;
}

/* Responsive styles */
@media (max-width: 768px) {
    .table-responsive {
        border-color: var(--border-color);
    }
    
    .btn-sm {
        padding: 0.3rem 0.6rem;
    }
}
</style>

<!-- Main content -->
<div class="card">
    <div class="card-body">
        <!-- Add New Coupon Button -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">All Coupons</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal">
                <i class="fas fa-plus"></i> Add New Coupon
            </button>
        </div>

        <!-- Coupons Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Min Order</th>
                        <th>Max Discount</th>
                        <th>Valid From</th>
                        <th>Valid Until</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td><?= htmlspecialchars($coupon['code']) ?></td>
                        <td><?= ucfirst($coupon['discount_type']) ?></td>
                        <td><?= $coupon['discount_type'] === 'percentage' ? $coupon['discount_value'] . '%' : '₹' . $coupon['discount_value'] ?></td>
                        <td>₹<?= $coupon['min_order_amount'] ?></td>
                        <td><?= $coupon['max_discount_amount'] ? '₹' . $coupon['max_discount_amount'] : '-' ?></td>
                        <td><?= date('Y-m-d', strtotime($coupon['valid_from'])) ?></td>
                        <td><?= date('Y-m-d', strtotime($coupon['valid_until'])) ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?= $coupon['times_used'] ?><?= $coupon['usage_limit'] ? '/' . $coupon['usage_limit'] : '' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $coupon['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $coupon['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-coupon" 
                                    data-coupon='<?= json_encode($coupon) ?>'
                                    data-bs-toggle="modal" 
                                    data-bs-target="#couponModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="coupon_id" value="<?= $coupon['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="coupon_id" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Coupon Code</label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Discount Type</label>
                        <select class="form-select" name="discount_type" required>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Discount Value</label>
                        <input type="number" class="form-control" name="discount_value" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Minimum Order Amount</label>
                        <input type="number" class="form-control" name="min_order_amount" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Maximum Discount Amount</label>
                        <input type="number" class="form-control" name="max_discount_amount" step="0.01">
                        <small class="text-muted">Leave empty for no limit</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Valid From</label>
                        <input type="datetime-local" class="form-control" name="valid_from" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Valid Until</label>
                        <input type="datetime-local" class="form-control" name="valid_until" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Usage Limit</label>
                        <input type="number" class="form-control" name="usage_limit">
                        <small class="text-muted">Leave empty for unlimited usage</small>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit coupon button clicks
    document.querySelectorAll('.edit-coupon').forEach(button => {
        button.addEventListener('click', function() {
            const coupon = JSON.parse(this.dataset.coupon);
            const form = document.querySelector('#couponModal form');
            
            form.querySelector('[name="action"]').value = 'edit';
            form.querySelector('[name="coupon_id"]').value = coupon.id;
            form.querySelector('[name="code"]').value = coupon.code;
            form.querySelector('[name="discount_type"]').value = coupon.discount_type;
            form.querySelector('[name="discount_value"]').value = coupon.discount_value;
            form.querySelector('[name="min_order_amount"]').value = coupon.min_order_amount;
            form.querySelector('[name="max_discount_amount"]').value = coupon.max_discount_amount || '';
            form.querySelector('[name="valid_from"]').value = coupon.valid_from.slice(0, 16);
            form.querySelector('[name="valid_until"]').value = coupon.valid_until.slice(0, 16);
            form.querySelector('[name="usage_limit"]').value = coupon.usage_limit || '';
            form.querySelector('[name="is_active"]').checked = coupon.is_active == 1;
            
            // Update modal title
            document.querySelector('#couponModal .modal-title').textContent = 'Edit Coupon';
        });
    });

    // Reset form when adding new coupon
    document.querySelector('[data-bs-target="#couponModal"]').addEventListener('click', function() {
        const form = document.querySelector('#couponModal form');
        form.reset();
        form.querySelector('[name="action"]').value = 'add';
        form.querySelector('[name="coupon_id"]').value = '';
        
        // Reset modal title
        document.querySelector('#couponModal .modal-title').textContent = 'Add New Coupon';
    });
});
</script>

<?php
$content = ob_get_clean();
include 'admin_layout.php';
?> 