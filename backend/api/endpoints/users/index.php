<?php
// Include CORS handler
require_once __DIR__ . '/cors.php';

/**
 * Users Endpoints
 * 
 * These endpoints handle user-related operations.
 */

// Load required models
require_once __DIR__ . '/../../../models/User.php';
require_once __DIR__ . '/../../../models/Business.php';

$userModel = new User();
$businessModel = new Business();

// Get business ID from authenticated user
$businessId = $userData['business_id'];

// Check if user has permission to access user management
// Only admin and manager roles can access user management
if (!in_array($userData['role'], ['admin', 'manager'])) {
    sendForbiddenResponse('You do not have permission to access user management');
}

// Route based on request method and path
if ($method === 'GET' && count($pathParts) === 1) {
    // GET /users - List users for the business
    $users = $userModel->getByBusinessId($businessId);
    
    // Remove sensitive information
    $users = array_map(function($user) {
        unset($user['password_hash']);
        return $user;
    }, $users);
    
    sendSuccessResponse($users);
} elseif ($method === 'POST' && count($pathParts) === 1) {
    // POST /users - Create a new user
    
    // Only admin can create users
    if ($userData['role'] !== 'admin') {
        sendForbiddenResponse('Only admins can create users');
    }
    
    // Check if business has reached user limit
    if ($businessModel->hasReachedUserLimit($businessId)) {
        sendErrorResponse('You have reached the maximum number of users allowed for your subscription plan', 403);
    }
    
    // Validate required fields
    $requiredFields = ['name', 'email', 'password', 'role'];
    $errors = [];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = ucfirst($field) . ' is required';
        }
    }
    
    if (!empty($errors)) {
        sendValidationErrorResponse($errors);
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        sendValidationErrorResponse(['email' => 'Invalid email format']);
    }
    
    // Validate role
    $allowedRoles = ['admin', 'manager', 'support', 'viewer'];
    if (!in_array($data['role'], $allowedRoles)) {
        sendValidationErrorResponse(['role' => 'Invalid role']);
    }
    
    // Create user
    $userId = $userModel->create(
        $businessId,
        $data['name'],
        $data['email'],
        $data['password'],
        $data['role']
    );
    
    if (!$userId) {
        sendErrorResponse('Failed to create user. Email may already be in use.', 500);
    }
    
    // Get created user
    $user = $userModel->getById($userId);
    
    // Remove sensitive information
    unset($user['password_hash']);
    
    sendSuccessResponse($user, 201, 'User created successfully');
} elseif ($method === 'GET' && count($pathParts) === 2) {
    // GET /users/{id} - Get user details
    $userId = intval($pathParts[1]);
    
    // Get user
    $user = $userModel->getById($userId);
    
    if (!$user) {
        sendNotFoundResponse('User not found');
    }
    
    // Verify user belongs to the same business
    if ($user['business_id'] !== $businessId) {
        sendForbiddenResponse('You do not have permission to access this user');
    }
    
    // Remove sensitive information
    unset($user['password_hash']);
    
    sendSuccessResponse($user);
} elseif ($method === 'PUT' && count($pathParts) === 2) {
    // PUT /users/{id} - Update user
    $userId = intval($pathParts[1]);
    
    // Get user
    $user = $userModel->getById($userId);
    
    if (!$user) {
        sendNotFoundResponse('User not found');
    }
    
    // Verify user belongs to the same business
    if ($user['business_id'] !== $businessId) {
        sendForbiddenResponse('You do not have permission to update this user');
    }
    
    // Only admin can update other users' roles
    // Managers can update other fields but not roles
    if ($userData['role'] !== 'admin' && isset($data['role'])) {
        sendForbiddenResponse('Only admins can update user roles');
    }
    
    // Users can't update their own role
    if ($userId === $userData['user_id'] && isset($data['role'])) {
        sendForbiddenResponse('You cannot change your own role');
    }
    
    // Validate role if provided
    if (isset($data['role'])) {
        $allowedRoles = ['admin', 'manager', 'support', 'viewer'];
        if (!in_array($data['role'], $allowedRoles)) {
            sendValidationErrorResponse(['role' => 'Invalid role']);
        }
    }
    
    // Prepare update data
    $updateData = [];
    $allowedFields = ['name', 'email', 'role', 'password'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field]) && !empty($data[$field])) {
            $updateData[$field] = $data[$field];
        }
    }
    
    if (empty($updateData)) {
        sendValidationErrorResponse(['error' => 'No valid fields to update']);
    }
    
    // Update user
    $success = $userModel->update($userId, $updateData);
    
    if (!$success) {
        sendErrorResponse('Failed to update user', 500);
    }
    
    // Get updated user
    $user = $userModel->getById($userId);
    
    // Remove sensitive information
    unset($user['password_hash']);
    
    sendSuccessResponse($user, 200, 'User updated successfully');
} elseif ($method === 'DELETE' && count($pathParts) === 2) {
    // DELETE /users/{id} - Delete user
    $userId = intval($pathParts[1]);
    
    // Only admin can delete users
    if ($userData['role'] !== 'admin') {
        sendForbiddenResponse('Only admins can delete users');
    }
    
    // Get user
    $user = $userModel->getById($userId);
    
    if (!$user) {
        sendNotFoundResponse('User not found');
    }
    
    // Verify user belongs to the same business
    if ($user['business_id'] !== $businessId) {
        sendForbiddenResponse('You do not have permission to delete this user');
    }
    
    // Users can't delete themselves
    if ($userId === $userData['user_id']) {
        sendForbiddenResponse('You cannot delete your own account');
    }
    
    // Delete user
    $success = $userModel->delete($userId);
    
    if (!$success) {
        sendErrorResponse('Failed to delete user', 500);
    }
    
    sendSuccessResponse(null, 200, 'User deleted successfully');
} else {
    sendNotFoundResponse('Endpoint not found');
}
