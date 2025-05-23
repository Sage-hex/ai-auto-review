<?php
/**
 * Platform Service
 * 
 * This class handles integration with external review platforms (Google, Yelp, Facebook).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/AIService.php';

class PlatformService {
    private $db;
    private $review;
    private $aiService;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = getDbConnection();
        $this->review = new Review();
        $this->aiService = new AIService();
    }
    
    /**
     * Get platform tokens for a business
     * 
     * @param int $businessId Business ID
     * @param string $platform Platform name (optional)
     * @return array Platform tokens
     */
    public function getPlatformTokens($businessId, $platform = null) {
        try {
            $sql = "
                SELECT id, business_id, platform, access_token, refresh_token, expires_at
                FROM platform_tokens
                WHERE business_id = :business_id
            ";
            
            $params = [':business_id' => $businessId];
            
            if ($platform !== null) {
                $sql .= " AND platform = :platform";
                $params[':platform'] = $platform;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            if ($platform !== null) {
                return $stmt->fetch();
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("PlatformService::getPlatformTokens Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save platform token for a business
     * 
     * @param int $businessId Business ID
     * @param string $platform Platform name
     * @param string $accessToken Access token
     * @param string $refreshToken Refresh token (optional)
     * @param string $expiresAt Expiration date (optional)
     * @return bool True if successful, false if not
     */
    public function savePlatformToken($businessId, $platform, $accessToken, $refreshToken = null, $expiresAt = null) {
        try {
            // Check if token already exists
            $existingToken = $this->getPlatformTokens($businessId, $platform);
            
            if ($existingToken) {
                // Update existing token
                $sql = "
                    UPDATE platform_tokens
                    SET access_token = :access_token,
                        refresh_token = :refresh_token,
                        expires_at = :expires_at
                    WHERE business_id = :business_id AND platform = :platform
                ";
            } else {
                // Create new token
                $sql = "
                    INSERT INTO platform_tokens (business_id, platform, access_token, refresh_token, expires_at)
                    VALUES (:business_id, :platform, :access_token, :refresh_token, :expires_at)
                ";
            }
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->bindParam(':platform', $platform, PDO::PARAM_STR);
            $stmt->bindParam(':access_token', $accessToken, PDO::PARAM_STR);
            $stmt->bindParam(':refresh_token', $refreshToken, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expiresAt, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PlatformService::savePlatformToken Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete platform token for a business
     * 
     * @param int $businessId Business ID
     * @param string $platform Platform name
     * @return bool True if successful, false if not
     */
    public function deletePlatformToken($businessId, $platform) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM platform_tokens
                WHERE business_id = :business_id AND platform = :platform
            ");
            
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->bindParam(':platform', $platform, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PlatformService::deletePlatformToken Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sync reviews from all platforms for a business
     * 
     * @param int $businessId Business ID
     * @return array Sync results
     */
    public function syncAllPlatforms($businessId) {
        $results = [
            'total_synced' => 0,
            'platforms' => []
        ];
        
        // Get all platform tokens for the business
        $platformTokens = $this->getPlatformTokens($businessId);
        
        foreach ($platformTokens as $token) {
            $platform = $token['platform'];
            $syncResult = $this->syncPlatform($businessId, $platform);
            
            $results['platforms'][$platform] = $syncResult;
            $results['total_synced'] += $syncResult['synced'];
        }
        
        return $results;
    }
    
    /**
     * Sync reviews from a specific platform for a business
     * 
     * @param int $businessId Business ID
     * @param string $platform Platform name
     * @return array Sync results
     */
    public function syncPlatform($businessId, $platform) {
        $result = [
            'platform' => $platform,
            'synced' => 0,
            'errors' => []
        ];
        
        try {
            // Get platform token
            $token = $this->getPlatformTokens($businessId, $platform);
            
            if (!$token) {
                $result['errors'][] = "No token found for platform: $platform";
                return $result;
            }
            
            // Fetch reviews based on platform
            $reviews = [];
            
            switch ($platform) {
                case 'google':
                    $reviews = $this->fetchGoogleReviews($token);
                    break;
                case 'yelp':
                    $reviews = $this->fetchYelpReviews($token);
                    break;
                case 'facebook':
                    $reviews = $this->fetchFacebookReviews($token);
                    break;
                default:
                    $result['errors'][] = "Unsupported platform: $platform";
                    return $result;
            }
            
            if (empty($reviews)) {
                $result['errors'][] = "No reviews fetched from $platform";
                return $result;
            }
            
            // Process and save reviews
            foreach ($reviews as $reviewData) {
                // Analyze sentiment
                $sentiment = $this->aiService->analyzeSentiment($reviewData['content']);
                
                // Prepare review data
                $review = [
                    'business_id' => $businessId,
                    'platform' => $platform,
                    'review_id' => $reviewData['review_id'],
                    'user_name' => $reviewData['user_name'],
                    'rating' => $reviewData['rating'],
                    'content' => $reviewData['content'],
                    'sentiment' => $sentiment,
                    'language' => $reviewData['language'] ?? 'en'
                ];
                
                // Save review
                $reviewId = $this->review->create($review);
                
                if ($reviewId) {
                    $result['synced']++;
                }
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("PlatformService::syncPlatform Error: " . $e->getMessage());
            $result['errors'][] = "Error syncing $platform: " . $e->getMessage();
            return $result;
        }
    }
    
    /**
     * Fetch reviews from Google My Business API
     * 
     * @param array $token Platform token
     * @return array Reviews data
     */
    private function fetchGoogleReviews($token) {
        // In a real implementation, this would use the Google My Business API
        // For this example, we'll return mock data
        
        return [
            [
                'review_id' => 'google_' . uniqid(),
                'user_name' => 'John Doe',
                'rating' => 4,
                'content' => 'Great service! The staff was very friendly and helpful.',
                'language' => 'en'
            ],
            [
                'review_id' => 'google_' . uniqid(),
                'user_name' => 'Jane Smith',
                'rating' => 3,
                'content' => 'Good experience overall, but the wait time was a bit long.',
                'language' => 'en'
            ],
            [
                'review_id' => 'google_' . uniqid(),
                'user_name' => 'Bob Johnson',
                'rating' => 5,
                'content' => 'Absolutely amazing! Will definitely come back again.',
                'language' => 'en'
            ]
        ];
    }
    
    /**
     * Fetch reviews from Yelp API
     * 
     * @param array $token Platform token
     * @return array Reviews data
     */
    private function fetchYelpReviews($token) {
        // In a real implementation, this would use the Yelp API
        // For this example, we'll return mock data
        
        return [
            [
                'review_id' => 'yelp_' . uniqid(),
                'user_name' => 'Alice Brown',
                'rating' => 2,
                'content' => 'Disappointing experience. The food was cold and service was slow.',
                'language' => 'en'
            ],
            [
                'review_id' => 'yelp_' . uniqid(),
                'user_name' => 'Charlie Davis',
                'rating' => 4,
                'content' => 'Really enjoyed my meal here. Great atmosphere and friendly staff.',
                'language' => 'en'
            ]
        ];
    }
    
    /**
     * Fetch reviews from Facebook Graph API
     * 
     * @param array $token Platform token
     * @return array Reviews data
     */
    private function fetchFacebookReviews($token) {
        // In a real implementation, this would use the Facebook Graph API
        // For this example, we'll return mock data
        
        return [
            [
                'review_id' => 'fb_' . uniqid(),
                'user_name' => 'David Wilson',
                'rating' => 5,
                'content' => 'Best place in town! Highly recommend to everyone.',
                'language' => 'en'
            ],
            [
                'review_id' => 'fb_' . uniqid(),
                'user_name' => 'Emma Taylor',
                'rating' => 3,
                'content' => 'Decent place. Nothing special but not bad either.',
                'language' => 'en'
            ]
        ];
    }
    
    /**
     * Post a response to a review on the platform
     * 
     * @param int $businessId Business ID
     * @param string $platform Platform name
     * @param string $reviewId External review ID
     * @param string $responseText Response text
     * @return bool True if successful, false if not
     */
    public function postResponse($businessId, $platform, $reviewId, $responseText) {
        try {
            // Get platform token
            $token = $this->getPlatformTokens($businessId, $platform);
            
            if (!$token) {
                error_log("PlatformService::postResponse No token found for platform: $platform");
                return false;
            }
            
            // Post response based on platform
            switch ($platform) {
                case 'google':
                    return $this->postGoogleResponse($token, $reviewId, $responseText);
                case 'yelp':
                    return $this->postYelpResponse($token, $reviewId, $responseText);
                case 'facebook':
                    return $this->postFacebookResponse($token, $reviewId, $responseText);
                default:
                    error_log("PlatformService::postResponse Unsupported platform: $platform");
                    return false;
            }
        } catch (Exception $e) {
            error_log("PlatformService::postResponse Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Post a response to a Google review
     * 
     * @param array $token Platform token
     * @param string $reviewId External review ID
     * @param string $responseText Response text
     * @return bool True if successful, false if not
     */
    private function postGoogleResponse($token, $reviewId, $responseText) {
        // In a real implementation, this would use the Google My Business API
        // For this example, we'll just return true
        
        return true;
    }
    
    /**
     * Post a response to a Yelp review
     * 
     * @param array $token Platform token
     * @param string $reviewId External review ID
     * @param string $responseText Response text
     * @return bool True if successful, false if not
     */
    private function postYelpResponse($token, $reviewId, $responseText) {
        // In a real implementation, this would use the Yelp API
        // For this example, we'll just return true
        
        return true;
    }
    
    /**
     * Post a response to a Facebook review
     * 
     * @param array $token Platform token
     * @param string $reviewId External review ID
     * @param string $responseText Response text
     * @return bool True if successful, false if not
     */
    private function postFacebookResponse($token, $reviewId, $responseText) {
        // In a real implementation, this would use the Facebook Graph API
        // For this example, we'll just return true
        
        return true;
    }
}
