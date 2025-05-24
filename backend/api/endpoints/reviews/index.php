<?php
// Include CORS handler
require_once __DIR__ . '/cors.php';

/**
 * Reviews Endpoints
 * 
 * These endpoints handle review-related operations.
 */

// Include CORS headers utility
require_once __DIR__ . '/../../common/cors.php';

// Include bootstrap for other utilities
require_once __DIR__ . '/../../common/bootstrap.php';

// Instead of loading models and services, we'll use mock data for development
// Simplified mock response functions
function sendSuccessResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
    exit;
}

function sendErrorResponse($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

function sendNotFoundResponse($message) {
    sendErrorResponse($message, 404);
}

// Route based on request method and path
if ($method === 'GET' && count($pathParts) === 1) {
    // GET /reviews - List reviews for the business
    
    // Parse query parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    
    // Parse filters
    $platform = isset($_GET['platform']) ? $_GET['platform'] : '';
    $rating = isset($_GET['rating']) && is_numeric($_GET['rating']) ? intval($_GET['rating']) : 0;
    $sentiment = isset($_GET['sentiment']) ? $_GET['sentiment'] : '';
    
    // Create mock reviews data
    $mockReviews = [
        [
            'id' => 1,
            'business_id' => 1,
            'platform' => 'google',
            'review_id' => 'g_1234',
            'rating' => 4,
            'content' => 'Great service! Would definitely recommend.',
            'reviewer_name' => 'John Doe',
            'sentiment' => 'positive',
            'date_posted' => '2025-05-15',
            'last_updated' => '2025-05-15',
            'has_response' => false
        ],
        [
            'id' => 2,
            'business_id' => 1,
            'platform' => 'yelp',
            'review_id' => 'y_5678',
            'rating' => 3,
            'content' => 'Service was okay but could be better.',
            'reviewer_name' => 'Jane Smith',
            'sentiment' => 'neutral',
            'date_posted' => '2025-05-10',
            'last_updated' => '2025-05-10',
            'has_response' => true
        ],
        [
            'id' => 3,
            'business_id' => 1,
            'platform' => 'facebook',
            'review_id' => 'f_9012',
            'rating' => 2,
            'content' => 'Very disappointed with the product quality.',
            'reviewer_name' => 'Mike Johnson',
            'sentiment' => 'negative',
            'date_posted' => '2025-05-05',
            'last_updated' => '2025-05-05',
            'has_response' => false
        ]
    ];
    
    // Apply filters if any
    $filteredReviews = array_filter($mockReviews, function($review) use ($platform, $rating, $sentiment) {
        $platformMatch = empty($platform) || $review['platform'] === $platform;
        $ratingMatch = $rating === 0 || $review['rating'] === $rating;
        $sentimentMatch = empty($sentiment) || $review['sentiment'] === $sentiment;
        
        return $platformMatch && $ratingMatch && $sentimentMatch;
    });
    
    // Prepare pagination data
    $total = count($filteredReviews);
    $totalPages = ceil($total / $limit);
    
    // Create response structure
    $result = [
        'reviews' => array_values($filteredReviews),
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
            'per_page' => $limit
        ]
    ];
    
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
    
    // Create mock stats data
    $stats = [
        'total_reviews' => 10,
        'average_rating' => 3.7,
        'ratings_breakdown' => [
            5 => 3,
            4 => 4,
            3 => 2,
            2 => 1,
            1 => 0
        ],
        'sentiment_breakdown' => [
            'positive' => 7,
            'neutral' => 2,
            'negative' => 1
        ],
        'platform_breakdown' => [
            'google' => 5,
            'yelp' => 3,
            'facebook' => 2
        ],
        'recent_trend' => [
            ['date' => '2025-05-01', 'count' => 2, 'average_rating' => 3.5],
            ['date' => '2025-05-08', 'count' => 3, 'average_rating' => 4.0],
            ['date' => '2025-05-15', 'count' => 5, 'average_rating' => 3.8]
        ]
    ];
    
    sendSuccessResponse($stats);
} else {
    sendNotFoundResponse('Endpoint not found');
}
