<?php
/**
 * Application Configuration
 * 
 * This file contains the main configuration settings for the application.
 */

// Application settings
define('APP_NAME', 'AI Auto Review');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/AiAutoReview');
define('API_URL', APP_URL . '/backend/api');

// JWT Authentication settings
define('JWT_SECRET', 'your_jwt_secret_key_here'); // Change this in production
define('JWT_EXPIRY', 86400); // 24 hours in seconds

// Google Gemini API settings
define('GEMINI_API_KEY', 'AIzaSyAPlReHI0CBsYGn8-orNTF25BoHfbGJsvs'); // Gemini API key
define('GEMINI_MODEL', 'gemini-1.5-pro'); // You can also use gemini-1.0-pro or gemini-1.5-flash for different performance/cost tradeoffs

// Subscription plans
define('PLANS', [
    'free' => [
        'name' => 'Free Trial',
        'price' => 0,
        'review_limit' => 50,
        'user_limit' => 2,
        'platforms' => ['google'],
        'features' => ['ai_responses', 'basic_analytics']
    ],
    'basic' => [
        'name' => 'Basic',
        'price' => 29.99,
        'review_limit' => 200,
        'user_limit' => 5,
        'platforms' => ['google', 'yelp'],
        'features' => ['ai_responses', 'basic_analytics', 'response_approval']
    ],
    'pro' => [
        'name' => 'Professional',
        'price' => 99.99,
        'review_limit' => 1000,
        'user_limit' => 15,
        'platforms' => ['google', 'yelp', 'facebook'],
        'features' => ['ai_responses', 'advanced_analytics', 'response_approval', 'scheduled_responses', 'custom_templates']
    ]
]);

// Error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('UTC');

// Include database configuration
require_once __DIR__ . '/database.php';
