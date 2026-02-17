<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load configuration
require_once '../config/config.php';

// Load helpers
require_once INCLUDES_PATH . '/helpers.php';

// Start session
require_once INCLUDES_PATH . '/Session.php';

echo "Testing database connection...<br>";

try {
    $db = new Database();
    echo "Database connection: SUCCESS<br>";
    
    // Test a simple query
    $result = $db->table('users')->first();
    if ($result) {
        echo "Query test: SUCCESS - Found user: " . $result->username . "<br>";
    } else {
        echo "Query test: No users found<br>";
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
    echo "Error File: " . $e->getFile() . " line " . $e->getLine() . "<br>";
}

echo "Session test...<br>";

try {
    $session = Session::getInstance();
    echo "Session: SUCCESS<br>";
} catch (Exception $e) {
    echo "Session Error: " . $e->getMessage() . "<br>";
}

echo "Configuration test...<br>";
echo "APP_ENV: " . APP_ENV . "<br>";
echo "Database host: " . DB_HOST . "<br>";
echo "Database name: " . DB_NAME . "<br>";

echo "<br><strong>Test completed!</strong><br>";
echo "If you see this page, the basic setup is working.";
?>
