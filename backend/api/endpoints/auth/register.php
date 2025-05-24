<?php
// Include CORS handler
require_once __DIR__ . '/cors.php';

/**
 * Register Endpoint
 * 
 * This endpoint handles user and business registration.
 */

// Include bootstrap file which handles CORS and other setup
require_once __DIR__ . '/../../common/bootstrap.php';

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

// Load models
require_once __DIR__ . '/../../../models/User.php';
require_once __DIR__ . '/../../../models/Business.php';

// Create model instances
$userModel = new User();
$businessModel = new Business();

// Check if email already exists
try {
    if ($userModel->getByEmail($data['email'])) {
        sendErrorResponse('Email already in use', 409);
    }
} catch (Exception $e) {
    error_log('Error checking email: ' . $e->getMessage());
    // Continue execution - this might be because the table doesn't exist yet
}

// Get database connection
try {
    $db = getDbConnection();
    
    // Check if database connection was successful
    if (!$db) {
        error_log('Failed to connect to database');
        sendErrorResponse('Database connection failed', 500);
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Log the attempt
        error_log('Attempting to create business: ' . $data['business_name']);
        
        // Create business
        $businessId = $businessModel->create($data['business_name'], 'free');
        
        if (!$businessId) {
            error_log('Business creation returned false');
            throw new Exception('Failed to create business');
        }
        
        error_log('Business created successfully with ID: ' . $businessId);
        
        // Create user as admin
        $userId = $userModel->create($businessId, $data['name'], $data['email'], $data['password'], 'admin');
        
        if (!$userId) {
            throw new Exception('Failed to create user');
        }
        
        // Commit transaction
        $db->commit();
        
        // Generate JWT token
        $token = generateJWT($userId, $businessId, 'admin');
        
        // Try to log the registration, but don't fail if it doesn't work
        try {
            $logSql = "
                INSERT INTO logs (user_id, action, description)
                VALUES (:user_id, 'register', 'User registered with business')
            ";
            
            $logStmt = $db->prepare($logSql);
            $logStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $logStmt->execute();
        } catch (Exception $e) {
            error_log('Failed to log registration: ' . $e->getMessage());
            // Continue execution - logging failure is not critical
        }
        
        // Return user data and token
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
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        error_log('Registration Error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        sendErrorResponse('Registration failed: ' . $e->getMessage(), 500);
    }
} catch (Exception $e) {
    error_log('Database Error: ' . $e->getMessage());
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}

// Clean any output buffers to ensure clean JSON response
while (ob_get_level() > 0) {
    ob_end_clean();
}
