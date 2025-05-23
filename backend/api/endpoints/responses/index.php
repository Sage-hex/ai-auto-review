<?php
/**
 * Responses Endpoints
 * 
 * These endpoints handle response-related operations.
 */

// Load required models and services
require_once __DIR__ . '/../../../models/Response.php';
require_once __DIR__ . '/../../../models/Review.php';
require_once __DIR__ . '/../../../services/PlatformService.php';

$responseModel = new Response();
$reviewModel = new Review();
$platformService = new PlatformService();

// Get user data from authenticated user
$businessId = $userData['business_id'];
$userId = $userData['user_id'];

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
    
    // Get pending responses
    $responses = $responseModel->getPendingResponses($businessId);
    
    sendSuccessResponse($responses);
} else {
    sendNotFoundResponse('Endpoint not found');
}
