<?php
/**
 * AI Service
 * 
 * This class handles integration with Google Gemini API for generating review responses.
 */

require_once __DIR__ . '/../config/config.php';

class AIService {
    /**
     * Generate a response for a review using Google Gemini API
     * 
     * @param array $review Review data
     * @param string $businessName Business name
     * @param string $businessType Business type (e.g., restaurant, hotel)
     * @param string $tone Response tone (e.g., friendly, professional, apologetic)
     * @return string|bool Generated response if successful, false if not
     */
    public function generateResponse($review, $businessName, $businessType = '', $tone = 'professional') {
        try {
            // Determine appropriate tone based on rating
            if (empty($tone)) {
                if ($review['rating'] <= 2) {
                    $tone = 'apologetic, solution-oriented';
                } elseif ($review['rating'] == 3) {
                    $tone = 'appreciative, helpful';
                } else {
                    $tone = 'grateful, friendly';
                }
            }
            
            // Prepare the prompt for Gemini
            $systemPrompt = "You are a professional customer support assistant for $businessName, a $businessType.";
            
            $userPrompt = "Write a polite and helpful response for this " . $review['rating'] . "-star review:\n\n";
            $userPrompt .= "\"" . $review['content'] . "\"\n\n";
            $userPrompt .= "Tone: $tone.\n";
            $userPrompt .= "Business: $businessName, a $businessType.\n";
            $userPrompt .= "Keep the response concise (under 150 words), personalized, and authentic.";
            
            $response = $this->callGemini($systemPrompt, $userPrompt);
            
            if (!$response) {
                return false;
            }
            
            return $response;
        } catch (Exception $e) {
            error_log("AIService::generateResponse Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Call Google Gemini API
     * 
     * @param string $systemPrompt System prompt
     * @param string $userPrompt User prompt
     * @return string|bool Generated text if successful, false if not
     */
    private function callGemini($systemPrompt, $userPrompt) {
        try {
            $apiKey = GEMINI_API_KEY;
            $model = GEMINI_MODEL;
            
            // Gemini uses a different format than OpenAI
            $data = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => $systemPrompt . "\n\n" . $userPrompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 300,
                    'topP' => 0.95,
                    'topK' => 40
                ]
            ];
            
            $headers = [
                'Content-Type: application/json'
            ];
            
            // Gemini API endpoint with API key as query parameter
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;
            
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                error_log('AIService::callGemini Curl error: ' . curl_error($ch));
                curl_close($ch);
                return false;
            }
            
            curl_close($ch);
            
            $responseData = json_decode($response, true);
            
            // Gemini response format is different from OpenAI
            if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                error_log('AIService::callGemini Invalid response: ' . $response);
                return false;
            }
            
            return $responseData['candidates'][0]['content']['parts'][0]['text'];
        } catch (Exception $e) {
            error_log("AIService::callOpenAI Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Analyze sentiment of a review
     * 
     * @param string $reviewText Review text
     * @return string Sentiment (positive, neutral, negative)
     */
    public function analyzeSentiment($reviewText) {
        try {
            // Simple sentiment analysis based on rating
            // In a real implementation, you would use OpenAI or another NLP service
            
            $systemPrompt = "You are a sentiment analysis expert.";
            
            $userPrompt = "Analyze the sentiment of this review and respond with only one word: 'positive', 'neutral', or 'negative'.\n\n";
            $userPrompt .= "Review: \"" . $reviewText . "\"";
            
            $sentiment = $this->callGemini($systemPrompt, $userPrompt);
            
            if (!$sentiment) {
                // Default to neutral if API call fails
                return 'neutral';
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
            error_log("AIService::analyzeSentiment Error: " . $e->getMessage());
            return 'neutral';
        }
    }
}
