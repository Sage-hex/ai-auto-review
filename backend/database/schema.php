<?php
/**
 * Database Schema
 * 
 * Defines the database schema and provides utility functions for schema management
 * 
 * @package AiAutoReview
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get the complete database schema as SQL
 * 
 * @return string SQL schema
 */
function getDatabaseSchema() {
    return file_get_contents(__DIR__ . '/../../database_setup.sql');
}

/**
 * Verify database schema exists and is properly set up
 * 
 * @return bool True if schema is correctly set up
 */
function verifyDatabaseSchema() {
    $db = getDbConnection();
    
    if (!$db) {
        return false;
    }
    
    try {
        // Check if required tables exist
        $requiredTables = [
            'ar_business',
            'ar_user',
            'ar_review',
            'ar_response',
            'ar_business_setting'
        ];
        
        foreach ($requiredTables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
            
            if ($stmt->rowCount() === 0) {
                error_log("Table {$table} does not exist");
                return false;
            }
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Schema verification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get database version information
 * 
 * @return array Database version information
 */
function getDatabaseInfo() {
    $db = getDbConnection();
    
    if (!$db) {
        return [
            'connected' => false,
            'version' => null,
            'tables' => []
        ];
    }
    
    try {
        // Get database version
        $stmt = $db->query("SELECT VERSION() as version");
        $version = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
        
        // Get table information
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $tableInfo = [];
        
        foreach ($tables as $table) {
            $stmt = $db->query("SELECT COUNT(*) as count FROM {$table}");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $tableInfo[$table] = [
                'count' => $count
            ];
        }
        
        return [
            'connected' => true,
            'version' => $version,
            'tables' => $tableInfo
        ];
    } catch (PDOException $e) {
        error_log("Database info error: " . $e->getMessage());
        return [
            'connected' => true,
            'version' => null,
            'error' => $e->getMessage(),
            'tables' => []
        ];
    }
}
