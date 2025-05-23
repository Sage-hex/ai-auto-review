<?php
/**
 * Test Registration Script
 * 
 * This script tests the registration process directly
 */

// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once __DIR__ . '/backend/config/database.php';

// Sample registration data
$data = [
    'business_name' => 'Test Business',
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123'
];

echo "<h1>Registration Test</h1>";

// Test database connection
try {
    $db = getDbConnection();
    if ($db) {
        echo "<p style='color:green'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color:red'>× Database connection failed</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color:red'>× Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Start transaction
try {
    echo "<h2>Starting Registration Process</h2>";
    
    $db->beginTransaction();
    
    // 1. First create the business record
    echo "<p>Creating business record...</p>";
    
    $createBusinessSql = "INSERT INTO ar_business (business_name, subscription_type, business_status, date_created) VALUES (?, ?, ?, NOW())";
    $businessStmt = $db->prepare($createBusinessSql);
    $businessStmt->execute([
        $data['business_name'],
        'free',  // Default subscription plan
        'trialing'  // Default status
    ]);
    
    // Get the new business ID
    $businessId = $db->lastInsertId();
    
    if (!$businessId) {
        throw new Exception('Failed to create business record');
    }
    
    echo "<p style='color:green'>✓ Business created with ID: $businessId</p>";
    
    // 2. Now create the user record
    echo "<p>Creating user record...</p>";
    
    $createUserSql = "INSERT INTO ar_user (business_id, full_name, email_address, password_hash, user_role, date_created) VALUES (?, ?, ?, ?, ?, NOW())";
    $userStmt = $db->prepare($createUserSql);
    $userStmt->execute([
        $businessId,
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT), // Securely hash the password
        'admin'  // First user is always admin
    ]);
    
    // Get the new user ID
    $userId = $db->lastInsertId();
    
    if (!$userId) {
        throw new Exception('Failed to create user record');
    }
    
    echo "<p style='color:green'>✓ User created with ID: $userId</p>";
    
    // Test if generateJWT function exists
    echo "<p>Testing JWT generation...</p>";
    
    if (file_exists(__DIR__ . '/backend/utils/jwt.php')) {
        echo "<p style='color:green'>✓ JWT utility file exists</p>";
        
        // Include JWT file
        require_once __DIR__ . '/backend/utils/jwt.php';
        
        if (function_exists('generateJWT')) {
            echo "<p style='color:green'>✓ generateJWT function exists</p>";
            
            try {
                $token = generateJWT($userId, $businessId, 'admin');
                echo "<p style='color:green'>✓ JWT token generated successfully</p>";
                echo "<pre>$token</pre>";
            } catch (Exception $e) {
                echo "<p style='color:red'>× Failed to generate JWT: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color:red'>× generateJWT function doesn't exist</p>";
        }
    } else {
        echo "<p style='color:red'>× JWT utility file doesn't exist</p>";
    }
    
    // Commit the transaction
    $db->commit();
    echo "<p style='color:green'>✓ Transaction committed successfully</p>";
    
    echo "<h2>Registration Test Completed Successfully</h2>";
    
} catch (Exception $e) {
    // Rollback the transaction
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
        echo "<p style='color:red'>× Transaction rolled back</p>";
    }
    
    echo "<p style='color:red'>× Error: " . $e->getMessage() . "</p>";
    
    // Show stack trace for debugging
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
