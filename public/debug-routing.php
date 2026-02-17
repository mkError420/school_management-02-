<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Debugging routing...<br>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "<br>";

// Test basic functionality
echo "<br>Testing basic includes...<br>";
try {
    require_once '../config/config.php';
    echo "✅ Config loaded<br>";
    
    require_once INCLUDES_PATH . '/helpers.php';
    echo "✅ Helpers loaded<br>";
    
    require_once INCLUDES_PATH . '/Session.php';
    echo "✅ Session loaded<br>";
    
    require_once INCLUDES_PATH . '/Database.php';
    echo "✅ Database loaded<br>";
    
    echo "<br>✅ All includes successful!<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
