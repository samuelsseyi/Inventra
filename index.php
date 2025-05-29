<?php
/*
* Inventra - Inventory Management System
* Main Entry Point
* 
* Developed for Nigerian businesses to efficiently manage their inventory
*/

// Initialize session
session_start();

// Import required files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !in_array($_GET['page'] ?? '', ['login', 'register'])) {
    if (isset($_GET['page']) && $_GET['page'] !== '') {
        // If trying to access dashboard pages, redirect to login
        header('Location: auth/login.php');
        exit();
    } else {
        // If no specific page requested, show landing page
        require_once 'pages/landing.php';
        exit();
    }
}

// Get the requested page
$page = $_GET['page'] ?? 'dashboard';

// Define allowed pages and their corresponding files
$allowed_pages = [
    'dashboard' => 'dashboard/index.php',
    'products' => 'dashboard/products.php',
    'add-product' => 'dashboard/product_form.php',
    'categories' => 'dashboard/categories.php',
    'stock' => 'dashboard/stock.php',
    'sales' => 'dashboard/sales.php',
    'reports' => 'dashboard/reports.php',
    'profile' => 'dashboard/profile.php',
    'delete-product' => 'dashboard/delete_product.php'
];

// Check if the requested page exists
if (!array_key_exists($page, $allowed_pages)) {
    header("HTTP/1.0 404 Not Found");
    echo "Error: The requested page was not found.";
    exit();
}

// Include the requested page
require_once $allowed_pages[$page]; 