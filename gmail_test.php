<?php
// Gmail Test Script
header('Content-Type: text/html; charset=UTF-8');

// Display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load environment variables
require_once __DIR__ . '/backend/config/env.php';

// Include the Gmail helper
require_once __DIR__ . '/backend/helpers/gmail_helper.php';

echo "<h1>Gmail Test Script</h1>";

// Check if environment variables are loaded
echo "<h2>Environment Variables</h2>";
echo "<ul>";
echo "<li>SMTP_HOST: " . (getenv('SMTP_HOST') ?: 'Not set') . "</li>";
echo "<li>SMTP_PORT: " . (getenv('SMTP_PORT') ?: 'Not set') . "</li>";
echo "<li>SMTP_USERNAME: " . (getenv('SMTP_USERNAME') ?: 'Not set') . "</li>";
echo "<li>SMTP_PASSWORD: " . (getenv('SMTP_PASSWORD') ? 'Set (hidden)' : 'Not set') . "</li>";
echo "<li>MAIL_FROM_EMAIL: " . (getenv('MAIL_FROM_EMAIL') ?: 'Not set') . "</li>";
echo "<li>MAIL_FROM_NAME: " . (getenv('MAIL_FROM_NAME') ?: 'Not set') . "</li>";
echo "</ul>";

// Test sending an email
if (isset($_POST['send_test'])) {
    $to = $_POST['email'];
    $subject = "Test Email from AI Auto Review";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4f46e5; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9fafb; }
            .code { font-size: 32px; font-weight: bold; text-align: center; letter-spacing: 5px; margin: 30px 0; color: #4f46e5; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>AI Auto Review</h1>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>This is a test email from AI Auto Review.</p>
                <div class='code'>123456</div>
                <p>If you received this email, your email configuration is working correctly.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " AI Auto Review. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Try sending via Gmail
    echo "<h2>Sending Test Email</h2>";
    echo "<p>Attempting to send email to: $to</p>";
    
    $result = sendGmailEmail($to, $subject, $message);
    
    if ($result) {
        echo "<p style='color: green; font-weight: bold;'>Email sent successfully via Gmail!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Failed to send email via Gmail.</p>";
        
        // Try using the fallback method
        echo "<p>Trying fallback method...</p>";
        
        $fallback_result = sendOTPFallback($to, $subject, $message);
        
        if ($fallback_result) {
            echo "<p style='color: green; font-weight: bold;'>Email sent successfully via fallback method!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>All email methods failed.</p>";
        }
    }
}
?>

<h2>Send Test Email</h2>
<form method="post" action="">
    <label for="email">Email address:</label><br>
    <input type="email" id="email" name="email" value="<?php echo getenv('SMTP_USERNAME'); ?>" required style="width: 300px; padding: 8px; margin: 10px 0;"><br>
    <input type="submit" name="send_test" value="Send Test Email" style="padding: 10px 15px; background-color: #4f46e5; color: white; border: none; cursor: pointer;">
</form>

<h2>Troubleshooting</h2>
<ul>
    <li>Make sure your Gmail account has "Less secure app access" enabled or you're using an App Password.</li>
    <li>Check that your SMTP credentials in the .env file are correct.</li>
    <li>If using Gmail, ensure you've generated an App Password if you have 2-factor authentication enabled.</li>
    <li>Check the PHP error logs for more detailed error messages.</li>
</ul>
