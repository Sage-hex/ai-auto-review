<?php
/**
 * Database Setup Script
 * 
 * This script creates the necessary database and tables for the AI Auto Review application.
 */

// Enable error reporting for setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'aiautoreview';

echo "<h1>AI Auto Review Database Setup</h1>";

try {
    // Connect to MySQL server without database
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Connected to MySQL server successfully.</p>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
    echo "<p>Database '$database' created or already exists.</p>";
    
    // Select the database
    $pdo->exec("USE `$database`");
    echo "<p>Using database '$database'.</p>";
    
    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/setup.sql');
    
    // Split SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p>Database tables created successfully.</p>";
    
    // Check if tables were created
    $tables = ['businesses', 'users', 'logs', 'reviews', 'integrations', 'response_templates'];
    echo "<h2>Table Status:</h2>";
    echo "<ul>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE :table");
        $stmt->execute([':table' => $table]);
        
        if ($stmt->rowCount() > 0) {
            echo "<li>Table '$table': <span style='color:green'>Created</span></li>";
            
            // Show table structure
            $stmt = $pdo->prepare("DESCRIBE `$table`");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<li>Table '$table': <span style='color:red'>Not Created</span></li>";
        }
    }
    
    echo "</ul>";
    echo "<p>Database setup completed successfully.</p>";
    echo "<p><a href='/AiAutoReview/'>Return to application</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
