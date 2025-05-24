<?php
/**
 * Fix CORS for All API Endpoints
 * 
 * This script adds the CORS handler to all API endpoints
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting CORS fix for all API endpoints...\n";

// Base directory
$baseDir = __DIR__ . '/backend/api/endpoints';

// Create CORS handler if it doesn't exist
$corsHandlerPath = __DIR__ . '/backend/api/cors.php';
if (!file_exists($corsHandlerPath)) {
    echo "Creating CORS handler file...\n";
    
    $corsContent = <<<'EOT'
<?php
/**
 * CORS Handler
 * 
 * This file handles Cross-Origin Resource Sharing (CORS) for all API endpoints
 */

// Set CORS headers
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
EOT;
    
    file_put_contents($corsHandlerPath, $corsContent);
    echo "CORS handler created successfully.\n";
}

// Find all PHP files recursively
function findPhpFiles($dir) {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            $result = array_merge($result, findPhpFiles($path));
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $result[] = $path;
        }
    }
    
    return $result;
}

// Check and fix CORS in a PHP file
function fixCorsInFile($file) {
    echo "Processing file: " . basename($file) . "...\n";
    
    // Read file content
    $content = file_get_contents($file);
    
    // Check if CORS handler is already included
    if (strpos($content, "require_once __DIR__ . '/../../cors.php'") !== false ||
        strpos($content, "require_once __DIR__ . '/../cors.php'") !== false ||
        strpos($content, "require_once __DIR__ . '/cors.php'") !== false) {
        echo "  - CORS handler already included.\n";
        return;
    }
    
    // Determine relative path to cors.php
    $relativePath = '';
    $apiDir = str_replace('\\', '/', __DIR__ . '/backend/api');
    $fileDir = str_replace('\\', '/', dirname($file));
    
    $relativeDir = str_replace($apiDir, '', $fileDir);
    $depth = substr_count($relativeDir, '/');
    
    if ($depth === 0) {
        $relativePath = "require_once __DIR__ . '/cors.php';";
    } else {
        $relativePath = "require_once __DIR__ . '/" . str_repeat('../', $depth) . "cors.php';";
    }
    
    // Find a good place to insert the CORS handler
    // After opening PHP tag or after any initial comments
    $pattern = '/<\?php\s*(?:\/\*[\s\S]*?\*\/\s*)?(?:\/\/[^\n]*\s*)*(?=\S)/';
    if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        $position = $matches[0][1] + strlen($matches[0][0]);
        
        // Insert CORS handler
        $newContent = substr($content, 0, $position) . 
                      "\n// Include CORS handler\n" . 
                      $relativePath . "\n\n" . 
                      substr($content, $position);
        
        // Write back to file
        file_put_contents($file, $newContent);
        echo "  - CORS handler added successfully.\n";
    } else {
        echo "  - Could not find a suitable position to insert CORS handler.\n";
    }
}

// Get all PHP files
$phpFiles = findPhpFiles($baseDir);
echo "Found " . count($phpFiles) . " PHP files.\n";

// Fix CORS in each file
foreach ($phpFiles as $file) {
    fixCorsInFile($file);
}

echo "\nCORS fix completed successfully!\n";
echo "Please restart your web server for the changes to take effect.\n";
?>
