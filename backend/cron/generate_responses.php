<?php
/**
 * Generate AI Responses Cron Job
 * 
 * This script is meant to be run as a cron job to automatically generate AI responses for new reviews.
 * Recommended schedule: Every 2 hours
 * 
 * Example cron entry:
 * 0 */2 * * * php /path/to/AiAutoReview/backend/cron/generate_responses.php
 */

// Load required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/AIService.php';
require_once __DIR__ . '/../models/Business.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Response.php';

// Initialize services and models
$aiService = new AIService();
$businessModel = new Business();
$reviewModel = new Review();
$responseModel = new Response();

// Get all active businesses
$db = getDbConnection();
$stmt = $db->prepare("
    SELECT id, name, subscription_plan, status
    FROM businesses
    WHERE status IN ('active', 'trialing')
");
$stmt->execute();
$businesses = $stmt->fetchAll();

// Track results
$results = [
    'total_businesses' => count($businesses),
    'businesses_processed' => 0,
    'responses_generated' => 0,
    'errors' => []
];

// Process each business
foreach ($businesses as $business) {
    try {
        echo "Processing business: {$business['name']} (ID: {$business['id']})\n";
        
        // Check if business has AI response feature
        if (!$businessModel->hasFeatureAccess($business['id'], 'ai_responses')) {
            echo "Business {$business['id']} does not have access to AI responses feature. Skipping.\n";
            continue;
        }
        
        // Get reviews without responses
        $stmt = $db->prepare("
            SELECT r.*
            FROM reviews r
            LEFT JOIN (
                SELECT review_id, COUNT(*) as response_count
                FROM responses
                GROUP BY review_id
            ) rc ON r.id = rc.review_id
            WHERE r.business_id = :business_id
            AND (rc.response_count IS NULL OR rc.response_count = 0)
            ORDER BY r.created_at DESC
            LIMIT 20
        ");
        
        $stmt->bindParam(':business_id', $business['id'], PDO::PARAM_INT);
        $stmt->execute();
        $reviews = $stmt->fetchAll();
        
        echo "Found " . count($reviews) . " reviews without responses for business {$business['id']}\n";
        
        // Generate responses for each review
        foreach ($reviews as $review) {
            try {
                // Generate AI response
                $responseText = $aiService->generateResponse(
                    $review,
                    $business['name'],
                    '', // Business type (empty for now)
                    '' // Tone (empty for auto-detection based on rating)
                );
                
                if (!$responseText) {
                    $results['errors'][] = "Business {$business['id']}, Review {$review['id']}: Failed to generate AI response";
                    continue;
                }
                
                // Save response
                $responseId = $responseModel->create($business['id'], $review['id'], $responseText);
                
                if (!$responseId) {
                    $results['errors'][] = "Business {$business['id']}, Review {$review['id']}: Failed to save AI response";
                    continue;
                }
                
                $results['responses_generated']++;
                echo "Generated response for review {$review['id']}\n";
            } catch (Exception $e) {
                $results['errors'][] = "Business {$business['id']}, Review {$review['id']}: " . $e->getMessage();
                echo "Error generating response for review {$review['id']}: " . $e->getMessage() . "\n";
            }
        }
        
        $results['businesses_processed']++;
    } catch (Exception $e) {
        $results['errors'][] = "Business {$business['id']} ({$business['name']}): " . $e->getMessage();
        echo "Error processing business {$business['id']}: " . $e->getMessage() . "\n";
    }
}

// Output results
echo "\nResponse generation completed at " . date('Y-m-d H:i:s') . "\n";
echo "Processed {$results['businesses_processed']} of {$results['total_businesses']} businesses\n";
echo "Generated {$results['responses_generated']} responses\n";

if (!empty($results['errors'])) {
    echo "Errors encountered: " . count($results['errors']) . "\n";
    foreach ($results['errors'] as $error) {
        echo "- {$error}\n";
    }
}

// Log results to database
$logSql = "
    INSERT INTO logs (user_id, action, description)
    VALUES (NULL, 'cron_generate_responses', :description)
";

$logStmt = $db->prepare($logSql);
$logStmt->bindParam(':description', json_encode($results), PDO::PARAM_STR);
$logStmt->execute();
