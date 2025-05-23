<?php
/**
 * Logger Class
 * 
 * Provides methods for logging system events, errors, and user actions
 */
class Logger {
    // Log levels
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';
    const DEBUG = 'debug';
    
    // Log file path
    private static $logPath = '../logs/';
    
    /**
     * Log a message to the appropriate log file
     *
     * @param string $message Message to log
     * @param string $level Log level (error, warning, info, debug)
     * @param array $context Additional context data
     * @return bool True if log was written successfully, false otherwise
     */
    public static function log($message, $level = self::INFO, $context = []) {
        // Create logs directory if it doesn't exist
        if (!file_exists(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        // Determine log file based on level
        $logFile = self::$logPath . $level . '_' . date('Y-m-d') . '.log';
        
        // Format timestamp
        $timestamp = date('Y-m-d H:i:s');
        
        // Format context data as JSON if not empty
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        
        // Format log entry
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextString}" . PHP_EOL;
        
        // Write to log file
        return file_put_contents($logFile, $logEntry, FILE_APPEND) !== false;
    }
    
    /**
     * Log an error message
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @return bool True if log was written successfully, false otherwise
     */
    public static function error($message, $context = []) {
        return self::log($message, self::ERROR, $context);
    }
    
    /**
     * Log a warning message
     *
     * @param string $message Warning message
     * @param array $context Additional context data
     * @return bool True if log was written successfully, false otherwise
     */
    public static function warning($message, $context = []) {
        return self::log($message, self::WARNING, $context);
    }
    
    /**
     * Log an info message
     *
     * @param string $message Info message
     * @param array $context Additional context data
     * @return bool True if log was written successfully, false otherwise
     */
    public static function info($message, $context = []) {
        return self::log($message, self::INFO, $context);
    }
    
    /**
     * Log a debug message
     *
     * @param string $message Debug message
     * @param array $context Additional context data
     * @return bool True if log was written successfully, false otherwise
     */
    public static function debug($message, $context = []) {
        return self::log($message, self::DEBUG, $context);
    }
    
    /**
     * Log an API request
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $params Request parameters
     * @param int $userId User ID (if authenticated)
     * @return bool True if log was written successfully, false otherwise
     */
    public static function apiRequest($endpoint, $method, $params = [], $userId = null) {
        $context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'params' => $params,
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        return self::log("API Request: {$method} {$endpoint}", self::INFO, $context);
    }
    
    /**
     * Log a user action
     *
     * @param int $userId User ID
     * @param string $action Action performed
     * @param array $details Action details
     * @return bool True if log was written successfully, false otherwise
     */
    public static function userAction($userId, $action, $details = []) {
        $context = [
            'user_id' => $userId,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        return self::log("User Action: {$action}", self::INFO, $context);
    }
    
    /**
     * Log an exception
     *
     * @param Exception $exception Exception object
     * @param array $context Additional context data
     * @return bool True if log was written successfully, false otherwise
     */
    public static function exception($exception, $context = []) {
        $context = array_merge([
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ], $context);
        
        return self::error($exception->getMessage(), $context);
    }
}
