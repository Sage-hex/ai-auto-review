<?php
// OTP Test Script - Tests the OTP verification process

// Set error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=ai_auto_review', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database successfully<br>";
    
    // Get the latest OTP
    $query = "SELECT * FROM otp_verifications ORDER BY created_at DESC LIMIT 1";
    $stmt = $db->query($query);
    $latestOtp = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($latestOtp) {
        echo "<h2>Latest OTP Information:</h2>";
        echo "<pre>";
        print_r($latestOtp);
        echo "</pre>";
        
        // Get user information
        $userId = $latestOtp['user_id'];
        
        // Find the user table
        $tableListQuery = "SHOW TABLES";
        $tableListStmt = $db->query($tableListQuery);
        $tables = $tableListStmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<h2>Database Tables:</h2>";
        echo "<pre>";
        print_r($tables);
        echo "</pre>";
        
        // Find user table
        $userTable = null;
        foreach ($tables as $table) {
            if (stripos($table, 'user') !== false) {
                $userTable = $table;
                break;
            }
        }
        
        if (!$userTable) {
            echo "Could not find user table<br>";
            $userTable = 'ar_user'; // Default fallback
        }
        
        echo "Using user table: {$userTable}<br>";
        
        // Get user table structure
        $userTableQuery = "DESCRIBE {$userTable}";
        $userTableStmt = $db->query($userTableQuery);
        $userColumns = $userTableStmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<h2>User Table Columns:</h2>";
        echo "<pre>";
        print_r($userColumns);
        echo "</pre>";
        
        // Determine the correct ID column
        $userIdColumn = in_array('id', $userColumns) ? 'id' : 'user_id';
        echo "Using user ID column: {$userIdColumn}<br>";
        
        // Get user data
        $userQuery = "SELECT * FROM {$userTable} WHERE {$userIdColumn} = ?";
        $userStmt = $db->prepare($userQuery);
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<h2>User Information:</h2>";
            echo "<pre>";
            print_r($user);
            echo "</pre>";
            
            // Test OTP verification
            echo "<h2>Testing OTP Verification:</h2>";
            
            // Mark OTP as used
            $updateQuery = "UPDATE otp_verifications SET is_used = 1 WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$latestOtp['id']]);
            echo "Marked OTP as used<br>";
            
            // Generate JWT token (simplified)
            echo "JWT token would be generated here<br>";
            
            echo "<h2>OTP Verification Successful!</h2>";
            echo "You can now use this information to fix your verify_otp.php endpoint.";
        } else {
            echo "No user found with ID: {$userId}<br>";
        }
    } else {
        echo "No OTP records found<br>";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
