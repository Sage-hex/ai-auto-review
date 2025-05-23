<?php
/**
 * Platforms Endpoints
 * 
 * These endpoints handle platform integration operations.
 */

// Load required services
require_once __DIR__ . '/../../../services/PlatformService.php';

$platformService = new PlatformService();

// Get business ID from authenticated user
$businessId = $userData['business_id'];

// Route based on request method and path
if ($method === 'GET' && count($pathParts) === 1) {
    // GET /platforms - Get platform integrations
    
    // Get platform tokens
    $tokens = $platformService->getPlatformTokens($businessId);
    
    // Format response to hide sensitive data
    $platforms = [];
    
    foreach ($tokens as $token) {
        $platforms[] = [
            'id' => $token['id'],
            'platform' => $token['platform'],
            'connected' => true,
            'expires_at' => $token['expires_at']
        ];
    }
    
    // Add missing platforms
    $availablePlatforms = ['google', 'yelp', 'facebook'];
    $connectedPlatforms = array_column($platforms, 'platform');
    
    foreach ($availablePlatforms as $platform) {
        if (!in_array($platform, $connectedPlatforms)) {
            $platforms[] = [
                'id' => null,
                'platform' => $platform,
                'connected' => false,
                'expires_at' => null
            ];
        }
    }
    
    sendSuccessResponse($platforms);
} elseif ($method === 'POST' && count($pathParts) === 2) {
    // POST /platforms/{platform} - Connect platform
    $platform = $pathParts[1];
    
    // Check if user has permission to manage platforms
    if (!in_array($userData['role'], ['admin', 'manager'])) {
        sendForbiddenResponse('You do not have permission to manage platform integrations');
    }
    
    // Validate platform
    $allowedPlatforms = ['google', 'yelp', 'facebook'];
    
    if (!in_array($platform, $allowedPlatforms)) {
        sendValidationErrorResponse(['platform' => 'Invalid platform']);
    }
    
    // Validate required fields
    if (empty($data['access_token'])) {
        sendValidationErrorResponse(['access_token' => 'Access token is required']);
    }
    
    // Save platform token
    $refreshToken = $data['refresh_token'] ?? null;
    $expiresAt = $data['expires_at'] ?? null;
    
    $success = $platformService->savePlatformToken(
        $businessId,
        $platform,
        $data['access_token'],
        $refreshToken,
        $expiresAt
    );
    
    if (!$success) {
        sendErrorResponse('Failed to connect platform', 500);
    }
    
    sendSuccessResponse([
        'platform' => $platform,
        'connected' => true,
        'expires_at' => $expiresAt
    ], 200, 'Platform connected successfully');
} elseif ($method === 'DELETE' && count($pathParts) === 2) {
    // DELETE /platforms/{platform} - Disconnect platform
    $platform = $pathParts[1];
    
    // Check if user has permission to manage platforms
    if (!in_array($userData['role'], ['admin', 'manager'])) {
        sendForbiddenResponse('You do not have permission to manage platform integrations');
    }
    
    // Validate platform
    $allowedPlatforms = ['google', 'yelp', 'facebook'];
    
    if (!in_array($platform, $allowedPlatforms)) {
        sendValidationErrorResponse(['platform' => 'Invalid platform']);
    }
    
    // Delete platform token
    $success = $platformService->deletePlatformToken($businessId, $platform);
    
    if (!$success) {
        sendErrorResponse('Failed to disconnect platform', 500);
    }
    
    sendSuccessResponse(null, 200, 'Platform disconnected successfully');
} else {
    sendNotFoundResponse('Endpoint not found');
}
