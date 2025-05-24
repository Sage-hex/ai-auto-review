<?php
/**
 * Mailgun Test Script
 * 
 * This script tests the Mailgun API integration to ensure emails can be sent
 */

// Include environment variables
require_once __DIR__ . '/backend/config/env.php';

// Set error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mailgun API details from environment variables
$mailgunApiKey = getenv('MAILGUN_API_KEY');
$mailgunDomain = getenv('MAILGUN_DOMAIN');
$fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@aiautoreview.com';
$fromName = getenv('MAIL_FROM_NAME') ?: 'AI Auto Review';

// Test recipient - replace with your email
$recipientEmail = 'realitynuel@gmail.com'; // Your email to receive the test

echo "<h1>Mailgun Test</h1>";
echo "<p>Testing email sending with Mailgun...</p>";

// Function to send email using Mailgun API
function sendMailgunEmail($to, $subject, $html, $text, $from) {
    global $mailgunApiKey, $mailgunDomain;
    
    echo "<p>Mailgun API Key: " . substr($mailgunApiKey, 0, 8) . "..." . substr($mailgunApiKey, -8) . "</p>";
    echo "<p>Mailgun Domain: " . $mailgunDomain . "</p>";
    
    // Prepare the API URL
    $url = "https://api.mailgun.net/v3/{$mailgunDomain}/messages";
    
    // Prepare the form data
    $data = array(
        'from' => $from,
        'to' => $to,
        'subject' => $subject,
        'html' => $html,
        'text' => $text
    );
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "api:{$mailgunApiKey}");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    
    // Execute the request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    // Close cURL
    curl_close($ch);
    
    // Debug information
    echo "<h3>cURL Response:</h3>";
    echo "<pre>" . print_r($response, true) . "</pre>";
    
    if ($error) {
        echo "<h3>cURL Error:</h3>";
        echo "<pre>" . print_r($error, true) . "</pre>";
    }
    
    echo "<h3>cURL Info:</h3>";
    echo "<pre>" . print_r($info, true) . "</pre>";
    
    return $response;
}

// Create email content
$subject = "Mailgun Test Email";
$html = "<html><body><h1>Test Email</h1><p>This is a test email sent via Mailgun API at " . date('Y-m-d H:i:s') . "</p></body></html>";
$text = "Test Email\n\nThis is a test email sent via Mailgun API at " . date('Y-m-d H:i:s');
$from = "{$fromName} <{$fromEmail}>";

echo "<p>Sending test email to: " . $recipientEmail . "</p>";

// Send the test email
$result = sendMailgunEmail($recipientEmail, $subject, $html, $text, $from);

echo "<h2>Result:</h2>";
echo "<pre>" . print_r(json_decode($result, true), true) . "</pre>";

// Also test if we can load the environment variables
echo "<h2>Environment Variables Test:</h2>";
echo "<p>MAILGUN_API_KEY: " . (getenv('MAILGUN_API_KEY') ? "✓ Loaded" : "✗ Not loaded") . "</p>";
echo "<p>MAILGUN_DOMAIN: " . (getenv('MAILGUN_DOMAIN') ? "✓ Loaded" : "✗ Not loaded") . "</p>";
echo "<p>SMTP_USERNAME: " . (getenv('SMTP_USERNAME') ? "✓ Loaded" : "✗ Not loaded") . "</p>";
echo "<p>SMTP_PASSWORD: " . (getenv('SMTP_PASSWORD') ? "✓ Loaded" : "✗ Not loaded") . "</p>";
?>
