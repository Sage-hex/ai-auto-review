<?php
/**
 * Database Setup Script
 * 
 * This script sets up the database for the AiAutoReview platform
 * It creates all necessary tables with the proper structure
 * 
 * @package AiAutoReview
 */

require_once __DIR__ . '/../config/database.php';

// Import SQL from the main schema file
function importDatabaseSchema() {
    $db = getDbConnection();
    
    if (!$db) {
        return [
            'success' => false,
            'message' => 'Failed to connect to database'
        ];
    }
    
    try {
        // Read the SQL file
        $sqlFile = file_get_contents(__DIR__ . '/../../database_setup.sql');
        error_log("Reading SQL file from: " . __DIR__ . '/../../database_setup.sql');
        
        if (!$sqlFile) {
            return [
                'success' => false,
                'message' => 'Failed to read SQL file'
            ];
        }
        
        // Execute each SQL statement
        $statements = explode(';', $sqlFile);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            if (!empty($statement)) {
                $db->exec($statement);
            }
        }
        
        return [
            'success' => true,
            'message' => 'Database setup completed successfully'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database setup failed: ' . $e->getMessage()
        ];
    }
}

// Run the setup if this file is executed directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $result = importDatabaseSchema();
    
    echo $result['success'] ? "✓ " : "× ";
    echo $result['message'] . "\n";
    
    exit($result['success'] ? 0 : 1);
}
