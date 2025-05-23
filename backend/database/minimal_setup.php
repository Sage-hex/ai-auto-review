<?php
/**
 * Minimal Database Setup Script
 * 
 * This script creates the minimal tables needed for registration without foreign keys initially.
 */

// Enable error reporting for setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'aiautoreview';

echo "<h1>AI Auto Review Minimal Database Setup</h1>";

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
    $tables = ['logs', 'users', 'businesses'];
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            echo "<p>Dropped table $table if it existed.</p>";
        } catch (Exception $e) {
            echo "<p>Warning: Could not drop table $table: " . $e->getMessage() . "</p>";
        }
    }
    
    // Step 1: Create businesses table with no foreign keys
    try {
        $sql = "
        CREATE TABLE `businesses` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `plan` VARCHAR(50) NOT NULL DEFAULT 'free',
            `trial_ends_at` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
        echo "<p>Created businesses table successfully.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error creating businesses table: " . $e->getMessage() . "</p>";
        throw $e;
    }
    
    // Step 2: Create users table with no foreign keys initially
    try {
        $sql = "
        CREATE TABLE `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `business_id` INT NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('admin', 'manager', 'support', 'viewer') NOT NULL DEFAULT 'viewer',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
        echo "<p>Created users table successfully.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error creating users table: " . $e->getMessage() . "</p>";
        throw $e;
    }
    
    // Step 3: Create logs table with no foreign keys initially
    try {
        $sql = "
        CREATE TABLE `logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT,
            `action` VARCHAR(100) NOT NULL,
            `description` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
        echo "<p>Created logs table successfully.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error creating logs table: " . $e->getMessage() . "</p>";
        throw $e;
    }
    
    // Step 4: Now add foreign key constraints
    try {
        // Add foreign key to users table
        $sql = "
        ALTER TABLE `users` 
        ADD CONSTRAINT `fk_users_business` 
        FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) 
        ON DELETE CASCADE;
        ";
        $pdo->exec($sql);
        echo "<p>Added foreign key constraint to users table.</p>";
        
        // Add foreign key to logs table
        $sql = "
        ALTER TABLE `logs` 
        ADD CONSTRAINT `fk_logs_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE SET NULL;
        ";
        $pdo->exec($sql);
        echo "<p>Added foreign key constraint to logs table.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error adding foreign key constraints: " . $e->getMessage() . "</p>";
        // Continue execution even if adding constraints fails
        echo "<p>Tables were created but without foreign key constraints.</p>";
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<h2>Database setup completed!</h2>";
    echo "<p>The essential tables for registration have been created.</p>";
    echo "<p><a href='/AiAutoReview/'>Return to application</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Critical Error: " . $e->getMessage() . "</p>";
    
    // Additional debugging information
    echo "<pre>";
    print_r($e->getTrace());
    echo "</pre>";
}
?>
