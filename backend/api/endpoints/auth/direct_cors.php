<?php
/**
 * Direct CORS Handler for Auth Endpoints
 * 
 * This file directly sets CORS headers for auth endpoints without any dependencies
 */

// Log for debugging
error_log("Direct CORS handler called");

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
    
    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
