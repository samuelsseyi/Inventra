<?php
// Dashboard page
require_once 'includes/functions.php';

// Get dashboard stats
$total_products = getTotalProducts() ?? 0;
$low_stock_items = getLowStockItems() ?? 0;
$total_orders = getTotalOrders() ?? 0;
$monthly_revenue = getMonthlyRevenue() ?? 0;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                <i class="fas fa-plus"></i> Quick Add
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stats-card">
                <div class="stats-card-value"><?php echo number_format($total_products); ?></div>
                <div class="stats-card-label">Total Products</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stats-card">
                <div class="stats-card-value"><?php echo number_format($low_stock_items); ?></div>
                <div class="stats-card-label">Low Stock Items</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stats-card">
                <div class="stats-card-value"><?php echo number_format($total_orders); ?></div>
                <div class="stats-card-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stats-card">
                <div class="stats-card-value">₦<?php echo number_format($monthly_revenue); ?></div>
                <div class="stats-card-label">Monthly Revenue</div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-md-6 mb-4">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">Recent Orders</h5>
                    <a href="index.php?page=orders" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_orders = getRecentOrders(5) ?? [];
                            foreach ($recent_orders as $order):
                            ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>₦<?php echo number_format($order['amount']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusColor($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="col-md-6 mb-4">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">Low Stock Alerts</h5>
                    <a href="index.php?page=inventory" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Current Stock</th>
                                <th>Minimum Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $low_stock_products = getLowStockProducts(5) ?? [];
                            foreach ($low_stock_products as $product):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $product['current_stock']; ?></td>
                                <td><?php echo $product['min_stock']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="reorderProduct(<?php echo $product['id']; ?>)">
                                        Reorder
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Modal -->
<div class="modal fade" id="quickAddModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Add</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <a href="index.php?page=products&action=add" class="btn btn-outline-primary">
                        <i class="fas fa-box"></i> Add Product
                    </a>
                    <a href="index.php?page=orders&action=add" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart"></i> Create Order
                    </a>
                    <a href="index.php?page=suppliers&action=add" class="btn btn-outline-primary">
                        <i class="fas fa-truck"></i> Add Supplier
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function reorderProduct(productId) {
    if (confirm('Are you sure you want to reorder this product?')) {
        window.location.href = `index.php?page=stock&action=reorder&product_id=${productId}`;
    }
}
</script> 