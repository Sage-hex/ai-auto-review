<?php
/**
 * Direct SMTP Helper
 * 
 * A simple SMTP implementation that doesn't require cURL or OpenSSL
 * This is designed for development environments where extensions might be limited
 */

/**
 * Send an email using direct SMTP connection
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $html HTML content of the email
 * @param string $from From address (optional)
 * @return bool Success status
 */
function sendDirectSMTP($to, $subject, $html, $from = '') {
    // Get configuration from environment
    $smtp_host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtp_port = getenv('SMTP_PORT') ?: 587;
    $smtp_user = getenv('SMTP_USERNAME');
    $smtp_pass = getenv('SMTP_PASSWORD');
    $from_email = getenv('MAIL_FROM_EMAIL') ?: 'noreply@aiautoreview.com';
    $from_name = getenv('MAIL_FROM_NAME') ?: 'AI Auto Review';
    
    // Set from address if not provided
    if (empty($from)) {
        $from = "{$from_name} <{$from_email}>";
    }
    
    // Check required configuration
    if (empty($smtp_user) || empty($smtp_pass)) {
        error_log("SMTP credentials not configured");
        return false;
    }
    
    // Generate a boundary for multipart messages
    $boundary = md5(time());
    
    // Prepare headers
    $headers = "From: {$from}\r\n";
    $headers .= "Reply-To: {$from_email}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    
    // Prepare message body
    $message = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html)) . "\r\n\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $html . "\r\n\r\n";
    $message .= "--{$boundary}--\r\n";
    
    // Connect to SMTP server
    error_log("Connecting to SMTP server: {$smtp_host}:{$smtp_port}");
    $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
    if (!$socket) {
        error_log("SMTP Connection Error: {$errstr} ({$errno})");
        return false;
    }
    
    // Read server greeting
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '220') {
        error_log("SMTP Error: " . $response);
        fclose($socket);
        return false;
    }
    
    // Send EHLO command
    fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        error_log("SMTP Error (EHLO): " . $response);
        fclose($socket);
        return false;
    }
    
    // Clear remaining EHLO response lines
    while (substr($response, 3, 1) == '-') {
        $response = fgets($socket, 515);
    }
    
    // Send AUTH LOGIN command
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '334') {
        error_log("SMTP Error (AUTH): " . $response);
        fclose($socket);
        return false;
    }
    
    // Send username
    fputs($socket, base64_encode($smtp_user) . "\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '334') {
        error_log("SMTP Error (USERNAME): " . $response);
        fclose($socket);
        return false;
    }
    
    // Send password
    fputs($socket, base64_encode($smtp_pass) . "\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '235') {
        error_log("SMTP Error (PASSWORD): " . $response);
        fclose($socket);
        return false;
    }
    
    // Send MAIL FROM command
    fputs($socket, "MAIL FROM: <{$from_email}>\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        error_log("SMTP Error (MAIL FROM): " . $response);
        fclose($socket);
        return false;
    }
    
    // Send RCPT TO command
    fputs($socket, "RCPT TO: <{$to}>\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        error_log("SMTP Error (RCPT TO): " . $response);
        fclose($socket);
        return false;
    }
    
    // Send DATA command
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '354') {
        error_log("SMTP Error (DATA): " . $response);
        fclose($socket);
        return false;
    }
    
    // Send email headers and body
    fputs($socket, "Subject: {$subject}\r\n");
    fputs($socket, "To: {$to}\r\n");
    fputs($socket, $headers . "\r\n");
    fputs($socket, $message . "\r\n.\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        error_log("SMTP Error (MESSAGE): " . $response);
        fclose($socket);
        return false;
    }
    
    // Send QUIT command
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    error_log("Email sent successfully to {$to} via direct SMTP");
    return true;
}

/**
 * Send an OTP email using direct SMTP
 * 
 * @param string $to Recipient email
 * @param string $otp OTP code
 * @return bool Success status
 */
function sendOTPEmailDirect($to, $otp) {
    // Get from address
    $from_email = getenv('MAIL_FROM_EMAIL') ?: 'noreply@aiautoreview.com';
    $from_name = getenv('MAIL_FROM_NAME') ?: 'AI Auto Review';
    $from = "{$from_name} <{$from_email}>";
    
    // Prepare email content
    $subject = "Your OTP Verification Code";
    $html = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
            <h1 style='color: #4a6ee0;'>Your Verification Code</h1>
            <p>Thank you for registering with AI Auto Review. Please use the following code to verify your email address:</p>
            <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                {$otp}
            </div>
            <p>This code will expire in 10 minutes.</p>
            <p>If you didn't request this code, please ignore this email.</p>
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p style='font-size: 12px; color: #777;'>This is an automated message, please do not reply to this email.</p>
        </div>
    </body>
    </html>";
    
    // Send the email
    return sendDirectSMTP($to, $subject, $html, $from);
}
