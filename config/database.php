<?php
/*
* Database Configuration File
* Contains all database settings for the application
* Created by: Inventra Team
* Date: <?php echo date('Y-m-d'); ?>
*/

// Database configuration
$db_host = 'localhost';
$db_name = 'inventra_db';
$db_user = 'root';
$db_pass = '';

// Create connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?> 