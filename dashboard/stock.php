<?php
$page_title = 'Stock Management';
$current_page = 'stock';

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user has permission to manage stock (Admin or Manager only)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied. Only administrators and managers can manage stock.';
    exit();
}

$error = null;
$success = null;

// Handle stock movement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = (int)$_POST['quantity'];
    $type = $_POST['type'] ?? 'in';
    $reason = trim($_POST['reason']);

    if (!$product_id || $quantity <= 0) {
        $error = "Please select a product and enter a valid quantity.";
    } else {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Verify product ownership (within same business)
            $stmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND business_code = ?");
            $stmt->bind_param("is", $product_id, $_SESSION['business_code']);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result->fetch_assoc()) {
                throw new Exception("Product not found or access denied.");
            }

            // Add stock movement record
            $stmt = $conn->prepare("INSERT INTO stock_movements (user_id, business_code, product_id, type, quantity, reason) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isisis", $_SESSION['user_id'], $_SESSION['business_code'], $product_id, $type, $quantity, $reason);
            $stmt->execute();

            // Update product quantity
            $quantity_change = $type === 'in' ? $quantity : -$quantity;
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ? AND business_code = ?");
            $stmt->bind_param("iis", $quantity_change, $product_id, $_SESSION['business_code']);
            $stmt->execute();

            // Commit transaction
            $conn->commit();
            $success = "Stock " . ($type === 'in' ? "added" : "removed") . " successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            error_log($e->getMessage());
            $error = $e->getMessage();
        }
    }
}

// Get products for dropdown
try {
    $stmt = $conn->prepare("SELECT id, name, quantity FROM products WHERE business_code = ? ORDER BY name");
    $stmt->bind_param("s", $_SESSION['business_code']);
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

// Get recent stock movements
try {
    $stmt = $conn->prepare("
        SELECT sm.*, p.name as product_name 
        FROM stock_movements sm
        JOIN products p ON sm.product_id = p.id
        WHERE sm.business_code = ?
        ORDER BY sm.created_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("s", $_SESSION['business_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stock_movements = [];
    while ($row = $result->fetch_assoc()) {
        $stock_movements[] = $row;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while fetching stock movements.";
}

// Start output buffering
ob_start();
?>

<!-- Stock Management Content -->
<div class="row g-4">
    <!-- Stock Movement Form -->
    <div class="col-md-4">
        <div class="dashboard-card">
            <h5 class="mb-4">Stock Movement</h5>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Product * <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Select the product you want to add or remove stock for."></i></label>
                    <select class="form-select" name="product_id" id="product-select" required style="width: 100%">
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?> 
                            (Current Stock: <?php echo $product['quantity']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Movement Type * <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Choose 'Stock In' to add stock, or 'Stock Out' to remove stock."></i></label>
                    <select class="form-select" name="type" required>
                        <option value="in">Stock In</option>
                        <option value="out">Stock Out</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity * <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Enter the number of items to add or remove."></i></label>
                    <input type="number" class="form-control" name="quantity" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Optionally, provide a reason for this stock movement (e.g., new delivery, sale, damage)."></i></label>
                    <textarea class="form-control" name="reason" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Record Movement
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="col-md-8">
        <div class="dashboard-card">
            <h5 class="mb-4">Recent Stock Movements</h5>
            
            <?php if (empty($stock_movements)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h5>No Stock Movements</h5>
                    <p class="text-muted">Record your first stock movement using the form.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Stock In increases inventory, Stock Out decreases it."></i></th>
                                <th>Quantity</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stock_movements as $movement): ?>
                            <tr>
                                <td><?php echo date('M j, Y g:i A', strtotime($movement['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($movement['product_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $movement['type'] === 'in' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($movement['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($movement['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($movement['reason']); ?></td>
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug logs
    console.log('DOMContentLoaded fired on stock page');
    console.log('window.jQuery:', typeof window.jQuery !== 'undefined');
    console.log('window.Select2:', typeof window.Select2 !== 'undefined');
    var productSelect = document.getElementById('product-select');
    console.log('productSelect exists:', !!productSelect);
    // Initialize Select2 for product dropdown
    if (window.jQuery) {
        $('#product-select').select2({
            placeholder: 'Search or select a product',
            allowClear: true,
            width: '100%'
        });
        console.log('Select2 initialization attempted');
    } else {
        console.log('jQuery not loaded, Select2 not initialized');
    }
});
</script>
SCRIPTS;
$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?> 