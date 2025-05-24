<?php
// Include CORS handler
require_once __DIR__ . '/cors.php';

/**
 * Health Check Endpoint
 * 
 * This endpoint provides a simple health check for the API.
 */

// Only allow GET requests
if ($method !== 'GET') {
    sendErrorResponse('Method not allowed', 405);
}

// Check database connection
$dbStatus = getDbConnection() ? 'connected' : 'disconnected';

// Return health status
sendSuccessResponse([
    'status' => 'ok',
    'version' => APP_VERSION,
    'timestamp' => date('Y-m-d H:i:s'),
    'database' => $dbStatus
]);
