<?php
/**
 * CORS Test Endpoint
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the request
error_log("CORS test endpoint accessed at: " . __FILE__);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

// Include the direct CORS handler
require_once __DIR__ . '/direct_cors.php';

// Set content type to JSON
header('Content-Type: application/json');

// Return success response
echo json_encode(['success' => true, 'message' => 'CORS headers have been set correctly']);
