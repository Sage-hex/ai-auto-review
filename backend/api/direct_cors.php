<?php
/**
 * Direct CORS Handler
 * 
 * This file directly sets CORS headers without any dependencies
 */

// Log for debugging with file info
error_log("Direct CORS handler called from file: " . __FILE__);
error_log("Called by: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Unknown'));

// Verbose logging of included file
error_log("The full path of this CORS handler is: " . __FILE__);

// Only set CORS headers if they haven't been sent already
if (!headers_sent()) {
    // Clear any existing headers to avoid conflicts
    header_remove('Access-Control-Allow-Origin');
    
    // Set CORS headers
    header('Access-Control-Allow-Origin: http://localhost:5173');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 3600'); // Cache preflight for 1 hour
    
    // Log for debugging
    error_log("CORS headers set by direct_cors.php");
    error_log("Access-Control-Allow-Origin header set to: http://localhost:5173");
    
    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
