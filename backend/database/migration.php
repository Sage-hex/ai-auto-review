<?php
/**
 * Database Migration
 * 
 * Handles database migrations for the AiAutoReview platform
 * 
 * @package AiAutoReview
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Applies database migrations to bring the schema up to date
 * 
 * @return array Result with success status and messages
 */
function runDatabaseMigration() {
    $db = getDbConnection();
    
    if (!$db) {
        return [
            'success' => false,
            'message' => 'Failed to connect to database'
        ];
    }
    
    try {
        // Create migration tracking table if it doesn't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS ar_migrations (
                migration_id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Check if the main schema migration has been applied
        $stmt = $db->prepare("SELECT 1 FROM ar_migrations WHERE migration_name = 'initial_schema'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // Execute the main schema migration
            $sqlFile = file_get_contents(__DIR__ . '/../../database_setup.sql');
            
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
            
            // Record the migration
            $stmt = $db->prepare("INSERT INTO ar_migrations (migration_name) VALUES ('initial_schema')");
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Initial schema migration applied successfully'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Database is already up to date'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Migration failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Get migration history
 * 
 * @return array Migration history records
 */
function getMigrationHistory() {
    $db = getDbConnection();
    
    if (!$db) {
        return [];
    }
    
    try {
        $stmt = $db->query("
            SELECT migration_name, executed_at 
            FROM ar_migrations 
            ORDER BY executed_at
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Migration history error: " . $e->getMessage());
        return [];
    }
}

// Run the migration if this file is executed directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $result = runDatabaseMigration();
    
    echo $result['success'] ? "✓ " : "× ";
    echo $result['message'] . "\n";
    
    if ($result['success']) {
        echo "\nMigration History:\n";
        $history = getMigrationHistory();
        
        foreach ($history as $migration) {
            echo "- {$migration['migration_name']} (executed on {$migration['executed_at']})\n";
        }
    }
    
    exit($result['success'] ? 0 : 1);
}
