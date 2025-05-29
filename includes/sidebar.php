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
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'inventory' ? 'active' : ''; ?>" href="index.php?page=inventory">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'products' ? 'active' : ''; ?>" href="index.php?page=products">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?>" href="index.php?page=categories">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'suppliers' ? 'active' : ''; ?>" href="index.php?page=suppliers">
                    <i class="fas fa-truck"></i>
                    <span>Suppliers</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'orders' ? 'active' : ''; ?>" href="index.php?page=orders">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'reports' ? 'active' : ''; ?>" href="index.php?page=reports">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>" href="index.php?page=users">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div> 