<?php
/*
* Utility Functions
* Contains helper functions used throughout the application
*
* Dependencies:
* - config/database.php (for database connection)
* - includes/auth.php (for user-related functions)
*/

// Ensure required files are included
require_once 'config/database.php';
require_once 'auth.php';

/**
 * Sanitize user input
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Format currency amount
 * @param float $amount
 * @return string
 */
function formatCurrency($amount) {
    return '₦' . number_format($amount, 2);
}

/**
 * Generate random string
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

/**
 * Format date to readable format
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Format datetime to readable format
 * @param string $datetime
 * @return string
 */
function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

/**
 * Get time elapsed string
 * @param string $datetime
 * @return string
 */
function getTimeElapsed($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    return 'Just now';
}

/**
 * Check if string contains a search term
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function containsString($haystack, $needle) {
    return stripos($haystack, $needle) !== false;
}

/**
 * Get file extension
 * @param string $filename
 * @return string
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file is an image
 * @param string $filename
 * @return bool
 */
function isImage($filename) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    return in_array(getFileExtension($filename), $allowed);
}

/**
 * Generate a unique filename
 * @param string $originalName
 * @return string
 */
function generateUniqueFilename($originalName) {
    $extension = getFileExtension($originalName);
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Format file size
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    }
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}

/**
 * Truncate text to specified length
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get user IP address
 * @return string
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Log activity
 * @param string $action
 * @param string $description
 * @return bool
 */
function logActivity($action, $description) {
    global $conn;
    $user_id = $_SESSION['user_id'] ?? 0;
    $business_code = $_SESSION['business_code'] ?? '';
    $ip_address = getUserIP();
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, business_code, action, description, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $business_code, $action, $description, $ip_address);
    
    return $stmt->execute();
}

/**
 * Check subscription status
 * @param string $business_code
 * @return bool
 */
function hasActiveSubscription($business_code) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT subscription_status, subscription_end_date FROM users WHERE business_code = ?");
    $stmt->bind_param("s", $business_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['subscription_status'] === 'premium') {
            return strtotime($row['subscription_end_date']) > time();
        }
    }
    return false;
}

/**
 * Generate dashboard stats
 * @param string $business_code
 * @return array
 */
function getDashboardStats($business_code) {
    global $conn;
    
    // Total products
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE business_code = ?");
    $stmt->bind_param("s", $business_code);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_assoc()['total'];
    
    // Total sales today
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(total_amount) as amount FROM sales WHERE business_code = ? AND DATE(created_at) = ?");
    $stmt->bind_param("ss", $business_code, $today);
    $stmt->execute();
    $sales = $stmt->get_result()->fetch_assoc();
    
    // Low stock items
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE business_code = ? AND quantity <= reorder_level");
    $stmt->bind_param("s", $business_code);
    $stmt->execute();
    $low_stock = $stmt->get_result()->fetch_assoc()['total'];
    
    return [
        'total_products' => $products,
        'sales_today' => $sales['total'],
        'revenue_today' => $sales['amount'] ?? 0,
        'low_stock_items' => $low_stock
    ];
}

/**
 * Get total number of products in inventory
 */
function getTotalProducts() {
    global $conn;
    try {
        $result = $conn->query("SELECT COUNT(*) as total FROM products");
        $row = $result->fetch_assoc();
        return $row['total'];
    } catch (Exception $e) {
        error_log("Error getting total products: " . $e->getMessage());
        return null;
    }
}

/**
 * Get number of items with stock below minimum level
 */
function getLowStockItems() {
    global $conn;
    try {
        $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE current_stock < min_stock");
        $row = $result->fetch_assoc();
        return $row['total'];
    } catch (Exception $e) {
        error_log("Error getting low stock items: " . $e->getMessage());
        return null;
    }
}

/**
 * Get total number of orders
 */
function getTotalOrders() {
    global $conn;
    try {
        $result = $conn->query("SELECT COUNT(*) as total FROM orders");
        $row = $result->fetch_assoc();
        return $row['total'];
    } catch (Exception $e) {
        error_log("Error getting total orders: " . $e->getMessage());
        return null;
    }
}

/**
 * Get total revenue for the current month
 */
function getMonthlyRevenue() {
    global $conn;
    try {
        $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $row = $result->fetch_assoc();
        return $row['total'] ?: 0;
    } catch (Exception $e) {
        error_log("Error getting monthly revenue: " . $e->getMessage());
        return null;
    }
}

/**
 * Get recent orders with limit
 */
function getRecentOrders($limit = 5) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting recent orders: " . $e->getMessage());
        return null;
    }
}

/**
 * Get products with stock below minimum level
 */
function getLowStockProducts($limit = 5) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE current_stock < min_stock ORDER BY current_stock ASC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting low stock products: " . $e->getMessage());
        return null;
    }
}

/**
 * Get color class for order status
 */
function getStatusColor($status) {
    return match (strtolower($status)) {
        'completed' => 'success',
        'pending' => 'warning',
        'cancelled' => 'danger',
        'processing' => 'info',
        default => 'secondary'
    };
}

/**
 * Send email using configured mail settings
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email message body
 * @return bool Whether the email was sent successfully
 */
function sendEmail($to, $subject, $message) {
    $headers = [
        'From' => 'noreply@inventra.com',
        'Reply-To' => 'support@inventra.com',
        'X-Mailer' => 'PHP/' . phpversion(),
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=UTF-8'
    ];
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Upload file with validation and security checks
 * 
 * @param array $file The uploaded file array from $_FILES
 * @param string $destination The destination path for the file
 * @return bool Whether the file was uploaded successfully
 */
function uploadFile($file, $destination) {
    try {
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type');
        }
        
        if ($file['size'] > 5242880) { // 5MB max
            throw new Exception('File too large');
        }
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }
        
        // Move file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error uploading file: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete file
 */
function deleteFile($path) {
    try {
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    } catch (Exception $e) {
        error_log("Error deleting file: " . $e->getMessage());
        return false;
    }
}
?> 