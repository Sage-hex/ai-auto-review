<?php
/**
 * JWT Authentication Utilities
 * 
 * This file contains functions for JWT token generation and validation.
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Generate a JWT token for a user
 * 
 * @param int $userId User ID
 * @param int $businessId Business ID
 * @param string $role User role
 * @return string JWT token
 */
function generateJWT($userId, $businessId, $role) {
    $issuedAt = time();
    $expiryTime = $issuedAt + JWT_EXPIRY;
    
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expiryTime,
        'user_id' => $userId,
        'business_id' => $businessId,
        'role' => $role
    ];
    
    $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64_encode(json_encode($payload));
    
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = base64_encode($signature);
    
    return "$header.$payload.$signature";
}

/**
 * Validate a JWT token
 * 
 * @param string $token JWT token
 * @return array|bool User data if valid, false if invalid
 */
function validateJWT($token) {
    $parts = explode('.', $token);
    
    if (count($parts) !== 3) {
        return false;
    }
    
    list($header, $payload, $signature) = $parts;
    
    $verifySignature = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    
    if ($signature !== $verifySignature) {
        return false;
    }
    
    $payload = json_decode(base64_decode($payload), true);
    
    if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
        return false;
    }
    
    return [
        'user_id' => $payload['user_id'],
        'business_id' => $payload['business_id'],
        'role' => $payload['role']
    ];
}

/**
 * Get JWT token from Authorization header
 * 
 * @return string|bool Token if found, false if not
 */
function getJWTFromHeader() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        return false;
    }
    
    $authHeader = $headers['Authorization'];
    
    if (strpos($authHeader, 'Bearer ') !== 0) {
        return false;
    }
    
    return substr($authHeader, 7);
}

/**
 * Authenticate the current request using JWT
 * 
 * @return array|bool User data if authenticated, false if not
 */
function authenticateRequest() {
    $token = getJWTFromHeader();
    
    if (!$token) {
        return false;
    }
    
    return validateJWT($token);
}

/**
 * Check if user has required role
 * 
 * @param array $userData User data from authenticateRequest()
 * @param array $allowedRoles Array of allowed roles
 * @return bool True if authorized, false if not
 */
function authorizeRole($userData, $allowedRoles) {
    if (!$userData || !isset($userData['role'])) {
        return false;
    }
    
    return in_array($userData['role'], $allowedRoles);
}
