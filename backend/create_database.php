<?php
/**
 * Database Creation Script
 * 
 * This script creates the database and tables needed for the AI Auto Review application.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'aiautoreview';

try {
    // Connect to MySQL without database
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$database' created or already exists.<br>";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create businesses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `businesses` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `plan` ENUM('free', 'basic', 'pro') NOT NULL DEFAULT 'free',
        `trial_ends_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table 'businesses' created or already exists.<br>";
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `business_id` INT UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'manager', 'support', 'viewer') NOT NULL DEFAULT 'admin',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table 'users' created or already exists.<br>";
    
    // Create platforms table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `platforms` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `business_id` INT UNSIGNED NOT NULL,
        `name` ENUM('google', 'yelp', 'facebook') NOT NULL,
        `credentials` JSON NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `business_platform` (`business_id`, `name`),
        FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table 'platforms' created or already exists.<br>";
    
    // Create reviews table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `reviews` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `business_id` INT UNSIGNED NOT NULL,
        `platform_id` INT UNSIGNED NOT NULL,
        `external_id` VARCHAR(255) NOT NULL,
        `reviewer_name` VARCHAR(255) NOT NULL,
        `rating` TINYINT UNSIGNED NOT NULL,
        `content` TEXT NOT NULL,
        `review_date` DATETIME NOT NULL,
        `status` ENUM('new', 'pending', 'responded', 'ignored') NOT NULL DEFAULT 'new',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `platform_review` (`platform_id`, `external_id`),
        FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table 'reviews' created or already exists.<br>";
    
    // Create responses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `responses` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `review_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NULL,
        `content` TEXT NOT NULL,
        `is_ai_generated` TINYINT(1) NOT NULL DEFAULT 1,
        `is_published` TINYINT(1) NOT NULL DEFAULT 0,
        `published_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table 'responses' created or already exists.<br>";
    
    // Create logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `logs` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT UNSIGNED NULL,
        `action` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `ip_address` VARCHAR(45) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table 'logs' created or already exists.<br>";
    
    echo "<br>Database setup completed successfully!";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
