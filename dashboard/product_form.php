<?php
$page_title = isset($_GET['id']) ? 'Edit Product' : 'Add Product';
$current_page = 'products';

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user has permission to add/edit products (Admin or Manager only)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied. Only administrators and managers can add/edit products.';
    exit();
}

$error = null;
$success = null;
$product = [
    'name' => '',
    'sku' => '',
    'category_id' => '',
    'quantity' => 0,
    'price' => '',
    'min_stock' => 0
];

// Get categories for dropdown
try {
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE business_code = ? ORDER BY name");
    $stmt->bind_param("s", $_SESSION['business_code']);
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
        'sku' => trim($_POST['sku']),
        'category_id' => $_POST['category_id'] ?: null,
        'quantity' => (int)$_POST['quantity'],
        'price' => (float)$_POST['price'],
        'min_stock' => (int)$_POST['min_stock']
    ];

    if (empty($product['name'])) {
        $error = "Product name is required.";
    } elseif (empty($product['sku'])) {
        $error = "SKU is required.";
    } else {
        try {
            if (isset($_GET['id'])) {
                // Verify product ownership (within same business)
                $stmt = $conn->prepare("SELECT business_code FROM products WHERE id = ?");
                $stmt->bind_param("i", $_GET['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $existing = $result->fetch_assoc();

                if (!$existing || $existing['business_code'] !== $_SESSION['business_code']) {
                    throw new Exception("Product not found or access denied.");
                }

                // Update existing product
                $stmt = $conn->prepare("
                    UPDATE products 
                    SET name = ?, sku = ?, category_id = ?, 
                        quantity = ?, price = ?, min_stock = ?
                    WHERE id = ? AND business_code = ?
                ");
                $stmt->bind_param("ssiidii",
                    $product['name'], $product['sku'],
                    $product['category_id'], $product['quantity'], $product['price'],
                    $product['min_stock'], $_GET['id'], $_SESSION['business_code']
                );
            } else {
                // Insert new product
                $stmt = $conn->prepare("
                    INSERT INTO products (user_id, business_code, name, sku, category_id, 
                                       quantity, price, min_stock)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("isssiidi",
                    $_SESSION['user_id'], $_SESSION['business_code'], $product['name'], $product['sku'],
                    $product['category_id'], $product['quantity'], $product['price'],
                    $product['min_stock']
                );
            }
            
            if ($stmt->execute()) {
                if (isset($_GET['id'])) {
                    header('Location: index.php?page=products&success=1');
                    exit;
                } else {
                    $success = "Product added successfully! You can add another product below.";
                    // Clear form fields for next entry
                    $product = [
                        'name' => '',
                        'sku' => '',
                        'category_id' => '',
                        'quantity' => 0,
                        'price' => '',
                        'min_stock' => 0
                    ];
                }
            } else {
                throw new Exception("Database execution failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "A product with this SKU already exists in your inventory.";
            } else {
                error_log("Product save error: " . $e->getMessage());
                $error = "An error occurred while saving the product: " . $e->getMessage();
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

            <?php if ($success): ?>
            <div class="alert alert-success"> <?php echo $success; ?> </div>
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
                    <input type="number" class="form-control" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Current Stock</label>
                    <input type="number" class="form-control" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Reorder Level <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Set the minimum stock quantity before you need to restock this product. You'll get a low stock alert when inventory falls below this level."></i></label>
                    <input type="number" class="form-control" name="min_stock" value="<?php echo htmlspecialchars($product['min_stock']); ?>">
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