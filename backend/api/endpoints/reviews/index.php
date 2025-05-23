<?php
/**
 * Reviews Endpoints
 * 
 * These endpoints handle review-related operations.
 */

// Load required models and services
require_once __DIR__ . '/../../../models/Review.php';
require_once __DIR__ . '/../../../models/Response.php';
require_once __DIR__ . '/../../../services/PlatformService.php';
require_once __DIR__ . '/../../../services/AIService.php';

$reviewModel = new Review();
$responseModel = new Response();
$platformService = new PlatformService();
$aiService = new AIService();

// Get business ID from authenticated user
$businessId = $userData['business_id'];

// Route based on request method and path
if ($method === 'GET' && count($pathParts) === 1) {
    // GET /reviews - List reviews for the business
    
    // Parse query parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    
    // Parse filters
    $filters = [];
    
    if (isset($_GET['platform']) && !empty($_GET['platform'])) {
        $filters['platform'] = $_GET['platform'];
    }
    
    if (isset($_GET['rating']) && is_numeric($_GET['rating'])) {
        $filters['rating'] = intval($_GET['rating']);
    }
    
    if (isset($_GET['sentiment']) && !empty($_GET['sentiment'])) {
        $filters['sentiment'] = $_GET['sentiment'];
    }
    
    // Get reviews
    $result = $reviewModel->getByBusinessId($businessId, $filters, $page, $limit);
    
    sendSuccessResponse($result);
} elseif ($method === 'GET' && count($pathParts) === 2) {
    // GET /reviews/{id} - Get review details
    $reviewId = intval($pathParts[1]);
    
    // Get review
    $review = $reviewModel->getById($reviewId, $businessId);
    
    if (!$review) {
        sendNotFoundResponse('Review not found');
    }
    
    // Get responses for this review
    $responses = $responseModel->getByReviewId($reviewId, $businessId);
    
    // Combine review and responses
    $result = [
        'review' => $review,
        'responses' => $responses
    ];
    
    sendSuccessResponse($result);
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'sync') {
    // POST /reviews/sync - Sync reviews from platforms
    
    // Check if user has permission to sync reviews
    if (!in_array($userData['role'], ['admin', 'manager'])) {
        sendForbiddenResponse('You do not have permission to sync reviews');
    }
    
    // Sync reviews from all platforms
    $result = $platformService->syncAllPlatforms($businessId);
    
    sendSuccessResponse($result, 200, 'Reviews synced successfully');
} elseif ($method === 'POST' && count($pathParts) === 3 && $pathParts[2] === 'generate') {
    // POST /reviews/{id}/generate - Generate AI response for a review
    $reviewId = intval($pathParts[1]);
    
    // Get review
    $review = $reviewModel->getById($reviewId, $businessId);
    
    if (!$review) {
        sendNotFoundResponse('Review not found');
    }
    
    // Validate input
    $businessName = isset($data['business_name']) ? $data['business_name'] : '';
    $businessType = isset($data['business_type']) ? $data['business_type'] : '';
    $tone = isset($data['tone']) ? $data['tone'] : '';
    
    // Generate AI response
    $responseText = $aiService->generateResponse($review, $businessName, $businessType, $tone);
    
    if (!$responseText) {
        sendErrorResponse('Failed to generate AI response', 500);
    }
    
    // Save response
    $responseId = $responseModel->create($businessId, $reviewId, $responseText);
    
    if (!$responseId) {
        sendErrorResponse('Failed to save response', 500);
    }
    
    // Get created response
    $response = $responseModel->getById($responseId);
    
    sendSuccessResponse($response, 201, 'AI response generated successfully');
} elseif ($method === 'GET' && count($pathParts) === 2 && $pathParts[1] === 'stats') {
    // GET /reviews/stats - Get review statistics
    
    // Get review statistics
    $stats = $reviewModel->getStatistics($businessId);
    
    sendSuccessResponse($stats);
} else {
    sendNotFoundResponse('Endpoint not found');
}
