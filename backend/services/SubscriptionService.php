<?php
/**
 * SubscriptionService Class
 * 
 * Handles subscription management, plan changes, and feature access control
 */
class SubscriptionService {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all available subscription plans
     *
     * @return array Array of subscription plans
     */
    public function getPlans() {
        $plans = [
            [
                'id' => 'free',
                'name' => 'Free Plan',
                'price' => 0,
                'features' => [
                    'users' => 2,
                    'platforms' => ['google'],
                    'responses_per_month' => 50,
                    'analytics' => 'basic',
                    'support' => 'email'
                ]
            ],
            [
                'id' => 'basic',
                'name' => 'Basic Plan',
                'price' => 29,
                'features' => [
                    'users' => 5,
                    'platforms' => ['google', 'yelp'],
                    'responses_per_month' => 200,
                    'analytics' => 'advanced',
                    'support' => 'email_chat'
                ]
            ],
            [
                'id' => 'pro',
                'name' => 'Pro Plan',
                'price' => 79,
                'features' => [
                    'users' => 15,
                    'platforms' => ['google', 'yelp', 'facebook'],
                    'responses_per_month' => 'unlimited',
                    'analytics' => 'advanced_custom',
                    'support' => 'priority'
                ]
            ]
        ];
        
        return $plans;
    }
    
    /**
     * Get a specific subscription plan by ID
     *
     * @param string $planId Plan ID to retrieve
     * @return array|null Plan details or null if not found
     */
    public function getPlan($planId) {
        $plans = $this->getPlans();
        
        foreach ($plans as $plan) {
            if ($plan['id'] === $planId) {
                return $plan;
            }
        }
        
        return null;
    }
    
    /**
     * Change a business's subscription plan
     *
     * @param int $businessId Business ID
     * @param string $planId New plan ID
     * @return bool True if plan was changed successfully, false otherwise
     */
    public function changePlan($businessId, $planId) {
        // Validate plan exists
        $plan = $this->getPlan($planId);
        if (!$plan) {
            return false;
        }
        
        try {
            $query = "UPDATE businesses SET 
                      subscription_plan = :plan,
                      updated_at = NOW()
                      WHERE id = :business_id";
            
            $params = [
                ':plan' => $planId,
                ':business_id' => $businessId
            ];
            
            $this->db->query($query, $params);
            
            // Log the plan change
            Logger::info("Business #$businessId changed subscription to $planId plan");
            
            return true;
        } catch (Exception $e) {
            Logger::exception($e, ['business_id' => $businessId, 'plan_id' => $planId]);
            return false;
        }
    }
    
    /**
     * Start a free trial for a business
     *
     * @param int $businessId Business ID
     * @param string $planId Plan ID for the trial
     * @param int $trialDays Number of trial days (default: 14)
     * @return bool True if trial was started successfully, false otherwise
     */
    public function startTrial($businessId, $planId, $trialDays = 14) {
        // Validate plan exists
        $plan = $this->getPlan($planId);
        if (!$plan) {
            return false;
        }
        
        try {
            $trialEndDate = date('Y-m-d H:i:s', strtotime("+$trialDays days"));
            
            $query = "UPDATE businesses SET 
                      subscription_plan = :plan,
                      subscription_trial_ends = :trial_end,
                      updated_at = NOW()
                      WHERE id = :business_id";
            
            $params = [
                ':plan' => $planId,
                ':trial_end' => $trialEndDate,
                ':business_id' => $businessId
            ];
            
            $this->db->query($query, $params);
            
            // Log the trial start
            Logger::info("Business #$businessId started $trialDays-day trial of $planId plan");
            
            return true;
        } catch (Exception $e) {
            Logger::exception($e, [
                'business_id' => $businessId, 
                'plan_id' => $planId,
                'trial_days' => $trialDays
            ]);
            return false;
        }
    }
    
    /**
     * Check if a business has access to a specific feature
     *
     * @param int $businessId Business ID
     * @param string $feature Feature to check
     * @param mixed $value Value to check against (optional)
     * @return bool True if business has access to the feature, false otherwise
     */
    public function hasFeatureAccess($businessId, $feature, $value = null) {
        try {
            // Get business subscription plan
            $query = "SELECT subscription_plan FROM businesses WHERE id = :business_id";
            $params = [':business_id' => $businessId];
            $result = $this->db->query($query, $params);
            
            if (!$result) {
                return false;
            }
            
            $planId = $result[0]['subscription_plan'];
            $plan = $this->getPlan($planId);
            
            if (!$plan) {
                return false;
            }
            
            // Check if feature exists in plan
            if (!isset($plan['features'][$feature])) {
                return false;
            }
            
            // If no specific value to check, just return true if feature exists
            if ($value === null) {
                return true;
            }
            
            $featureValue = $plan['features'][$feature];
            
            // Handle different feature types
            switch ($feature) {
                case 'users':
                    // Check if user count is within limit
                    return $value <= $featureValue;
                
                case 'platforms':
                    // Check if platform is supported
                    return in_array($value, $featureValue);
                
                case 'responses_per_month':
                    // Check if response count is within limit or unlimited
                    return $featureValue === 'unlimited' || $value <= $featureValue;
                
                default:
                    // For other features, check exact match
                    return $featureValue === $value;
            }
        } catch (Exception $e) {
            Logger::exception($e, [
                'business_id' => $businessId, 
                'feature' => $feature,
                'value' => $value
            ]);
            return false;
        }
    }
    
    /**
     * Get the number of AI responses used this month
     *
     * @param int $businessId Business ID
     * @return int Number of responses used
     */
    public function getResponsesUsedThisMonth($businessId) {
        try {
            $startOfMonth = date('Y-m-01 00:00:00');
            $endOfMonth = date('Y-m-t 23:59:59');
            
            $query = "SELECT COUNT(*) as count FROM responses 
                      WHERE business_id = :business_id 
                      AND is_ai_generated = 1
                      AND created_at BETWEEN :start_date AND :end_date";
            
            $params = [
                ':business_id' => $businessId,
                ':start_date' => $startOfMonth,
                ':end_date' => $endOfMonth
            ];
            
            $result = $this->db->query($query, $params);
            
            return $result ? (int)$result[0]['count'] : 0;
        } catch (Exception $e) {
            Logger::exception($e, ['business_id' => $businessId]);
            return 0;
        }
    }
    
    /**
     * Check if a business has reached their monthly AI response limit
     *
     * @param int $businessId Business ID
     * @return bool True if limit reached, false otherwise
     */
    public function hasReachedResponseLimit($businessId) {
        try {
            // Get business subscription plan
            $query = "SELECT subscription_plan FROM businesses WHERE id = :business_id";
            $params = [':business_id' => $businessId];
            $result = $this->db->query($query, $params);
            
            if (!$result) {
                return true; // Assume limit reached if can't determine plan
            }
            
            $planId = $result[0]['subscription_plan'];
            $plan = $this->getPlan($planId);
            
            if (!$plan) {
                return true;
            }
            
            // If unlimited responses, never reach limit
            if ($plan['features']['responses_per_month'] === 'unlimited') {
                return false;
            }
            
            // Get responses used this month
            $responsesUsed = $this->getResponsesUsedThisMonth($businessId);
            
            // Check if limit reached
            return $responsesUsed >= $plan['features']['responses_per_month'];
        } catch (Exception $e) {
            Logger::exception($e, ['business_id' => $businessId]);
            return true; // Assume limit reached on error
        }
    }
    
    /**
     * Get subscription usage statistics for a business
     *
     * @param int $businessId Business ID
     * @return array Subscription usage statistics
     */
    public function getUsageStats($businessId) {
        try {
            // Get business subscription plan
            $query = "SELECT subscription_plan FROM businesses WHERE id = :business_id";
            $params = [':business_id' => $businessId];
            $result = $this->db->query($query, $params);
            
            if (!$result) {
                return [];
            }
            
            $planId = $result[0]['subscription_plan'];
            $plan = $this->getPlan($planId);
            
            if (!$plan) {
                return [];
            }
            
            // Get user count
            $query = "SELECT COUNT(*) as count FROM users WHERE business_id = :business_id";
            $params = [':business_id' => $businessId];
            $userResult = $this->db->query($query, $params);
            $userCount = $userResult ? (int)$userResult[0]['count'] : 0;
            
            // Get responses used this month
            $responsesUsed = $this->getResponsesUsedThisMonth($businessId);
            
            // Get connected platforms
            $query = "SELECT platform FROM platform_connections WHERE business_id = :business_id";
            $params = [':business_id' => $businessId];
            $platformResults = $this->db->query($query, $params);
            
            $connectedPlatforms = [];
            if ($platformResults) {
                foreach ($platformResults as $row) {
                    $connectedPlatforms[] = $row['platform'];
                }
            }
            
            return [
                'plan' => $planId,
                'plan_name' => $plan['name'],
                'users' => [
                    'used' => $userCount,
                    'limit' => $plan['features']['users'],
                    'percentage' => $plan['features']['users'] > 0 ? 
                        round(($userCount / $plan['features']['users']) * 100) : 0
                ],
                'responses' => [
                    'used' => $responsesUsed,
                    'limit' => $plan['features']['responses_per_month'],
                    'percentage' => $plan['features']['responses_per_month'] !== 'unlimited' ? 
                        round(($responsesUsed / $plan['features']['responses_per_month']) * 100) : 0,
                    'unlimited' => $plan['features']['responses_per_month'] === 'unlimited'
                ],
                'platforms' => [
                    'available' => $plan['features']['platforms'],
                    'connected' => $connectedPlatforms
                ]
            ];
        } catch (Exception $e) {
            Logger::exception($e, ['business_id' => $businessId]);
            return [];
        }
    }
}
