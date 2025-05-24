<?php
/**
 * Email Configuration
 * 
 * This file contains email configuration settings for the application.
 * All sensitive information is loaded from environment variables.
 */

// Include environment loader
require_once __DIR__ . '/env.php';

// Email sender information
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'AI Auto Review'));
define('MAIL_FROM_EMAIL', env('MAIL_FROM_EMAIL', 'noreply@aiautoreview.com'));

// SMTP Configuration
define('USE_SMTP', env('USE_SMTP', false)); // Set to true to use SMTP instead of PHP mail() function
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com')); // SMTP server
define('SMTP_PORT', env('SMTP_PORT', 587)); // Port 587 for TLS
define('SMTP_USERNAME', env('SMTP_USERNAME', '')); // Email username
define('SMTP_PASSWORD', env('SMTP_PASSWORD', '')); // Email password
define('SMTP_SECURE', env('SMTP_SECURE', 'tls')); // Use 'tls' for TLS, 'ssl' for SSL

// Mail headers
function getEmailHeaders() {
    return [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_EMAIL . '>',
        'X-Mailer: PHP/' . phpversion()
    ];
}
