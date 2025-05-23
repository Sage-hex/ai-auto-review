<?php
/**
 * Generate AI Response Endpoint
 * 
 * This endpoint generates an AI response for a review using Google Gemini.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Review.php';
require_once __DIR__ . '/../../../models/Response.php';
require_once __DIR__ . '/../../../models/User.php';
require_once __DIR__ . '/../../../models/Business.php';
require_once __DIR__ . '/../../../services/AIService.php';
require_once __DIR__ . '/../../../utils/auth.php';
require_once __DIR__ . '/../../../utils/logger.php';

// Initialize response array
$response = [
    'status' => 'error',
    'message' => '',
    'data' => null
];

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Authenticate user
    $user = authenticate();
    if (!$user) {
        throw new Exception('Unauthorized', 401);
    }

    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate required fields
    if (!isset($data['review_id']) || empty($data['review_id'])) {
        throw new Exception('Review ID is required', 400);
    }

    // Initialize models and services
    $reviewModel = new Review();
    $responseModel = new Response();
    $businessModel = new Business();
    $aiService = new AIService();

    // Get review details
    $review = $reviewModel->getById($data['review_id']);
    if (!$review) {
        throw new Exception('Review not found', 404);
    }

    // Check if user has access to this review (belongs to their business)
    if ($review['business_id'] != $user['business_id']) {
        throw new Exception('You do not have permission to access this review', 403);
    }

    // Get business details
    $business = $businessModel->getById($user['business_id']);
    if (!$business) {
        throw new Exception('Business not found', 404);
    }

    // Prepare data for AI response generation
    $businessName = $data['business_name'] ?? $business['name'];
    $businessType = $data['business_type'] ?? '';
    $tone = $data['tone'] ?? 'professional';

    // Generate AI response
    $generatedResponse = $aiService->generateResponse(
        $review,
        $businessName,
        $businessType,
        $tone
    );

    if (!$generatedResponse) {
        throw new Exception('Failed to generate AI response', 500);
    }

    // Check if we already have a response for this review
    $existingResponse = $responseModel->getByReviewId($review['id']);
    
    // Create or update response
    $responseData = [
        'business_id' => $user['business_id'],
        'review_id' => $review['id'],
        'response_text' => $generatedResponse,
        'status' => 'pending'
    ];

    if ($existingResponse) {
        // Update existing response
        $result = $responseModel->update($existingResponse['id'], $user['business_id'], $generatedResponse);
        $responseId = $existingResponse['id'];
    } else {
        // Create new response
        $responseId = $responseModel->create($user['business_id'], $review['id'], $generatedResponse);
        $result = $responseId > 0;
    }

    if (!$result) {
        throw new Exception('Failed to save generated response', 500);
    }

    // Get the saved response
    $savedResponse = $responseModel->getById($responseId);

    // Return success response
    $response['status'] = 'success';
    $response['message'] = 'Response generated successfully';
    $response['data'] = $savedResponse;

} catch (Exception $e) {
    // Log error
    error_log('AI Response Generation Error: ' . $e->getMessage());

    // Set error response
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() && $e->getCode() >= 400 ? $e->getCode() : 500);
}

// Return JSON response
echo json_encode($response);
