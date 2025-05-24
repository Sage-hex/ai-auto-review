<?php
/**
 * Email Test Script
 * This script tests various email sending methods to diagnose issues
 */

// Include configuration
require_once __DIR__ . '/backend/config/env.php';
require_once __DIR__ . '/backend/helpers/mailgun_helper.php';
require_once __DIR__ . '/backend/helpers/gmail_helper.php';
require_once __DIR__ . '/backend/helpers/email_helper.php';

// Set error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Email Diagnostic Test</h1>";

// Get SMTP settings from environment
echo "<h2>SMTP Configuration</h2>";
echo "<pre>";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "SMTP Username: " . SMTP_USERNAME . "\n";
echo "SMTP Password: " . str_repeat('*', strlen(SMTP_PASSWORD)) . "\n";
echo "SMTP Secure: " . SMTP_SECURE . "\n";
echo "</pre>";

// Test connection to Gmail SMTP server
echo "<h2>Testing SMTP Connection</h2>";
$smtp_conn = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 5);
if (!$smtp_conn) {
    echo "<p style='color:red'>❌ Failed to connect to SMTP server: $errstr ($errno)</p>";
} else {
    echo "<p style='color:green'>✅ Successfully connected to SMTP server</p>";
    fclose($smtp_conn);
}

// Test TLS connection if applicable
if (SMTP_SECURE == 'tls') {
    echo "<h2>Testing TLS Connection</h2>";
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);
    $tls_conn = @stream_socket_client(
        'tls://' . SMTP_HOST . ':' . SMTP_PORT,
        $errno,
        $errstr,
        5,
        STREAM_CLIENT_CONNECT,
        $context
    );
    if (!$tls_conn) {
        echo "<p style='color:red'>❌ Failed to establish TLS connection: $errstr ($errno)</p>";
    } else {
        echo "<p style='color:green'>✅ Successfully established TLS connection</p>";
        fclose($tls_conn);
    }
}

// Try to identify known Gmail issues
echo "<h2>Gmail-specific Checks</h2>";
echo "<ul>";

// Check for less secure app access
echo "<li>Less Secure App Access: For accounts with 2-step verification enabled (recommended), you <strong>must</strong> use App Passwords. For accounts without 2-step verification, Google no longer supports 'Less Secure Apps' access - you must enable 2-step verification and use App Passwords.</li>";

// Check password
if (strlen(SMTP_PASSWORD) != 16 || strpos(SMTP_PASSWORD, ' ') !== false) {
    echo "<li style='color:red'>❌ Your SMTP password doesn't look like a valid 16-character App Password (without spaces). If you're using your regular Gmail password, it won't work.</li>";
} else {
    echo "<li style='color:green'>✅ Your SMTP password has the correct format for an App Password</li>";
}

// Check username format
if (!filter_var(SMTP_USERNAME, FILTER_VALIDATE_EMAIL)) {
    echo "<li style='color:red'>❌ Your SMTP username doesn't appear to be a valid email address</li>";
} else {
    echo "<li style='color:green'>✅ Your SMTP username is correctly formatted as an email address</li>";
}

echo "</ul>";

// Test sending an email
echo "<h2>Testing Email Sending</h2>";

$to = SMTP_USERNAME; // Send to self for testing
$subject = "Test Email from AI Auto Review";
$message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4f46e5; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9fafb; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>AI Auto Review</h1>
        </div>
        <div class='content'>
            <p>Hello,</p>
            <p>This is a test email sent at " . date('Y-m-d H:i:s') . "</p>
            <p>If you received this email, your email configuration is working correctly.</p>
        </div>
    </div>
</body>
</html>
";

try {
    echo "<p>Attempting to send test email to: $to</p>";
    $mailer = new SMTPMailer();
    $mailer->setDebug(true);
    $result = $mailer->send($to, $subject, $message);
    
    if ($result) {
        echo "<p style='color:green'>✅ Test email sent successfully!</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to send test email</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Exception while sending email: " . $e->getMessage() . "</p>";
}

echo "<h2>Common Solutions</h2>";
echo "<ol>";
echo "<li><strong>Enable 2-Step Verification:</strong> Go to your Google Account > Security and enable 2-Step Verification</li>";
echo "<li><strong>Create App Password:</strong> After enabling 2-Step Verification, go to Google Account > Security > App Passwords and create a new password for 'Mail' app</li>";
echo "<li><strong>Update .env File:</strong> Replace your current SMTP_PASSWORD with the 16-character App Password (without spaces)</li>";
echo "<li><strong>Check Spam/Promotions:</strong> Look in your Spam folder or Promotions tab for the test emails</li>";
echo "<li><strong>Check Firewall:</strong> Ensure your server/computer allows outgoing connections on port 587</li>";
echo "</ol>";
