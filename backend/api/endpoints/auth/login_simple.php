<?php
/**
 * Simple Login Endpoint
 * 
 * This endpoint provides a simplified login without database dependencies.
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser, but log them

// Start output buffering to prevent any unwanted output
ob_start();

// Enable CORS for all origins during development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400'); // 24 hours cache
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents('php://input');

// Log the raw input for debugging
error_log('Login raw input: ' . $rawInput);

// Try to decode the JSON input
$data = json_decode($rawInput, true);

// Check for JSON parsing errors
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    sendErrorResponse('Invalid JSON input: ' . json_last_error_msg(), 400);
}

// Only allow POST requests
if ($method !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Validate required fields
if (empty($data['email']) || empty($data['password'])) {
    $errors = [];
    if (empty($data['email'])) $errors['email'] = 'Email is required';
    if (empty($data['password'])) $errors['password'] = 'Password is required';
    sendValidationErrorResponse($errors);
}

// Log the login attempt
error_log('Login attempt: ' . $data['email']);

// For testing purposes, accept any credentials
$userId = rand(1000, 9999);
$businessId = rand(1000, 9999);

// Generate a simple JWT token
$token = base64_encode(json_encode([
    'user_id' => $userId,
    'business_id' => $businessId,
    'role' => 'admin',
    'exp' => time() + (7 * 24 * 60 * 60) // 7 days
]));

// Return success response with mock data
sendSuccessResponse([
    'user' => [
        'id' => $userId,
        'name' => 'Test User',
        'email' => $data['email'],
        'role' => 'admin',
        'business_id' => $businessId
    ],
    'business' => [
        'id' => $businessId,
        'name' => 'Test Business',
        'subscription_plan' => 'free',
        'status' => 'trialing'
    ],
    'token' => $token
], 200, 'Login successful');

// Helper functions
function sendSuccessResponse($data, $statusCode = 200, $message = 'Success') {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'success',
        'message' => $message,
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

function sendValidationErrorResponse($errors) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit;
}

// Clean any output buffers to ensure clean JSON response
while (ob_get_level() > 0) {
    ob_end_clean();
}
?>
