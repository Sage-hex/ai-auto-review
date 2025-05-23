<?php
/**
 * Login Endpoint
 * 
 * This endpoint handles user authentication and returns a JWT token.
 */

// Enable CORS for all origins during development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400'); // 24 hours cache

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($method !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Validate required fields
if (empty($data['email']) || empty($data['password'])) {
    sendValidationErrorResponse([
        'email' => empty($data['email']) ? 'Email is required' : null,
        'password' => empty($data['password']) ? 'Password is required' : null
    ]);
}

// Load User model
require_once __DIR__ . '/../../../models/User.php';
require_once __DIR__ . '/../../../models/Business.php';

$userModel = new User();
$businessModel = new Business();

// Authenticate user
$user = $userModel->authenticate($data['email'], $data['password']);

if (!$user) {
    sendErrorResponse('Invalid email or password', 401);
}

// Get business status
$business = $businessModel->getById($user['business_id']);

if (!$business) {
    sendErrorResponse('Business not found', 404);
}

// Check if business is suspended
if ($business['status'] === 'suspended') {
    sendErrorResponse('Your account has been suspended. Please contact support.', 403);
}

// Generate JWT token
$token = generateJWT($user['id'], $user['business_id'], $user['role']);

// Log successful login
$logSql = "
    INSERT INTO logs (user_id, action, description)
    VALUES (:user_id, 'login', 'User logged in successfully')
";

$logStmt = getDbConnection()->prepare($logSql);
$logStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
$logStmt->execute();

// Return user data and token
sendSuccessResponse([
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'business_id' => $user['business_id']
    ],
    'business' => [
        'id' => $business['id'],
        'name' => $business['name'],
        'subscription_plan' => $business['subscription_plan'],
        'status' => $business['status']
    ],
    'token' => $token
], 200, 'Login successful');
