<?php
/**
 * CORS Handler
 * 
 * This file handles Cross-Origin Resource Sharing (CORS) for all API endpoints
 */

// Define a unique key for checking if CORS headers have been sent
define('CORS_HEADERS_SET', 'cors_headers_already_set');

// Only set CORS headers if they haven't been sent already
if (!headers_sent() && !isset($GLOBALS[CORS_HEADERS_SET])) {
    // For development, just allow the Vite dev server
    header('Access-Control-Allow-Origin: http://localhost:5173');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 3600'); // Cache preflight for 1 hour
    
    // Mark that we've set CORS headers to prevent duplication
    $GLOBALS[CORS_HEADERS_SET] = true;
    
    // Log for debugging
    error_log("CORS headers set in cors.php");
    
    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
