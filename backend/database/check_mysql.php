<?php
/**
 * MySQL Check Script
 * 
 * This script checks the MySQL server configuration and connection.
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MySQL Server Check</h1>";

// Database credentials
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'aiautoreview';

// Check MySQL server version
echo "<h2>MySQL Server Information</h2>";
try {
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "<p>MySQL Server Version: $version</p>";
    echo "<p>Connection to MySQL server: <span style='color:green'>Successful</span></p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    $databaseExists = $stmt->rowCount() > 0;
    
    if ($databaseExists) {
        echo "<p>Database '$database': <span style='color:green'>Exists</span></p>";
        
        // Connect to the database
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check tables
        $tables = ['businesses', 'users', 'logs'];
        echo "<h2>Table Status:</h2>";
        echo "<ul>";
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                echo "<li>Table '$table': <span style='color:green'>Exists</span></li>";
                
                // Show table structure
                $stmt = $pdo->query("DESCRIBE `$table`");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<ul>";
                foreach ($columns as $column) {
                    echo "<li>{$column['Field']} - {$column['Type']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<li>Table '$table': <span style='color:red'>Does not exist</span></li>";
            }
        }
        
        echo "</ul>";
    } else {
        echo "<p>Database '$database': <span style='color:red'>Does not exist</span></p>";
    }
    
    // Check MySQL variables that might affect foreign keys
    echo "<h2>MySQL Variables:</h2>";
    $variables = [
        'foreign_key_checks',
        'innodb_force_recovery',
        'innodb_strict_mode',
        'sql_mode'
    ];
    
    echo "<ul>";
    foreach ($variables as $variable) {
        $stmt = $pdo->query("SHOW VARIABLES LIKE '$variable'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<li>$variable: {$row['Value']}</li>";
    }
    echo "</ul>";
    
    // Check MySQL storage engines
    echo "<h2>Available Storage Engines:</h2>";
    $stmt = $pdo->query("SHOW ENGINES");
    $engines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($engines as $engine) {
        $support = $engine['Support'];
        $color = ($support == 'YES' || $support == 'DEFAULT') ? 'green' : ($support == 'NO' ? 'red' : 'orange');
        echo "<li>{$engine['Engine']}: <span style='color:$color'>{$engine['Support']}</span> - {$engine['Comment']}</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
