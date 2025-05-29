<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Check if product exists and has no stock movements
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM stock_movements WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $has_movements = $result->fetch_assoc()['count'] > 0;

        if ($has_movements) {
            $_SESSION['error'] = "Cannot delete product. It has stock movement records.";
        } else {
            // Delete the product
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Product deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete product.";
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $_SESSION['error'] = "An error occurred while deleting the product.";
    }
}

header('Location: index.php?page=products');
exit;
?> 