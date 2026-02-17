<?php
/**
 * School Management System - Root Entry Point
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load configuration first (constants are defined here)
require_once '../config/config.php';

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

// Remove base directory from URI (handle both encoded and unencoded)
$basePaths = ['/School management/public', '/School%20management/public'];
foreach ($basePaths as $basePath) {
    if (strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
        break;
    }
}

$uri = trim($uri, '/');

// Simple routing
if ($uri === '') {
    // Load and show login page directly
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->showLogin();
} elseif ($uri === 'login' && $requestMethod === 'POST') {
    // Handle login form submission
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->login();
} elseif ($uri === 'login') {
    // Show login page for GET requests
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->showLogin();
} elseif ($uri === 'admin' || $uri === 'admin/dashboard') {
    // Admin dashboard
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->dashboard();
} elseif ($uri === 'admin/dashboard') {
    // Admin dashboard (alternative route)
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->dashboard();
} elseif ($uri === 'teacher' || $uri === 'teacher/dashboard') {
    // Teacher dashboard
    require_once APP_PATH . '/controllers/TeacherController.php';
    $controller = new TeacherController();
    $controller->dashboard();
} elseif ($uri === 'student' || $uri === 'student/dashboard') {
    // Student dashboard
    require_once APP_PATH . '/controllers/StudentController.php';
    $controller = new StudentController();
    $controller->dashboard();
} else {
    // Handle other routes
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The page '" . htmlspecialchars($uri) . "' was not found.</p>";
    echo "<p><a href='/School management/'>Go to Login</a></p>";
}
?>
