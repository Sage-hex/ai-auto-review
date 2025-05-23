<?php
/**
 * OpenAI Utilities
 * 
 * This file contains utility functions for interacting with the OpenAI API.
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Generate a response using OpenAI's API
 * 
 * @param string $prompt The prompt to send to OpenAI
 * @param float $temperature Temperature parameter (0-1)
 * @param int $maxTokens Maximum tokens to generate
 * @param string $model OpenAI model to use
 * @return string|bool Generated text if successful, false if not
 */
function generateOpenAIResponse($prompt, $temperature = 0.7, $maxTokens = 300, $model = 'gpt-3.5-turbo') {
    try {
        $apiKey = OPENAI_API_KEY;
        
        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional customer support assistant who writes helpful responses to customer reviews.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log('OpenAI API Curl error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            error_log('OpenAI API Invalid response: ' . $response);
            return false;
        }
        
        return $responseData['choices'][0]['message']['content'];
    } catch (Exception $e) {
        error_log("OpenAI API Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a prompt for generating a review response
 * 
 * @param string $reviewContent The content of the review
 * @param int $rating The rating (1-5)
 * @param string $businessName The name of the business
 * @param string $businessType The type of business (e.g., restaurant, hotel)
 * @param string $tone The tone for the response (e.g., professional, friendly)
 * @param bool $includePromotion Whether to include a promotion
 * @param string $promotionText The promotion text to include
 * @return string The generated prompt
 */
function createReviewResponsePrompt($reviewContent, $rating, $businessName, $businessType = '', $tone = 'professional', $includePromotion = false, $promotionText = '') {
    $prompt = "Write a polite and helpful response for this $rating-star review:\n\n";
    $prompt .= "\"$reviewContent\"\n\n";
    
    $prompt .= "Business: $businessName";
    if (!empty($businessType)) {
        $prompt .= ", a $businessType";
    }
    $prompt .= ".\n";
    
    $prompt .= "Tone: $tone.\n";
    
    if ($includePromotion && !empty($promotionText)) {
        $prompt .= "Include this promotion: $promotionText\n";
    }
    
    $prompt .= "Guidelines:\n";
    $prompt .= "- Keep the response concise (under 150 words)\n";
    $prompt .= "- Make it personalized and authentic\n";
    $prompt .= "- Address the specific points mentioned in the review\n";
    $prompt .= "- If it's a negative review, be apologetic and solution-oriented\n";
    $prompt .= "- If it's a positive review, express gratitude\n";
    $prompt .= "- Do not use generic templates or clichés\n";
    
    return $prompt;
}

/**
 * Analyze sentiment of a review using OpenAI
 * 
 * @param string $reviewContent The content of the review
 * @param int $rating The rating (1-5)
 * @return string Sentiment (positive, neutral, negative)
 */
function analyzeSentimentWithOpenAI($reviewContent, $rating) {
    try {
        $prompt = "Analyze the sentiment of this $rating-star review and respond with only one word: 'positive', 'neutral', or 'negative'.\n\n";
        $prompt .= "Review: \"$reviewContent\"";
        
        $sentiment = generateOpenAIResponse($prompt, 0.3, 50);
        
        if (!$sentiment) {
            // Default based on rating if API call fails
            if ($rating >= 4) {
                return 'positive';
            } elseif ($rating <= 2) {
                return 'negative';
            } else {
                return 'neutral';
            }
        }
        
        // Clean up response to ensure it's just one of the expected values
        $sentiment = strtolower(trim($sentiment));
        
        if (strpos($sentiment, 'positive') !== false) {
            return 'positive';
        } elseif (strpos($sentiment, 'negative') !== false) {
            return 'negative';
        } else {
            return 'neutral';
        }
    } catch (Exception $e) {
        error_log("Sentiment Analysis Error: " . $e->getMessage());
        
        // Default based on rating if exception occurs
        if ($rating >= 4) {
            return 'positive';
        } elseif ($rating <= 2) {
            return 'negative';
        } else {
            return 'neutral';
        }
    }
}
