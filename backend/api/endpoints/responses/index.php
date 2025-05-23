<?php
/**
 * Responses Endpoints
 * 
 * These endpoints handle response-related operations.
 */

// Include CORS headers utility
require_once __DIR__ . '/../../common/cors.php';

// Include bootstrap for other utilities
require_once __DIR__ . '/../../common/bootstrap.php';

// Define response utility functions locally
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

function sendForbiddenResponse($message) {
    sendErrorResponse($message, 403);
}

// Parse request method and path
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim(substr($uri, strpos($uri, '/api/') + 5), '/'));

// Default user data for development
$userId = 1;
$businessId = 1;

// Get raw input data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Route based on request method and path
if ($method === 'GET' && count($pathParts) === 2) {
    // GET /responses/{id} - Get response details
    $responseId = intval($pathParts[1]);
    
    // Get response
    $response = $responseModel->getById($responseId, $businessId);
    
    if (!$response) {
        sendNotFoundResponse('Response not found');
    }
    
    sendSuccessResponse($response);
} elseif ($method === 'PUT' && count($pathParts) === 2) {
    // PUT /responses/{id} - Update response
    $responseId = intval($pathParts[1]);
    
    // Get response
    $response = $responseModel->getById($responseId, $businessId);
    
    if (!$response) {
        sendNotFoundResponse('Response not found');
    }
    
    // Check if user has permission to edit responses
    if (!in_array($userData['role'], ['admin', 'manager', 'support'])) {
        sendForbiddenResponse('You do not have permission to edit responses');
    }
    
    // Validate input
    if (empty($data['response_text'])) {
        sendValidationErrorResponse(['response_text' => 'Response text is required']);
    }
    
    // Update response
    $success = $responseModel->update($responseId, $businessId, $data['response_text']);
    
    if (!$success) {
        sendErrorResponse('Failed to update response', 500);
    }
    
    // Get updated response
    $response = $responseModel->getById($responseId, $businessId);
    
    sendSuccessResponse($response, 200, 'Response updated successfully');
} elseif ($method === 'POST' && count($pathParts) === 3 && $pathParts[2] === 'approve') {
    // POST /responses/{id}/approve - Approve response
    $responseId = intval($pathParts[1]);
    
    // Get response
    $response = $responseModel->getById($responseId, $businessId);
    
    if (!$response) {
        sendNotFoundResponse('Response not found');
    }
    
    // Check if user has permission to approve responses
    if (!in_array($userData['role'], ['admin', 'manager'])) {
        sendForbiddenResponse('You do not have permission to approve responses');
    }
    
    // Approve response
    $success = $responseModel->approve($responseId, $businessId, $userId);
    
    if (!$success) {
        sendErrorResponse('Failed to approve response', 500);
    }
    
    // Get updated response
    $response = $responseModel->getById($responseId, $businessId);
    
    sendSuccessResponse($response, 200, 'Response approved successfully');
} elseif ($method === 'POST' && count($pathParts) === 3 && $pathParts[2] === 'post') {
    // POST /responses/{id}/post - Post response to platform
    $responseId = intval($pathParts[1]);
    
    // Get response
    $response = $responseModel->getById($responseId, $businessId);
    
    if (!$response) {
        sendNotFoundResponse('Response not found');
    }
    
    // Check if response is approved
    if ($response['status'] !== 'approved') {
        sendErrorResponse('Response must be approved before posting', 400);
    }
    
    // Check if user has permission to post responses
    if (!in_array($userData['role'], ['admin', 'manager'])) {
        sendForbiddenResponse('You do not have permission to post responses');
    }
    
    // Get review
    $review = $reviewModel->getById($response['review_id'], $businessId);
    
    if (!$review) {
        sendErrorResponse('Review not found', 500);
    }
    
    // Post response to platform
    $success = $platformService->postResponse(
        $businessId,
        $review['platform'],
        $review['review_id'],
        $response['response_text']
    );
    
    if (!$success) {
        sendErrorResponse('Failed to post response to platform', 500);
    }
    
    // Mark response as posted
    $success = $responseModel->markAsPosted($responseId, $businessId);
    
    if (!$success) {
        sendErrorResponse('Failed to mark response as posted', 500);
    }
    
    // Get updated response
    $response = $responseModel->getById($responseId, $businessId);
    
    sendSuccessResponse($response, 200, 'Response posted successfully');
} elseif ($method === 'GET' && count($pathParts) === 2 && $pathParts[1] === 'pending') {
    // GET /responses/pending - Get pending responses
    
    // Create mock pending responses data
    $pendingResponses = [
        [
            'id' => 101,
            'business_id' => 1,
            'review_id' => 1,
            'response_text' => 'Thank you for your positive feedback! We strive to provide excellent service and are glad you had a great experience.',
            'status' => 'pending',
            'created_by' => 0,  // 0 indicates AI-generated
            'approved_by' => null,
            'created_at' => '2025-05-20',
            'updated_at' => '2025-05-20',
            'posted_at' => null,
            'review' => [
                'id' => 1,
                'platform' => 'google',
                'rating' => 4,
                'content' => 'Great service! Would definitely recommend.',
                'reviewer_name' => 'John Doe',
                'date_posted' => '2025-05-15'
            ]
        ],
        [
            'id' => 103,
            'business_id' => 1,
            'review_id' => 3,
            'response_text' => 'We are sorry to hear about your experience. We take product quality very seriously and would like to make this right. Please contact our customer service team at support@example.com.',
            'status' => 'pending',
            'created_by' => 0,
            'approved_by' => null,
            'created_at' => '2025-05-21',
            'updated_at' => '2025-05-21',
            'posted_at' => null,
            'review' => [
                'id' => 3,
                'platform' => 'facebook',
                'rating' => 2,
                'content' => 'Very disappointed with the product quality.',
                'reviewer_name' => 'Mike Johnson',
                'date_posted' => '2025-05-05'
            ]
        ]
    ];
    
    sendSuccessResponse($pendingResponses);
} else {
    sendNotFoundResponse('Endpoint not found');
}
