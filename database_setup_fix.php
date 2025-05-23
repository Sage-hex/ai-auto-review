<?php
/**
 * Improved Database Setup Script
 * 
 * This script properly sets up the database for the AiAutoReview platform
 * with better error handling and debugging
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/backend/config/database.php';

// Create database if not exists
function createDatabase() {
    echo "Step 1: Creating database if not exists...\n";
    
    try {
        $dsn = "mysql:host=" . DB_HOST;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        echo "✓ Database created or already exists\n";
        return true;
    } catch (PDOException $e) {
        echo "× Failed to create database: " . $e->getMessage() . "\n";
        return false;
    }
}

// Drop existing tables
function dropTables() {
    echo "Step 2: Dropping existing tables...\n";
    
    try {
        $db = getDbConnection();
        
        if (!$db) {
            echo "× Failed to connect to database\n";
            return false;
        }
        
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        $tables = [
            'ar_business_setting',
            'ar_response',
            'ar_review',
            'ar_user',
            'ar_business'
        ];
        
        foreach ($tables as $table) {
            try {
                $db->exec("DROP TABLE IF EXISTS $table");
                echo "✓ Dropped table $table (if existed)\n";
            } catch (PDOException $e) {
                echo "× Failed to drop table $table: " . $e->getMessage() . "\n";
            }
        }
        
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
        return true;
    } catch (PDOException $e) {
        echo "× Error dropping tables: " . $e->getMessage() . "\n";
        return false;
    }
}

// Create tables
function createTables() {
    echo "Step 3: Creating tables...\n";
    
    try {
        $db = getDbConnection();
        
        if (!$db) {
            echo "× Failed to connect to database\n";
            return false;
        }
        
        // Create business table
        $db->exec("
            CREATE TABLE ar_business (
                business_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                business_name VARCHAR(255) NOT NULL,
                subscription_type ENUM('free', 'basic', 'professional', 'enterprise') NOT NULL DEFAULT 'free',
                business_status ENUM('trialing', 'active', 'inactive', 'cancelled') NOT NULL DEFAULT 'trialing',
                date_created DATETIME NOT NULL,
                date_updated DATETIME NULL DEFAULT NULL,
                INDEX idx_business_status (business_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Created table ar_business\n";
        
        // Create user table
        $db->exec("
            CREATE TABLE ar_user (
                user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                business_id INT UNSIGNED NOT NULL,
                full_name VARCHAR(255) NOT NULL,
                email_address VARCHAR(255) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                user_role ENUM('admin', 'manager', 'support', 'viewer') NOT NULL DEFAULT 'viewer',
                date_created DATETIME NOT NULL,
                date_updated DATETIME NULL DEFAULT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                last_login_date DATETIME NULL,
                UNIQUE INDEX idx_email (email_address),
                INDEX idx_business_user (business_id),
                CONSTRAINT fk_user_business FOREIGN KEY (business_id) 
                    REFERENCES ar_business(business_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Created table ar_user\n";
        
        // Create review table
        $db->exec("
            CREATE TABLE ar_review (
                review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                business_id INT UNSIGNED NOT NULL,
                platform_name VARCHAR(50) NOT NULL,
                external_review_id VARCHAR(255) NOT NULL,
                rating_value TINYINT UNSIGNED NOT NULL,
                review_content TEXT NOT NULL,
                reviewer_name VARCHAR(255) NOT NULL,
                sentiment_value ENUM('positive', 'neutral', 'negative') NOT NULL,
                date_posted DATE NOT NULL,
                date_updated DATETIME NOT NULL,
                has_response BOOLEAN NOT NULL DEFAULT FALSE,
                UNIQUE INDEX idx_platform_review (platform_name, external_review_id),
                INDEX idx_business_review (business_id),
                INDEX idx_sentiment (sentiment_value),
                INDEX idx_rating (rating_value),
                CONSTRAINT fk_review_business FOREIGN KEY (business_id) 
                    REFERENCES ar_business(business_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Created table ar_review\n";
        
        // Create response table
        $db->exec("
            CREATE TABLE ar_response (
                response_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                business_id INT UNSIGNED NOT NULL,
                review_id INT UNSIGNED NOT NULL,
                response_content TEXT NOT NULL,
                response_status ENUM('pending', 'approved', 'posted') NOT NULL DEFAULT 'pending',
                created_by_user_id INT UNSIGNED NULL,
                approved_by_user_id INT UNSIGNED NULL,
                date_created DATETIME NOT NULL,
                date_updated DATETIME NULL DEFAULT NULL,
                date_posted DATETIME NULL DEFAULT NULL,
                is_ai_generated BOOLEAN NOT NULL DEFAULT TRUE,
                INDEX idx_business_response (business_id),
                INDEX idx_review_response (review_id),
                INDEX idx_response_status (response_status),
                CONSTRAINT fk_response_business FOREIGN KEY (business_id) 
                    REFERENCES ar_business(business_id) ON DELETE CASCADE,
                CONSTRAINT fk_response_review FOREIGN KEY (review_id) 
                    REFERENCES ar_review(review_id) ON DELETE CASCADE,
                CONSTRAINT fk_response_creator FOREIGN KEY (created_by_user_id) 
                    REFERENCES ar_user(user_id) ON DELETE SET NULL,
                CONSTRAINT fk_response_approver FOREIGN KEY (approved_by_user_id) 
                    REFERENCES ar_user(user_id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Created table ar_response\n";
        
        // Create settings table
        $db->exec("
            CREATE TABLE ar_business_setting (
                setting_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                business_id INT UNSIGNED NOT NULL,
                setting_key VARCHAR(100) NOT NULL,
                setting_value TEXT NOT NULL,
                date_created DATETIME NOT NULL,
                date_updated DATETIME NULL DEFAULT NULL,
                UNIQUE INDEX idx_business_setting (business_id, setting_key),
                CONSTRAINT fk_setting_business FOREIGN KEY (business_id) 
                    REFERENCES ar_business(business_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Created table ar_business_setting\n";
        
        return true;
    } catch (PDOException $e) {
        echo "× Error creating tables: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run setup process
echo "Starting database setup process...\n";
$dbCreated = createDatabase();

if ($dbCreated) {
    $tablesDropped = dropTables();
    
    if ($tablesDropped) {
        $tablesCreated = createTables();
        
        if ($tablesCreated) {
            echo "\n✓ Database setup completed successfully!\n";
        } else {
            echo "\n× Database setup failed at table creation step\n";
        }
    } else {
        echo "\n× Database setup failed at table dropping step\n";
    }
} else {
    echo "\n× Database setup failed at database creation step\n";
}
