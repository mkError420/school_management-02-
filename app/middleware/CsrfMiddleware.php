<?php
/**
 * CSRF Protection Middleware
 * School Management System
 */

class CsrfMiddleware {
    private static $tokenLength = 32;
    private static $tokenExpiry = 3600; // 1 hour
    
    /**
     * Generate CSRF token
     * @return string
     */
    public static function generateToken() {
        $session = Session::getInstance();
        
        // Check if existing token is still valid
        if (isset($_SESSION['csrf_token']) && isset($_SESSION['csrf_token_time'])) {
            $tokenAge = time() - $_SESSION['csrf_token_time'];
            if ($tokenAge < self::$tokenExpiry) {
                return $_SESSION['csrf_token'];
            }
        }
        
        // Generate new token
        $token = bin2hex(random_bytes(self::$tokenLength));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     * @param string $token
     * @return bool
     */
    public static function validateToken($token) {
        $session = Session::getInstance();
        
        // Check if token exists
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Check if token has expired
        $tokenAge = time() - $_SESSION['csrf_token_time'];
        if ($tokenAge > self::$tokenExpiry) {
            self::clearToken();
            return false;
        }
        
        // Validate token
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Clear CSRF token
     */
    public static function clearToken() {
        $session = Session::getInstance();
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }
    
    /**
     * Get CSRF token for forms
     * @return string
     */
    public static function getToken() {
        return self::generateToken();
    }
    
    /**
     * Get CSRF token HTML input
     * @return string
     */
    public static function getHiddenInput() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Get CSRF token meta tag
     * @return string
     */
    public static function getMetaTag() {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Validate request CSRF token
     * @param array $request
     * @return bool
     */
    public static function validateRequest($request = null) {
        if ($request === null) {
            $request = $_REQUEST;
        }
        
        // Get token from various sources
        $token = null;
        
        // Check POST data
        if (isset($request['csrf_token'])) {
            $token = $request['csrf_token'];
        }
        // Check header
        elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        // Check GET data (only for specific routes)
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($request['csrf_token'])) {
            $token = $request['csrf_token'];
        }
        
        if ($token === null) {
            return false;
        }
        
        return self::validateToken($token);
    }
    
    /**
     * Apply CSRF protection to request
     * @param callable $callback
     * @return mixed
     */
    public static function protect($callback) {
        // Skip CSRF validation for safe methods
        $safeMethods = ['GET', 'HEAD', 'OPTIONS'];
        if (in_array($_SERVER['REQUEST_METHOD'], $safeMethods)) {
            return $callback();
        }
        
        // Validate CSRF token
        if (!self::validateRequest()) {
            if (isAjaxRequest()) {
                jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid CSRF token');
            } else {
                http_response_code(HTTP_BAD_REQUEST);
                echo '<h1>Invalid CSRF Token</h1><p>The request could not be validated. Please try again.</p>';
            }
            exit;
        }
        
        return $callback();
    }
    
    /**
     * Regenerate CSRF token
     * @return string
     */
    public static function regenerateToken() {
        self::clearToken();
        return self::generateToken();
    }
    
    /**
     * Check if token is expired
     * @return bool
     */
    public static function isTokenExpired() {
        $session = Session::getInstance();
        
        if (!isset($_SESSION['csrf_token_time'])) {
            return true;
        }
        
        $tokenAge = time() - $_SESSION['csrf_token_time'];
        return $tokenAge > self::$tokenExpiry;
    }
    
    /**
     * Get token age in seconds
     * @return int
     */
    public static function getTokenAge() {
        $session = Session::getInstance();
        
        if (!isset($_SESSION['csrf_token_time'])) {
            return 0;
        }
        
        return time() - $_SESSION['csrf_token_time'];
    }
    
    /**
     * Get token expiry time
     * @return int
     */
    public static function getTokenExpiry() {
        return self::$tokenExpiry;
    }
    
    /**
     * Set token expiry time
     * @param int $expiry
     */
    public static function setTokenExpiry($expiry) {
        self::$tokenExpiry = $expiry;
    }
    
    /**
     * Validate AJAX request
     * @return bool
     */
    public static function validateAjaxRequest() {
        if (!isAjaxRequest()) {
            return false;
        }
        
        return self::validateRequest();
    }
    
    /**
     * Get JavaScript for CSRF protection
     * @return string
     */
    public static function getJavaScript() {
        $token = self::getToken();
        
        return '<script>
// CSRF Protection
(function() {
    const csrfToken = "' . $token . '";
    
    // Add CSRF token to all AJAX requests
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        options.headers = options.headers || {};
        
        if (options.method && ["POST", "PUT", "DELETE"].includes(options.method.toUpperCase())) {
            options.headers["X-CSRF-Token"] = csrfToken;
        }
        
        return originalFetch(url, options);
    };
    
    // Add CSRF token to all forms
    document.addEventListener("DOMContentLoaded", function() {
        const forms = document.querySelectorAll("form");
        forms.forEach(function(form) {
            if (!form.querySelector("input[name=\'csrf_token\']")) {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "csrf_token";
                input.value = csrfToken;
                form.appendChild(input);
            }
        });
    });
    
    // Update CSRF token periodically
    setInterval(function() {
        fetch("/api/csrf-refresh", {method: "POST"})
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    window.csrfToken = data.token;
                }
            })
            .catch(error => console.error("Failed to refresh CSRF token:", error));
    }, 300000); // Refresh every 5 minutes
})();
</script>';
    }
}
