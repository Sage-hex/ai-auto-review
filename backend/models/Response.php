<?php
/**
 * Response Model
 * 
 * This class handles response-related database operations.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Review.php';

class Response {
    private $db;
    private $review;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = getDbConnection();
        $this->review = new Review();
    }
    
    /**
     * Get a response by ID
     * 
     * @param int $id Response ID
     * @param int $businessId Business ID for validation
     * @return array|bool Response data if found, false if not
     */
    public function getById($id, $businessId = null) {
        try {
            $sql = "
                SELECT r.*, u.name as approver_name
                FROM responses r
                LEFT JOIN users u ON r.approved_by = u.id
                WHERE r.id = :id
            ";
            
            $params = [':id' => $id];
            
            if ($businessId !== null) {
                $sql .= " AND r.business_id = :business_id";
                $params[':business_id'] = $businessId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $response = $stmt->fetch();
            
            if (!$response) {
                return false;
            }
            
            return $response;
        } catch (PDOException $e) {
            error_log("Response::getById Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get responses by review ID
     * 
     * @param int $reviewId Review ID
     * @param int $businessId Business ID for validation
     * @return array Responses data
     */
    public function getByReviewId($reviewId, $businessId = null) {
        try {
            $sql = "
                SELECT r.*, u.name as approver_name
                FROM responses r
                LEFT JOIN users u ON r.approved_by = u.id
                WHERE r.review_id = :review_id
            ";
            
            $params = [':review_id' => $reviewId];
            
            if ($businessId !== null) {
                $sql .= " AND r.business_id = :business_id";
                $params[':business_id'] = $businessId;
            }
            
            $sql .= " ORDER BY r.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Response::getByReviewId Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create a new response
     * 
     * @param int $businessId Business ID
     * @param int $reviewId Review ID
     * @param string $responseText Response text
     * @return int|bool New response ID if successful, false if not
     */
    public function create($businessId, $reviewId, $responseText) {
        try {
            // Verify that the review exists and belongs to the business
            $review = $this->review->getById($reviewId, $businessId);
            
            if (!$review) {
                return false;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO responses (business_id, review_id, response_text, status)
                VALUES (:business_id, :review_id, :response_text, 'pending')
            ");
            
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
            $stmt->bindParam(':response_text', $responseText, PDO::PARAM_STR);
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Response::create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a response
     * 
     * @param int $id Response ID
     * @param int $businessId Business ID for validation
     * @param string $responseText Updated response text
     * @return bool True if successful, false if not
     */
    public function update($id, $businessId, $responseText) {
        try {
            // Verify that the response exists and belongs to the business
            $response = $this->getById($id, $businessId);
            
            if (!$response) {
                return false;
            }
            
            // Only allow updating pending responses
            if ($response['status'] !== 'pending') {
                return false;
            }
            
            $stmt = $this->db->prepare("
                UPDATE responses
                SET response_text = :response_text
                WHERE id = :id AND business_id = :business_id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->bindParam(':response_text', $responseText, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Response::update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Approve a response
     * 
     * @param int $id Response ID
     * @param int $businessId Business ID for validation
     * @param int $userId User ID approving the response
     * @return bool True if successful, false if not
     */
    public function approve($id, $businessId, $userId) {
        try {
            // Verify that the response exists and belongs to the business
            $response = $this->getById($id, $businessId);
            
            if (!$response) {
                return false;
            }
            
            // Only allow approving pending responses
            if ($response['status'] !== 'pending') {
                return false;
            }
            
            $stmt = $this->db->prepare("
                UPDATE responses
                SET status = 'approved', approved_by = :user_id
                WHERE id = :id AND business_id = :business_id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Response::approve Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark a response as posted
     * 
     * @param int $id Response ID
     * @param int $businessId Business ID for validation
     * @return bool True if successful, false if not
     */
    public function markAsPosted($id, $businessId) {
        try {
            // Verify that the response exists and belongs to the business
            $response = $this->getById($id, $businessId);
            
            if (!$response) {
                return false;
            }
            
            // Only allow posting approved responses
            if ($response['status'] !== 'approved') {
                return false;
            }
            
            $now = date('Y-m-d H:i:s');
            
            $stmt = $this->db->prepare("
                UPDATE responses
                SET status = 'posted', posted_at = :posted_at
                WHERE id = :id AND business_id = :business_id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->bindParam(':posted_at', $now, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Response::markAsPosted Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get pending responses for a business
     * 
     * @param int $businessId Business ID
     * @param int $limit Maximum number of responses to return
     * @return array Pending responses
     */
    public function getPendingResponses($businessId, $limit = 20) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, rv.platform, rv.rating, rv.user_name, rv.content as review_content
                FROM responses r
                JOIN reviews rv ON r.review_id = rv.id
                WHERE r.business_id = :business_id AND r.status = 'pending'
                ORDER BY r.created_at ASC
                LIMIT :limit
            ");
            
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Response::getPendingResponses Error: " . $e->getMessage());
            return [];
        }
    }
}
