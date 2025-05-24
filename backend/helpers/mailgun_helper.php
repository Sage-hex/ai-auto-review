<?php
/**
 * Mailgun Helper
 * 
 * This file contains helper functions for sending emails via Mailgun API.
 */

// Include the email configuration
require_once __DIR__ . '/../config/env.php';

/**
 * Send an email using Mailgun API
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $html Email body (HTML)
 * @param string $text Plain text version of the email (optional)
 * @param array $options Additional options like cc, bcc, attachments, etc.
 * @return bool Whether the email was sent successfully
 */
function sendMailgunEmail($to, $subject, $html, $text = '', $options = []) {
    // Get Mailgun configuration from environment
    $apiKey = env('MAILGUN_API_KEY', '');
    $domain = env('MAILGUN_DOMAIN', '');
    $fromEmail = env('MAIL_FROM_EMAIL', 'noreply@aiautoreview.com');
    $fromName = env('MAIL_FROM_NAME', 'AI Auto Review');
    
    // Log attempt
    error_log("Attempting to send email via Mailgun to: $to");
    
    // Validate required configuration
    if (empty($apiKey) || empty($domain)) {
        error_log("Mailgun Error: Missing API key or domain configuration");
        return false;
    }
    
    try {
        // Prepare the API endpoint
        $endpoint = "https://api.mailgun.net/v3/$domain/messages";
        
        // Prepare the form data
        $formData = [
            'from' => "$fromName <$fromEmail>",
            'to' => $to,
            'subject' => $subject,
            'html' => $html
        ];
        
        // Add plain text version if provided
        if (!empty($text)) {
            $formData['text'] = $text;
        }
        
        // Add any additional options
        if (!empty($options)) {
            $formData = array_merge($formData, $options);
        }
        
        // Prepare curl request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "api:$apiKey");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check for errors
        if (curl_errno($ch)) {
            error_log("Mailgun cURL Error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        // Process response
        $result = json_decode($response, true);
        
        // Check if the request was successful
        if ($httpCode == 200 && isset($result['id'])) {
            error_log("Mailgun: Email sent successfully to $to (ID: {$result['id']})");
            return true;
        } else {
            error_log("Mailgun Error: " . ($result['message'] ?? "HTTP Status $httpCode"));
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Mailgun Exception: " . $e->getMessage());
        return false;
    }
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
