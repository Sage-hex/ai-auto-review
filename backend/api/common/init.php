<?php
/**
 * API Initialization
 * 
 * This file initializes the API environment and provides common utilities.
 */

// Include database configuration
require_once __DIR__ . '/../../config/database.php';

// The Content-Type header is set in bootstrap.php
// CORS headers are applied by the cors.php middleware

// Database connection
function getDbConnection() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO(
                'mysql:host=localhost;dbname=aiautoreview;charset=utf8mb4',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            sendErrorResponse('Database connection failed', 500);
        }
    }
    
    return $db;
}

// Response utilities
function sendSuccessResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
    exit;
}

function sendErrorResponse($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

function sendValidationErrorResponse($errors, $statusCode = 422) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit;
}

// JWT utilities
function generateJWT($userId, $businessId, $role) {
    $header = base64_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));
    
    $issuedAt = time();
    $expiresAt = $issuedAt + (60 * 60 * 24 * 7); // 7 days
    
    $payload = base64_encode(json_encode([
        'sub' => $userId,
        'bid' => $businessId,
        'role' => $role,
        'iat' => $issuedAt,
        'exp' => $expiresAt
    ]));
    
    $secret = 'your-secret-key-change-this-in-production';
    
    $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    
    return "$header.$payload.$signature";
}

function verifyJWT($token) {
    if (empty($token)) {
        return false;
    }
    
    $parts = explode('.', $token);
    
    if (count($parts) !== 3) {
        return false;
    }
    
    list($header, $payload, $signature) = $parts;
    
    $secret = 'your-secret-key-change-this-in-production';
    
    $expectedSignature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    
    if ($signature !== $expectedSignature) {
        return false;
    }
    
    $decodedPayload = json_decode(base64_decode($payload), true);
    
    if (!$decodedPayload || $decodedPayload['exp'] < time()) {
        return false;
    }
    
    return $decodedPayload;
}
