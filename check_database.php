<?php
/**
 * Database Check Script
 * 
 * This script checks if the database and required tables exist
 */

// Include database configuration
require_once __DIR__ . '/backend/config/database.php';

// Check database connection
$db = getDbConnection();
if (!$db) {
    echo "Database connection failed!<br>";
    exit;
}
echo "Database connection successful!<br>";

// Check if tables exist
try {
    $tables = ['ar_business', 'ar_user', 'ar_review', 'ar_response', 'ar_business_setting'];
    $existingTables = [];
    $missingTables = [];
    
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $db->query($query);
        
        if ($stmt->rowCount() > 0) {
            $existingTables[] = $table;
        } else {
            $missingTables[] = $table;
        }
    }
    
    echo "<h3>Database Tables Check</h3>";
    
    if (empty($missingTables)) {
        echo "<p style='color: green;'>All required tables exist!</p>";
    } else {
        echo "<p style='color: red;'>Missing tables: " . implode(', ', $missingTables) . "</p>";
    }
    
    echo "<h4>Existing Tables:</h4>";
    echo "<ul>";
    foreach ($existingTables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // If we have existing tables, check their structure
    if (!empty($existingTables)) {
        echo "<h3>Table Structure Check</h3>";
        
        foreach ($existingTables as $table) {
            echo "<h4>$table Structure:</h4>";
            
            $query = "DESCRIBE $table";
            $stmt = $db->query($query);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>{$column['Field']}</td>";
                echo "<td>{$column['Type']}</td>";
                echo "<td>{$column['Null']}</td>";
                echo "<td>{$column['Key']}</td>";
                echo "<td>{$column['Default']}</td>";
                echo "<td>{$column['Extra']}</td>";
                echo "</tr>";
            }
            
            echo "</table><br>";
        }
    }
    
    // If we have missing tables, run the setup script
    if (!empty($missingTables)) {
        echo "<h3>Running Database Setup</h3>";
        
        // Include the setup script
        require_once __DIR__ . '/backend/database/setup.php';
        
        $result = importDatabaseSchema();
        
        if ($result['success']) {
            echo "<p style='color: green;'>{$result['message']}</p>";
        } else {
            echo "<p style='color: red;'>{$result['message']}</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "Error checking tables: " . $e->getMessage();
}
?>
