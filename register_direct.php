<?php
/**
 * Direct Registration Endpoint
 * 
 * This is a simplified, direct registration endpoint for debugging
 */

// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set content type
header('Content-Type: application/json');

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // 24 hours cache for preflight requests
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include required files
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/config/config.php';
require_once __DIR__ . '/backend/utils/jwt.php';

// Helper functions
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Get the request data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Log the request for debugging
file_put_contents(__DIR__ . '/register_debug_log.txt', 
    date('Y-m-d H:i:s') . " - Request: " . $rawInput . "\n", 
    FILE_APPEND);

// Check if this is a valid request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

// Check if we have all required fields
$requiredFields = ['business_name', 'name', 'email', 'password'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    sendResponse([
        'status' => 'error', 
        'message' => 'Missing required fields: ' . implode(', ', $missingFields)
    ], 400);
}

// Try to register
try {
    // Get database connection
    $db = getDbConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Log each step for debugging
    $log = "Starting registration process\n";
    
    // 1. First create the business record
    $createBusinessSql = "INSERT INTO ar_business (business_name, subscription_type, business_status, date_created) VALUES (?, ?, ?, NOW())";
    $businessStmt = $db->prepare($createBusinessSql);
    $businessStmt->execute([
        $data['business_name'],
        'free',  // Default subscription plan
        'trialing'  // Default status
    ]);
    
    // Get the new business ID
    $businessId = $db->lastInsertId();
    
    $log .= "Business created with ID: $businessId\n";
    
    if (!$businessId) {
        throw new Exception('Failed to create business record');
    }
    
    // 2. Now create the user record
    $createUserSql = "INSERT INTO ar_user (business_id, full_name, email_address, password_hash, user_role, date_created) VALUES (?, ?, ?, ?, ?, NOW())";
    $userStmt = $db->prepare($createUserSql);
    $userStmt->execute([
        $businessId,
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_BCRYPT), // Securely hash the password
        'admin'  // First user is always admin
    ]);
    
    // Get the new user ID
    $userId = $db->lastInsertId();
    
    $log .= "User created with ID: $userId\n";
    
    if (!$userId) {
        throw new Exception('Failed to create user record');
    }
    
    // 3. Generate JWT token
    $log .= "Generating JWT token\n";
    $token = generateJWT($userId, $businessId, 'admin');
    
    $log .= "JWT token generated\n";
    
    // 4. Commit transaction
    $db->commit();
    $log .= "Transaction committed\n";
    
    // Prepare response data
    $responseData = [
        'status' => 'success',
        'message' => 'Registration successful',
        'data' => [
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
        ]
    ];
    
    // Log the success
    file_put_contents(__DIR__ . '/register_debug_log.txt', 
        date('Y-m-d H:i:s') . " - Success: " . $log . "\n", 
        FILE_APPEND);
    
    // Send success response
    sendResponse($responseData, 201);
    
} catch (Exception $e) {
    // Rollback transaction if there was an error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
        $log .= "Transaction rolled back\n";
    }
    
    // Log the error
    $errorMessage = "Error during registration: " . $e->getMessage() . "\n" . $e->getTraceAsString();
    file_put_contents(__DIR__ . '/register_debug_log.txt', 
        date('Y-m-d H:i:s') . " - Error: " . $errorMessage . "\n", 
        FILE_APPEND);
    
    // Check for duplicate email
    if ($e instanceof PDOException && $e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
        sendResponse(['status' => 'error', 'message' => 'Email address is already in use'], 409);
    } else {
        // Send error response
        sendResponse(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()], 500);
    }
}
