<?php
/**
 * Application Configuration
 * School Management System
 */

// Define constants
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Application settings
define('APP_NAME', 'School Management System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, production

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security settings
define('ENCRYPTION_KEY', 'your-secret-key-here-change-in-production');
define('HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_LIFETIME', 2592000); // 30 days

// Pagination
define('ITEMS_PER_PAGE', 10);
define('MAX_ITEMS_PER_PAGE', 50);

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Email settings (if needed)
define('MAIL_FROM_EMAIL', 'noreply@school.com');
define('MAIL_FROM_NAME', APP_NAME);

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/error.log');
}

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', APP_ENV === 'production');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// CORS headers (if API is used separately)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $message = "Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}";
    
    if (APP_ENV === 'development') {
        echo "<div class='alert alert-danger'>{$message}</div>";
    } else {
        error_log($message);
        echo "<div class='alert alert-danger'>Something went wrong. Please try again later.</div>";
    }
    
    return true;
}

// Set custom error handler
set_error_handler('customErrorHandler');

// Global helper function for configuration
function config($key, $default = null) {
    $config = [
        'app' => [
            'name' => APP_NAME,
            'version' => APP_VERSION,
            'env' => APP_ENV,
        ],
        'database' => [
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'charset' => DB_CHARSET,
        ],
        'security' => [
            'encryption_key' => ENCRYPTION_KEY,
            'session_lifetime' => SESSION_LIFETIME,
            'remember_lifetime' => REMEMBER_LIFETIME,
        ],
        'pagination' => [
            'items_per_page' => ITEMS_PER_PAGE,
            'max_items_per_page' => MAX_ITEMS_PER_PAGE,
        ],
        'upload' => [
            'max_file_size' => MAX_FILE_SIZE,
            'allowed_types' => ALLOWED_FILE_TYPES,
        ],
    ];
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}
