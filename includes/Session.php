<?php
/**
 * Session Management Class
 * School Management System
 */

require_once CONFIG_PATH . '/config.php';

class Session {
    private static $instance = null;
    private $sessionStarted = false;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        if (!$this->sessionStarted) {
            $this->startSession();
        }
    }
    
    /**
     * Get singleton instance
     * @return Session
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Start secure session
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', APP_ENV === 'production');
            ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
            
            // Set custom session name
            session_name('SMS_SESSION');
            
            // Start session
            session_start();
            
            // Regenerate session ID for security
            if (!isset($_SESSION['regenerated'])) {
                $this->regenerate();
                $_SESSION['regenerated'] = true;
            }
            
            $this->sessionStarted = true;
        }
    }
    
    /**
     * Set session value
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
        return true;
    }
    
    /**
     * Get session value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     * @param string $key
     * @return bool
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     * @param string $key
     * @return bool
     */
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }
    
    /**
     * Clear all session data
     * @return bool
     */
    public function clear() {
        $_SESSION = [];
        return true;
    }
    
    /**
     * Destroy session completely
     * @return bool
     */
    public function destroy() {
        if ($this->sessionStarted) {
            // Clear session data
            $_SESSION = [];
            
            // Delete session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            // Destroy session
            session_destroy();
            $this->sessionStarted = false;
            return true;
        }
        return false;
    }
    
    /**
     * Regenerate session ID
     * @param bool $deleteOldSession
     * @return bool
     */
    public function regenerate($deleteOldSession = true) {
        if ($this->sessionStarted) {
            return session_regenerate_id($deleteOldSession);
        }
        return false;
    }
    
    /**
     * Set flash message
     * @param string $message
     * @param string $type
     * @return bool
     */
    public function setFlash($message, $type = 'info') {
        $this->set('flash_message', $message);
        $this->set('flash_type', $type);
        return true;
    }
    
    /**
     * Get flash message
     * @return array|null
     */
    public function getFlash() {
        if ($this->has('flash_message')) {
            $flash = [
                'message' => $this->get('flash_message'),
                'type' => $this->get('flash_type', 'info')
            ];
            
            // Remove flash message after getting it
            $this->remove('flash_message');
            $this->remove('flash_type');
            
            return $flash;
        }
        return null;
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return $this->has('user_id') && $this->has('user_role');
    }
    
    /**
     * Get current user ID
     * @return int|null
     */
    public function getUserId() {
        return $this->get('user_id');
    }
    
    /**
     * Get current user role
     * @return string|null
     */
    public function getUserRole() {
        return $this->get('user_role');
    }
    
    /**
     * Get current user data
     * @return array|null
     */
    public function getUser() {
        return $this->get('user');
    }
    
    /**
     * Login user
     * @param object $user
     * @param bool $remember
     * @return bool
     */
    public function login($user, $remember = false) {
        // Set session data
        $this->set('user_id', $user->id);
        $this->set('user_role', $user->role);
        $this->set('user', (array) $user);
        $this->set('login_time', time());
        
        // Regenerate session ID for security
        $this->regenerate();
        
        // Handle remember me
        if ($remember) {
            $this->setRememberMe($user->id);
        }
        
        return true;
    }
    
    /**
     * Logout user
     * @return bool
     */
    public function logout() {
        // Clear remember me cookie
        $this->clearRememberMe();
        
        // Clear session data
        $this->clear();
        
        // Regenerate session ID
        $this->regenerate();
        
        return true;
    }
    
    /**
     * Set remember me cookie
     * @param int $userId
     * @return bool
     */
    private function setRememberMe($userId) {
        $token = bin2hex(random_bytes(32));
        $selector = bin2hex(random_bytes(16));
        $expires = time() + REMEMBER_LIFETIME;
        
        // Store in session
        $this->set('remember_token', $token);
        $this->set('remember_selector', $selector);
        
        // Set cookie
        $cookieValue = $selector . ':' . $token;
        setcookie(
            'remember_me',
            $cookieValue,
            $expires,
            '/',
            '',
            APP_ENV === 'production',
            true
        );
        
        return true;
    }
    
    /**
     * Clear remember me cookie
     * @return bool
     */
    private function clearRememberMe() {
        if (isset($_COOKIE['remember_me'])) {
            setcookie(
                'remember_me',
                '',
                time() - 3600,
                '/',
                '',
                APP_ENV === 'production',
                true
            );
            unset($_COOKIE['remember_me']);
        }
        
        $this->remove('remember_token');
        $this->remove('remember_selector');
        
        return true;
    }
    
    /**
     * Check session timeout
     * @return bool
     */
    public function isExpired() {
        $loginTime = $this->get('login_time');
        if ($loginTime) {
            return (time() - $loginTime) > SESSION_LIFETIME;
        }
        return true;
    }
    
    /**
     * Refresh session timeout
     * @return bool
     */
    public function refresh() {
        if ($this->isLoggedIn()) {
            $this->set('login_time', time());
            return true;
        }
        return false;
    }
    
    /**
     * Validate session
     * @return bool
     */
    public function validate() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if ($this->isExpired()) {
            $this->logout();
            return false;
        }
        
        $this->refresh();
        return true;
    }
    
    /**
     * Get session ID
     * @return string
     */
    public function getId() {
        return session_id();
    }
    
    /**
     * Get all session data
     * @return array
     */
    public function all() {
        return $_SESSION;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {}
}
