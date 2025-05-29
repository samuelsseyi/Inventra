<?php
$page_title = 'Stock Management';
$current_page = 'stock';

require_once 'config/database.php';
require_once 'includes/functions.php';

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

            // Add stock movement record
            $stmt = $conn->prepare("INSERT INTO stock_movements (product_id, type, quantity, reason) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isis", $product_id, $type, $quantity, $reason);
            $stmt->execute();

            // Update product quantity
            $quantity_change = $type === 'in' ? $quantity : -$quantity;
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity_change, $product_id);
            $stmt->execute();

            // Commit transaction
            $conn->commit();
            $success = "Stock " . ($type === 'in' ? "added" : "removed") . " successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            error_log($e->getMessage());
            $error = "An error occurred while updating stock.";
        }
    }
}

// Get products for dropdown
try {
    $result = $conn->query("SELECT id, name, quantity FROM products ORDER BY name");
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
    $result = $conn->query("
        SELECT sm.*, p.name as product_name 
        FROM stock_movements sm
        JOIN products p ON sm.product_id = p.id
        ORDER BY sm.created_at DESC 
        LIMIT 10
    ");
    $movements = [];
    while ($row = $result->fetch_assoc()) {
        $movements[] = $row;
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
                    <label class="form-label">Product *</label>
                    <select class="form-select" name="product_id" required>
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
                    <label class="form-label">Movement Type *</label>
                    <select class="form-select" name="type" required>
                        <option value="in">Stock In</option>
                        <option value="out">Stock Out</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity *</label>
                    <input type="number" class="form-control" name="quantity" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason</label>
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
            
            <?php if (empty($movements)): ?>
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
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $movement): ?>
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
$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?> 