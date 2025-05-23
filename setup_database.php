<?php
/**
 * Direct Database Setup Script
 * 
 * This script directly sets up the database for the AiAutoReview platform
 * It creates all necessary tables with the proper structure
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'aiautoreview');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connect to MySQL without database to check if database exists
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Setup</h2>";
    
    // Check if database exists, create if not
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        echo "<p>Creating database '" . DB_NAME . "'...</p>";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color:green'>Database created successfully!</p>";
    } else {
        echo "<p>Database '" . DB_NAME . "' already exists.</p>";
    }
    
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables
    echo "<h3>Creating Tables</h3>";
    
    // Drop existing tables if they exist to avoid conflicts
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS ar_response");
    $pdo->exec("DROP TABLE IF EXISTS ar_review");
    $pdo->exec("DROP TABLE IF EXISTS ar_user");
    $pdo->exec("DROP TABLE IF EXISTS ar_business");
    $pdo->exec("DROP TABLE IF EXISTS ar_business_setting");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<p>Dropped existing tables.</p>";
    
    // Create business table
    $pdo->exec("CREATE TABLE ar_business (
        business_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        business_name VARCHAR(255) NOT NULL,
        subscription_type ENUM('free', 'basic', 'professional', 'enterprise') NOT NULL DEFAULT 'free',
        business_status ENUM('trialing', 'active', 'inactive', 'cancelled') NOT NULL DEFAULT 'trialing',
        date_created DATETIME NOT NULL,
        date_updated DATETIME NULL DEFAULT NULL,
        INDEX idx_business_status (business_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<p>Created ar_business table.</p>";
    
    // Create user table
    $pdo->exec("CREATE TABLE ar_user (
        user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        business_id INT UNSIGNED NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        email_address VARCHAR(255) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        user_role ENUM('admin', 'manager', 'support', 'viewer') NOT NULL DEFAULT 'viewer',
        date_created DATETIME NOT NULL,
        date_updated DATETIME NULL DEFAULT NULL,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        last_login_date DATETIME NULL,
        UNIQUE INDEX idx_email (email_address),
        INDEX idx_business_user (business_id),
        CONSTRAINT fk_user_business FOREIGN KEY (business_id) 
            REFERENCES ar_business(business_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<p>Created ar_user table.</p>";
    
    // Create review table
    $pdo->exec("CREATE TABLE ar_review (
        review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        business_id INT UNSIGNED NOT NULL,
        platform_name VARCHAR(50) NOT NULL,
        external_review_id VARCHAR(255) NOT NULL,
        rating_value TINYINT UNSIGNED NOT NULL,
        review_content TEXT NOT NULL,
        reviewer_name VARCHAR(255) NOT NULL,
        sentiment_value ENUM('positive', 'neutral', 'negative') NOT NULL,
        date_posted DATE NOT NULL,
        date_updated DATETIME NOT NULL,
        has_response BOOLEAN NOT NULL DEFAULT FALSE,
        UNIQUE INDEX idx_platform_review (platform_name, external_review_id),
        INDEX idx_business_review (business_id),
        INDEX idx_sentiment (sentiment_value),
        INDEX idx_rating (rating_value),
        CONSTRAINT fk_review_business FOREIGN KEY (business_id) 
            REFERENCES ar_business(business_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<p>Created ar_review table.</p>";
    
    // Create response table
    $pdo->exec("CREATE TABLE ar_response (
        response_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        business_id INT UNSIGNED NOT NULL,
        review_id INT UNSIGNED NOT NULL,
        response_content TEXT NOT NULL,
        response_status ENUM('pending', 'approved', 'posted') NOT NULL DEFAULT 'pending',
        created_by_user_id INT UNSIGNED NULL,
        approved_by_user_id INT UNSIGNED NULL,
        date_created DATETIME NOT NULL,
        date_updated DATETIME NULL DEFAULT NULL,
        date_posted DATETIME NULL DEFAULT NULL,
        is_ai_generated BOOLEAN NOT NULL DEFAULT TRUE,
        INDEX idx_business_response (business_id),
        INDEX idx_review_response (review_id),
        INDEX idx_response_status (response_status),
        CONSTRAINT fk_response_business FOREIGN KEY (business_id) 
            REFERENCES ar_business(business_id) ON DELETE CASCADE,
        CONSTRAINT fk_response_review FOREIGN KEY (review_id) 
            REFERENCES ar_review(review_id) ON DELETE CASCADE,
        CONSTRAINT fk_response_creator FOREIGN KEY (created_by_user_id) 
            REFERENCES ar_user(user_id) ON DELETE SET NULL,
        CONSTRAINT fk_response_approver FOREIGN KEY (approved_by_user_id) 
            REFERENCES ar_user(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<p>Created ar_response table.</p>";
    
    // Create settings table for business configuration
    $pdo->exec("CREATE TABLE ar_business_setting (
        setting_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        business_id INT UNSIGNED NOT NULL,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT NOT NULL,
        date_created DATETIME NOT NULL,
        date_updated DATETIME NULL DEFAULT NULL,
        UNIQUE INDEX idx_business_setting (business_id, setting_key),
        CONSTRAINT fk_setting_business FOREIGN KEY (business_id) 
            REFERENCES ar_business(business_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<p>Created ar_business_setting table.</p>";
    
    echo "<h3>Database Setup Complete</h3>";
    echo "<p style='color:green'>All tables have been created successfully!</p>";
    echo "<p>You can now <a href='index.php'>return to the application</a>.</p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color:red'>Database Setup Error</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
