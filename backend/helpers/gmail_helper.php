<?php
/**
 * Gmail Helper
 * 
 * A simplified helper for sending emails through Gmail using basic socket connections
 */

// Include the email configuration
require_once __DIR__ . '/../config/email_config.php';

/**
 * Send an email using Gmail's SMTP server
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @return bool Whether the email was sent successfully
 */
function sendGmailEmail($to, $subject, $message) {
    // Configuration
    $from = MAIL_FROM_EMAIL;
    $fromName = MAIL_FROM_NAME;
    $username = SMTP_USERNAME;
    $password = SMTP_PASSWORD;
    
    // Log attempt
    error_log("Attempting to send Gmail to: $to");
    
    try {
        // Connect to Gmail SMTP
        $socket = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
        if (!$socket) {
            error_log("Gmail Error: Could not connect to SMTP server: $errstr ($errno)");
            return false;
        }
        
        // Read greeting
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '220') {
            error_log("Gmail Error: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Say hello
        fputs($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("Gmail Error: EHLO failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Skip through EHLO response extensions
        while (substr($response, 3, 1) === '-') {
            $response = fgets($socket, 515);
        }
        
        // Request to start TLS
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '220') {
            error_log("Gmail Error: STARTTLS failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Upgrade connection to TLS
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log("Gmail Error: Failed to enable TLS encryption");
            fclose($socket);
            return false;
        }
        
        // Say hello again after TLS
        fputs($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("Gmail Error: EHLO after TLS failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Skip through EHLO response extensions
        while (substr($response, 3, 1) === '-') {
            $response = fgets($socket, 515);
        }
        
        // Authenticate
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '334') {
            error_log("Gmail Error: AUTH failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Send username
        fputs($socket, base64_encode($username) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '334') {
            error_log("Gmail Error: Username failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Send password
        fputs($socket, base64_encode($password) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '235') {
            error_log("Gmail Error: Password failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Set sender
        fputs($socket, "MAIL FROM:<$from>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("Gmail Error: MAIL FROM failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Set recipient
        fputs($socket, "RCPT TO:<$to>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("Gmail Error: RCPT TO failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Begin data
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '354') {
            error_log("Gmail Error: DATA failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Construct email headers
        $headers = [
            "From: $fromName <$from>",
            "To: $to",
            "Subject: $subject",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "Date: " . date("r")
        ];
        
        // Send headers and message
        fputs($socket, implode("\r\n", $headers) . "\r\n\r\n" . $message . "\r\n.\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("Gmail Error: Message body failed: " . trim($response));
            fclose($socket);
            return false;
        }
        
        // Quit and close connection
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        error_log("Gmail: Email sent successfully to $to");
        return true;
        
    } catch (Exception $e) {
        error_log("Gmail Exception: " . $e->getMessage());
        if (isset($socket) && is_resource($socket)) {
            fclose($socket);
        }
        return false;
    }
}

/**
 * Fallback function for sending OTP via a direct method
 * This uses file_get_contents to call a free email API service
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @return bool Whether the email was sent successfully
 */
function sendOTPFallback($to, $subject, $message) {
    error_log("Using fallback email method for: $to");
    
    // Since Gmail failed, we'll use PHP's mail function as last resort
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_EMAIL . '>',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}
