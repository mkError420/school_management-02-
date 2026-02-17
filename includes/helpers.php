<?php
/**
 * Helper Functions
 * School Management System
 */

require_once CONFIG_PATH . '/constants.php';

/**
 * Autoloader for classes
 * @param string $className
 */
spl_autoload_register(function ($className) {
    $paths = [
        APP_PATH . '/models/',
        APP_PATH . '/controllers/',
        APP_PATH . '/middleware/',
        INCLUDES_PATH . '/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token is valid (1 hour expiry)
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate pagination HTML
 * @param int $currentPage
 * @param int $totalPages
 * @param string $url
 * @return string
 */
function generatePagination($currentPage, $totalPages, $url = '') {
    if ($totalPages <= 1) return '';
    
    $pagination = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($currentPage - 1) . '">Previous</a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=1">1</a></li>';
        if ($start > 2) {
            $pagination .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $pagination .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $pagination .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($currentPage + 1) . '">Next</a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
    }
    
    $pagination .= '</ul></nav>';
    
    return $pagination;
}

/**
 * Format currency
 * @param float $amount
 * @param string $currency
 * @return string
 */
function formatCurrency($amount, $currency = 'BDT') {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Truncate text
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Generate slug from string
 * @param string $text
 * @return string
 */
function generateSlug($text) {
    // Convert to lowercase and replace spaces with hyphen
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Remove hyphens from beginning and end
    return trim($text, '-');
}

/**
 * Get file extension
 * @param string $filename
 * @return string
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Format file size
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Check if date is within range
 * @param string $date
 * @param string $startDate
 * @param string $endDate
 * @return bool
 */
function isDateInRange($date, $startDate, $endDate) {
    $date = strtotime($date);
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    
    return $date >= $start && $date <= $end;
}

/**
 * Get days between two dates
 * @param string $startDate
 * @param string $endDate
 * @return int
 */
function getDaysBetween($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    
    return $interval->days;
}

/**
 * Get current academic year
 * @return string
 */
function getCurrentAcademicYear() {
    $currentYear = date('Y');
    $currentMonth = date('n');
    
    if ($currentMonth >= 6) { // July onwards
        return $currentYear . '-' . ($currentYear + 1);
    } else {
        return ($currentYear - 1) . '-' . $currentYear;
    }
}

/**
 * Get user friendly time ago
 * @param string $datetime
 * @return string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 2592000) {
        return floor($diff / 86400) . ' days ago';
    } elseif ($diff < 31536000) {
        return floor($diff / 2592000) . ' months ago';
    } else {
        return floor($diff / 31536000) . ' years ago';
    }
}

/**
 * Create directory if it doesn't exist
 * @param string $path
 * @return bool
 */
function createDirectory($path) {
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

/**
 * Delete file if it exists
 * @param string $path
 * @return bool
 */
function deleteFile($path) {
    if (file_exists($path)) {
        return unlink($path);
    }
    return true;
}

/**
 * Upload file
 * @param array $file
 * @param string $directory
 * @param array $allowedTypes
 * @param int $maxSize
 * @return array
 */
function uploadFile($file, $directory, $allowedTypes = [], $maxSize = 5242880) {
    $result = [
        'success' => false,
        'message' => '',
        'filename' => ''
    ];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $result['message'] = 'No file uploaded or invalid file';
        return $result;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $result['message'] = 'File size exceeds maximum limit';
        return $result;
    }
    
    // Check file type
    if (!empty($allowedTypes)) {
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, $allowedTypes)) {
            $result['message'] = 'File type not allowed';
            return $result;
        }
    }
    
    // Generate unique filename
    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filepath = $directory . '/' . $filename;
    
    // Create directory if it doesn't exist
    if (!createDirectory($directory)) {
        $result['message'] = 'Failed to create upload directory';
        return $result;
    }
    
    // Move file to destination
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $result['success'] = true;
        $result['filename'] = $filename;
        $result['message'] = 'File uploaded successfully';
    } else {
        $result['message'] = 'Failed to upload file';
    }
    
    return $result;
}

/**
 * Send JSON response
 * @param mixed $data
 * @param int $statusCode
 * @param string $message
 */
function jsonResponse($data = null, $statusCode = 200, $message = '') {
    header_remove();
    header('Content-Type: application/json');
    http_response_code($statusCode);
    
    $response = [
        'status' => $statusCode >= 200 && $statusCode < 300 ? 'success' : 'error',
        'message' => $message,
        'data' => $data
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Log message to file
 * @param string $message
 * @param string $type
 */
function logMessage($message, $type = 'info') {
    $logFile = LOGS_PATH . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Get client IP address
 * @return string
 */
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Check if request is AJAX
 * @return bool
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get current URL
 * @return string
 */
function getCurrentURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    
    return $protocol . '://' . $host . $uri;
}

/**
 * Generate random password
 * @param int $length
 * @return string
 */
function generatePassword($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $password;
}

/**
 * Check password strength
 * @param string $password
 * @return array
 */
function checkPasswordStrength($password) {
    $strength = 0;
    $feedback = [];
    
    if (strlen($password) >= 8) {
        $strength++;
    } else {
        $feedback[] = 'Password should be at least 8 characters';
    }
    
    if (preg_match('/[a-z]/', $password)) {
        $strength++;
    } else {
        $feedback[] = 'Password should contain lowercase letters';
    }
    
    if (preg_match('/[A-Z]/', $password)) {
        $strength++;
    } else {
        $feedback[] = 'Password should contain uppercase letters';
    }
    
    if (preg_match('/[0-9]/', $password)) {
        $strength++;
    } else {
        $feedback[] = 'Password should contain numbers';
    }
    
    if (preg_match('/[!@#$%^&*]/', $password)) {
        $strength++;
    } else {
        $feedback[] = 'Password should contain special characters';
    }
    
    $strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    
    return [
        'strength' => $strength,
        'level' => $strengthLevels[$strength] ?? 'Very Weak',
        'feedback' => $feedback
    ];
}
