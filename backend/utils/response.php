<?php
/**
 * API Response Utilities
 * 
 * This file contains functions for standardized API responses.
 */

/**
 * Send a JSON response
 * 
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 * @param string $message Response message
 */
function sendResponse($data = null, $statusCode = 200, $message = 'Success') {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'status' => $statusCode < 400 ? 'success' : 'error',
        'message' => $message,
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Send a success response
 * 
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 * @param string $message Success message
 */
function sendSuccessResponse($data = null, $statusCode = 200, $message = 'Success') {
    sendResponse($data, $statusCode, $message);
}

/**
 * Send an error response
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @param mixed $errors Additional error details
 */
function sendErrorResponse($message = 'An error occurred', $statusCode = 400, $errors = null) {
    $data = null;
    
    if ($errors !== null) {
        $data = ['errors' => $errors];
    }
    
    sendResponse($data, $statusCode, $message);
}

/**
 * Send a not found response
 * 
 * @param string $message Not found message
 */
function sendNotFoundResponse($message = 'Resource not found') {
    sendErrorResponse($message, 404);
}

/**
 * Send an unauthorized response
 * 
 * @param string $message Unauthorized message
 */
function sendUnauthorizedResponse($message = 'Unauthorized access') {
    sendErrorResponse($message, 401);
}

/**
 * Send a forbidden response
 * 
 * @param string $message Forbidden message
 */
function sendForbiddenResponse($message = 'Access forbidden') {
    sendErrorResponse($message, 403);
}

/**
 * Send a validation error response
 * 
 * @param array $errors Validation errors
 * @param string $message Validation error message
 */
function sendValidationErrorResponse($errors, $message = 'Validation failed') {
    sendErrorResponse($message, 422, $errors);
}
