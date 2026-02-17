<?php
/**
 * School Management System - Working Index
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load configuration (constants are defined here)
require_once CONFIG_PATH . '/config.php';

// Load helpers
require_once INCLUDES_PATH . '/helpers.php';

// Load database classes
require_once INCLUDES_PATH . '/Database.php';

// Start session
require_once INCLUDES_PATH . '/Session.php';
$session = Session::getInstance();

// Parse request
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = parse_url($requestUri, PHP_URL_PATH);

// Remove base directory from URI
$basePaths = ['/School management/public', '/School%20management/public'];
foreach ($basePaths as $basePath) {
    if (strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
        break;
    }
}

$uri = trim($uri, '/');

// Simple routing
if ($uri === '' || $uri === 'login') {
    // Show login page
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->showLogin();
} elseif ($uri === 'debug' || isset($_GET['debug'])) {
    echo "<h1>Debug Information</h1>";
    echo "<p>REQUEST_URI: " . htmlspecialchars($requestUri) . "</p>";
    echo "<p>Clean URI: '" . htmlspecialchars($uri) . "'</p>";
    echo "<p>Method: " . $requestMethod . "</p>";
    echo "<p>ROOT_PATH: " . ROOT_PATH . "</p>";
    echo "<p>CONFIG_PATH: " . CONFIG_PATH . "</p>";
    echo "<p>INCLUDES_PATH: " . INCLUDES_PATH . "</p>";
    echo "<p>APP_PATH: " . APP_PATH . "</p>";
    echo "<p>PHP_SELF: " . $_SERVER['PHP_SELF'] . "</p>";
    echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
} else {
    echo "<h1>Page Not Found</h1>";
    echo "<p>The page '" . htmlspecialchars($uri) . "' was not found.</p>";
    echo "<p><a href=''>Go to Login</a></p>";
}
?>
