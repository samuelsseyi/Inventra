<?php
$page_title = 'Reports';
$current_page = 'reports';

require_once 'config/database.php';
require_once 'includes/functions.php';

$error = null;

try {
    // Get total products value
    $result = $conn->query("SELECT SUM(quantity * unit_price) as total_value FROM products");
    $total_value = $result->fetch_assoc()['total_value'] ?? 0;

    // Get low stock products
    $result = $conn->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.quantity <= p.reorder_level 
        ORDER BY p.quantity ASC
    ");
    $low_stock = [];
    while ($row = $result->fetch_assoc()) {
        $low_stock[] = $row;
    }

    // Get stock movement summary
    $result = $conn->query("
        SELECT 
            DATE(created_at) as date,
            type,
            SUM(quantity) as total_quantity
        FROM stock_movements
        GROUP BY DATE(created_at), type
        ORDER BY date DESC
        LIMIT 7
    ");
    $movements = [];
    while ($row = $result->fetch_assoc()) {
        $movements[] = $row;
    }

    // Get top products by value
    $result = $conn->query("
        SELECT p.*, c.name as category_name,
               (p.quantity * p.unit_price) as total_value
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY total_value DESC
        LIMIT 5
    ");
    $top_products = [];
    while ($row = $result->fetch_assoc()) {
        $top_products[] = $row;
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while generating reports.";
}

// Start output buffering
ob_start();
?>

<!-- Reports Content -->
<div class="row g-4">
    <!-- Total Inventory Value -->
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Total Inventory Value</h6>
                    <h3 class="mb-0">₦<?php echo number_format($total_value, 2); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-md-6">
        <div class="dashboard-card">
            <h5 class="mb-4">Low Stock Alert</h5>
            <?php if (empty($low_stock)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h6>All Products Well Stocked</h6>
                    <p class="text-muted">No products are below reorder level.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>
                                    <span class="badge bg-danger">
                                        <?php echo number_format($product['quantity']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($product['reorder_level']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Products by Value -->
    <div class="col-md-6">
        <div class="dashboard-card">
            <h5 class="mb-4">Top Products by Value</h5>
            <?php if (empty($top_products)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h6>No Products Found</h6>
                    <p class="text-muted">Add products to see their values.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo number_format($product['quantity']); ?></td>
                                <td>₦<?php echo number_format($product['total_value'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Stock Movements -->
    <div class="col-12">
        <div class="dashboard-card">
            <h5 class="mb-4">Stock Movement Summary (Last 7 Days)</h5>
            <?php if (empty($movements)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                    <h6>No Recent Movements</h6>
                    <p class="text-muted">Stock movements will appear here.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Total Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $movement): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($movement['date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $movement['type'] === 'in' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($movement['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($movement['total_quantity']); ?></td>
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