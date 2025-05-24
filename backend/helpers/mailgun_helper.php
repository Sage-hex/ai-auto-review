<?php
/**
 * Mailgun Helper
 * 
 * Functions for sending emails via Mailgun API with fallback methods
 */

// Include environment variables
require_once __DIR__ . '/../config/env.php';

/**
 * Send an email using Mailgun API
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $html HTML content of the email
 * @param string $text Plain text content of the email (optional)
 * @param array $options Additional options
 * @return bool Success status
 */
function sendMailgunEmail($to, $subject, $html, $text = '', $options = []) {
    // Get Mailgun configuration from environment
    $apiKey = getenv('MAILGUN_API_KEY');
    $domain = getenv('MAILGUN_DOMAIN');
    $fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@aiautoreview.com';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'AI Auto Review';
    
    // Check if Mailgun is configured
    if (!$apiKey || !$domain) {
        error_log("Mailgun not configured. Missing API key or domain.");
        return sendFallbackEmail($to, $subject, $html, $text);
    }
    
    // Set from address
    $from = isset($options['from']) ? $options['from'] : "{$fromName} <{$fromEmail}>";
    
    // Prepare the form data
    $data = [
        'from' => $from,
        'to' => $to,
        'subject' => $subject,
        'html' => $html,
    ];
    
    // Add text version if provided
    if (!empty($text)) {
        $data['text'] = $text;
    } else {
        // Generate plain text version if not provided
        $data['text'] = htmlToText($html);
    }
    
    // Add CC if provided
    if (isset($options['cc'])) {
        $data['cc'] = $options['cc'];
    }
    
    // Add BCC if provided
    if (isset($options['bcc'])) {
        $data['bcc'] = $options['bcc'];
    }
    
    // Log the attempt
    error_log("Attempting to send email to {$to} via Mailgun");
    
    // Try to send using cURL if available
    if (function_exists('curl_init')) {
        try {
            // Initialize cURL
            $ch = curl_init();
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, "https://api.mailgun.net/v3/{$domain}/messages");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "api:{$apiKey}");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
            
            // Execute the request
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close cURL
            curl_close($ch);
            
            // Log the response for debugging
            if ($response) {
                error_log("Mailgun API response: " . $response);
                
                // Check if the request was successful
                if ($httpCode >= 200 && $httpCode < 300) {
                    error_log("Email sent successfully via Mailgun to {$to}");
                    return true;
                } else {
                    error_log("Mailgun API error: HTTP status {$httpCode}");
                }
            }
            
            if ($error) {
                error_log("Mailgun cURL error: " . $error);
            }
            
            // If we got here, Mailgun failed - try fallback
            return sendFallbackEmail($to, $subject, $html, $text);
            
        } catch (Exception $e) {
            error_log("Mailgun exception: " . $e->getMessage());
            return sendFallbackEmail($to, $subject, $html, $text);
        }
    } else {
        // cURL not available, use fallback
        error_log("cURL not available for Mailgun API, using fallback");
        return sendFallbackEmail($to, $subject, $html, $text);
    }
}

/**
 * Send email using PHP's mail() function as a fallback
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $html HTML content
 * @param string $text Plain text content
 * @return bool Success status
 */
function sendFallbackEmail($to, $subject, $html, $text = '') {
    error_log("Using PHP mail() as fallback for {$to}");
    
    // Get from address
    $fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@aiautoreview.com';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'AI Auto Review';
    $from = "{$fromName} <{$fromEmail}>";
    
    // Set up headers for HTML email
    $headers = "From: {$from}\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Try to send the email
    $result = mail($to, $subject, $html, $headers);
    
    if ($result) {
        error_log("Email sent successfully via mail() to {$to}");
    } else {
        error_log("Failed to send email via mail() to {$to}");
    }
    
    return $result;
}

/**
 * Generate a plain text version of an HTML email
 * 
 * @param string $html HTML content
 * @return string Plain text content
 */
function htmlToText($html) {
    // Simple HTML to text conversion
    $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html));
    $text = html_entity_decode($text);
    $text = preg_replace('/[\r\n]+/', "\n", $text);
    $text = preg_replace('/[ \t]+/', ' ', $text);
    return trim($text);
}
