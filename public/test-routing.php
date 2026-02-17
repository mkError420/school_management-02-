<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Testing routing logic...<br>";

// Simulate the routing logic from index.php
$requestUri = $_SERVER['REQUEST_URI'];
echo "REQUEST_URI: " . $requestUri . "<br>";

$uri = parse_url($requestUri, PHP_URL_PATH);
echo "Parsed URI: " . $uri . "<br>";

// Remove base directory from URI (handle both encoded and unencoded)
$basePaths = ['/School management/public', '/School%20management/public'];
foreach ($basePaths as $basePath) {
    if (strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
        echo "Removed base path: " . $basePath . "<br>";
        echo "Remaining URI: " . $uri . "<br>";
        break;
    }
}

$uri = trim($uri, '/');
echo "Final URI: '" . $uri . "'<br>";

// Test route matching
$routes = [
    '' => 'AuthController@showLogin',
    'login' => 'AuthController@showLogin',
    'debug-routing' => 'DebugController@debug',
];

if (isset($routes[$uri])) {
    echo "✅ Route found: " . $routes[$uri] . "<br>";
} else {
    echo "❌ No route found for: '" . $uri . "'<br>";
    echo "Available routes: " . implode(', ', array_keys($routes)) . "<br>";
}
?>
