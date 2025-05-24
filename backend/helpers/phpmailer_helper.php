<?php
/**
 * PHPMailer Helper
 * 
 * This file contains functions for sending emails using PHPMailer.
 * PHPMailer is a more reliable email sending library that handles proper SMTP communication.
 */

// Include the email configuration
require_once __DIR__ . '/../config/email_config.php';

// Define PHPMailer class if not available (simplified version for direct SMTP)
if (!class_exists('SMTPMailer')) {
    
    class SMTPMailer {
        private $smtpHost;
        private $smtpPort;
        private $smtpUser;
        private $smtpPass;
        private $smtpSecure;
        private $from;
        private $fromName;
        private $isHTML = true;
        private $debug = false;
        private $socket;
        private $timeout = 30;
        
        public function __construct() {
            $this->smtpHost = SMTP_HOST;
            $this->smtpPort = SMTP_PORT;
            $this->smtpUser = SMTP_USERNAME;
            $this->smtpPass = SMTP_PASSWORD;
            $this->smtpSecure = SMTP_SECURE;
            $this->from = MAIL_FROM_EMAIL;
            $this->fromName = MAIL_FROM_NAME;
        }
        
        public function setFrom($email, $name = '') {
            $this->from = $email;
            $this->fromName = $name;
            return $this;
        }
        
        public function setHTML($isHTML = true) {
            $this->isHTML = $isHTML;
            return $this;
        }
        
        public function setDebug($debug = true) {
            $this->debug = $debug;
            return $this;
        }
        
        private function log($message) {
            if ($this->debug) {
                error_log("[SMTP] $message");
            }
        }
        
        private function getResponse() {
            $response = '';
            while (substr($response, 3, 1) != ' ') {
                if (!($response = fgets($this->socket, 515))) {
                    $this->log("Failed to get response");
                    return false;
                }
                $this->log("RESPONSE: $response");
            }
            return $response;
        }
        
        private function sendCommand($command) {
            $this->log("COMMAND: $command");
            fputs($this->socket, $command . "\r\n");
            return $this->getResponse();
        }
        
        public function send($to, $subject, $body, $additionalHeaders = []) {
            $this->log("Attempting to send email to $to");
            
            try {
                // Connect to server
                $this->socket = fsockopen(
                    ($this->smtpSecure == 'ssl' ? 'ssl://' : '') . $this->smtpHost, 
                    $this->smtpPort, 
                    $errno, 
                    $errstr, 
                    $this->timeout
                );
                
                if (!$this->socket) {
                    $this->log("Connection failed: $errstr ($errno)");
                    return false;
                }
                
                // Get server greeting
                if (!$this->getResponse()) {
                    return false;
                }
                
                // Say hello with a valid hostname
                $hostname = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';  
                $response = $this->sendCommand("EHLO " . $hostname);
                if (substr($response, 0, 3) !== '250') {
                    $this->log("EHLO failed: $response");
                    fclose($this->socket);
                    return false;
                }
                
                // Start TLS if needed
                if ($this->smtpSecure === 'tls') {
                    $response = $this->sendCommand("STARTTLS");
                    if (substr($response, 0, 3) !== '220') {
                        $this->log("STARTTLS failed: $response");
                        fclose($this->socket);
                        return false;
                    }
                    
                    stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                    
                    // Say hello again after TLS
                    $response = $this->sendCommand("EHLO " . $_SERVER['SERVER_NAME']);
                    if (substr($response, 0, 3) !== '250') {
                        $this->log("EHLO after TLS failed: $response");
                        fclose($this->socket);
                        return false;
                    }
                }
                
                // Authenticate
                $response = $this->sendCommand("AUTH LOGIN");
                if (substr($response, 0, 3) !== '334') {
                    $this->log("AUTH failed: $response");
                    fclose($this->socket);
                    return false;
                }
                
                $response = $this->sendCommand(base64_encode($this->smtpUser));
                if (substr($response, 0, 3) !== '334') {
                    $this->log("Username failed: $response");
                    fclose($this->socket);
                    return false;
                }
                
                $response = $this->sendCommand(base64_encode($this->smtpPass));
                if (substr($response, 0, 3) !== '235') {
                    $this->log("Password failed: $response");
                    fclose($this->socket);
                    return false;
                }
                
                // Set sender
                $response = $this->sendCommand("MAIL FROM:<{$this->from}>");
                if (substr($response, 0, 3) !== '250') {
                    $this->log("MAIL FROM failed: $response");
                    fclose($this->socket);
                    return false;
                }
                
                // Set recipient
                $response = $this->sendCommand("RCPT TO:<{$to}>");
                if (substr($response, 0, 3) !== '250') {
                    $this->log("RCPT TO failed: $response");
                    fclose($this->socket);
                    return false;
                }
                
                // Send data
                $response = $this->sendCommand("DATA");
                if (substr($response, 0, 3) !== '354') {
                    $this->log("DATA failed: $response");
                    fclose($this->socket);
                    return false;
                }
                
                // Construct headers
                $headers = [
                    "From: {$this->fromName} <{$this->from}>",
                    "To: $to",
                    "Subject: $subject",
                    "Date: " . date("r"),
                    "MIME-Version: 1.0"
                ];
                
                // Add content type
                if ($this->isHTML) {
                    $headers[] = "Content-Type: text/html; charset=UTF-8";
                } else {
                    $headers[] = "Content-Type: text/plain; charset=UTF-8";
                }
                
                // Add custom headers
                foreach ($additionalHeaders as $header) {
                    $headers[] = $header;
                }
                
                // Prepare email
                $email = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
                
                // Send email
                fputs($this->socket, $email . "\r\n");
                
                $response = $this->getResponse();
                if (substr($response, 0, 3) !== '250') {
                    $this->log("Message body failed: $response");
                    fclose($this->socket);
                    return false;
                }
                
                // Say goodbye
                $this->sendCommand("QUIT");
                fclose($this->socket);
                
                $this->log("Email sent successfully to $to");
                return true;
                
            } catch (Exception $e) {
                $this->log("Exception: " . $e->getMessage());
                if (isset($this->socket) && is_resource($this->socket)) {
                    fclose($this->socket);
                }
                return false;
            }
        }
    }
}

/**
 * Send an email using the SMTPMailer class
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @param array $additionalHeaders Additional headers
 * @return bool Whether the email was sent successfully
 */
function sendMailerEmail($to, $subject, $message, $additionalHeaders = []) {
    try {
        $mailer = new SMTPMailer();
        $mailer->setDebug(true); // Enable logging for troubleshooting
        
        // Send the email
        $result = $mailer->send($to, $subject, $message, $additionalHeaders);
        
        if ($result) {
            error_log("SMTPMailer: Email sent successfully to $to");
            return true;
        } else {
            error_log("SMTPMailer: Failed to send email to $to");
            return false;
        }
    } catch (Exception $e) {
        error_log("SMTPMailer Exception: " . $e->getMessage());
        return false;
    }
}
