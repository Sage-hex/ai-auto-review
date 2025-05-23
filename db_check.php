<?php
// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'aiautoreview');
define('DB_USER', 'root');
define('DB_PASS', '');

echo "<h1>Database Check</h1>";

// Test MySQL connection
try {
    echo "<h2>Testing MySQL Connection</h2>";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>MySQL connection successful!</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        echo "<p>Database '" . DB_NAME . "' does not exist. Creating it now...</p>";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p>Database created successfully!</p>";
    } else {
        echo "<p>Database '" . DB_NAME . "' exists.</p>";
    }
    
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Database connection successful!</p>";
    
    // Create tables if they don't exist
    echo "<h2>Creating Tables</h2>";
    
    // Create business table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ar_business (
        business_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        business_name VARCHAR(255) NOT NULL,
        subscription_type ENUM('free', 'basic', 'professional', 'enterprise') NOT NULL DEFAULT 'free',
        business_status ENUM('trialing', 'active', 'inactive', 'cancelled') NOT NULL DEFAULT 'trialing',
        date_created DATETIME NOT NULL,
        date_updated DATETIME NULL DEFAULT NULL,
        INDEX idx_business_status (business_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<p>Business table created or already exists.</p>";
    
    // Create user table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ar_user (
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
    
    echo "<p>User table created or already exists.</p>";
    
    // Check if tables exist
    $tables = ['ar_business', 'ar_user'];
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($query);
        $exists = $stmt->rowCount() > 0;
        echo "<p>Table $table " . ($exists ? "exists" : "does not exist") . ".</p>";
    }
    
    echo "<h2>Database Setup Complete</h2>";
    
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
