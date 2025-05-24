<?php
/**
 * Email Helper
 * 
 * This file contains helper functions for sending emails.
 */

// Include the email configuration file
require_once __DIR__ . '/../config/email_config.php';

/**
 * Send an email using either PHP mail() function or SMTP
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @param array $additional_headers Additional headers to include
 * @return bool Whether the email was sent successfully
 */
function sendEmail($to, $subject, $message, $additional_headers = []) {
    // Combine default headers with additional headers
    $headers = getEmailHeaders();
    $headers = array_merge($headers, $additional_headers);
    $headers_string = implode("\r\n", $headers);
    
    // Log the email attempt
    error_log("Attempting to send email to: $to, Subject: $subject");

    if (USE_SMTP) {
        return sendEmailSmtp($to, $subject, $message, $headers);
    } else {
        // Use the standard PHP mail function
        $result = mail($to, $subject, $message, $headers_string);
        
        // Log the result
        if ($result) {
            error_log("Email sent successfully to: $to");
        } else {
            error_log("Failed to send email to: $to");
        }
        
        return $result;
    }
}

/**
 * Send an email using SMTP
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @param array $headers Email headers
 * @return bool Whether the email was sent successfully
 */
function sendEmailSmtp($to, $subject, $message, $headers) {
    try {
        // Basic SMTP implementation using PHP sockets
        // Note: For a production environment, using a library like PHPMailer is recommended
        
        $smtp = fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 30);
        if (!$smtp) {
            error_log("SMTP Error: $errstr ($errno)");
            return false;
        }
        
        // Parse response
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '220') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // EHLO command
        fputs($smtp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // Consume additional EHLO responses
        while (substr($response, 3, 1) === '-') {
            $response = fgets($smtp, 515);
        }
        
        // STARTTLS for secure connections if required
        if (SMTP_SECURE === 'tls') {
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '220') {
                error_log("SMTP Error: $response");
                fclose($smtp);
                return false;
            }
            
            // Upgrade the connection to TLS
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            // Send EHLO again after TLS
            fputs($smtp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '250') {
                error_log("SMTP Error: $response");
                fclose($smtp);
                return false;
            }
            
            // Consume additional EHLO responses
            while (substr($response, 3, 1) === '-') {
                $response = fgets($smtp, 515);
            }
        }
        
        // AUTH LOGIN
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '334') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // Send username
        fputs($smtp, base64_encode(SMTP_USERNAME) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '334') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // Send password
        fputs($smtp, base64_encode(SMTP_PASSWORD) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '235') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // MAIL FROM
        fputs($smtp, "MAIL FROM: <" . MAIL_FROM_EMAIL . ">\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // RCPT TO
        fputs($smtp, "RCPT TO: <$to>\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // DATA
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '354') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // Send email headers
        foreach ($headers as $header) {
            fputs($smtp, $header . "\r\n");
        }
        
        // Send subject
        fputs($smtp, "Subject: $subject\r\n");
        
        // Send to
        fputs($smtp, "To: $to\r\n");
        
        // Empty line to separate headers from body
        fputs($smtp, "\r\n");
        
        // Send message body
        fputs($smtp, $message . "\r\n");
        
        // End of data
        fputs($smtp, ".\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            error_log("SMTP Error: $response");
            fclose($smtp);
            return false;
        }
        
        // QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        error_log("Email sent successfully to: $to via SMTP");
        return true;
        
    } catch (Exception $e) {
        error_log("SMTP Exception: " . $e->getMessage());
        return false;
    }
}
