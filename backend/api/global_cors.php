<?php
/**
 * Global CORS Handler
 * 
 * This file handles Cross-Origin Resource Sharing (CORS) for all API endpoints
 * Place this at the beginning of all API files
 */

// Always clear any previous headers to prevent conflicts
if (!headers_sent()) {
    // Allow requests from your frontend
    header('Access-Control-Allow-Origin: http://localhost:5173');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 3600'); // Cache preflight for 1 hour
    
    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
