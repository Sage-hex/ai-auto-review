<?php
/**
 * Business Model
 * 
 * This class handles business-related database operations.
 */

require_once __DIR__ . '/../config/config.php';

class Business {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    /**
     * Get a business by ID
     * 
     * @param int $id Business ID
     * @return array|bool Business data if found, false if not
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, plan, trial_ends_at, created_at
                FROM businesses
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $business = $stmt->fetch();
            
            if (!$business) {
                return false;
            }
            
            return $business;
        } catch (PDOException $e) {
            error_log("Business::getById Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new business
     * 
     * @param string $name Business name
     * @param string $plan Subscription plan (default: free)
     * @return int|bool New business ID if successful, false if not
     */
    public function create($name, $plan = 'free') {
        try {
            // Set trial end date to 14 days from now
            $trialEndsAt = date('Y-m-d H:i:s', strtotime('+14 days'));
            
            $stmt = $this->db->prepare("
                INSERT INTO businesses (name, plan, trial_ends_at)
                VALUES (:name, :plan, :trial_ends_at)
            ");
            
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':plan', $plan, PDO::PARAM_STR);
            $stmt->bindParam(':trial_ends_at', $trialEndsAt, PDO::PARAM_STR);
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Business::create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a business
     * 
     * @param int $id Business ID
     * @param array $data Business data to update
     * @return bool True if successful, false if not
     */
    public function update($id, $data) {
        try {
            $allowedFields = ['name', 'plan', 'trial_ends_at'];
            $updates = [];
            $params = [':id' => $id];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "$field = :$field";
                    $params[":$field"] = $value;
                }
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $updateStr = implode(', ', $updates);
            
            $stmt = $this->db->prepare("
                UPDATE businesses
                SET $updateStr
                WHERE id = :id
            ");
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Business::update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a business has access to a feature based on its subscription plan
     * 
     * @param int $businessId Business ID
     * @param string $feature Feature to check
     * @return bool True if has access, false if not
     */
    public function hasFeatureAccess($businessId, $feature) {
        try {
            $business = $this->getById($businessId);
            
            if (!$business || $business['status'] === 'suspended') {
                return false;
            }
            
            $plan = $business['subscription_plan'];
            $planFeatures = PLANS[$plan]['features'] ?? [];
            
            return in_array($feature, $planFeatures);
        } catch (Exception $e) {
            error_log("Business::hasFeatureAccess Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a business has reached its review limit
     * 
     * @param int $businessId Business ID
     * @return bool True if limit reached, false if not
     */
    public function hasReachedReviewLimit($businessId) {
        try {
            $business = $this->getById($businessId);
            
            if (!$business) {
                return true;
            }
            
            $plan = $business['subscription_plan'];
            $reviewLimit = PLANS[$plan]['review_limit'] ?? 0;
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as review_count
                FROM reviews
                WHERE business_id = :business_id
            ");
            
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch();
            
            return $result['review_count'] >= $reviewLimit;
        } catch (Exception $e) {
            error_log("Business::hasReachedReviewLimit Error: " . $e->getMessage());
            return true;
        }
    }
    
    /**
     * Check if a business has reached its user limit
     * 
     * @param int $businessId Business ID
     * @return bool True if limit reached, false if not
     */
    public function hasReachedUserLimit($businessId) {
        try {
            $business = $this->getById($businessId);
            
            if (!$business) {
                return true;
            }
            
            $plan = $business['subscription_plan'];
            $userLimit = PLANS[$plan]['user_limit'] ?? 0;
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as user_count
                FROM users
                WHERE business_id = :business_id
            ");
            
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch();
            
            return $result['user_count'] >= $userLimit;
        } catch (Exception $e) {
            error_log("Business::hasReachedUserLimit Error: " . $e->getMessage());
            return true;
        }
    }
}
