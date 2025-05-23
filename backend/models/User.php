<?php
/**
 * User Model
 * 
 * This class handles user-related database operations.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Business.php';

class User {
    private $db;
    private $business;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = getDbConnection();
        $this->business = new Business();
    }
    
    /**
     * Get a user by ID
     * 
     * @param int $id User ID
     * @return array|bool User data if found, false if not
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, business_id, name, email, role, created_at
                FROM ar_user
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("User::getById Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a user by email
     * 
     * @param string $email User email
     * @return array|bool User data if found, false if not
     */
    public function getByEmail($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT user_id, business_id, full_name, email_address, password_hash, user_role, date_created
                FROM ar_user
                WHERE email_address = :email
            ");
            
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("User::getByEmail Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new user
     * 
     * @param int $businessId Business ID
     * @param string $name User name
     * @param string $email User email
     * @param string $password User password (plain text)
     * @param string $role User role (default: viewer)
     * @return int|bool New user ID if successful, false if not
     */
    public function create($businessId, $name, $email, $password, $role = 'viewer') {
        try {
            // Check if business has reached user limit
            if ($this->business->hasReachedUserLimit($businessId)) {
                return false;
            }
            
            // Check if email already exists
            if ($this->getByEmail($email)) {
                return false;
            }
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO ar_user (business_id, full_name, email_address, password_hash, user_role, date_created)
                VALUES (:business_id, :name, :email, :password, :role, NOW())
            ");
            
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("User::create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a user
     * 
     * @param int $id User ID
     * @param array $data User data to update
     * @return bool True if successful, false if not
     */
    public function update($id, $data) {
        try {
            $allowedFields = ['name', 'email', 'role'];
            $updates = [];
            $params = [':id' => $id];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "$field = :$field";
                    $params[":$field"] = $value;
                }
            }
            
            // Handle password update separately
            if (isset($data['password']) && !empty($data['password'])) {
                $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                $updates[] = "password = :password";
                $params[':password'] = $passwordHash;
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $updateStr = implode(', ', $updates);
            
            $stmt = $this->db->prepare("
                UPDATE ar_user
                SET $updateStr
                WHERE id = :id
            ");
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("User::update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a user
     * 
     * @param int $id User ID
     * @return bool True if successful, false if not
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM ar_user
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User::delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get users by business ID
     * 
     * @param int $businessId Business ID
     * @return array Users data
     */
    public function getByBusinessId($businessId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, business_id, name, email, role, created_at
                FROM ar_user
                WHERE business_id = :business_id
                ORDER BY created_at DESC
            ");
            
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("User::getByBusinessId Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Authenticate a user with email and password
     * 
     * @param string $email User email
     * @param string $password User password (plain text)
     * @return array|bool User data if authenticated, false if not
     */
    public function authenticate($email, $password) {
        try {
            $user = $this->getByEmail($email);
            
            if (!$user) {
                return false;
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                return false;
            }
            
            // Remove password hash from user data
            unset($user['password_hash']);
            
            return $user;
        } catch (Exception $e) {
            error_log("User::authenticate Error: " . $e->getMessage());
            return false;
        }
    }
}
