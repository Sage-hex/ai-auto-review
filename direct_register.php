<?php
/**
 * Direct Registration Test
 * 
 * This script tests the registration process directly without going through the API
 */

// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define JWT constants if not already defined
if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'your_jwt_secret_key_here'); // Change this in production
    define('JWT_EXPIRY', 86400); // 24 hours in seconds
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'aiautoreview');
define('DB_USER', 'root');
define('DB_PASS', '');

// Function to log errors with stack trace
function logError($message, $exception = null) {
    error_log("ERROR: " . $message);
    if ($exception instanceof Exception) {
        error_log("Exception: " . $exception->getMessage());
        error_log("Stack trace: " . $exception->getTraceAsString());
    }
}

// Function to get database connection
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Log error and return false
        logError("Database Connection Error: " . $e->getMessage(), $e);
        return false;
    }
}

// Function to generate JWT token
function generateJWT($userId, $businessId, $role) {
    $issuedAt = time();
    $expiryTime = $issuedAt + JWT_EXPIRY;
    
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expiryTime,
        'user_id' => $userId,
        'business_id' => $businessId,
        'role' => $role
    ];
    
    // Create header and encode it properly for JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    
    // Create payload and encode it properly for JWT
    $payloadJson = json_encode($payload);
    $payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));
    
    // Create signature
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    // Return the complete token
    return "$header.$payload.$signature";
}

// Function to register a new user
function registerUser($businessName, $fullName, $email, $password) {
    try {
        echo "<h2>Starting Registration Process</h2>";
        
        // Get database connection
        $db = getDbConnection();
        if (!$db) {
            echo "<p style='color:red'>Database connection failed</p>";
            return false;
        }
        echo "<p>Database connection established</p>";
        
        // Check if database exists and tables are created
        try {
            $checkTablesQuery = "SHOW TABLES LIKE 'ar_business'";
            $stmt = $db->query($checkTablesQuery);
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                echo "<p style='color:red'>Required database tables don't exist</p>";
                
                // Create tables
                echo "<p>Creating required tables...</p>";
                
                // Create business table
                $db->exec("CREATE TABLE IF NOT EXISTS ar_business (
                    business_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    business_name VARCHAR(255) NOT NULL,
                    subscription_type ENUM('free', 'basic', 'professional', 'enterprise') NOT NULL DEFAULT 'free',
                    business_status ENUM('trialing', 'active', 'inactive', 'cancelled') NOT NULL DEFAULT 'trialing',
                    date_created DATETIME NOT NULL,
                    date_updated DATETIME NULL DEFAULT NULL,
                    INDEX idx_business_status (business_status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                
                echo "<p>Business table created</p>";
                
                // Create user table
                $db->exec("CREATE TABLE IF NOT EXISTS ar_user (
                    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    business_id INT UNSIGNED NOT NULL,
                    full_name VARCHAR(255) NOT NULL,
                    email_address VARCHAR(255) NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    user_role ENUM('admin', 'manager', 'support', 'viewer') NOT NULL DEFAULT 'viewer',
                    date_created DATETIME NOT NULL,
                    date_updated DATETIME NULL DEFAULT NULL,
                    is_active BOOLEAN NOT NULL DEFAULT TRUE,
                    last_login_date DATETIME NULL,
                    UNIQUE INDEX idx_email (email_address),
                    INDEX idx_business_user (business_id),
                    CONSTRAINT fk_user_business FOREIGN KEY (business_id) 
                        REFERENCES ar_business(business_id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                
                echo "<p>User table created</p>";
            }
            echo "<p>Database tables verified</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>Error checking database tables: " . $e->getMessage() . "</p>";
            return false;
        }
        
        // Begin transaction
        $db->beginTransaction();
        echo "<p>Transaction started</p>";
        
        try {
            // Check if email already exists
            $checkEmailSql = "SELECT COUNT(*) FROM ar_user WHERE email_address = ?";
            $checkStmt = $db->prepare($checkEmailSql);
            $checkStmt->execute([$email]);
            $emailExists = (int)$checkStmt->fetchColumn() > 0;
            
            if ($emailExists) {
                $db->rollBack();
                echo "<p style='color:red'>Email already exists: " . $email . "</p>";
                return false;
            }
            
            // 1. First create the business record
            $createBusinessSql = "INSERT INTO ar_business (business_name, subscription_type, business_status, date_created) VALUES (?, ?, ?, NOW())";
            $businessStmt = $db->prepare($createBusinessSql);
            echo "<p>Executing business creation query</p>";
            $businessStmt->execute([
                $businessName,
                'free',  // Default subscription plan
                'trialing'  // Default status
            ]);
            
            // Get the new business ID
            $businessId = $db->lastInsertId();
            echo "<p>Business created with ID: " . $businessId . "</p>";
            
            if (!$businessId) {
                throw new Exception('Failed to create business record');
            }
            
            // 2. Now create the user record
            $createUserSql = "INSERT INTO ar_user (business_id, full_name, email_address, password_hash, user_role, date_created) VALUES (?, ?, ?, ?, ?, NOW())";
            $userStmt = $db->prepare($createUserSql);
            echo "<p>Executing user creation query</p>";
            $userStmt->execute([
                $businessId,
                $fullName,
                $email,
                password_hash($password, PASSWORD_BCRYPT), // Securely hash the password
                'admin'  // First user is always admin
            ]);
            
            // Get the new user ID
            $userId = $db->lastInsertId();
            echo "<p>User created with ID: " . $userId . "</p>";
            
            if (!$userId) {
                throw new Exception('Failed to create user record');
            }
            
            // Commit the transaction
            $db->commit();
            echo "<p style='color:green'>Transaction committed successfully</p>";
            
            // Generate JWT token
            echo "<p>Generating JWT token</p>";
            $token = generateJWT($userId, $businessId, 'admin');
            echo "<p>JWT token generated</p>";
            
            // Prepare response data
            $responseData = [
                'message' => 'Registration successful',
                'user' => [
                    'id' => $userId,
                    'name' => $fullName,
                    'email' => $email,
                    'role' => 'admin',
                    'business_id' => $businessId
                ],
                'business' => [
                    'id' => $businessId,
                    'name' => $businessName,
                    'subscription_plan' => 'free',
                    'status' => 'trialing'
                ],
                'token' => $token
            ];
            
            echo "<h2 style='color:green'>Registration Successful!</h2>";
            echo "<pre>" . print_r($responseData, true) . "</pre>";
            
            return $responseData;
            
        } catch (PDOException $e) {
            // Rollback transaction on database error
            $db->rollBack();
            echo "<p style='color:red'>Database error during registration: " . $e->getMessage() . "</p>";
            
            // Check for duplicate email (MySQL error code 1062 for duplicate entry)
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "<p style='color:red'>Email address is already registered</p>";
            } else {
                echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
            }
            return false;
        }
    } catch (Exception $e) {
        // Rollback transaction if there was an error
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        
        echo "<p style='color:red'>General error during registration: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Test data
$businessName = 'Test Business';
$fullName = 'Test User';
$email = 'test@example.com';
$password = 'password123';

// HTML header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Direct Registration Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .form { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        input, button { margin: 5px 0; padding: 8px; width: 100%; }
        button { background: #4CAF50; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Direct Registration Test</h1>";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $businessName = $_POST['business_name'] ?? 'Test Business';
    $fullName = $_POST['full_name'] ?? 'Test User';
    $email = $_POST['email'] ?? 'test@example.com';
    $password = $_POST['password'] ?? 'password123';
    
    // Register the user
    registerUser($businessName, $fullName, $email, $password);
} else {
    // Display the form
    echo "<div class='form'>
        <form method='post'>
            <h2>Register New User</h2>
            <input type='text' name='business_name' placeholder='Business Name' value='$businessName' required>
            <input type='text' name='full_name' placeholder='Full Name' value='$fullName' required>
            <input type='email' name='email' placeholder='Email' value='$email' required>
            <input type='password' name='password' placeholder='Password' value='$password' required>
            <button type='submit'>Register</button>
        </form>
    </div>";
}

echo "</body></html>";
?>
