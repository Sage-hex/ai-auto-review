<?php
/**
 * Sync Reviews Cron Job
 * 
 * This script is meant to be run as a cron job to automatically sync reviews from platforms.
 * Recommended schedule: Hourly
 * 
 * Example cron entry:
 * 0 * * * * php /path/to/AiAutoReview/backend/cron/sync_reviews.php
 */

// Load required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/PlatformService.php';
require_once __DIR__ . '/../models/Business.php';

// Initialize services and models
$platformService = new PlatformService();
$businessModel = new Business();

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
    'reviews_synced' => 0,
    'errors' => []
];

// Process each business
foreach ($businesses as $business) {
    try {
        echo "Processing business: {$business['name']} (ID: {$business['id']})\n";
        
        // Sync reviews for this business
        $syncResult = $platformService->syncAllPlatforms($business['id']);
        
        // Update results
        $results['businesses_processed']++;
        $results['reviews_synced'] += $syncResult['total_synced'];
        
        // Log any errors
        foreach ($syncResult['platforms'] as $platform => $platformResult) {
            if (!empty($platformResult['errors'])) {
                foreach ($platformResult['errors'] as $error) {
                    $results['errors'][] = "Business {$business['id']} ({$business['name']}), Platform {$platform}: {$error}";
                }
            }
        }
        
        echo "Synced {$syncResult['total_synced']} reviews for business {$business['id']}\n";
    } catch (Exception $e) {
        $results['errors'][] = "Business {$business['id']} ({$business['name']}): " . $e->getMessage();
        echo "Error processing business {$business['id']}: " . $e->getMessage() . "\n";
    }
}

// Output results
echo "\nSync completed at " . date('Y-m-d H:i:s') . "\n";
echo "Processed {$results['businesses_processed']} of {$results['total_businesses']} businesses\n";
echo "Synced {$results['reviews_synced']} reviews\n";

if (!empty($results['errors'])) {
    echo "Errors encountered: " . count($results['errors']) . "\n";
    foreach ($results['errors'] as $error) {
        echo "- {$error}\n";
    }
}

// Log results to database
$logSql = "
    INSERT INTO logs (user_id, action, description)
    VALUES (NULL, 'cron_sync_reviews', :description)
";

$logStmt = $db->prepare($logSql);
$logStmt->bindParam(':description', json_encode($results), PDO::PARAM_STR);
$logStmt->execute();
