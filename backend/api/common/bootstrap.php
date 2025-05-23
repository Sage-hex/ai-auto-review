<?php
/**
 * API Bootstrap
 * 
 * Common bootstrap file for all API endpoints
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser, but log them

// Include necessary files
require_once __DIR__ . '/init.php';

// Include CORS headers utility
require_once __DIR__ . '/cors.php';

// Set content type to JSON for all API responses
header('Content-Type: application/json');

// Start output buffering to prevent any unwanted output
ob_start();

// Parse request data
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim(substr($uri, strpos($uri, '/api/') + 5), '/'));

// Get the raw input data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Default user data for development (when not authenticated)
$userData = [
    'user_id' => 1,
    'business_id' => 1,
    'role' => 'admin'
];
