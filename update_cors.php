<?php
/**
 * Update CORS Script
 * This script adds the CORS handler include to all API endpoint files
 */

// Directory to scan
$apiDir = __DIR__ . '/backend/api/endpoints';

// Find all PHP files recursively
function findPhpFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

// Update file to include CORS handler
function updateFile($file) {
    $content = file_get_contents($file);
    
    // Skip if already updated
    if (strpos($content, "require_once __DIR__ . '/../../cors.php'") !== false ||
        strpos($content, "require_once __DIR__ . '/../cors.php'") !== false ||
        strpos($content, "require_once __DIR__ . '/cors.php'") !== false) {
        echo "Skipping already updated file: $file\n";
        return;
    }
    
    // Determine relative path to cors.php
    $relativePath = '';
    $depth = substr_count(str_replace(__DIR__ . '/backend/api/endpoints', '', $file), '/');
    
    if ($depth === 0) {
        $relativePath = "require_once __DIR__ . '/cors.php';";
    } else {
        $relativePath = "require_once __DIR__ . '/" . str_repeat('../', $depth) . "cors.php';";
    }
    
    // Add CORS include after opening PHP tag
    $updated = preg_replace(
        '/<\?php/',
        "<?php\n// Include CORS handler\n$relativePath\n",
        $content,
        1
    );
    
    // Write back to file
    file_put_contents($file, $updated);
    echo "Updated file: $file\n";
}

// Get all PHP files
$files = findPhpFiles($apiDir);
echo "Found " . count($files) . " PHP files to update\n";

// Update each file
foreach ($files as $file) {
    updateFile($file);
}

echo "CORS update complete!\n";
