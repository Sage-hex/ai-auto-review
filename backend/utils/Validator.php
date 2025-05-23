<?php
/**
 * Validator Class
 * 
 * Provides methods for validating and sanitizing input data
 */
class Validator {
    /**
     * Validate and sanitize email address
     *
     * @param string $email Email address to validate
     * @return string|false Sanitized email or false if invalid
     */
    public static function email($email) {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }
    
    /**
     * Sanitize string input
     *
     * @param string $string Input string to sanitize
     * @return string Sanitized string
     */
    public static function string($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate and sanitize integer
     *
     * @param mixed $int Integer to validate
     * @return int|false Sanitized integer or false if invalid
     */
    public static function integer($int) {
        return filter_var($int, FILTER_VALIDATE_INT);
    }
    
    /**
     * Validate and sanitize float
     *
     * @param mixed $float Float to validate
     * @return float|false Sanitized float or false if invalid
     */
    public static function float($float) {
        return filter_var($float, FILTER_VALIDATE_FLOAT);
    }
    
    /**
     * Validate and sanitize URL
     *
     * @param string $url URL to validate
     * @return string|false Sanitized URL or false if invalid
     */
    public static function url($url) {
        $url = filter_var(trim($url), FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
    }
    
    /**
     * Validate date format
     *
     * @param string $date Date string to validate
     * @param string $format Expected date format
     * @return bool True if date is valid, false otherwise
     */
    public static function date($date, $format = 'Y-m-d') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate password strength
     *
     * @param string $password Password to validate
     * @param int $minLength Minimum password length
     * @return bool True if password meets requirements, false otherwise
     */
    public static function password($password, $minLength = 8) {
        // Check minimum length
        if (strlen($password) < $minLength) {
            return false;
        }
        
        // Check if password has at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check if password has at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check if password has at least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check if password has at least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate if value is in allowed list
     *
     * @param mixed $value Value to check
     * @param array $allowedValues Array of allowed values
     * @return bool True if value is in allowed list, false otherwise
     */
    public static function inList($value, array $allowedValues) {
        return in_array($value, $allowedValues);
    }
    
    /**
     * Validate JSON string
     *
     * @param string $json JSON string to validate
     * @return bool True if valid JSON, false otherwise
     */
    public static function json($json) {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Validate phone number format
     *
     * @param string $phone Phone number to validate
     * @return bool True if valid phone format, false otherwise
     */
    public static function phone($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if phone number has valid length (10-15 digits)
        return (strlen($phone) >= 10 && strlen($phone) <= 15);
    }
    
    /**
     * Validate required fields in an array
     *
     * @param array $data Data array to check
     * @param array $requiredFields List of required field keys
     * @return array|bool Array of missing fields or true if all required fields exist
     */
    public static function required(array $data, array $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing[] = $field;
            }
        }
        
        return empty($missing) ? true : $missing;
    }
}
