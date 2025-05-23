<?php
/**
 * Database Class
 * 
 * This class provides a singleton database connection.
 */

require_once __DIR__ . '/../config/database.php';

class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->connection = getDbConnection();
        
        if (!$this->connection) {
            throw new Exception('Database connection failed');
        }
    }
    
    /**
     * Get database instance (singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Get database connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * Get a single record
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|bool Record if found, false if not
     */
    public function getRecord($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get multiple records
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Records
     */
    public function getRecords($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Insert a record
     * 
     * @param string $table Table name
     * @param array $data Record data
     * @return int|bool Last insert ID if successful, false if not
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute(array_values($data));
        
        if (!$result) {
            return false;
        }
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * Update a record
     * 
     * @param string $table Table name
     * @param array $data Record data
     * @param string $where Where clause
     * @param array $params Where parameters
     * @return bool True if successful, false if not
     */
    public function update($table, $data, $where, $params = []) {
        $set = [];
        
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
        }
        
        $set = implode(', ', $set);
        
        $sql = "UPDATE $table SET $set WHERE $where";
        
        $stmt = $this->connection->prepare($sql);
        
        $values = array_merge(array_values($data), $params);
        
        return $stmt->execute($values);
    }
    
    /**
     * Delete a record
     * 
     * @param string $table Table name
     * @param string $where Where clause
     * @param array $params Where parameters
     * @return bool True if successful, false if not
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        
        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute($params);
    }
}
