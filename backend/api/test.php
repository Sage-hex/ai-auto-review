<?php
// Set CORS headers directly
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 3600');

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Test response
$response = [
    'success' => true,
    'message' => 'API test successful',
    'cors' => 'CORS headers are working',
    'time' => date('Y-m-d H:i:s'),
];

// Return response
echo json_encode($response);
?>
