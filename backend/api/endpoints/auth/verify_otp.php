<?php
/**
 * OTP Verification Endpoint
 * 
 * A dedicated endpoint for OTP verification to avoid conflicts
 */

// Include the direct CORS handler (no dependencies)
require_once __DIR__ . '/direct_cors.php';

// Enable error reporting

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set error log path
ini_set('error_log', __DIR__ . '/../../../../logs/php_errors.log');

// CORS headers are already set by the included cors.php file
// Just set the content type header
header('Content-Type: application/json');

// Log for debugging
error_log("Using global CORS headers from cors.php for OTP verification");

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Check if the required fields are present
if (!isset($data['user_id']) || !isset($data['otp'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Extract the data
$userId = $data['user_id'];
$otp = $data['otp'];

// Log the verification attempt
error_log("OTP verification attempt for user ID: {$userId} with OTP: {$otp}");

// Function to send error response
function sendErrorResponse($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Function to send success response
function sendSuccessResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// Function to generate JWT token
function generateJWT($user) {
    $jwt_secret = getenv('JWT_SECRET') ?: 'your_jwt_secret_key_here_make_this_long_and_secure';
    $jwt_expiry = getenv('JWT_EXPIRY') ?: 86400; // 24 hours
    
    $issuedAt = time();
    $expiresAt = $issuedAt + $jwt_expiry;
    
    // Determine the correct column names based on what's available
    $userId = isset($user['id']) ? $user['id'] : (isset($user['user_id']) ? $user['user_id'] : null);
    $email = isset($user['email']) ? $user['email'] : (isset($user['email_address']) ? $user['email_address'] : null);
    $name = isset($user['name']) ? $user['name'] : (isset($user['full_name']) ? $user['full_name'] : null);
    
    // Log the user data we're using
    error_log("Generating JWT with user ID: {$userId}, email: {$email}, name: {$name}");
    
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expiresAt,
        'user_id' => $userId,
        'email' => $email,
        'name' => $name
    ];
    
    // Encode Header
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $header = base64_encode($header);
    
    // Encode Payload
    $payload = json_encode($payload);
    $payload = base64_encode($payload);
    
    // Create Signature
    $signature = hash_hmac('sha256', "$header.$payload", $jwt_secret, true);
    $signature = base64_encode($signature);
    
    return "$header.$payload.$signature";
}

try {
    // Connect to the database
    require_once __DIR__ . '/../../../config/database.php';
    $db = getDbConnection();
    error_log("Connected to database successfully");
    
    // Verify the OTP
    error_log("Checking OTP verification for user ID: {$userId} with OTP: {$otp}");
    
    // Check the structure of the otp_verifications table
    $columnsQuery = "DESCRIBE otp_verifications";
    $columnsStmt = $db->query($columnsQuery);
    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("OTP verifications table columns: " . implode(", ", $columns));
    
    // Adjust query based on actual table structure
    // Use the correct column names based on what we found in the database
    $query = "SELECT * FROM otp_verifications WHERE user_id = ? AND otp = ? AND is_used = 0";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId, $otp]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$verification) {
        error_log("No matching OTP verification found");
        sendErrorResponse('Invalid or expired OTP for user ID: ' . $userId);
    }
    
    error_log("Found OTP verification record: " . json_encode($verification));
    
    // Mark the OTP as used - adjust query based on available columns
    $updateQuery = "UPDATE otp_verifications SET is_used = 1 WHERE user_id = ? AND otp = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([$userId, $otp]);
    error_log("Marked OTP as used");
    
    // Check user table structure - first find the correct table name
    $tableListQuery = "SHOW TABLES";
    $tableListStmt = $db->query($tableListQuery);
    $tables = $tableListStmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Database tables: " . implode(", ", $tables));
    
    // Find the user table (could be ar_user or users)
    $userTable = null;
    foreach ($tables as $table) {
        if (stripos($table, 'user') !== false) {
            $userTable = $table;
            break;
        }
    }
    
    if (!$userTable) {
        error_log("Could not find user table");
        $userTable = 'ar_user'; // Default fallback
    }
    
    error_log("Using user table: {$userTable}");
    
    // Check user table structure
    $userTableQuery = "DESCRIBE {$userTable}";
    $userTableStmt = $db->query($userTableQuery);
    $userColumns = $userTableStmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("User table columns: " . implode(", ", $userColumns));
    
    // Update user verification status - adjust query based on available columns
    // First determine the correct ID column name
    $userIdColumn = in_array('id', $userColumns) ? 'id' : 'user_id';
    error_log("Using user ID column: {$userIdColumn}");
    
    if (in_array('email_verified', $userColumns) && in_array('email_verified_at', $userColumns)) {
        $updateUserQuery = "UPDATE {$userTable} SET email_verified = 1, email_verified_at = NOW() WHERE {$userIdColumn} = ?";
    } elseif (in_array('email_verified', $userColumns)) {
        $updateUserQuery = "UPDATE {$userTable} SET email_verified = 1 WHERE {$userIdColumn} = ?";
    } else {
        // If no verification columns exist, we'll skip this step
        $updateUserQuery = null;
    }
    
    if ($updateUserQuery) {
        $updateUserStmt = $db->prepare($updateUserQuery);
        $updateUserStmt->execute([$userId]);
        error_log("Updated user verification status");
    } else {
        error_log("Skipped user verification status update - columns not found");
    }
    
    // Get user data for token generation
    $userQuery = "SELECT * FROM {$userTable} WHERE {$userIdColumn} = ?";
    error_log("User query: {$userQuery} with ID: {$userId}");
    $userStmt = $db->prepare($userQuery);
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    // Log user data for debugging
    if ($user) {
        error_log("Found user data: " . json_encode(array_keys($user)));
    } else {
        error_log("No user found with ID: {$userId}");
    }
    
    if (!$user) {
        error_log("User not found for ID: $userId");
        sendErrorResponse('User not found');
    }
    
    // Generate JWT token
    $token = generateJWT($user);
    
    // Determine the correct user data structure based on available columns
    $userId = isset($user['id']) ? $user['id'] : (isset($user['user_id']) ? $user['user_id'] : null);
    $email = isset($user['email']) ? $user['email'] : (isset($user['email_address']) ? $user['email_address'] : null);
    $name = isset($user['name']) ? $user['name'] : (isset($user['full_name']) ? $user['full_name'] : null);
    
    // Return success with token
    sendSuccessResponse([
        'message' => 'Email verified successfully',
        'token' => $token,
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'email_verified' => true
        ]
    ]);
    
    // Log successful verification
    error_log("OTP verification successful for user ID: {$userId}");
    
} catch (PDOException $e) {
    error_log("Database error during OTP verification: " . $e->getMessage());
    sendErrorResponse('An error occurred during verification');
} catch (Exception $e) {
    error_log("General error during OTP verification: " . $e->getMessage());
    sendErrorResponse('An error occurred during verification');
}
?>
