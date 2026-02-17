<?php
/**
 * School Management System - Root Entry Point
 */

// Simple redirect to public directory
// Handle both encoded and unencoded URLs
$currentPath = $_SERVER['PHP_SELF'];
$redirectPath = str_replace('index.php', 'public/', $currentPath);

header('Location: ' . $redirectPath);
exit;
?>
