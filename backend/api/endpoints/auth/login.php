<?php
/**
 * Login Endpoint
 * 
 * This endpoint handles user authentication.
 */

// Include CORS middleware first
require_once __DIR__ . '/../../common/bootstrap.php';

// Now continue with the rest of the endpoint logic

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
    sendValidationErrorResponse([
        'email' => empty($data['email']) ? 'Email is required' : null,
        'password' => empty($data['password']) ? 'Password is required' : null
    ]);
}

// Log the login attempt
error_log('Login attempt: ' . $data['email']);

// Load models
try {
    require_once __DIR__ . '/../../../models/User.php';
    require_once __DIR__ . '/../../../models/Business.php';
    
    $userModel = new User();
    $businessModel = new Business();
    
    // Authenticate user
    try {
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
        if (isset($business['status']) && $business['status'] === 'suspended') {
            sendErrorResponse('Your account has been suspended. Please contact support.', 403);
        }
        
        // Generate JWT token
        $token = generateJWT($user['id'], $user['business_id'], $user['role']);
        
        // Try to log successful login, but continue if it fails
        try {
            $logSql = "
                INSERT INTO logs (user_id, action, description)
                VALUES (:user_id, 'login', 'User logged in successfully')
            ";
            
            $logStmt = getDbConnection()->prepare($logSql);
            $logStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
            $logStmt->execute();
        } catch (Exception $e) {
            error_log('Failed to log login: ' . $e->getMessage());
            // Continue execution - logging failure is not critical
        }
        
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
                'subscription_plan' => isset($business['plan']) ? $business['plan'] : 'free',
                'status' => isset($business['status']) ? $business['status'] : 'trialing'
            ],
            'token' => $token
        ], 200, 'Login successful');
    } catch (Exception $e) {
        error_log('Authentication error: ' . $e->getMessage());
        sendErrorResponse('Authentication failed: ' . $e->getMessage(), 500);
    }
} catch (Exception $e) {
    error_log('Login Error: ' . $e->getMessage());
    sendErrorResponse('Login failed: ' . $e->getMessage(), 500);
}

// Clean any output buffers to ensure clean JSON response
while (ob_get_level() > 0) {
    ob_end_clean();
}
