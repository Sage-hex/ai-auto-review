<?php
/**
 * Debug Authentication
 * 
 * This script helps debug authentication issues
 */

// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once __DIR__ . '/backend/config/database.php';

echo "<h1>Authentication Debug</h1>";

// Test database connection
try {
    $db = getDbConnection();
    if ($db) {
        echo "<p style='color:green'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color:red'>× Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>× Database error: " . $e->getMessage() . "</p>";
}

// Check if the required tables exist
$tables = ['ar_business', 'ar_user', 'ar_review', 'ar_response', 'ar_business_setting'];
echo "<h2>Database Tables</h2>";
echo "<ul>";

foreach ($tables as $table) {
    try {
        $query = $db->query("SHOW TABLES LIKE '$table'");
        $exists = $query && $query->rowCount() > 0;
        
        echo "<li style='color:" . ($exists ? 'green' : 'red') . "'>";
        echo ($exists ? '✓' : '×') . " $table";
        
        if ($exists) {
            // Show table structure
            $structure = $db->query("DESCRIBE $table");
            $columns = $structure->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']}</li>";
            }
            echo "</ul>";
            
            // Show row count
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch(PDO::FETCH_ASSOC);
            echo "<p>Row count: {$count['count']}</p>";
        }
        
        echo "</li>";
    } catch (Exception $e) {
        echo "<li style='color:red'>× Error checking $table: " . $e->getMessage() . "</li>";
    }
}

echo "</ul>";

// Check model files
echo "<h2>Model Files</h2>";

$models = [
    'User' => '/backend/models/User.php',
    'Business' => '/backend/models/Business.php'
];

foreach ($models as $name => $path) {
    $fullPath = __DIR__ . $path;
    if (file_exists($fullPath)) {
        echo "<p style='color:green'>✓ $name model exists at $path</p>";
    } else {
        echo "<p style='color:red'>× $name model doesn't exist at $path</p>";
    }
}

// Check handler.php file
$handlerPath = __DIR__ . '/backend/api/endpoints/auth/handler.php';
if (file_exists($handlerPath)) {
    echo "<p style='color:green'>✓ Auth handler exists</p>";
    
    // Show part of the handler code
    $handlerCode = file_get_contents($handlerPath);
    echo "<pre>" . htmlspecialchars(substr($handlerCode, 0, 500)) . "...</pre>";
} else {
    echo "<p style='color:red'>× Auth handler doesn't exist</p>";
}

// Check JWT generation function
echo "<h2>JWT Function</h2>";
if (function_exists('generateJWT')) {
    echo "<p style='color:green'>✓ generateJWT function exists</p>";
} else {
    echo "<p style='color:red'>× generateJWT function doesn't exist</p>";
    
    // Check jwt.php file
    $jwtPath = __DIR__ . '/backend/utils/jwt.php';
    if (file_exists($jwtPath)) {
        echo "<p style='color:green'>✓ JWT utility file exists at $jwtPath</p>";
    } else {
        echo "<p style='color:red'>× JWT utility file doesn't exist at $jwtPath</p>";
    }
}

// Display server information
echo "<h2>Server Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
