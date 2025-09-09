<?php
require_once 'config/database.php';

echo "Adding business_code columns to tables...\n";

try {
    // Add business_code to products table
    $conn->query("ALTER TABLE products ADD COLUMN business_code VARCHAR(50) AFTER user_id");
    echo "✓ Added business_code to products table\n";
} catch (Exception $e) {
    echo "⚠ business_code column may already exist in products: " . $e->getMessage() . "\n";
}

try {
    // Add business_code to categories table
    $conn->query("ALTER TABLE categories ADD COLUMN business_code VARCHAR(50) AFTER user_id");
    echo "✓ Added business_code to categories table\n";
} catch (Exception $e) {
    echo "⚠ business_code column may already exist in categories: " . $e->getMessage() . "\n";
}

try {
    // Add business_code to stock_movements table
    $conn->query("ALTER TABLE stock_movements ADD COLUMN business_code VARCHAR(50) AFTER user_id");
    echo "✓ Added business_code to stock_movements table\n";
} catch (Exception $e) {
    echo "⚠ business_code column may already exist in stock_movements: " . $e->getMessage() . "\n";
}

try {
    // Add business_code to sales table
    $conn->query("ALTER TABLE sales ADD COLUMN business_code VARCHAR(50) AFTER user_id");
    echo "✓ Added business_code to sales table\n";
} catch (Exception $e) {
    echo "⚠ business_code column may already exist in sales: " . $e->getMessage() . "\n";
}

echo "\nMigration completed!\n";
?>
