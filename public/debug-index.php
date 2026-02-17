<?php
// Enable all error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Debugging index.php...<br><br>";

try {
    echo "1. Loading configuration...<br>";
    require_once '../config/config.php';
    echo "✅ Configuration loaded<br>";
    
    echo "2. Loading helpers...<br>";
    require_once INCLUDES_PATH . '/helpers.php';
    echo "✅ Helpers loaded<br>";
    
    echo "3. Starting session...<br>";
    require_once INCLUDES_PATH . '/Session.php';
    $session = Session::getInstance();
    echo "✅ Session started<br>";
    
    echo "4. Loading database classes...<br>";
    require_once INCLUDES_PATH . '/Database.php';
    echo "✅ Database classes loaded<br>";
    
    echo "5. Testing database connection...<br>";
    $db = new QueryBuilder();
    echo "✅ Database connected<br>";
    
    echo "6. Testing query...<br>";
    $result = $db->table('users')->first();
    if ($result) {
        echo "✅ Query successful - Found user: " . $result->username . "<br>";
    } else {
        echo "⚠️ No users found<br>";
    }
    
    echo "<br><strong>All basic tests passed!</strong><br>";
    
    echo "<br>Constants:<br>";
    echo "APP_ENV: " . APP_ENV . "<br>";
    echo "INCLUDES_PATH: " . INCLUDES_PATH . "<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " line " . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " line " . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>
