<?php
echo "Apache can access this file!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "<br>";

// Test if we can access the public directory
$publicPath = __DIR__ . '/public';
if (is_dir($publicPath)) {
    echo "✅ Public directory exists: " . $publicPath . "<br>";
    
    // List files in public directory
    $files = scandir($publicPath);
    echo "Files in public directory:<br>";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "- " . $file . "<br>";
        }
    }
} else {
    echo "❌ Public directory not found: " . $publicPath . "<br>";
}
?>
