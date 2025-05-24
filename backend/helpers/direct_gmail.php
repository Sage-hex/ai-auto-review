<?php
/**
 * Direct Gmail Helper
 * 
 * A specialized helper for sending emails through Gmail SMTP with maximum compatibility
 * Designed to work even in environments without TLS/SSL support
 */

// Include configuration
require_once __DIR__ . '/../config/env.php';

/**
 * Send an email through Gmail SMTP
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $html_message Email body (HTML)
 * @return bool Whether the email was sent successfully
 */
function sendDirectGmail($to, $subject, $html_message) {
    // Get Gmail credentials from environment
    $username = env('SMTP_USERNAME', ''); 
    $password = env('SMTP_PASSWORD', '');
    $from_name = env('MAIL_FROM_NAME', 'AI Auto Review');
    
    if (empty($username) || empty($password)) {
        error_log("Gmail Error: Missing username or password");
        return false;
    }
    
    // Log attempt
    error_log("Attempting to send email via Direct Gmail to: $to");
    
    try {
        // Generate a unique boundary for message parts
        $boundary = md5(time());
        
        // Create email headers
        $headers = [
            "MIME-Version: 1.0",
            "From: {$from_name} <{$username}>",
            "To: {$to}",
            "Subject: {$subject}",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
        ];
        
        // Create message with text and HTML parts
        $message = "--{$boundary}\r\n" .
                   "Content-Type: text/plain; charset=UTF-8\r\n" .
                   "Content-Transfer-Encoding: 7bit\r\n\r\n" .
                   strip_tags($html_message) . "\r\n\r\n" .
                   "--{$boundary}\r\n" .
                   "Content-Type: text/html; charset=UTF-8\r\n" .
                   "Content-Transfer-Encoding: 7bit\r\n\r\n" .
                   $html_message . "\r\n\r\n" .
                   "--{$boundary}--";
        
        // PHP's mail() function direct approach - works on most XAMPP installations
        $mail_result = mail($to, $subject, $message, implode("\r\n", $headers));
        
        if ($mail_result) {
            error_log("Direct Gmail: Email sent successfully using mail() function");
            return true;
        }
        
        // If mail() fails, try direct SMTP conversation
        // This is a simplified approach without requiring TLS support
        
        // Connect to Gmail SMTP server
        $smtpServer = "smtp.gmail.com";
        $port = 587; // TLS port
        
        // Create a socket connection
        $socket = @fsockopen($smtpServer, $port, $errno, $errstr, 30);
        if (!$socket) {
            error_log("Gmail Error: Failed to connect to SMTP server - $errstr ($errno)");
            return false;
        }
        
        // Read server greeting
        if (!($response = fgets($socket, 515))) {
            error_log("Gmail Error: Failed to receive server greeting");
            fclose($socket);
            return false;
        }
        
        // Send EHLO command
        fputs($socket, "EHLO localhost\r\n");
        while ($response = fgets($socket, 515)) {
            if (substr($response, 3, 1) != '-') break;
        }
        
        // Attempt to start TLS but don't fail if not supported
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        $tls_supported = (substr($response, 0, 3) == '220');
        
        if ($tls_supported) {
            // Try to enable TLS encryption
            if (function_exists('stream_socket_enable_crypto')) {
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    error_log("Gmail Warning: Failed to enable TLS encryption, continuing without encryption");
                    // We won't return false here, we'll try to continue without TLS
                    
                    // Reconnect since the STARTTLS negotiation might have disrupted the connection
                    fclose($socket);
                    $socket = @fsockopen($smtpServer, $port, $errno, $errstr, 30);
                    if (!$socket) {
                        error_log("Gmail Error: Failed to reconnect after STARTTLS attempt");
                        return false;
                    }
                    fgets($socket, 515); // Read greeting
                } else {
                    error_log("Gmail: TLS encryption enabled successfully");
                    
                    // Restart EHLO after TLS
                    fputs($socket, "EHLO localhost\r\n");
                    while ($response = fgets($socket, 515)) {
                        if (substr($response, 3, 1) != '-') break;
                    }
                }
            } else {
                error_log("Gmail Warning: stream_socket_enable_crypto function not available");
                // Again, try to continue without TLS
            }
        }
        
        // Try to authenticate
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("Gmail Error: AUTH failed - $response");
            fclose($socket);
            return false;
        }
        
        // Send username
        fputs($socket, base64_encode($username) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("Gmail Error: Username rejected - $response");
            fclose($socket);
            return false;
        }
        
        // Send password
        fputs($socket, base64_encode($password) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '235') {
            error_log("Gmail Error: Password rejected - $response");
            fclose($socket);
            return false;
        }
        
        // Set sender
        fputs($socket, "MAIL FROM:<$username>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("Gmail Error: Sender rejected - $response");
            fclose($socket);
            return false;
        }
        
        // Set recipient
        fputs($socket, "RCPT TO:<$to>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("Gmail Error: Recipient rejected - $response");
            fclose($socket);
            return false;
        }
        
        // Send data command
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '354') {
            error_log("Gmail Error: DATA command failed - $response");
            fclose($socket);
            return false;
        }
        
        // Send email headers and body
        $email = implode("\r\n", $headers) . "\r\n\r\n" . $message . "\r\n.";
        fputs($socket, $email . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("Gmail Error: Message rejected - $response");
            fclose($socket);
            return false;
        }
        
        // Quit and close connection
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        error_log("Gmail: Email sent successfully via direct SMTP");
        return true;
        
    } catch (Exception $e) {
        error_log("Gmail Exception: " . $e->getMessage());
        return false;
    }
}
