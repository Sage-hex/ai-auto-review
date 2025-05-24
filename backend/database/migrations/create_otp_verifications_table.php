<?php
/**
 * Create OTP Verifications Table Migration
 * 
 * This migration creates the otp_verifications table for storing OTP codes
 */

// Include database configuration
require_once __DIR__ . '/../../config/database.php';

// Get database connection
$db = getDbConnection();

if (!$db) {
    die("Database connection failed\n");
}

// Create otp_verifications table
$sql = "CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expiry_time INT NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_id (user_id),
    INDEX idx_otp (otp),
    INDEX idx_expiry (expiry_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $db->exec($sql);
    echo "OTP verifications table created successfully\n";
} catch (PDOException $e) {
    die("Error creating OTP verifications table: " . $e->getMessage() . "\n");
}

// Add is_verified column to users table if it doesn't exist
$sql = "SHOW COLUMNS FROM users LIKE 'is_verified'";
$result = $db->query($sql);

if ($result->rowCount() == 0) {
    try {
        $sql = "ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER email";
        $db->exec($sql);
        echo "Added is_verified column to users table\n";
    } catch (PDOException $e) {
        die("Error adding is_verified column: " . $e->getMessage() . "\n");
    }
}

echo "Migration completed successfully\n";
