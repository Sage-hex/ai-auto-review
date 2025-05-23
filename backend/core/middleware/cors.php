<?php
/**
 * CORS Middleware
 * 
 * Handles Cross-Origin Resource Sharing (CORS) headers for all API endpoints
 */

/**
 * Apply CORS headers to the response
 * 
 * @param string $allowedOrigin Origin to allow (default *)
 * @return void
 */
function applyCorsHeaders($allowedOrigin = '*') {
    // Check if origin header exists
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Use the client's requested origin if we're allowing all origins
        $origin = $allowedOrigin === '*' ? $_SERVER['HTTP_ORIGIN'] : $allowedOrigin;
        header("Access-Control-Allow-Origin: {$origin}");
    } else {
        header("Access-Control-Allow-Origin: {$allowedOrigin}");
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400'); // 24 hours cache for preflight requests
    header('Access-Control-Allow-Credentials: true');
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
