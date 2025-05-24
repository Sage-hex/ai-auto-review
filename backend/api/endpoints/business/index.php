<?php
// Include CORS handler
require_once __DIR__ . '/cors.php';

/**
 * Business Endpoints
 * 
 * These endpoints handle business-related operations.
 */

// Load required models
require_once __DIR__ . '/../../../models/Business.php';

$businessModel = new Business();

// Get business ID from authenticated user
$businessId = $userData['business_id'];

// Route based on request method and path
if ($method === 'GET' && count($pathParts) === 1) {
    // GET /business - Get business profile
    $business = $businessModel->getById($businessId);
    
    if (!$business) {
        sendNotFoundResponse('Business not found');
    }
    
    sendSuccessResponse([
        'id' => $business['id'],
        'name' => $business['name'],
        'subscription_plan' => $business['subscription_plan'],
        'status' => $business['status'],
        'trial_ends_at' => $business['trial_ends_at'],
        'created_at' => $business['created_at']
    ]);
} elseif ($method === 'PUT' && count($pathParts) === 1) {
    // PUT /business - Update business profile
    
    // Check if user is admin
    if ($userData['role'] !== 'admin') {
        sendForbiddenResponse('Only admins can update business profile');
    }
    
    // Validate input
    $allowedFields = ['name'];
    $updateData = [];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateData[$field] = $data[$field];
        }
    }
    
    if (empty($updateData)) {
        sendValidationErrorResponse(['name' => 'No valid fields to update']);
    }
    
    // Update business
    $success = $businessModel->update($businessId, $updateData);
    
    if (!$success) {
        sendErrorResponse('Failed to update business profile', 500);
    }
    
    // Get updated business
    $business = $businessModel->getById($businessId);
    
    sendSuccessResponse([
        'id' => $business['id'],
        'name' => $business['name'],
        'subscription_plan' => $business['subscription_plan'],
        'status' => $business['status'],
        'trial_ends_at' => $business['trial_ends_at'],
        'created_at' => $business['created_at']
    ], 200, 'Business profile updated successfully');
} else {
    sendNotFoundResponse('Endpoint not found');
}
