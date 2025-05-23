<?php
/**
 * Simple Register Endpoint
 * 
 * This endpoint provides a simplified registration without database dependencies.
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
error_log('Raw input: ' . $rawInput);

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
$requiredFields = ['business_name', 'name', 'email', 'password'];
$errors = [];

// Log the data for debugging
error_log('Decoded data: ' . print_r($data, true));

// Check if data is null or not an array
if (!is_array($data)) {
    sendErrorResponse('Invalid request data format', 400);
}

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
    }
}

if (!empty($errors)) {
    sendValidationErrorResponse($errors);
}

// Validate email format
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendValidationErrorResponse(['email' => 'Invalid email format']);
}

// Log the registration attempt
error_log('Registration attempt: ' . $data['email']);

// Generate mock IDs for testing
$businessId = rand(1000, 9999);
$userId = rand(1000, 9999);

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
        'name' => $data['name'],
        'email' => $data['email'],
        'role' => 'admin',
        'business_id' => $businessId
    ],
    'business' => [
        'id' => $businessId,
        'name' => $data['business_name'],
        'subscription_plan' => 'free',
        'status' => 'trialing'
    ],
    'token' => $token
], 201, 'Registration successful');

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
