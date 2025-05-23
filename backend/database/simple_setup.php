<?php
/**
 * Simple Database Setup Script
 * 
 * This script creates only the essential tables needed for registration.
 */

// Enable error reporting for setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'aiautoreview';

echo "<h1>AI Auto Review Simple Database Setup</h1>";

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
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop existing tables if they exist
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("DROP TABLE IF EXISTS businesses");
    
    echo "<p>Dropped existing tables.</p>";
    
    // Create businesses table
    $businessesTable = "
    CREATE TABLE businesses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        plan VARCHAR(50) NOT NULL DEFAULT 'free',
        trial_ends_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($businessesTable);
    echo "<p>Created businesses table.</p>";
    
    // Create users table
    $usersTable = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        business_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'manager', 'support', 'viewer') NOT NULL DEFAULT 'viewer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (business_id),
        FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($usersTable);
    echo "<p>Created users table.</p>";
    
    // Create a simple logs table
    $logsTable = "
    CREATE TABLE logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($logsTable);
    echo "<p>Created logs table.</p>";
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<h2>Database setup completed successfully!</h2>";
    echo "<p>The essential tables for registration have been created.</p>";
    echo "<p><a href='/AiAutoReview/'>Return to application</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    
    // Additional debugging information
    echo "<pre>";
    print_r($e->getTrace());
    echo "</pre>";
}
?>
