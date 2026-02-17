<?php
/**
 * School Management System - Standalone Entry Point
 */

// Set error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define paths
define('ROOT_PATH', __DIR__);
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');

// Load configuration
require_once CONFIG_PATH . '/config.php';

// Load helpers
require_once INCLUDES_PATH . '/helpers.php';

// Start session
require_once INCLUDES_PATH . '/Session.php';
$session = Session::getInstance();

// Load database classes
require_once INCLUDES_PATH . '/Database.php';

// Load models
require_once ROOT_PATH . '/app/models/User.php';

// Load controllers
require_once ROOT_PATH . '/app/controllers/AuthController.php';

// Parse request
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = str_replace(['/School management/run.php', '/run.php'], '', $uri);
$uri = trim($uri, '/');

// Simple routing
if ($uri === '' || $uri === 'login') {
    $controller = new AuthController();
    $controller->showLogin();
} else {
    echo "Page not found: " . htmlspecialchars($uri);
}
?>
