<?php
/**
 * Test Endpoint
 * 
 * This endpoint is used to test if the API is accessible and functioning correctly.
 */

// Enable CORS for all origins during development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Test database connection
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=aiautoreview;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    $dbStatus = 'connected';
} catch (PDOException $e) {
    $dbStatus = 'error: ' . $e->getMessage();
}

// Return API status
echo json_encode([
    'status' => 'success',
    'message' => 'API is working correctly',
    'timestamp' => date('Y-m-d H:i:s'),
    'database' => $dbStatus,
    'php_version' => PHP_VERSION
]);
?>
