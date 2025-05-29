<?php
$page_title = 'Dashboard';
$current_page = 'dashboard';

require_once 'config/database.php';
require_once 'includes/functions.php';

// Get quick stats
$total_products = 0;
$low_stock_count = 0;
$total_categories = 0;
$recent_movements = [];

try {
    // Get total products
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $row = $result->fetch_assoc();
    $total_products = $row['count'];

    // Get low stock products count
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE quantity <= reorder_level");
    $row = $result->fetch_assoc();
    $low_stock_count = $row['count'];

    // Get total categories
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $row = $result->fetch_assoc();
    $total_categories = $row['count'];

    // Get recent stock movements
    $result = $conn->query("
        SELECT sm.*, p.name as product_name 
        FROM stock_movements sm
        JOIN products p ON sm.product_id = p.id
        ORDER BY sm.created_at DESC 
        LIMIT 5
    ");
    $recent_movements = [];
    while ($row = $result->fetch_assoc()) {
        $recent_movements[] = $row;
    }
} catch (Exception $e) {
    // Log error and show generic message
    error_log($e->getMessage());
}

// Start output buffering
ob_start();
?>

<!-- Dashboard Content -->
<div class="row g-4">
    <!-- Quick Stats -->
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-box fa-2x text-primary"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Total Products</h6>
                    <h3 class="mb-0"><?php echo number_format($total_products); ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Low Stock Items</h6>
                    <h3 class="mb-0"><?php echo number_format($low_stock_count); ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-tags fa-2x text-success"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Categories</h6>
                    <h3 class="mb-0"><?php echo number_format($total_categories); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-12">
        <div class="dashboard-card">
            <h5 class="mb-4">Recent Stock Movements</h5>
            <?php if (empty($recent_movements)): ?>
            <p class="text-muted">No recent stock movements</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_movements as $movement): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($movement['product_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $movement['type'] === 'in' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($movement['type']); ?>
                                </span>
                            </td>
                            <td><?php echo number_format($movement['quantity']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($movement['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12">
        <div class="dashboard-card">
            <h5 class="mb-4">Quick Actions</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="index.php?page=add-product" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Add Product
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="index.php?page=categories&action=add" class="btn btn-secondary w-100">
                        <i class="fas fa-folder-plus me-2"></i>Add Category
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="index.php?page=stock&action=add" class="btn btn-success w-100">
                        <i class="fas fa-plus-circle me-2"></i>Stock In
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="index.php?page=stock&action=remove" class="btn btn-danger w-100">
                        <i class="fas fa-minus-circle me-2"></i>Stock Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?> 