<?php
$page_title = 'Sales';
$current_page = 'sales';

require_once 'config/database.php';
require_once 'includes/functions.php';

$error = null;
$success = null;

// Handle sales submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = (int)$_POST['quantity'];
    $customer_name = trim($_POST['customer_name']);
    $payment_method = $_POST['payment_method'] ?? 'cash';

    if (!$product_id || $quantity <= 0) {
        $error = "Please select a product and enter a valid quantity.";
    } else {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Get product details
            $stmt = $conn->prepare("SELECT name, quantity as current_stock, unit_price FROM products WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if (!$product) {
                throw new Exception("Product not found or access denied.");
            }

            if ($quantity > $product['current_stock']) {
                throw new Exception("Insufficient stock. Only " . $product['current_stock'] . " available.");
            }

            // Calculate total amount
            $total_amount = $quantity * $product['unit_price'];

            // Record sale
            $stmt = $conn->prepare("INSERT INTO sales (user_id, product_id, quantity, unit_price, total_amount, customer_name, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiddss", $_SESSION['user_id'], $product_id, $quantity, $product['unit_price'], $total_amount, $customer_name, $payment_method);
            $stmt->execute();

            // Update stock
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $product_id, $_SESSION['user_id']);
            $stmt->execute();

            // Add stock movement record
            $stmt = $conn->prepare("INSERT INTO stock_movements (user_id, product_id, type, quantity, reason) VALUES (?, ?, 'out', ?, 'Sale')");
            $stmt->bind_param("iii", $_SESSION['user_id'], $product_id, $quantity);
            $stmt->execute();

            // Commit transaction
            $conn->commit();
            $success = "Sale recorded successfully! Total Amount: ₦" . number_format($total_amount, 2);
        } catch (Exception $e) {
            $conn->rollback();
            error_log($e->getMessage());
            $error = $e->getMessage();
        }
    }
}

// Get products for dropdown
try {
    $stmt = $conn->prepare("SELECT id, name, quantity, unit_price FROM products WHERE quantity > 0 AND user_id = ? ORDER BY name");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while fetching products.";
}

// Get recent sales
try {
    $stmt = $conn->prepare("
        SELECT s.*, p.name as product_name 
        FROM sales s
        JOIN products p ON s.product_id = p.id
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $sales = [];
    while ($row = $result->fetch_assoc()) {
        $sales[] = $row;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while fetching sales.";
}

// Start output buffering
ob_start();
?>

<!-- Sales Content -->
<div class="row g-4">
    <!-- Record Sale Form -->
    <div class="col-md-4">
        <div class="dashboard-card">
            <h5 class="mb-4">Record Sale</h5>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Product *</label>
                    <select class="form-select" name="product_id" id="product-select" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>" 
                                data-price="<?php echo $product['unit_price']; ?>"
                                data-stock="<?php echo $product['quantity']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?> 
                            (Stock: <?php echo $product['quantity']; ?>) - 
                            ₦<?php echo number_format($product['unit_price'], 2); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity *</label>
                    <input type="number" class="form-control" name="quantity" id="quantity" min="1" required>
                    <div class="form-text">Available stock: <span id="available-stock">0</span></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Total Amount</label>
                    <div class="form-control bg-light">₦<span id="total-amount">0.00</span></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Customer Name</label>
                    <input type="text" class="form-control" name="customer_name">
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Method *</label>
                    <select class="form-select" name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="transfer">Bank Transfer</option>
                        <option value="pos">POS</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Record Sale
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="col-md-8">
        <div class="dashboard-card">
            <h5 class="mb-4">Recent Sales</h5>
            
            <?php if (empty($sales)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5>No Sales Records</h5>
                    <p class="text-muted">Record your first sale using the form.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo date('M j, Y g:i A', strtotime($sale['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                <td><?php echo number_format($sale['quantity']); ?></td>
                                <td>₦<?php echo number_format($sale['unit_price'], 2); ?></td>
                                <td>₦<?php echo number_format($sale['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($sale['payment_method']) {
                                            'cash' => 'success',
                                            'transfer' => 'primary',
                                            'pos' => 'info',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst($sale['payment_method']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$page_scripts = <<<SCRIPTS
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product-select');
    const quantityInput = document.getElementById('quantity');
    const availableStock = document.getElementById('available-stock');
    const totalAmount = document.getElementById('total-amount');

    function updateCalculations() {
        const selectedOption = productSelect.selectedOptions[0];
        if (selectedOption.value) {
            const price = parseFloat(selectedOption.dataset.price);
            const stock = parseInt(selectedOption.dataset.stock);
            const quantity = parseInt(quantityInput.value) || 0;

            availableStock.textContent = stock;
            totalAmount.textContent = (price * quantity).toFixed(2);

            // Update max quantity
            quantityInput.max = stock;
        } else {
            availableStock.textContent = '0';
            totalAmount.textContent = '0.00';
        }
    }

    productSelect.addEventListener('change', updateCalculations);
    quantityInput.addEventListener('input', updateCalculations);
});
</script>
SCRIPTS;

$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?> 