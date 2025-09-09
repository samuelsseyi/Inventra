<?php
require_once 'config/database.php';

echo "Populating business_code columns with existing data...\n";

try {
    // Update products table
    $result = $conn->query("SELECT DISTINCT user_id FROM products WHERE business_code IS NULL");
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $user_stmt = $conn->prepare("SELECT business_code FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if ($user && $user['business_code']) {
            $update_stmt = $conn->prepare("UPDATE products SET business_code = ? WHERE user_id = ? AND business_code IS NULL");
            $update_stmt->bind_param("si", $user['business_code'], $user_id);
            $update_stmt->execute();
            echo "✓ Updated products for user $user_id with business_code: " . $user['business_code'] . "\n";
        }
    }

    // Update categories table
    $result = $conn->query("SELECT DISTINCT user_id FROM categories WHERE business_code IS NULL");
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $user_stmt = $conn->prepare("SELECT business_code FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if ($user && $user['business_code']) {
            $update_stmt = $conn->prepare("UPDATE categories SET business_code = ? WHERE user_id = ? AND business_code IS NULL");
            $update_stmt->bind_param("si", $user['business_code'], $user_id);
            $update_stmt->execute();
            echo "✓ Updated categories for user $user_id with business_code: " . $user['business_code'] . "\n";
        }
    }

    // Update stock_movements table
    $result = $conn->query("SELECT DISTINCT user_id FROM stock_movements WHERE business_code IS NULL");
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $user_stmt = $conn->prepare("SELECT business_code FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if ($user && $user['business_code']) {
            $update_stmt = $conn->prepare("UPDATE stock_movements SET business_code = ? WHERE user_id = ? AND business_code IS NULL");
            $update_stmt->bind_param("si", $user['business_code'], $user_id);
            $update_stmt->execute();
            echo "✓ Updated stock_movements for user $user_id with business_code: " . $user['business_code'] . "\n";
        }
    }

    // Update sales table
    $result = $conn->query("SELECT DISTINCT user_id FROM sales WHERE business_code IS NULL");
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $user_stmt = $conn->prepare("SELECT business_code FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if ($user && $user['business_code']) {
            $update_stmt = $conn->prepare("UPDATE sales SET business_code = ? WHERE user_id = ? AND business_code IS NULL");
            $update_stmt->bind_param("si", $user['business_code'], $user_id);
            $update_stmt->execute();
            echo "✓ Updated sales for user $user_id with business_code: " . $user['business_code'] . "\n";
        }
    }

    echo "\nBusiness code population completed!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
