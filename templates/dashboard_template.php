<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Inventra Dashboard</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00d4d4;
            --secondary-color: #004080;
            --sidebar-width: 250px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--secondary-color), #001f40);
            color: white;
            padding: 1rem;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 1rem 0;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h4 {
            font-size: 1.25rem;
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin-top: 2rem;
        }
        .menu-item {
            margin-bottom: 0.5rem;
        }
        .menu-item a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .menu-item a:hover, .menu-item a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .menu-item i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }
        .dashboard-header {
            background: white;
            padding: 1rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-menu {
            position: relative;
        }
        .user-menu .dropdown-menu {
            right: 0;
            left: auto;
        }
        .dashboard-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 1.5rem;
            height: 100%;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 250px;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .navbar {
                padding: 0.5rem 1rem;
            }
            .navbar .container-fluid {
                padding: 0;
            }
            .content {
                padding: 1rem 0.5rem;
            }
            .dashboard-card {
                margin-bottom: 1rem;
            }
        }
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--secondary-color);
            font-size: 1.5rem;
            padding: 0.5rem;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        @media (max-width: 576px) {
            .form-group {
                margin-bottom: 0.75rem;
            }
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="assets/images/logo-white.png" alt="Inventra Logo" class="logo">
            <h4 class="mt-3 text-white">Inventra</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'add_product' ? 'active' : ''; ?>" href="index.php?page=add-product">
                    <i class="fas fa-plus me-2"></i>Add Product
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'products_list' ? 'active' : ''; ?>" href="index.php?page=products">
                    <i class="fas fa-box me-2"></i>Products List
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?>" href="index.php?page=categories">
                    <i class="fas fa-tags me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'stock' ? 'active' : ''; ?>" href="index.php?page=stock">
                    <i class="fas fa-boxes me-2"></i>Stock Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'sales' ? 'active' : ''; ?>" href="index.php?page=sales">
                    <i class="fas fa-shopping-cart me-2"></i>Sales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'reports' ? 'active' : ''; ?>" href="index.php?page=reports">
                    <i class="fas fa-chart-bar me-2"></i>Reports
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <nav class="navbar navbar-expand navbar-light bg-white">
            <div class="container-fluid">
                <button class="mobile-menu-toggle d-md-none me-3">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="h3 mb-0"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?page=profile">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="content">
            <?php echo $page_content; ?>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (mobileMenuToggle && sidebar) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside
                document.addEventListener('click', function(event) {
                    if (!sidebar.contains(event.target) && 
                        !mobileMenuToggle.contains(event.target) && 
                        sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                });
            }
            
            // Close sidebar on window resize if screen becomes larger
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768 && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        });
    </script>
    <?php if (isset($page_scripts)) echo $page_scripts; ?>
</body>
</html> 