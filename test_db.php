<?php
// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
require_once __DIR__ . '/backend/config/database.php';

// Test database connection
try {
    $db = getDbConnection();
    echo "Database connection successful!<br>";
    
    // Check if tables exist
    $tables = ['ar_business', 'ar_user'];
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $db->query($query);
        $exists = $stmt->rowCount() > 0;
        echo "Table $table " . ($exists ? "exists" : "does not exist") . "<br>";
    }
    
    // If tables don't exist, run the setup script
    if (!$exists) {
        echo "Running database setup...<br>";
        require_once __DIR__ . '/backend/database/setup.php';
        setupDatabase();
        echo "Database setup complete.<br>";
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>
