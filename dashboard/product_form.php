<?php
$page_title = isset($_GET['id']) ? 'Edit Product' : 'Add Product';
$current_page = 'products';

require_once 'config/database.php';
require_once 'includes/functions.php';

$error = null;
$success = null;
$product = [
    'name' => '',
    'description' => '',
    'sku' => '',
    'category_id' => '',
    'quantity' => 0,
    'unit_price' => '',
    'reorder_level' => 0
];

// Get categories for dropdown
try {
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while fetching categories.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $product = [
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'sku' => trim($_POST['sku']),
        'category_id' => $_POST['category_id'] ?: null,
        'quantity' => (int)$_POST['quantity'],
        'unit_price' => (float)$_POST['unit_price'],
        'reorder_level' => (int)$_POST['reorder_level']
    ];

    if (empty($product['name'])) {
        $error = "Product name is required.";
    } elseif (empty($product['sku'])) {
        $error = "SKU is required.";
    } else {
        try {
            if (isset($_GET['id'])) {
                // Verify product ownership
                $stmt = $conn->prepare("SELECT user_id FROM products WHERE id = ?");
                $stmt->bind_param("i", $_GET['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $existing = $result->fetch_assoc();

                if (!$existing || $existing['user_id'] !== $_SESSION['user_id']) {
                    throw new Exception("Product not found or access denied.");
                }

                // Update existing product
                $stmt = $conn->prepare("
                    UPDATE products 
                    SET name = ?, description = ?, sku = ?, category_id = ?, 
                        quantity = ?, unit_price = ?, reorder_level = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->bind_param("sssiidiii",
                    $product['name'], $product['description'], $product['sku'],
                    $product['category_id'], $product['quantity'], $product['unit_price'],
                    $product['reorder_level'], $_GET['id'], $_SESSION['user_id']
                );
            } else {
                // Insert new product
                $stmt = $conn->prepare("
                    INSERT INTO products (user_id, name, description, sku, category_id, 
                                       quantity, unit_price, reorder_level)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("isssiidd",
                    $_SESSION['user_id'], $product['name'], $product['description'], $product['sku'],
                    $product['category_id'], $product['quantity'], $product['unit_price'],
                    $product['reorder_level']
                );
            }
            
            if ($stmt->execute()) {
                header('Location: index.php?page=products&success=1');
                exit;
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "A product with this SKU already exists in your inventory.";
            } else {
                error_log($e->getMessage());
                $error = "An error occurred while saving the product.";
            }
        }
    }
}

// If editing, get existing product data
if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $_GET['id'], $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($existing = $result->fetch_assoc()) {
            $product = $existing;
        } else {
            header('Location: index.php?page=products');
            exit;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = "An error occurred while fetching the product.";
    }
}

// Start output buffering
ob_start();
?>

<!-- Product Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0"><?php echo $page_title; ?></h5>
                <a href="index.php?page=products" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                </a>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product Name *</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">SKU *</label>
                    <input type="text" class="form-control" name="sku" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Unit Price (₦)</label>
                    <input type="number" class="form-control" name="unit_price" step="0.01" value="<?php echo htmlspecialchars($product['unit_price']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Current Stock</label>
                    <input type="number" class="form-control" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Reorder Level</label>
                    <input type="number" class="form-control" name="reorder_level" value="<?php echo htmlspecialchars($product['reorder_level']); ?>">
                </div>

                <div class="col-12">
                    <hr>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?> 