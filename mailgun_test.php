<?php
/**
 * Mailgun Test Script
 * This script helps diagnose issues with Mailgun email delivery
 */

// Set error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include environment configuration
require_once __DIR__ . '/backend/config/env.php';

// Get Mailgun configuration
$apiKey = env('MAILGUN_API_KEY', '');
$domain = env('MAILGUN_DOMAIN', '');
$fromEmail = env('MAIL_FROM_EMAIL', 'noreply@aiautoreview.com');
$fromName = env('MAIL_FROM_NAME', 'AI Auto Review');

// Email recipient - this MUST be authorized in your Mailgun sandbox
$recipient = 'realitynuel@gmail.com'; // Replace with your email if different

echo "<h1>Mailgun Email Test</h1>";

// Check if curl is installed
echo "<h2>System Checks</h2>";
if (!function_exists('curl_version')) {
    echo "<p style='color:red'>❌ PHP curl extension is not installed or enabled. This is required for Mailgun API calls.</p>";
    echo "<p>To enable curl in XAMPP:</p>";
    echo "<ol>";
    echo "<li>Open php.ini (usually in xampp/php/php.ini)</li>";
    echo "<li>Find the line ;extension=curl and remove the semicolon</li>";
    echo "<li>Save the file and restart Apache</li>";
    echo "</ol>";
    exit;
} else {
    echo "<p style='color:green'>✅ PHP curl extension is installed</p>";
}

// Check Mailgun configuration
echo "<h2>Mailgun Configuration</h2>";
echo "<pre>";
echo "API Key: " . substr($apiKey, 0, 6) . "..." . substr($apiKey, -4) . "\n";
echo "Domain: " . $domain . "\n";
echo "From Email: " . $fromEmail . "\n";
echo "From Name: " . $fromName . "\n";
echo "Recipient: " . $recipient . "\n";
echo "</pre>";

if (empty($apiKey) || empty($domain)) {
    echo "<p style='color:red'>❌ Mailgun API key or domain is missing in your .env file</p>";
    exit;
}

// Check if using sandbox domain
if (strpos($domain, 'sandbox') !== false) {
    echo "<p style='color:orange'>⚠️ You are using a Mailgun sandbox domain. Make sure $recipient is added as an authorized recipient in your Mailgun dashboard.</p>";
}

// Test sending an email through Mailgun API
echo "<h2>Testing Mailgun API</h2>";

// Prepare the email
$subject = "Test Email from AI Auto Review at " . date('H:i:s');
$htmlBody = "
<html>
<body>
    <h1>Test Email</h1>
    <p>This is a test email sent from the Mailgun test script at " . date('Y-m-d H:i:s') . "</p>
</body>
</html>";
$textBody = "Test Email\n\nThis is a test email sent from the Mailgun test script at " . date('Y-m-d H:i:s');

// Show what we're about to send
echo "<p>Attempting to send test email to: $recipient</p>";
echo "<p>Subject: $subject</p>";

// Prepare the API endpoint
$endpoint = "https://api.mailgun.net/v3/$domain/messages";

// Prepare the form data
$formData = [
    'from' => "$fromName <$fromEmail>",
    'to' => $recipient,
    'subject' => $subject,
    'html' => $htmlBody,
    'text' => $textBody
];

// Show detailed curl information
echo "<h3>CURL Request Details:</h3>";
echo "<pre>";
echo "Endpoint: $endpoint\n";
echo "Authorization: Basic " . base64_encode("api:$apiKey") . "\n";
echo "Form Data:\n";
print_r($formData);
echo "</pre>";

// Execute the API call with detailed logging
try {
    // Prepare curl request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "api:$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    // Create a temporary file handle for the CURL debug output
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Get detailed curl information
    $info = curl_getinfo($ch);
    
    // Check for errors
    if (curl_errno($ch)) {
        echo "<p style='color:red'>❌ Curl Error: " . curl_error($ch) . "</p>";
    }
    
    // Display verbose information
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    echo "<h3>CURL Verbose Log:</h3>";
    echo "<pre>" . htmlspecialchars($verboseLog) . "</pre>";
    
    // Display curl info
    echo "<h3>CURL Info:</h3>";
    echo "<pre>";
    print_r($info);
    echo "</pre>";
    
    // Display response
    echo "<h3>API Response (HTTP Code: $httpCode):</h3>";
    echo "<pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
    
    // Check if the request was successful
    if ($httpCode == 200) {
        echo "<p style='color:green'>✅ Email sent successfully via Mailgun API!</p>";
        echo "<p>Check your inbox at $recipient for the test email.</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to send email via Mailgun API. HTTP Status: $httpCode</p>";
    }
    
    curl_close($ch);
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Exception: " . $e->getMessage() . "</p>";
}

echo "<h2>Recommendations</h2>";
echo "<ul>";
echo "<li>If you see SSL certificate errors, try adding <code>curl_setopt(\$ch, CURLOPT_SSL_VERIFYPEER, false);</code> to bypass SSL verification (not recommended for production)</li>";
echo "<li>Ensure you've added $recipient as an authorized recipient in your Mailgun sandbox</li>";
echo "<li>Check that your API key is correct and has the necessary permissions</li>";
echo "<li>If using a sandbox domain, consider upgrading to a verified domain for production use</li>";
echo "</ul>";
