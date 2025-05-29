<?php
/*
* Authentication Functions
* Core functions for user authentication and security management
*
* Dependencies:
* - config/database.php (for database connection)
*/

// Ensure database connection is included
require_once dirname(__FILE__) . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if current page is a login-related page
function isLoginPage() {
    $current_page = basename($_SERVER['PHP_SELF']);
    return in_array($current_page, ['login.php', 'register.php', 'forgot-password.php']);
}

// Register a new user
function registerUser($username, $email, $password, $business_name) {
    global $conn;
    try {
        // Hash password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate unique business identifier
        $business_code = 'BIZ' . rand(1000, 9999);
        
        // Prepare SQL statement to prevent injection
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, business_name, business_code, created_at) 
                               VALUES (?, ?, ?, ?, ?, NOW())");
        
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $business_name, $business_code);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error registering user: " . $e->getMessage());
        return false;
    }
}

// Authenticate user login
function loginUser($email, $password) {
    global $conn;
    try {
        // Prepare database query
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['business_name'] = $user['business_name'];
            $_SESSION['business_code'] = $user['business_code'];
            $_SESSION['user_role'] = $user['role'];
            
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error logging in user: " . $e->getMessage());
        return false;
    }
}

// Log out current user
function logoutUser() {
    session_destroy();
    // Redirect to landing page
    header("Location: ../index.php");
    exit();
}

// Check user permission level
function hasPermission($required_role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    // Admin has full access
    if ($_SESSION['user_role'] === 'admin') {
        return true;
    }
    
    return $_SESSION['user_role'] === $required_role;
}

// Get user by ID
function getUserById($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error getting user by ID: " . $e->getMessage());
        return null;
    }
}
?> 