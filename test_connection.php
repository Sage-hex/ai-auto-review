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

echo "<h1>Database Connection Test</h1>";

// Test MySQL connection without database
try {
    echo "<h2>1. Testing MySQL Connection</h2>";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>MySQL connection successful!</p>";
    
    // Check if database exists
    echo "<h2>2. Checking Database Existence</h2>";
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        echo "<p style='color:orange'>Database '" . DB_NAME . "' does not exist. Creating it now...</p>";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color:green'>Database created successfully!</p>";
    } else {
        echo "<p style='color:green'>Database '" . DB_NAME . "' exists.</p>";
    }
    
    // Connect to the database
    echo "<h2>3. Testing Database Connection</h2>";
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Check if tables exist
    echo "<h2>4. Checking Required Tables</h2>";
    $tables = ['ar_business', 'ar_user', 'ar_review', 'ar_response', 'ar_business_setting'];
    $missingTables = [];
    
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($query);
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            echo "<p>Table '$table' exists.</p>";
        } else {
            echo "<p style='color:red'>Table '$table' does not exist!</p>";
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo "<p style='color:orange'>Some tables are missing. Please run the setup_database.php script to create them.</p>";
        echo "<p><a href='setup_database.php' style='padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Run Database Setup</a></p>";
    } else {
        echo "<p style='color:green'>All required tables exist!</p>";
    }
    
    // Test JWT function
    echo "<h2>5. Testing JWT Generation</h2>";
    if (function_exists('generateJWT')) {
        echo "<p style='color:green'>JWT function exists!</p>";
        try {
            $token = generateJWT(1, 1, 'admin');
            echo "<p style='color:green'>JWT token generated successfully!</p>";
        } catch (Exception $e) {
            echo "<p style='color:red'>JWT generation error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>JWT function does not exist! Make sure jwt.php is properly included.</p>";
        
        // Try to include JWT file
        echo "<p>Attempting to include JWT file...</p>";
        try {
            require_once __DIR__ . '/backend/utils/jwt.php';
            echo "<p style='color:green'>JWT file included successfully!</p>";
            
            if (function_exists('generateJWT')) {
                echo "<p style='color:green'>JWT function now exists!</p>";
                try {
                    $token = generateJWT(1, 1, 'admin');
                    echo "<p style='color:green'>JWT token generated successfully!</p>";
                } catch (Exception $e) {
                    echo "<p style='color:red'>JWT generation error: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p style='color:red'>JWT function still does not exist after including the file!</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>Error including JWT file: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Connection failed: " . $e->getMessage() . "</p>";
}
?>
