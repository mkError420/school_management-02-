<?php
echo "Apache can access this file!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
?>
