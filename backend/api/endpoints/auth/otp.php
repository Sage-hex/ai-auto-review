<?php
/**
 * OTP API Endpoints
 * 
 * Handles OTP generation, verification, and resending
 */

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set error log path
ini_set('error_log', __DIR__ . '/../../../../logs/php_errors.log');

// Include the CORS handler - this will set all necessary CORS headers
require_once __DIR__ . '/../../cors.php';

/**
 * Helper functions for API responses
 */

// Define helper functions if they don't exist
if (!function_exists('sendSuccessResponse')) {
    function sendSuccessResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }
}

if (!function_exists('sendErrorResponse')) {
    function sendErrorResponse($message, $statusCode = 400) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}

if (!function_exists('sendMethodNotAllowedResponse')) {
    function sendMethodNotAllowedResponse() {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        exit;
    }
}

if (!function_exists('sendNotFoundResponse')) {
    function sendNotFoundResponse() {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Resource not found'
        ]);
        exit;
    }
}

// Get request method and route
$method = $_SERVER['REQUEST_METHOD'];
$route = isset($_GET['route']) ? $_GET['route'] : '';

// Set content type header
header('Content-Type: application/json');

// Handle different routes
switch ($route) {
    case 'generate':
        if ($method === 'POST') {
            handleGenerateOTP();
        } else {
            sendMethodNotAllowedResponse();
        }
        break;
    
    case 'verify':
        if ($method === 'POST') {
            handleVerifyOTP();
        } else {
            sendMethodNotAllowedResponse();
        }
        break;
    
    case 'resend':
        if ($method === 'POST') {
            handleResendOTP();
        } else {
            sendMethodNotAllowedResponse();
        }
        break;
    
    default:
        sendNotFoundResponse();
        break;
}

/**
 * Handle OTP generation
 */
function handleGenerateOTP() {
    try {
        // Get JSON data from request
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['email'])) {
            sendErrorResponse('Missing required fields: user_id, email', 400);
            return;
        }
        
        $userId = $data['user_id'];
        $email = $data['email'];
        
        // Generate OTP
        $otp = generateOTP();
        $expiryTime = time() + (15 * 60); // 15 minutes expiry
        
        // Get database connection
        $db = getDbConnection();
        if (!$db) {
            sendErrorResponse('Database connection failed', 500);
            return;
        }
        
        // Store OTP in database
        $stmt = $db->prepare("INSERT INTO otp_verifications (user_id, email, otp, expiry_time, created_at) 
                             VALUES (?, ?, ?, ?, NOW())
                             ON DUPLICATE KEY UPDATE otp = ?, expiry_time = ?, created_at = NOW()");
        
        $stmt->execute([$userId, $email, $otp, $expiryTime, $otp, $expiryTime]);
        
        // Send OTP via email
        $emailSent = sendOTPEmail($email, $otp);
        
        if (!$emailSent) {
            sendErrorResponse('Failed to send OTP email', 500);
            return;
        }
        
        // Define development mode flag (set to false in production)
        $devMode = true;
        
        // Return response (including OTP in development mode only)
        sendSuccessResponse([
            'message' => 'OTP generated and sent successfully',
            'email' => maskEmail($email),
            'expires_in' => 900, // 15 minutes in seconds
            'dev_otp' => $devMode ? $otp : null // Only include OTP in development mode
        ]);
        
    } catch (Exception $e) {
        logError('Error generating OTP', $e);
        sendErrorResponse('An error occurred while generating OTP', 500);
    }
}

/**
 * Handle OTP verification
 */
function handleVerifyOTP() {
    try {
        // Get JSON data from request
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['otp'])) {
            sendErrorResponse('Missing required fields: user_id, otp', 400);
            return;
        }
        
        $userId = $data['user_id'];
        $otp = $data['otp'];
        
        // Get database connection
        $db = getDbConnection();
        if (!$db) {
            sendErrorResponse('Database connection failed', 500);
            return;
        }
        
        // Check if OTP exists and is valid
        $stmt = $db->prepare("SELECT * FROM otp_verifications WHERE user_id = ? AND otp = ? AND expiry_time > ?");
        $currentTime = time();
        $stmt->execute([$userId, $otp, $currentTime]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            sendErrorResponse('Invalid or expired OTP', 400);
            return;
        }
        
        // Mark user as verified
        $stmt = $db->prepare("UPDATE users SET is_verified = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        
        // Mark OTP as used
        $stmt = $db->prepare("UPDATE otp_verifications SET is_used = 1, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Generate JWT token for the user
        $userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendErrorResponse('User not found', 404);
            return;
        }
        
        // Generate JWT token
        $token = generateJWT([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
        
        // Return success response with token
        sendSuccessResponse([
            'message' => 'OTP verified successfully',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'is_verified' => true
            ]
        ]);
        
    } catch (Exception $e) {
        logError('Error verifying OTP', $e);
        sendErrorResponse('An error occurred while verifying OTP', 500);
    }
}

/**
 * Handle OTP resend
 */
function handleResendOTP() {
    try {
        // Get JSON data from request
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['email'])) {
            sendErrorResponse('Missing required fields: user_id, email', 400);
            return;
        }
        
        $userId = $data['user_id'];
        $email = $data['email'];
        
        // Get database connection
        $db = getDbConnection();
        if (!$db) {
            sendErrorResponse('Database connection failed', 500);
            return;
        }
        
        // Check if user exists
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND email = ?");
        $stmt->execute([$userId, $email]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendErrorResponse('User not found', 404);
            return;
        }
        
        // Check if user is already verified
        if ($user['is_verified']) {
            sendErrorResponse('User is already verified', 400);
            return;
        }
        
        // Check for rate limiting (prevent spam)
        $stmt = $db->prepare("SELECT created_at FROM otp_verifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId]);
        
        $lastOTP = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastOTP) {
            $lastOTPTime = strtotime($lastOTP['created_at']);
            $currentTime = time();
            
            // Allow resend only after 1 minute
            if (($currentTime - $lastOTPTime) < 60) {
                sendErrorResponse('Please wait before requesting another OTP', 429);
                return;
            }
        }
        
        // Generate new OTP
        $otp = generateOTP();
        $expiryTime = time() + (15 * 60); // 15 minutes expiry
        
        // Store OTP in database
        $stmt = $db->prepare("INSERT INTO otp_verifications (user_id, email, otp, expiry_time, created_at) 
                             VALUES (?, ?, ?, ?, NOW())
                             ON DUPLICATE KEY UPDATE otp = ?, expiry_time = ?, created_at = NOW()");
        
        $stmt->execute([$userId, $email, $otp, $expiryTime, $otp, $expiryTime]);
        
        // Send OTP via email
        $emailSent = sendOTPEmail($email, $otp);
        
        if (!$emailSent) {
            sendErrorResponse('Failed to send OTP email', 500);
            return;
        }
        
        // Return success response
        sendSuccessResponse([
            'message' => 'OTP resent successfully',
            'email' => maskEmail($email),
            'expires_in' => 900 // 15 minutes in seconds
        ]);
        
    } catch (Exception $e) {
        logError('Error resending OTP', $e);
        sendErrorResponse('An error occurred while resending OTP', 500);
    }
}

/**
 * Generate a 6-digit OTP
 */
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send OTP via email
 */
function sendOTPEmail($email, $otp) {
    // Include our Gmail helper
    require_once __DIR__ . '/../../../helpers/gmail_helper.php';
    
    $subject = 'Your Verification Code for AI Auto Review';
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4f46e5; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9fafb; }
            .code { font-size: 32px; font-weight: bold; text-align: center; letter-spacing: 5px; margin: 30px 0; color: #4f46e5; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>AI Auto Review</h1>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>Thank you for signing up with AI Auto Review. To complete your registration, please use the following verification code:</p>
                <div class='code'>$otp</div>
                <p>This code will expire in 15 minutes.</p>
                <p>If you didn't request this code, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " AI Auto Review. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Log the attempt and check environment variables
    error_log("Attempting to send OTP Email to: $email");
    error_log("SMTP_HOST: " . getenv('SMTP_HOST'));
    error_log("SMTP_PORT: " . getenv('SMTP_PORT'));
    error_log("SMTP_USERNAME: " . getenv('SMTP_USERNAME'));
    error_log("SMTP_PASSWORD is set: " . (getenv('SMTP_PASSWORD') ? 'Yes' : 'No'));
    error_log("MAIL_FROM_EMAIL: " . getenv('MAIL_FROM_EMAIL'));
    
    // Always log the OTP for development purposes
    error_log("OTP Email to {$email}: {$otp}");
    
    // Try sending via Gmail first
    $result = sendGmailEmail($email, $subject, $message);
    
    if ($result) {
        error_log("Email sent successfully via Gmail");
        return true;
    }
    
    // If Gmail fails, try using the fallback method
    error_log("Gmail sending failed, trying fallback method");
    $fallback_result = sendOTPFallback($email, $subject, $message);
    
    if ($fallback_result) {
        error_log("Email sent successfully via fallback method");
        return true;
    }
    
    // For development mode, return true even if email sending fails
    // This allows testing the verification flow without actual email delivery
    $dev_mode = true;
    
    if ($dev_mode) {
        error_log("Using development mode - considering email as sent");
        return true;
    }
    
    error_log("All email sending methods failed for: {$email}");
    return false;
}

/**
 * Mask email for privacy
 */
if (!function_exists('maskEmail')) {
    function maskEmail($email) {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        
        return $maskedName . '@' . $domain;
    }
}

// Helper functions moved to the top of the file
