<?php
// Include CORS handler
require_once __DIR__ . '/cors.php';

/**
 * Subscription Endpoints
 * 
 * These endpoints handle subscription-related operations.
 */

// Load required models
require_once __DIR__ . '/../../../models/Business.php';

$businessModel = new Business();

// Get business ID from authenticated user
$businessId = $userData['business_id'];

// Route based on request method and path
if ($method === 'GET' && count($pathParts) === 1) {
    // GET /subscription - Get subscription details
    
    // Get business
    $business = $businessModel->getById($businessId);
    
    if (!$business) {
        sendNotFoundResponse('Business not found');
    }
    
    // Get plan details
    $plan = PLANS[$business['subscription_plan']] ?? null;
    
    if (!$plan) {
        sendErrorResponse('Invalid subscription plan', 500);
    }
    
    // Combine business and plan details
    $result = [
        'business_id' => $business['id'],
        'status' => $business['status'],
        'plan' => $business['subscription_plan'],
        'plan_details' => $plan,
        'trial_ends_at' => $business['trial_ends_at']
    ];
    
    sendSuccessResponse($result);
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'upgrade') {
    // POST /subscription/upgrade - Upgrade subscription plan
    
    // Check if user is admin
    if ($userData['role'] !== 'admin') {
        sendForbiddenResponse('Only admins can upgrade subscription plans');
    }
    
    // Validate input
    if (empty($data['plan'])) {
        sendValidationErrorResponse(['plan' => 'Plan is required']);
    }
    
    // Validate plan
    if (!isset(PLANS[$data['plan']])) {
        sendValidationErrorResponse(['plan' => 'Invalid plan']);
    }
    
    // Get business
    $business = $businessModel->getById($businessId);
    
    if (!$business) {
        sendNotFoundResponse('Business not found');
    }
    
    // Check if already on the same plan
    if ($business['subscription_plan'] === $data['plan']) {
        sendErrorResponse('Business is already on this plan', 400);
    }
    
    // In a real implementation, this would integrate with a payment gateway
    // For this example, we'll just update the plan directly
    
    $updateData = [
        'subscription_plan' => $data['plan'],
        'status' => 'active'
    ];
    
    // Update business
    $success = $businessModel->update($businessId, $updateData);
    
    if (!$success) {
        sendErrorResponse('Failed to upgrade subscription plan', 500);
    }
    
    // Get updated business
    $business = $businessModel->getById($businessId);
    
    // Get plan details
    $plan = PLANS[$business['subscription_plan']] ?? null;
    
    // Combine business and plan details
    $result = [
        'business_id' => $business['id'],
        'status' => $business['status'],
        'plan' => $business['subscription_plan'],
        'plan_details' => $plan,
        'trial_ends_at' => $business['trial_ends_at']
    ];
    
    sendSuccessResponse($result, 200, 'Subscription plan upgraded successfully');
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'cancel') {
    // POST /subscription/cancel - Cancel subscription
    
    // Check if user is admin
    if ($userData['role'] !== 'admin') {
        sendForbiddenResponse('Only admins can cancel subscriptions');
    }
    
    // Get business
    $business = $businessModel->getById($businessId);
    
    if (!$business) {
        sendNotFoundResponse('Business not found');
    }
    
    // In a real implementation, this would integrate with a payment gateway
    // For this example, we'll just downgrade to the free plan
    
    $updateData = [
        'subscription_plan' => 'free',
        'status' => 'active'
    ];
    
    // Update business
    $success = $businessModel->update($businessId, $updateData);
    
    if (!$success) {
        sendErrorResponse('Failed to cancel subscription', 500);
    }
    
    // Get updated business
    $business = $businessModel->getById($businessId);
    
    // Get plan details
    $plan = PLANS[$business['subscription_plan']] ?? null;
    
    // Combine business and plan details
    $result = [
        'business_id' => $business['id'],
        'status' => $business['status'],
        'plan' => $business['subscription_plan'],
        'plan_details' => $plan,
        'trial_ends_at' => $business['trial_ends_at']
    ];
    
    sendSuccessResponse($result, 200, 'Subscription cancelled successfully');
} else {
    sendNotFoundResponse('Endpoint not found');
}
