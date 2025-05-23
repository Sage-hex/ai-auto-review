<?php
/**
 * Auth Handler
 * 
 * Unified auth handler for registration and login
 */

// Only enable error reporting in development environment
if (getenv('APP_ENV') === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // In production, disable error display but still log errors
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

// Function to log errors with stack trace
function logError($message, $exception = null) {
    error_log("ERROR: " . $message);
    if ($exception instanceof Exception) {
        error_log("Exception: " . $exception->getMessage());
        error_log("Stack trace: " . $exception->getTraceAsString());
    }
}

// Define JWT constants if not already defined
if (!defined('JWT_SECRET')) {
    // Use environment variable for JWT secret if available, otherwise use a secure fallback
    $jwt_secret = getenv('JWT_SECRET');
    if (!$jwt_secret) {
        // Generate a secure random string as fallback
        $jwt_secret = bin2hex(random_bytes(32));
        // Log a warning that we're using a generated secret
        error_log("WARNING: JWT_SECRET environment variable not set. Using generated secret. This is not recommended for production.");
    }
    define('JWT_SECRET', $jwt_secret);
    define('JWT_EXPIRY', 86400); // 24 hours in seconds
}

// Include required files
try {
    require_once __DIR__ . '/../../../../backend/config/database.php';
    require_once __DIR__ . '/../../../../backend/utils/jwt_fixed.php';
} catch (Exception $e) {
    logError("Failed to include required files", $e);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server configuration error: ' . $e->getMessage()]);
    exit;
}

// Set CORS headers
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Log CORS headers for debugging
error_log("CORS headers set: Origin=http://localhost:5173");

// Enable detailed error logging
error_log("Auth handler initialized");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents('php://input');
error_log("Raw input: " . $rawInput);

// Define our response functions here to avoid dependencies
function sendSuccessResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
    exit;
}

// Function to send error response
function sendErrorResponse($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

// Get request data
$data = json_decode($rawInput, true);

// Check for JSON parsing errors
if (json_last_error() !== JSON_ERROR_NONE) {
    logError('Invalid JSON input: ' . json_last_error_msg());
    sendErrorResponse('Invalid JSON input: ' . json_last_error_msg(), 400);
}

error_log("Decoded data: " . print_r($data, true));

// Only allow POST requests
if ($method !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Determine if it's a login or registration request based on data
if (isset($data['business_name']) && isset($data['name']) && isset($data['email']) && isset($data['password'])) {
    // This is a registration request
    try {
        error_log("Starting registration process");
        
        // Get database connection
        $db = getDbConnection();
        if (!$db) {
            logError("Database connection failed");
            sendErrorResponse('Failed to connect to database', 500);
            exit;
        }
        error_log("Database connection established");
        
        // Check if database exists and tables are created
        try {
            $checkTablesQuery = "SHOW TABLES LIKE 'ar_business'";
            $stmt = $db->query($checkTablesQuery);
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                logError("Required database tables don't exist");
                sendErrorResponse('Database setup incomplete. Tables not found.', 500);
                exit;
            }
            error_log("Database tables verified");
        } catch (PDOException $e) {
            logError("Error checking database tables", $e);
            sendErrorResponse('Database error: ' . $e->getMessage(), 500);
            exit;
        }
        
        // Begin transaction to ensure both business and user are created
        $db->beginTransaction();
        error_log("Transaction started");
        
        try {
            // Check if email already exists
            $checkEmailSql = "SELECT COUNT(*) FROM ar_user WHERE email_address = ?";
            $checkStmt = $db->prepare($checkEmailSql);
            $checkStmt->execute([$data['email']]);
            $emailExists = (int)$checkStmt->fetchColumn() > 0;
            
            if ($emailExists) {
                $db->rollBack();
                error_log("Email already exists: " . $data['email']);
                sendErrorResponse('Email address is already registered', 409);
                exit;
            }
            
            // 1. First create the business record
            $createBusinessSql = "INSERT INTO ar_business (business_name, subscription_type, business_status, date_created) VALUES (?, ?, ?, NOW())";
            $businessStmt = $db->prepare($createBusinessSql);
            error_log("Executing business creation query");
            $businessStmt->execute([
                $data['business_name'],
                'free',  // Default subscription plan
                'trialing'  // Default status
            ]);
            
            // Get the new business ID
            $businessId = $db->lastInsertId();
            error_log("Business created with ID: " . $businessId);
            
            if (!$businessId) {
                throw new Exception('Failed to create business record');
            }
            
            // 2. Now create the user record
            $createUserSql = "INSERT INTO ar_user (business_id, full_name, email_address, password_hash, user_role, date_created) VALUES (?, ?, ?, ?, ?, NOW())";
            $userStmt = $db->prepare($createUserSql);
            error_log("Executing user creation query");
            $userStmt->execute([
                $businessId,
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT), // Securely hash the password
                'admin'  // First user is always admin
            ]);
            
            // Get the new user ID
            $userId = $db->lastInsertId();
            error_log("User created with ID: " . $userId);
            
            if (!$userId) {
                throw new Exception('Failed to create user record');
            }
            
            // Commit the transaction
            $db->commit();
            error_log("Transaction committed successfully");
            
            // Generate JWT token
            error_log("Generating JWT token");
            $token = generateJWT($userId, $businessId, 'admin');
            error_log("JWT token generated");
            
            // Prepare response data
            $responseData = [
                'message' => 'Registration successful',
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
            ];
            
            // Return success response
            error_log("Sending success response");
            sendSuccessResponse($responseData, 201);
            
        } catch (PDOException $e) {
            // Rollback transaction on database error
            $db->rollBack();
            logError("Database error during registration", $e);
            
            // Check for duplicate email (MySQL error code 1062 for duplicate entry)
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                sendErrorResponse('Email address is already registered', 409);
            } else {
                sendErrorResponse('Database error: ' . $e->getMessage(), 500);
            }
            exit;
        }
    } catch (Exception $e) {
        // Rollback transaction if there was an error
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        
        logError("General error during registration", $e);
        sendErrorResponse('Registration failed: ' . $e->getMessage(), 500);
    }
    
} elseif (isset($data['email']) && isset($data['password'])) {
    // This is a login request
    try {
        // Get database connection
        $db = getDbConnection();
        
        // Find user by email
        $findUserSql = "SELECT u.*, b.business_name, b.subscription_type, b.business_status 
                        FROM ar_user u 
                        JOIN ar_business b ON u.business_id = b.business_id 
                        WHERE u.email_address = ?";
        $stmt = $db->prepare($findUserSql);
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if user exists and password is correct
        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            sendErrorResponse('Invalid email or password', 401);
            exit;
        }
        
        // Generate JWT token
        $token = generateJWT($user['user_id'], $user['business_id'], $user['user_role']);
        
        // Prepare response data
        $responseData = [
            'message' => 'Login successful',
            'user' => [
                'id' => $user['user_id'],
                'name' => $user['full_name'],
                'email' => $user['email_address'],
                'role' => $user['user_role'],
                'business_id' => $user['business_id']
            ],
            'business' => [
                'id' => $user['business_id'],
                'name' => $user['business_name'],
                'subscription_plan' => $user['subscription_type'],
                'status' => $user['business_status']
            ],
            'token' => $token
        ];
        
        // Return success response
        sendSuccessResponse($responseData, 200);
        
    } catch (PDOException $e) {
        error_log('Database error during login: ' . $e->getMessage());
        sendErrorResponse('Login failed due to a database error', 500);
    } catch (Exception $e) {
        error_log('Error during login: ' . $e->getMessage());
        sendErrorResponse('Login failed: ' . $e->getMessage(), 500);
    }
    
} else {
    sendErrorResponse('Invalid request. Missing required fields.', 400);
}

// Clean any output buffers to ensure clean JSON response
while (ob_get_level() > 0) {
    ob_end_clean();
}
