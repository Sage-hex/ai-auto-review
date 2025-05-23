<?php
/**
 * Direct Registration Test
 * 
 * This script directly tests the registration process without going through the API
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/utils/jwt.php';

echo "<h2>Direct Registration Test</h2>";

// Test data
$testData = [
    'business_name' => 'Test Business',
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123'
];

echo "<h3>Test Data:</h3>";
echo "<pre>";
print_r($testData);
echo "</pre>";

// Get database connection
$db = getDbConnection();
if (!$db) {
    echo "<p style='color:red'>Database connection failed!</p>";
    exit;
}
echo "<p style='color:green'>Database connection successful!</p>";

// Check if tables exist
try {
    $tables = ['ar_business', 'ar_user'];
    $existingTables = [];
    $missingTables = [];
    
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $db->query($query);
        
        if ($stmt->rowCount() > 0) {
            $existingTables[] = $table;
        } else {
            $missingTables[] = $table;
        }
    }
    
    echo "<h3>Database Tables Check</h3>";
    
    if (empty($missingTables)) {
        echo "<p style='color:green'>All required tables exist!</p>";
    } else {
        echo "<p style='color:red'>Missing tables: " . implode(', ', $missingTables) . "</p>";
        echo "<p>Please run the setup_database.php script first to create the tables.</p>";
        exit;
    }
    
    // Begin transaction
    $db->beginTransaction();
    echo "<p>Transaction started...</p>";
    
    try {
        // Check if email already exists
        $checkEmailSql = "SELECT COUNT(*) FROM ar_user WHERE email_address = ?";
        $checkStmt = $db->prepare($checkEmailSql);
        $checkStmt->execute([$testData['email']]);
        $emailExists = (int)$checkStmt->fetchColumn() > 0;
        
        if ($emailExists) {
            echo "<p style='color:orange'>Email already exists: " . $testData['email'] . "</p>";
            echo "<p>Using a different email for testing...</p>";
            $testData['email'] = 'test' . time() . '@example.com';
        }
        
        // 1. First create the business record
        $createBusinessSql = "INSERT INTO ar_business (business_name, subscription_type, business_status, date_created) VALUES (?, ?, ?, NOW())";
        $businessStmt = $db->prepare($createBusinessSql);
        echo "<p>Executing business creation query...</p>";
        $businessStmt->execute([
            $testData['business_name'],
            'free',  // Default subscription plan
            'trialing'  // Default status
        ]);
        
        // Get the new business ID
        $businessId = $db->lastInsertId();
        echo "<p style='color:green'>Business created with ID: " . $businessId . "</p>";
        
        if (!$businessId) {
            throw new Exception('Failed to create business record');
        }
        
        // 2. Now create the user record
        $createUserSql = "INSERT INTO ar_user (business_id, full_name, email_address, password_hash, user_role, date_created) VALUES (?, ?, ?, ?, ?, NOW())";
        $userStmt = $db->prepare($createUserSql);
        echo "<p>Executing user creation query...</p>";
        $userStmt->execute([
            $businessId,
            $testData['name'],
            $testData['email'],
            password_hash($testData['password'], PASSWORD_BCRYPT), // Securely hash the password
            'admin'  // First user is always admin
        ]);
        
        // Get the new user ID
        $userId = $db->lastInsertId();
        echo "<p style='color:green'>User created with ID: " . $userId . "</p>";
        
        if (!$userId) {
            throw new Exception('Failed to create user record');
        }
        
        // Commit the transaction
        $db->commit();
        echo "<p style='color:green'>Transaction committed successfully!</p>";
        
        // Generate JWT token
        echo "<p>Generating JWT token...</p>";
        $token = generateJWT($userId, $businessId, 'admin');
        echo "<p style='color:green'>JWT token generated successfully!</p>";
        echo "<p>Token: " . substr($token, 0, 20) . "..." . "</p>";
        
        echo "<h3>Registration Test Successful!</h3>";
        echo "<p>The registration process works correctly when tested directly.</p>";
        echo "<p>This suggests that the issue might be with the API endpoint or the way the frontend is calling it.</p>";
        
    } catch (Exception $e) {
        // Rollback transaction if there was an error
        $db->rollBack();
        echo "<h3 style='color:red'>Registration Test Failed!</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        
        if ($e instanceof PDOException) {
            echo "<p>SQL State: " . $e->getCode() . "</p>";
            echo "<p>Driver Error Code: " . $e->errorInfo[1] . "</p>";
            echo "<p>Driver Error Message: " . $e->errorInfo[2] . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>Test Failed!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
