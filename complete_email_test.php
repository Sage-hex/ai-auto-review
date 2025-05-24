<?php
/**
 * Complete Email Test
 * 
 * This script tests all available email sending methods to diagnose issues
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include environment variables
require_once __DIR__ . '/backend/config/env.php';

// Include all email helpers
require_once __DIR__ . '/backend/helpers/mailgun_helper.php';
require_once __DIR__ . '/backend/helpers/gmail_helper.php';
require_once __DIR__ . '/backend/helpers/email_helper.php';
if (file_exists(__DIR__ . '/backend/helpers/direct_gmail.php')) {
    require_once __DIR__ . '/backend/helpers/direct_gmail.php';
}

// Set up test email parameters
$to = 'realitynuel@gmail.com'; // Your email address
$subject = 'Test Email from AI Auto Review ' . date('Y-m-d H:i:s');
$html = '
<html>
<body>
    <h1>Test Email</h1>
    <p>This is a test email sent at ' . date('Y-m-d H:i:s') . '</p>
    <p>If you received this, the email sending is working!</p>
</body>
</html>';
$text = "Test Email\n\nThis is a test email sent at " . date('Y-m-d H:i:s') . "\n\nIf you received this, the email sending is working!";

// Output page header
echo '<!DOCTYPE html>
<html>
<head>
    <title>Complete Email Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Complete Email Test</h1>
    <p>This script tests all available email sending methods to help diagnose issues.</p>
';

// Display environment configuration
echo '<div class="test-section">
    <h2>Environment Configuration</h2>';

// Check if environment variables are loaded
echo '<h3>Environment Variables</h3>';
echo '<pre>';
echo 'MAILGUN_API_KEY: ' . (getenv('MAILGUN_API_KEY') ? '✓ Set (' . substr(getenv('MAILGUN_API_KEY'), 0, 5) . '...)' : '✗ Not set') . "\n";
echo 'MAILGUN_DOMAIN: ' . (getenv('MAILGUN_DOMAIN') ? '✓ Set (' . getenv('MAILGUN_DOMAIN') . ')' : '✗ Not set') . "\n";
echo 'SMTP_HOST: ' . (getenv('SMTP_HOST') ? '✓ Set (' . getenv('SMTP_HOST') . ')' : '✗ Not set') . "\n";
echo 'SMTP_PORT: ' . (getenv('SMTP_PORT') ? '✓ Set (' . getenv('SMTP_PORT') . ')' : '✗ Not set') . "\n";
echo 'SMTP_USERNAME: ' . (getenv('SMTP_USERNAME') ? '✓ Set (' . getenv('SMTP_USERNAME') . ')' : '✗ Not set') . "\n";
echo 'SMTP_PASSWORD: ' . (getenv('SMTP_PASSWORD') ? '✓ Set (hidden)' : '✗ Not set') . "\n";
echo 'SMTP_SECURE: ' . (getenv('SMTP_SECURE') ? '✓ Set (' . getenv('SMTP_SECURE') . ')' : '✗ Not set') . "\n";
echo 'MAIL_FROM_EMAIL: ' . (getenv('MAIL_FROM_EMAIL') ? '✓ Set (' . getenv('MAIL_FROM_EMAIL') . ')' : '✗ Not set') . "\n";
echo 'MAIL_FROM_NAME: ' . (getenv('MAIL_FROM_NAME') ? '✓ Set (' . getenv('MAIL_FROM_NAME') . ')' : '✗ Not set') . "\n";
echo '</pre>';

// Check PHP mail configuration
echo '<h3>PHP Mail Configuration</h3>';
echo '<pre>';
$sendmail_path = ini_get('sendmail_path');
echo 'sendmail_path: ' . ($sendmail_path ? $sendmail_path : 'Not set') . "\n";
echo 'SMTP: ' . ini_get('SMTP') . "\n";
echo 'smtp_port: ' . ini_get('smtp_port') . "\n";
echo '</pre>';

// Check for required PHP extensions
echo '<h3>PHP Extensions</h3>';
echo '<pre>';
echo 'cURL: ' . (function_exists('curl_init') ? '✓ Enabled' : '✗ Not enabled') . "\n";
echo 'OpenSSL: ' . (extension_loaded('openssl') ? '✓ Enabled' : '✗ Not enabled') . "\n";
echo 'SMTP Functions: ' . (function_exists('fsockopen') ? '✓ Available' : '✗ Not available') . "\n";
echo '</pre>';

echo '</div>';

// Test 1: PHP mail() function
echo '<div class="test-section">
    <h2>Test 1: PHP mail() Function</h2>';
echo '<p>Testing native PHP mail() function...</p>';

// Start output buffering to capture error messages
ob_start();
$mail_result = mail($to, $subject . ' (PHP mail)', $html, 
    "From: AI Auto Review <noreply@aiautoreview.com>\r\n" .
    "Reply-To: noreply@aiautoreview.com\r\n" .
    "MIME-Version: 1.0\r\n" .
    "Content-Type: text/html; charset=UTF-8\r\n"
);
$mail_output = ob_get_clean();

if ($mail_result) {
    echo '<p class="success">✓ PHP mail() function returned success</p>';
} else {
    echo '<p class="error">✗ PHP mail() function failed</p>';
    if ($mail_output) {
        echo '<pre>' . htmlspecialchars($mail_output) . '</pre>';
    }
}

// Check mail log if available
$mail_log = '';
if (file_exists('/var/log/mail.log')) {
    $mail_log = shell_exec('tail -n 20 /var/log/mail.log');
} elseif (file_exists('/var/log/maillog')) {
    $mail_log = shell_exec('tail -n 20 /var/log/maillog');
}

if ($mail_log) {
    echo '<h3>Mail Log</h3>';
    echo '<pre>' . htmlspecialchars($mail_log) . '</pre>';
}

echo '</div>';

// Test 2: Mailgun API
echo '<div class="test-section">
    <h2>Test 2: Mailgun API</h2>';

if (!getenv('MAILGUN_API_KEY') || !getenv('MAILGUN_DOMAIN')) {
    echo '<p class="error">✗ Mailgun configuration is missing. Please check your .env file.</p>';
} else {
    echo '<p>Testing Mailgun API...</p>';
    
    // Start output buffering to capture error messages
    ob_start();
    $mailgun_result = sendMailgunEmail($to, $subject . ' (Mailgun)', $html, $text);
    $mailgun_output = ob_get_clean();
    
    if ($mailgun_result) {
        echo '<p class="success">✓ Mailgun API returned success</p>';
    } else {
        echo '<p class="error">✗ Mailgun API failed</p>';
        if ($mailgun_output) {
            echo '<pre>' . htmlspecialchars($mailgun_output) . '</pre>';
        }
    }
    
    // Check PHP error log for Mailgun errors
    if (file_exists(__DIR__ . '/logs/php_errors.log')) {
        $error_log = shell_exec('tail -n 20 ' . __DIR__ . '/logs/php_errors.log');
        if ($error_log) {
            echo '<h3>Error Log</h3>';
            echo '<pre>' . htmlspecialchars($error_log) . '</pre>';
        }
    }
}

echo '</div>';

// Test 3: Gmail SMTP
echo '<div class="test-section">
    <h2>Test 3: Gmail SMTP</h2>';

if (!getenv('SMTP_USERNAME') || !getenv('SMTP_PASSWORD')) {
    echo '<p class="error">✗ Gmail SMTP configuration is missing. Please check your .env file.</p>';
} else {
    echo '<p>Testing Gmail SMTP...</p>';
    
    // Test connection to Gmail SMTP server
    $smtp_conn = @fsockopen(getenv('SMTP_HOST'), getenv('SMTP_PORT'), $errno, $errstr, 5);
    if (!$smtp_conn) {
        echo '<p class="error">✗ Failed to connect to SMTP server: ' . $errstr . ' (' . $errno . ')</p>';
    } else {
        echo '<p class="success">✓ Successfully connected to SMTP server</p>';
        fclose($smtp_conn);
        
        // Try to send email via Gmail SMTP
        if (function_exists('sendGmailEmail')) {
            // Start output buffering to capture error messages
            ob_start();
            $gmail_result = sendGmailEmail($to, $subject . ' (Gmail SMTP)', $html, $text);
            $gmail_output = ob_get_clean();
            
            if ($gmail_result) {
                echo '<p class="success">✓ Gmail SMTP returned success</p>';
            } else {
                echo '<p class="error">✗ Gmail SMTP failed</p>';
                if ($gmail_output) {
                    echo '<pre>' . htmlspecialchars($gmail_output) . '</pre>';
                }
            }
        } else {
            echo '<p class="error">✗ sendGmailEmail function not found</p>';
        }
    }
}

echo '</div>';

// Test 4: Direct Gmail (if available)
if (function_exists('sendDirectGmailEmail')) {
    echo '<div class="test-section">
        <h2>Test 4: Direct Gmail (No TLS)</h2>';
    
    if (!getenv('SMTP_USERNAME') || !getenv('SMTP_PASSWORD')) {
        echo '<p class="error">✗ Gmail credentials are missing. Please check your .env file.</p>';
    } else {
        echo '<p>Testing Direct Gmail method (without TLS)...</p>';
        
        // Start output buffering to capture error messages
        ob_start();
        $direct_gmail_result = sendDirectGmailEmail($to, $subject . ' (Direct Gmail)', $html);
        $direct_gmail_output = ob_get_clean();
        
        if ($direct_gmail_result) {
            echo '<p class="success">✓ Direct Gmail method returned success</p>';
        } else {
            echo '<p class="error">✗ Direct Gmail method failed</p>';
            if ($direct_gmail_output) {
                echo '<pre>' . htmlspecialchars($direct_gmail_output) . '</pre>';
            }
        }
    }
    
    echo '</div>';
}

// Test 5: OTP Email Function
echo '<div class="test-section">
    <h2>Test 5: OTP Email Function</h2>';

// Check if OTP email function exists
$otp_file_path = __DIR__ . '/backend/api/endpoints/auth/otp.php';
if (file_exists($otp_file_path)) {
    // Include the OTP file to get the sendOTPEmail function
    include_once $otp_file_path;
    
    if (function_exists('sendOTPEmail')) {
        echo '<p>Testing OTP email function...</p>';
        
        // Generate a test OTP
        $test_otp = rand(100000, 999999);
        
        // Start output buffering to capture error messages
        ob_start();
        $otp_result = sendOTPEmail($to, $test_otp);
        $otp_output = ob_get_clean();
        
        if ($otp_result) {
            echo '<p class="success">✓ OTP email function returned success</p>';
            echo '<p>Test OTP code: ' . $test_otp . '</p>';
        } else {
            echo '<p class="error">✗ OTP email function failed</p>';
            if ($otp_output) {
                echo '<pre>' . htmlspecialchars($otp_output) . '</pre>';
            }
        }
    } else {
        echo '<p class="error">✗ sendOTPEmail function not found in otp.php</p>';
        
        // Display the relevant code from otp.php
        $otp_code = file_get_contents($otp_file_path);
        if (preg_match('/function\s+sendOTPEmail.*?\{.*?\}/s', $otp_code, $matches)) {
            echo '<h3>sendOTPEmail Function Code</h3>';
            echo '<pre>' . htmlspecialchars($matches[0]) . '</pre>';
        } else {
            echo '<p>Could not find sendOTPEmail function in the code.</p>';
        }
    }
} else {
    echo '<p class="error">✗ OTP file not found at: ' . $otp_file_path . '</p>';
}

echo '</div>';

// Summary and Recommendations
echo '<div class="test-section">
    <h2>Summary and Recommendations</h2>';

echo '<h3>Test Results</h3>';
echo '<ul>';
if (isset($mail_result)) echo '<li>PHP mail(): ' . ($mail_result ? '<span class="success">Success</span>' : '<span class="error">Failed</span>') . '</li>';
if (isset($mailgun_result)) echo '<li>Mailgun API: ' . ($mailgun_result ? '<span class="success">Success</span>' : '<span class="error">Failed</span>') . '</li>';
if (isset($gmail_result)) echo '<li>Gmail SMTP: ' . ($gmail_result ? '<span class="success">Success</span>' : '<span class="error">Failed</span>') . '</li>';
if (isset($direct_gmail_result)) echo '<li>Direct Gmail: ' . ($direct_gmail_result ? '<span class="success">Success</span>' : '<span class="error">Failed</span>') . '</li>';
if (isset($otp_result)) echo '<li>OTP Email: ' . ($otp_result ? '<span class="success">Success</span>' : '<span class="error">Failed</span>') . '</li>';
echo '</ul>';

echo '<h3>Recommendations</h3>';
echo '<ol>';
echo '<li><strong>Check Your Inbox:</strong> Look for test emails (including spam/junk folders)</li>';

if (!function_exists('curl_init')) {
    echo '<li><strong>Enable cURL:</strong> The cURL extension is required for Mailgun API. Add <code>extension=curl</code> to your php.ini file.</li>';
}

if (!getenv('MAILGUN_API_KEY') || !getenv('MAILGUN_DOMAIN')) {
    echo '<li><strong>Configure Mailgun:</strong> Add your Mailgun API key and domain to the .env file.</li>';
}

if (getenv('MAILGUN_DOMAIN') && strpos(getenv('MAILGUN_DOMAIN'), 'sandbox') !== false) {
    echo '<li><strong>Authorize Recipients:</strong> For Mailgun sandbox domains, you need to authorize recipient email addresses in the Mailgun dashboard.</li>';
}

if (!getenv('SMTP_USERNAME') || !getenv('SMTP_PASSWORD')) {
    echo '<li><strong>Configure Gmail SMTP:</strong> Add your Gmail credentials to the .env file.</li>';
}

echo '<li><strong>Check Firewall:</strong> Ensure your server allows outgoing connections on ports 25, 465, and 587.</li>';
echo '<li><strong>Check PHP Configuration:</strong> Make sure your PHP is configured to send emails.</li>';
echo '<li><strong>Check Error Logs:</strong> Review your PHP error logs for more details on failures.</li>';
echo '</ol>';

echo '</div>';

echo '</body>
</html>';
?>
