<?php
/**
 * Database Utilities
 * 
 * Provides common database utility functions for the AiAutoReview platform
 * 
 * @package AiAutoReview
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Execute a transaction safely
 * 
 * @param callable $callback Function to execute within transaction
 * @return array Result with success status and data/error message
 */
function executeTransaction($callback) {
    $db = getDbConnection();
    
    if (!$db) {
        return [
            'success' => false,
            'error' => 'Failed to connect to database'
        ];
    }
    
    try {
        $db->beginTransaction();
        
        $result = $callback($db);
        
        $db->commit();
        
        return [
            'success' => true,
            'data' => $result
        ];
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Check if a record exists in the database
 * 
 * @param string $table Table name
 * @param string $field Field name
 * @param mixed $value Field value
 * @return bool True if record exists
 */
function recordExists($table, $field, $value) {
    $db = getDbConnection();
    
    if (!$db) {
        return false;
    }
    
    try {
        $sql = "SELECT 1 FROM {$table} WHERE {$field} = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$value]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Record check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get records with pagination
 * 
 * @param string $table Table name
 * @param array $conditions Where conditions as field => value pairs
 * @param int $page Page number (1-based)
 * @param int $perPage Records per page
 * @param string $orderBy Order by clause
 * @return array Records and pagination info
 */
function getPaginatedRecords($table, $conditions = [], $page = 1, $perPage = 10, $orderBy = 'id DESC') {
    $db = getDbConnection();
    
    if (!$db) {
        return [
            'success' => false,
            'error' => 'Failed to connect to database',
            'records' => [],
            'pagination' => []
        ];
    }
    
    try {
        // Build where clause
        $whereClause = '';
        $params = [];
        
        if (!empty($conditions)) {
            $whereParts = [];
            
            foreach ($conditions as $field => $value) {
                $whereParts[] = "{$field} = ?";
                $params[] = $value;
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $whereParts);
        }
        
        // Count total records
        $countSql = "SELECT COUNT(*) as total FROM {$table} {$whereClause}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Calculate pagination
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Get records
        $recordsSql = "SELECT * FROM {$table} {$whereClause} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $recordsStmt = $db->prepare($recordsSql);
        $recordsStmt->execute($params);
        $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'records' => $records,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)$totalPages,
                'from' => $offset + 1,
                'to' => $offset + count($records)
            ]
        ];
    } catch (PDOException $e) {
        error_log("Pagination error: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'records' => [],
            'pagination' => []
        ];
    }
}
