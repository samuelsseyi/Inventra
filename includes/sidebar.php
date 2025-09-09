<?php
// Prevent direct access to this file
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__FILE__) . '/');
}

// Get current page for active menu highlighting
$current_page = $_GET['page'] ?? 'dashboard';
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-content">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <?php 
            $user_role = $_SESSION['user_role'] ?? 'cashier';
            // Admin and Manager can add products
            if ($user_role === 'admin' || $user_role === 'manager'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'add-product' ? 'active' : ''; ?>" href="index.php?page=add-product">
                    <i class="fas fa-plus"></i>
                    <span>Add Product</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php 
            // All roles can view products
            ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'products' ? 'active' : ''; ?>" href="index.php?page=products">
                    <i class="fas fa-box"></i>
                    <span>Products List</span>
                </a>
            </li>
            
            <?php 
            // Admin and Manager can manage categories
            if ($user_role === 'admin' || $user_role === 'manager'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?>" href="index.php?page=categories">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php 
            // Admin and Manager can manage stock
            if ($user_role === 'admin' || $user_role === 'manager'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'stock' ? 'active' : ''; ?>" href="index.php?page=stock">
                    <i class="fas fa-boxes"></i>
                    <span>Stock Management</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php 
            // All roles can record sales
            ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'sales' ? 'active' : ''; ?>" href="index.php?page=sales">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>
            </li>
            
            <?php 
            // Admin and Manager can view reports
            if ($user_role === 'admin' || $user_role === 'manager'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'reports' ? 'active' : ''; ?>" href="index.php?page=reports">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>" href="index.php?page=users">
                    <i class="fas fa-users-cog"></i>
                    <span>User Management</span>
                </a>
            </li>
        </ul>
    </div>
</div> 