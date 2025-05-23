<?php
/**
 * API Router
 * 
 * This file handles all API requests and routes them to the appropriate endpoint.
 */

// Include bootstrap file which handles CORS and other setup
require_once __DIR__ . '/common/bootstrap.php';

// Load required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/jwt.php';

// Parse request path
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/AiAutoReview/backend/api';
$path = str_replace($basePath, '', $requestUri);
$path = strtok($path, '?'); // Remove query string
$pathParts = explode('/', trim($path, '/'));

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request data
$data = [];
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse('Invalid JSON data', 400);
    }
}

// Define public routes (no authentication required)
$publicRoutes = [
    'POST:/login',
    'POST:/register',
    'GET:/health'
];

// Check if route requires authentication
$currentRoute = "$method:/$pathParts[0]";
$requiresAuth = !in_array($currentRoute, $publicRoutes);

// Authenticate request if required
$userData = null;
if ($requiresAuth) {
    $userData = authenticateRequest();
    
    if (!$userData) {
        sendUnauthorizedResponse('Authentication required');
    }
}

// Route the request
try {
    switch ($pathParts[0]) {
        case 'health':
            require_once __DIR__ . '/endpoints/health.php';
            break;
            
        case 'login':
            require_once __DIR__ . '/endpoints/auth/login.php';
            break;
            
        case 'register':
            require_once __DIR__ . '/endpoints/auth/register.php';
            break;
            
        case 'business':
            require_once __DIR__ . '/endpoints/business/index.php';
            break;
            
        case 'users':
            require_once __DIR__ . '/endpoints/users/index.php';
            break;
            
        case 'reviews':
            require_once __DIR__ . '/endpoints/reviews/index.php';
            break;
            
        case 'responses':
            require_once __DIR__ . '/endpoints/responses/index.php';
            break;
            
        case 'subscription':
            require_once __DIR__ . '/endpoints/subscription/index.php';
            break;
            
        case 'platforms':
            require_once __DIR__ . '/endpoints/platforms/index.php';
            break;
            
        default:
            sendNotFoundResponse('Endpoint not found');
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    sendErrorResponse('Internal server error', 500);
}
