<?php
/**
 * Reviews API Endpoint
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the direct CORS handler
require_once __DIR__ . '/../direct_cors.php';

// Set content type to JSON
header('Content-Type: application/json');

// Include mock data
require_once __DIR__ . '/mock_data.php';

// Get page parameter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get reviews data
$reviewsData = getMockReviews($page);

// Return response
echo json_encode($reviewsData);
