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

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Fix Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .container { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Database Fix Tool</h1>";

try {
    // Step 1: Connect to MySQL server
    echo "<h2>Step 1: Connecting to MySQL Server</h2>";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Successfully connected to MySQL server!</p>";
    
    // Step 2: Check if database exists
    echo "<h2>Step 2: Checking Database</h2>";
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        echo "<p class='warning'>Database '" . DB_NAME . "' does not exist. Creating it now...</p>";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p class='success'>Database created successfully!</p>";
    } else {
        echo "<p class='success'>Database '" . DB_NAME . "' exists.</p>";
    }
    
    // Step 3: Connect to the database
    echo "<h2>Step 3: Connecting to Database</h2>";
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Successfully connected to database!</p>";
    
    // Step 4: Check and create tables
    echo "<h2>Step 4: Creating/Verifying Tables</h2>";
    
    // Drop tables if they exist to avoid foreign key constraints issues
    echo "<h3>Dropping existing tables</h3>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS ar_response");
    $pdo->exec("DROP TABLE IF EXISTS ar_review");
    $pdo->exec("DROP TABLE IF EXISTS ar_user");
    $pdo->exec("DROP TABLE IF EXISTS ar_business");
    $pdo->exec("DROP TABLE IF EXISTS ar_business_setting");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p class='success'>Existing tables dropped successfully.</p>";
    
    // Create business table
    echo "<h3>Creating ar_business table</h3>";
    $pdo->exec("CREATE TABLE ar_business (
        business_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        business_name VARCHAR(255) NOT NULL,
        subscription_type ENUM('free', 'basic', 'professional', 'enterprise') NOT NULL DEFAULT 'free',
        business_status ENUM('trialing', 'active', 'inactive', 'cancelled') NOT NULL DEFAULT 'trialing',
        date_created DATETIME NOT NULL,
        date_updated DATETIME NULL DEFAULT NULL,
        INDEX idx_business_status (business_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "<p class='success'>ar_business table created successfully.</p>";
    
    // Create user table
    echo "<h3>Creating ar_user table</h3>";
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
    echo "<p class='success'>ar_user table created successfully.</p>";
    
    // Create review table
    echo "<h3>Creating ar_review table</h3>";
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
    echo "<p class='success'>ar_review table created successfully.</p>";
    
    // Create response table
    echo "<h3>Creating ar_response table</h3>";
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
    echo "<p class='success'>ar_response table created successfully.</p>";
    
    // Create settings table
    echo "<h3>Creating ar_business_setting table</h3>";
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
    echo "<p class='success'>ar_business_setting table created successfully.</p>";
    
    // Step 5: Verify tables
    echo "<h2>Step 5: Verifying Tables</h2>";
    $tables = ['ar_business', 'ar_user', 'ar_review', 'ar_response', 'ar_business_setting'];
    $allExist = true;
    
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($query);
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            echo "<p class='success'>Table '$table' exists.</p>";
        } else {
            echo "<p class='error'>Table '$table' does not exist!</p>";
            $allExist = false;
        }
    }
    
    if ($allExist) {
        echo "<h2 class='success'>Database Setup Complete!</h2>";
        echo "<p>All tables have been created successfully. You can now try to register a user.</p>";
        echo "<p><a href='index.php'>Return to the application</a></p>";
    } else {
        echo "<h2 class='error'>Database Setup Incomplete!</h2>";
        echo "<p>Some tables are missing. Please check the error messages above.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2 class='error'>Database Error</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div></body></html>";
?>
