<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection settings for the application.
 * Sensitive information is loaded from environment variables.
 */

// Include environment loader if not already included
if (!function_exists('env')) {
    require_once __DIR__ . '/env.php';
}

// Database credentials from environment variables
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'aiautoreview'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASSWORD', ''));

// Log warning if using default credentials in production
if (env('APP_ENV') !== 'development' && DB_USER === 'root' && DB_PASS === '') {
    error_log("WARNING: Using default database credentials in production environment. This is a security risk.");
}

// PDO Database connection
function getDbConnection() {
    try {
        // First check if the database exists
        $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Check if database exists
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        $dbExists = $stmt->rowCount() > 0;
        
        if (!$dbExists) {
            // Create the database if it doesn't exist
            error_log("Database '" . DB_NAME . "' does not exist. Creating it...");
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            error_log("Database created successfully");
        }
        
        // Connect to the database
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $dbConnection = new PDO($dsn, DB_USER, DB_PASS, $options);
        error_log("Connected to database successfully");
        return $dbConnection;
    } catch (PDOException $e) {
        // Log detailed error and return false
        error_log("Database Connection Error: " . $e->getMessage());
        error_log("Error Code: " . $e->getCode());
        error_log("Stack Trace: " . $e->getTraceAsString());
        return false;
    }
}
