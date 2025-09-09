<?php
/*
* Database Configuration File
* Contains all database settings for the application
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

    // Ensure auth email verification schema exists (idempotent)
    try {
        // Add verification columns to users table if missing
        $dbNameEscaped = $conn->real_escape_string($db_name);
        $checkCol = $conn->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_verified'");
        $checkCol->bind_param("s", $dbNameEscaped);
        $checkCol->execute();
        $res = $checkCol->get_result()->fetch_assoc();
        if (empty($res) || (int)$res['cnt'] === 0) {
            $conn->query("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN verified_at DATETIME NULL, ADD COLUMN verification_sent_at DATETIME NULL");
        }

        // Create email_verifications table if not exists
        $conn->query(
            "CREATE TABLE IF NOT EXISTS email_verifications (\n" .
            "  id INT AUTO_INCREMENT PRIMARY KEY,\n" .
            "  user_id INT NOT NULL,\n" .
            "  token VARCHAR(128) NOT NULL,\n" .
            "  expires_at DATETIME NOT NULL,\n" .
            "  verified_at DATETIME NULL,\n" .
            "  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" .
            "  INDEX (token),\n" .
            "  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE\n" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Exception $ie) {
        // Do not crash app if migration fails; log for debugging
        error_log('Schema ensure failed: ' . $ie->getMessage());
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?> 