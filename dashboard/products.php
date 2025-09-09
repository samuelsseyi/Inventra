<?php
$page_title = 'Products List';
$current_page = 'products_list';

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user has permission to view products (Admin or Manager only)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied. Only administrators and managers can view products.';
    exit();
}

// Handle search and filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';

// Prepare base query
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.business_code = ?";
$params = [$_SESSION['business_code']];
$types = "s";

// Add search condition
if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Add category filter
if (!empty($category)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Add sorting
switch ($sort) {
    case 'name_desc':
        $query .= " ORDER BY p.name DESC";
        break;
    case 'stock_asc':
        $query .= " ORDER BY p.quantity ASC";
        break;
    case 'stock_desc':
        $query .= " ORDER BY p.quantity DESC";
        break;
    default:
        $query .= " ORDER BY p.name ASC";
}

try {
    // Get categories for filter (only business categories)
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE business_code = ? ORDER BY name");
    $stmt->bind_param("s", $_SESSION['business_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    // Get products
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while fetching the products.";
}

// Start output buffering
ob_start();
?>

<!-- Products Content -->
<div class="row g-4">
    <!-- Header Actions -->
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Manage Products</h5>
                <a href="index.php?page=add-product" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </a>
            </div>

            <!-- Search and Filters -->
            <form class="row g-3">
                <input type="hidden" name="page" value="products">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort">
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="stock_asc" <?php echo $sort === 'stock_asc' ? 'selected' : ''; ?>>Stock (Low to High)</option>
                        <option value="stock_desc" <?php echo $sort === 'stock_desc' ? 'selected' : ''; ?>>Stock (High to Low)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products List -->
    <div class="col-12">
        <div class="dashboard-card">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h5>No Products Found</h5>
                    <p class="text-muted">Try adjusting your search or filters, or add a new product.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Stock <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Green means stock is above reorder level. Red means stock is low and needs restocking."></i></th>
                                <th>Unit Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>
                                <?php echo number_format($product['quantity']); ?>
                                </td>
                                <td>₦<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="index.php?page=add-product&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?page=delete-product&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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
$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?> 