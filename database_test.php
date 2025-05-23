<?php
/**
 * Database Test Script
 * 
 * This script tests the database connection and checks if the necessary tables exist
 */

// Include database configuration
require_once __DIR__ . '/backend/config/database.php';

// Test database connection
function testDatabaseConnection() {
    echo "Testing database connection...\n";
    
    try {
        $db = getDbConnection();
        
        if (!$db) {
            echo "× Failed to connect to database\n";
            return false;
        }
        
        echo "✓ Successfully connected to database\n";
        
        // Check if database exists
        $stmt = $db->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Current database: " . $result['db_name'] . "\n";
        
        // Check if tables exist
        $tables = ['ar_business', 'ar_user', 'ar_review', 'ar_response', 'ar_business_setting'];
        echo "Checking tables:\n";
        
        foreach ($tables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            $tableExists = $stmt->rowCount() > 0;
            
            echo ($tableExists ? "✓ " : "× ") . $table . "\n";
        }
        
        return true;
    } catch (PDOException $e) {
        echo "× Database Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Create database if not exists
function createDatabase() {
    echo "Attempting to create database...\n";
    
    try {
        $dsn = "mysql:host=" . DB_HOST;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        
        echo "✓ Database created or already exists\n";
        return true;
    } catch (PDOException $e) {
        echo "× Failed to create database: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run tests
createDatabase();
testDatabaseConnection();
