<?php
/**
 * Auth Handler
 * 
 * Unified auth handler for registration and login
 */

// Include the CORS handler
require_once __DIR__ . '/../../cors.php';

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set error log path
ini_set('error_log', __DIR__ . '/../../../../logs/php_errors.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../../../../logs')) {
    mkdir(__DIR__ . '/../../../../logs', 0777, true);
}

// Log that the handler is starting
error_log("Auth handler starting execution");

// Function to log errors with stack trace
function logError($message, $exception = null) {
    error_log("ERROR: " . $message);
    if ($exception instanceof Exception) {
        error_log("Exception: " . $exception->getMessage());
        error_log("Stack trace: " . $exception->getTraceAsString());
    }
}

// Function to mask email for privacy
if (!function_exists('maskEmail')) {
    function maskEmail($email) {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        
        return $maskedName . '@' . $domain;
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
// Allow all origins during development
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
// Don't use credentials to avoid CORS issues
header('Access-Control-Allow-Credentials: false');

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
        
        // Log successful connection
        error_log("Database connection successful");
        error_log("Database connection established");
        
        // Check if database exists and tables are created
        try {
            $checkTablesQuery = "SHOW TABLES LIKE 'ar_business'";
            $stmt = $db->query($checkTablesQuery);
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                // Create the necessary tables if they don't exist
                error_log("Creating required database tables");
                
                // Create ar_business table
                $createBusinessTableSql = "CREATE TABLE IF NOT EXISTS ar_business (
                    business_id INT AUTO_INCREMENT PRIMARY KEY,
                    business_name VARCHAR(255) NOT NULL,
                    subscription_type VARCHAR(50) DEFAULT 'free',
                    business_status VARCHAR(50) DEFAULT 'trialing',
                    date_created DATETIME NOT NULL,
                    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                
                $db->exec($createBusinessTableSql);
                error_log("ar_business table created");
                
                // Create ar_user table
                $createUserTableSql = "CREATE TABLE IF NOT EXISTS ar_user (
                    user_id INT AUTO_INCREMENT PRIMARY KEY,
                    business_id INT NOT NULL,
                    full_name VARCHAR(255) NOT NULL,
                    email_address VARCHAR(255) NOT NULL,
                    is_verified TINYINT(1) DEFAULT 0,
                    password_hash VARCHAR(255) NOT NULL,
                    user_role VARCHAR(50) NOT NULL,
                    date_created DATETIME NOT NULL,
                    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_email (email_address),
                    FOREIGN KEY (business_id) REFERENCES ar_business(business_id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                
                $db->exec($createUserTableSql);
                error_log("ar_user table created");
            }
            error_log("Database tables verified");
        } catch (PDOException $e) {
            logError("Error checking or creating database tables", $e);
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
            
            // Generate OTP for verification
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiryTime = time() + (15 * 60); // 15 minutes expiry
            
            // Store OTP in database
            try {
                // Check if otp_verifications table exists
                $checkOtpTableQuery = "SHOW TABLES LIKE 'otp_verifications'";
                $stmt = $db->query($checkOtpTableQuery);
                $otpTableExists = $stmt->rowCount() > 0;
                
                // Create the table if it doesn't exist - don't recreate if it exists
                if (!$otpTableExists) {
                    // Create the table if it doesn't exist
                    $createOtpTableSql = "CREATE TABLE IF NOT EXISTS otp_verifications (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        email VARCHAR(255) NOT NULL,
                        otp VARCHAR(6) NOT NULL,
                        expiry_time INT NOT NULL,
                        is_used TINYINT(1) DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_user_id (user_id),
                        INDEX idx_otp (otp),
                        INDEX idx_expiry (expiry_time)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                    
                    $db->exec($createOtpTableSql);
                    error_log("OTP verifications table created");
                    
                    // Add is_verified column to ar_user table if it doesn't exist
                    $checkVerifiedColumnSql = "SHOW COLUMNS FROM ar_user LIKE 'is_verified'";
                    $stmt = $db->query($checkVerifiedColumnSql);
                    $verifiedColumnExists = $stmt->rowCount() > 0;
                    
                    if (!$verifiedColumnExists) {
                        $addVerifiedColumnSql = "ALTER TABLE ar_user ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER email_address";
                        $db->exec($addVerifiedColumnSql);
                        error_log("Added is_verified column to ar_user table");
                    }
                }
                
                // Insert OTP record
                $insertOtpSql = "INSERT INTO otp_verifications (user_id, email, otp, expiry_time, created_at) 
                                VALUES (?, ?, ?, ?, NOW())
                                ON DUPLICATE KEY UPDATE otp = ?, expiry_time = ?, created_at = NOW()";
                
                $otpStmt = $db->prepare($insertOtpSql);
                $otpStmt->execute([$userId, $data['email'], $otp, $expiryTime, $otp, $expiryTime]);
                error_log("OTP stored in database");
                
                // Send OTP via email (in a real environment, use a proper email service)
                // For now, we'll just log it and include it in the response for development
                error_log("OTP Email to {$data['email']}: $otp");
                
                // In a production environment, you would send a real email here
                // sendOTPEmail($data['email'], $otp);
                
                // For development, include the OTP in the response
                $devMode = true; // Set to false in production
                
            } catch (Exception $e) {
                error_log("Error generating OTP: " . $e->getMessage());
                // Continue with registration even if OTP generation fails
            }
            
            // Prepare response data
            $responseData = [
                'message' => 'Registration successful. Please verify your email.',
                'user' => [
                    'id' => $userId,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => 'admin',
                    'business_id' => $businessId,
                    'is_verified' => false
                ],
                'business' => [
                    'id' => $businessId,
                    'name' => $data['business_name'],
                    'subscription_plan' => 'free',
                    'status' => 'trialing'
                ],
                'verification_required' => true,
                'verification_email' => maskEmail($data['email'])
            ];
            
            // Add OTP to the response for development mode
            if (isset($devMode) && $devMode) {
                $responseData['dev_otp'] = $otp;
            }
            
            // Use the maskEmail function defined at the top of the file
            
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
