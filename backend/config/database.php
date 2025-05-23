<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection settings for the application.
 */

// Database credentials - use environment variables if available, otherwise use defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'aiautoreview');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Log warning if using default credentials in production
if (getenv('APP_ENV') !== 'development' && DB_USER === 'root' && DB_PASS === '') {
    error_log("WARNING: Using default database credentials in production environment. This is a security risk.");
}

// PDO Database connection
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Log error and return false
        error_log("Database Connection Error: " . $e->getMessage());
        return false;
    }
}
