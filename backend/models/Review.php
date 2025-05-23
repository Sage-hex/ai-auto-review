<?php
/**
 * Review Model
 * 
 * This class handles review-related database operations.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Business.php';

class Review {
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
     * Get a review by ID
     * 
     * @param int $id Review ID
     * @param int $businessId Business ID for validation
     * @return array|bool Review data if found, false if not
     */
    public function getById($id, $businessId = null) {
        try {
            $sql = "
                SELECT r.*, 
                       (SELECT COUNT(*) FROM responses WHERE review_id = r.id) as has_response
                FROM reviews r
                WHERE r.id = :id
            ";
            
            $params = [':id' => $id];
            
            if ($businessId !== null) {
                $sql .= " AND r.business_id = :business_id";
                $params[':business_id'] = $businessId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $review = $stmt->fetch();
            
            if (!$review) {
                return false;
            }
            
            return $review;
        } catch (PDOException $e) {
            error_log("Review::getById Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get reviews by business ID with pagination and filtering
     * 
     * @param int $businessId Business ID
     * @param array $filters Filters (platform, rating, sentiment)
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array Reviews data and pagination info
     */
    public function getByBusinessId($businessId, $filters = [], $page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "
                SELECT r.*, 
                       (SELECT COUNT(*) FROM responses WHERE review_id = r.id) as has_response
                FROM reviews r
                WHERE r.business_id = :business_id
            ";
            
            $countSql = "
                SELECT COUNT(*) as total
                FROM reviews r
                WHERE r.business_id = :business_id
            ";
            
            $params = [':business_id' => $businessId];
            
            // Apply filters
            if (!empty($filters['platform'])) {
                $sql .= " AND r.platform = :platform";
                $countSql .= " AND r.platform = :platform";
                $params[':platform'] = $filters['platform'];
            }
            
            if (!empty($filters['rating'])) {
                $sql .= " AND r.rating = :rating";
                $countSql .= " AND r.rating = :rating";
                $params[':rating'] = $filters['rating'];
            }
            
            if (!empty($filters['sentiment'])) {
                $sql .= " AND r.sentiment = :sentiment";
                $countSql .= " AND r.sentiment = :sentiment";
                $params[':sentiment'] = $filters['sentiment'];
            }
            
            // Add sorting and pagination
            $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            // Get total count
            $countStmt = $this->db->prepare($countSql);
            foreach ($params as $key => $value) {
                if ($key !== ':limit' && $key !== ':offset') {
                    $countStmt->bindValue($key, $value);
                }
            }
            $countStmt->execute();
            $totalCount = $countStmt->fetch()['total'];
            
            // Get reviews
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            $reviews = $stmt->fetchAll();
            
            // Calculate pagination info
            $totalPages = ceil($totalCount / $limit);
            
            return [
                'reviews' => $reviews,
                'pagination' => [
                    'total' => $totalCount,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'has_more' => $page < $totalPages
                ]
            ];
        } catch (PDOException $e) {
            error_log("Review::getByBusinessId Error: " . $e->getMessage());
            return [
                'reviews' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'total_pages' => 0,
                    'has_more' => false
                ]
            ];
        }
    }
    
    /**
     * Create a new review
     * 
     * @param array $data Review data
     * @return int|bool New review ID if successful, false if not
     */
    public function create($data) {
        try {
            // Check if business has reached review limit
            if ($this->business->hasReachedReviewLimit($data['business_id'])) {
                return false;
            }
            
            // Check if review already exists
            $stmt = $this->db->prepare("
                SELECT id FROM reviews
                WHERE platform = :platform AND review_id = :review_id
            ");
            
            $stmt->bindParam(':platform', $data['platform'], PDO::PARAM_STR);
            $stmt->bindParam(':review_id', $data['review_id'], PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return false;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO reviews (
                    business_id, platform, review_id, user_name, 
                    rating, content, sentiment, language
                )
                VALUES (
                    :business_id, :platform, :review_id, :user_name,
                    :rating, :content, :sentiment, :language
                )
            ");
            
            $stmt->bindParam(':business_id', $data['business_id'], PDO::PARAM_INT);
            $stmt->bindParam(':platform', $data['platform'], PDO::PARAM_STR);
            $stmt->bindParam(':review_id', $data['review_id'], PDO::PARAM_STR);
            $stmt->bindParam(':user_name', $data['user_name'], PDO::PARAM_STR);
            $stmt->bindParam(':rating', $data['rating'], PDO::PARAM_INT);
            $stmt->bindParam(':content', $data['content'], PDO::PARAM_STR);
            $stmt->bindParam(':sentiment', $data['sentiment'], PDO::PARAM_STR);
            $stmt->bindParam(':language', $data['language'], PDO::PARAM_STR);
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Review::create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get review statistics for a business
     * 
     * @param int $businessId Business ID
     * @return array Review statistics
     */
    public function getStatistics($businessId) {
        try {
            // Get total reviews count
            $totalStmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM reviews
                WHERE business_id = :business_id
            ");
            
            $totalStmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $totalStmt->execute();
            $total = $totalStmt->fetch()['total'];
            
            // Get rating distribution
            $ratingStmt = $this->db->prepare("
                SELECT rating, COUNT(*) as count
                FROM reviews
                WHERE business_id = :business_id
                GROUP BY rating
                ORDER BY rating DESC
            ");
            
            $ratingStmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $ratingStmt->execute();
            $ratings = $ratingStmt->fetchAll();
            
            // Get platform distribution
            $platformStmt = $this->db->prepare("
                SELECT platform, COUNT(*) as count
                FROM reviews
                WHERE business_id = :business_id
                GROUP BY platform
            ");
            
            $platformStmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $platformStmt->execute();
            $platforms = $platformStmt->fetchAll();
            
            // Get sentiment distribution
            $sentimentStmt = $this->db->prepare("
                SELECT sentiment, COUNT(*) as count
                FROM reviews
                WHERE business_id = :business_id AND sentiment IS NOT NULL
                GROUP BY sentiment
            ");
            
            $sentimentStmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $sentimentStmt->execute();
            $sentiments = $sentimentStmt->fetchAll();
            
            // Calculate average rating
            $avgRatingStmt = $this->db->prepare("
                SELECT AVG(rating) as avg_rating
                FROM reviews
                WHERE business_id = :business_id
            ");
            
            $avgRatingStmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $avgRatingStmt->execute();
            $avgRating = $avgRatingStmt->fetch()['avg_rating'];
            
            return [
                'total' => $total,
                'average_rating' => round($avgRating, 1),
                'ratings' => $ratings,
                'platforms' => $platforms,
                'sentiments' => $sentiments
            ];
        } catch (PDOException $e) {
            error_log("Review::getStatistics Error: " . $e->getMessage());
            return [
                'total' => 0,
                'average_rating' => 0,
                'ratings' => [],
                'platforms' => [],
                'sentiments' => []
            ];
        }
    }
}
