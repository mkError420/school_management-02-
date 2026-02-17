<?php
/**
 * API Routes
 * School Management System
 */

// Load configuration
require_once '../config/config.php';

// Load helpers
require_once INCLUDES_PATH . '/helpers.php';

// Load models and controllers
require_once INCLUDES_PATH . '/Session.php';
require_once APP_PATH . '/controllers/ApiController.php';

// Parse request
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string from URI
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = str_replace('/School management/api', '', $uri);
$uri = trim($uri, '/');

// Route definitions
$routes = [
    // Students
    'GET' => [
        'students' => 'getStudents',
        'teachers' => 'getTeachers',
        'classes' => 'getClasses',
        'subjects' => 'getSubjects',
        'attendance' => 'getAttendance',
        'results' => 'getResults',
        'search' => 'search',
        'notifications' => 'getNotifications',
        'stats' => 'getStats',
        'dashboard' => 'getDashboard'
    ],
    'POST' => [
        'students' => 'createStudent',
        'teachers' => 'createTeacher',
        'classes' => 'createClass',
        'subjects' => 'createSubject',
        'attendance' => 'markAttendance',
        'results' => 'storeResult',
        'login' => 'login',
        'logout' => 'logout',
        'upload' => 'uploadFile',
        'export' => 'exportData'
    ],
    'PUT' => [
        'students' => 'updateStudent',
        'teachers' => 'updateTeacher',
        'classes' => 'updateClass',
        'subjects' => 'updateSubject',
        'attendance' => 'updateAttendance',
        'results' => 'updateResult',
        'profile' => 'updateProfile'
    ],
    'DELETE' => [
        'students' => 'deleteStudent',
        'teachers' => 'deleteTeacher',
        'classes' => 'deleteClass',
        'subjects' => 'deleteSubject',
        'attendance' => 'deleteAttendance',
        'results' => 'deleteResult'
    ]
];

// Route handler
function handleRoute($uri, $method) {
    global $routes;
    
    // Check if route exists
    if (isset($routes[$method][$uri])) {
        $action = $routes[$method][$uri];
        callAPIAction($action);
        return;
    }
    
    // Handle dynamic routes with parameters
    foreach ($routes[$method] as $pattern => $action) {
        if (strpos($pattern, '{') !== false) {
            $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';
            
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches); // Remove full match
                callAPIAction($action, $matches);
                return;
            }
        }
    }
    
    // 404 Not Found
    jsonResponse(null, HTTP_NOT_FOUND, 'API endpoint not found');
}

// Call API action
function callAPIAction($action, $params = []) {
    try {
        $controller = new ApiController();
        
        if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $params);
        } else {
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Method not found');
        }
    } catch (Exception $e) {
        jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Server error: ' . $e->getMessage());
    }
}

// Handle the request
handleRoute($uri, $requestMethod);
