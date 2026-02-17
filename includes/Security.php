<?php
/**
 * Security Helper Functions
 * School Management System
 */

/**
 * Security Class
 */
class Security {
    /**
     * Generate secure random string
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Generate secure token
     * @param int $length
     * @return string
     */
    public static function generateToken($length = 32) {
        return base64_encode(random_bytes($length));
    }
    
    /**
     * Hash password securely
     * @param string $password
     * @return string
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Sanitize input for XSS prevention
     * @param string $input
     * @return string
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize output for XSS prevention
     * @param string $output
     * @return string
     */
    public static function escape($output) {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Clean input for database
     * @param mixed $input
     * @return mixed
     */
    public static function clean($input) {
        if (is_array($input)) {
            return array_map([self::class, 'clean'], $input);
        }
        
        return trim(strip_tags($input));
    }
    
    /**
     * Validate email format
     * @param string $email
     * @return bool
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate URL format
     * @param string $url
     * @return bool
     */
    public static function isValidUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate IP address
     * @param string $ip
     * @return bool
     */
    public static function isValidIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
    
    /**
     * Get client IP address
     * @return string
     */
    public static function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (self::isValidIP($ip)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Check if request is secure (HTTPS)
     * @return bool
     */
    public static function isSecureRequest() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443 ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Generate secure hash
     * @param string $data
     * @param string $salt
     * @return string
     */
    public static function hash($data, $salt = '') {
        return hash('sha256', $data . $salt);
    }
    
    /**
     * Verify hash
     * @param string $data
     * @param string $hash
     * @param string $salt
     * @return bool
     */
    public static function verifyHash($data, $hash, $salt = '') {
        return hash_equals($hash, self::hash($data, $salt));
    }
    
    /**
     * Encrypt data
     * @param string $data
     * @param string $key
     * @return string
     */
    public static function encrypt($data, $key = ENCRYPTION_KEY) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data
     * @param string $data
     * @param string $key
     * @return string|false
     */
    public static function decrypt($data, $key = ENCRYPTION_KEY) {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Generate password reset token
     * @param string $email
     * @return array
     */
    public static function generatePasswordResetToken($email) {
        $token = self::generateRandomString(32);
        $expiry = time() + 3600; // 1 hour
        
        return [
            'token' => $token,
            'hash' => self::hash($token . $email),
            'expiry' => $expiry
        ];
    }
    
    /**
     * Verify password reset token
     * @param string $token
     * @param string $email
     * @param string $hash
     * @param int $expiry
     * @return bool
     */
    public static function verifyPasswordResetToken($token, $email, $hash, $expiry) {
        if (time() > $expiry) {
            return false;
        }
        
        return self::verifyHash($token . $email, $hash);
    }
    
    /**
     * Generate API key
     * @param int $userId
     * @return array
     */
    public static function generateAPIKey($userId) {
        $key = self::generateRandomString(64);
        $secret = self::generateRandomString(32);
        $hash = self::hash($key . $secret);
        
        return [
            'key' => $key,
            'secret' => $secret,
            'hash' => $hash
        ];
    }
    
    /**
     * Validate API key
     * @param string $key
     * @param string $secret
     * @param string $hash
     * @return bool
     */
    public static function validateAPIKey($key, $secret, $hash) {
        return self::verifyHash($key . $secret, $hash);
    }
    
    /**
     * Check for SQL injection patterns
     * @param string $input
     * @return bool
     */
    public static function containsSQLInjection($input) {
        $patterns = [
            '/(\s|^)(select|insert|update|delete|drop|create|alter|exec|execute|union|script)/i',
            '/(\s|^)(or|and)\s+\d+\s*=\s*\d+/i',
            '/(\s|^)(or|and)\s+["\'][^"\']*["\']\s*=\s*["\'][^"\']*["\']/i',
            '/["\'][^"\']*["\']\s*=\s*["\'][^"\']*["\']/i',
            '/["\'][^"\']*["\']\s*;\s*/i',
            '/\/\*.*\*\//',
            '/--.*$/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for XSS patterns
     * @param string $input
     * @return bool
     */
    public static function containsXSS($input) {
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
            '/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/onmouseover\s*=/i',
            '/onfocus\s*=/i',
            '/onblur\s*=/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate file upload
     * @param array $file
     * @param array $allowedTypes
     * @param int $maxSize
     * @return array
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum limit';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'File type not allowed';
            }
        }
        
        // Check for malicious file names
        $fileName = basename($file['name']);
        if (preg_match('/\.(php|phtml|php3|php4|php5|pl|py|cgi|sh|exe|bat|cmd)$/i', $fileName)) {
            $errors[] = 'Potentially dangerous file type';
        }
        
        return $errors;
    }
    
    /**
     * Generate secure filename
     * @param string $filename
     * @return string
     */
    public static function generateSecureFilename($filename) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        // Remove special characters
        $basename = preg_replace('/[^a-zA-Z0-9-_]/', '', $basename);
        
        // Add timestamp and random string
        $timestamp = time();
        $random = self::generateRandomString(8);
        
        return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Rate limiting check
     * @param string $key
     * @param int $maxAttempts
     * @param int $timeWindow
     * @return bool
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
        $session = Session::getInstance();
        $attempts = $session->get('rate_limit_' . $key, []);
        
        // Remove old attempts
        $now = time();
        $attempts = array_filter($attempts, function($timestamp) use ($now, $timeWindow) {
            return $now - $timestamp < $timeWindow;
        });
        
        // Check if limit exceeded
        if (count($attempts) >= $maxAttempts) {
            return false;
        }
        
        // Add current attempt
        $attempts[] = $now;
        $session->set('rate_limit_' . $key, $attempts);
        
        return true;
    }
    
    /**
     * Log security event
     * @param string $event
     * @param array $context
     */
    public static function logSecurityEvent($event, $context = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => self::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'context' => $context
        ];
        
        $logMessage = json_encode($logEntry);
        error_log($logMessage . PHP_EOL, 3, LOGS_PATH . '/security.log');
    }
    
    /**
     * Check for suspicious activity
     * @param array $request
     * @return array
     */
    public static function checkSuspiciousActivity($request) {
        $suspicious = [];
        
        // Check for SQL injection
        foreach ($request as $key => $value) {
            if (is_string($value) && self::containsSQLInjection($value)) {
                $suspicious[] = "SQL injection attempt detected in field: $key";
                self::logSecurityEvent('SQL_INJECTION_ATTEMPT', ['field' => $key, 'value' => $value]);
            }
            
            if (is_string($value) && self::containsXSS($value)) {
                $suspicious[] = "XSS attempt detected in field: $key";
                self::logSecurityEvent('XSS_ATTEMPT', ['field' => $key, 'value' => $value]);
            }
        }
        
        // Check for unusual user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($userAgent) || strlen($userAgent) < 10) {
            $suspicious[] = "Suspicious user agent detected";
            self::logSecurityEvent('SUSPICIOUS_USER_AGENT', ['user_agent' => $userAgent]);
        }
        
        return $suspicious;
    }
    
    /**
     * Generate secure session ID
     * @return string
     */
    public static function generateSecureSessionId() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Validate session ID
     * @param string $sessionId
     * @return bool
     */
    public static function validateSessionId($sessionId) {
        return preg_match('/^[a-f0-9]{64}$/', $sessionId) === 1;
    }
    
    /**
     * Get security headers
     * @return array
     */
    public static function getSecurityHeaders() {
        return [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'none';",
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ];
    }
    
    /**
     * Set security headers
     */
    public static function setSecurityHeaders() {
        $headers = self::getSecurityHeaders();
        
        foreach ($headers as $header => $value) {
            header("$header: $value");
        }
    }
}
