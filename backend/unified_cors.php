<?php
/**
 * Unified CORS Handler
 * 
 * This is the ONLY CORS handler that should be used across the entire application.
 * It ensures that CORS headers are set exactly once to prevent duplication.
 */

// Use a static variable to track if CORS headers have been set
static $corsHeadersSet = false;

// Function to set CORS headers exactly once
function setCorsHeaders() {
    global $corsHeadersSet;
    
    // Only set headers if they haven't been set already and headers haven't been sent
    if (!$corsHeadersSet && !headers_sent()) {
        // Set CORS headers for development
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 3600'); // Cache preflight for 1 hour
        
        // Mark that we've set the headers
        $corsHeadersSet = true;
        
        // Log for debugging
        error_log("CORS headers set by unified_cors.php");
        
        // Handle preflight OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}

// Set CORS headers immediately when this file is included
setCorsHeaders();
