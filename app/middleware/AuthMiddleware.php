<?php
/**
 * Authentication Middleware
 * School Management System
 */

require_once INCLUDES_PATH . '/Session.php';

class AuthMiddleware {
    private $session;
    private $allowedRoles;
    
    public function __construct($allowedRoles = []) {
        $this->session = Session::getInstance();
        $this->allowedRoles = $allowedRoles;
    }
    
    /**
     * Handle authentication check
     * @param callable $next
     * @return void
     */
    public function handle($next) {
        // Check if user is authenticated
        if (!$this->session->validate()) {
            $this->handleUnauthenticated();
            return;
        }
        
        // Check role permissions if roles are specified
        if (!empty($this->allowedRoles) && !$this->hasRequiredRole()) {
            $this->handleUnauthorized();
            return;
        }
        
        // Call next middleware or controller
        $next();
    }
    
    /**
     * Check if user has required role
     * @return bool
     */
    private function hasRequiredRole() {
        $userRole = $this->session->getUserRole();
        
        // Admin has access to everything
        if ($userRole === ROLE_ADMIN) {
            return true;
        }
        
        return in_array($userRole, $this->allowedRoles);
    }
    
    /**
     * Handle unauthenticated requests
     */
    private function handleUnauthenticated() {
        if (isAjaxRequest()) {
            jsonResponse(null, HTTP_UNAUTHORIZED, 'Please login to access this page');
        } else {
            $this->session->setFlash('Please login to access this page!', 'error');
            redirect('login');
        }
        exit;
    }
    
    /**
     * Handle unauthorized requests
     */
    private function handleUnauthorized() {
        if (isAjaxRequest()) {
            jsonResponse(null, HTTP_FORBIDDEN, 'Access denied!');
        } else {
            $this->session->setFlash('Access denied!', 'error');
            redirect('login');
        }
        exit;
    }
    
    /**
     * Create middleware for specific roles
     * @param array $roles
     * @return AuthMiddleware
     */
    public static function forRoles($roles) {
        return new self($roles);
    }
    
    /**
     * Create middleware for admin only
     * @return AuthMiddleware
     */
    public static function admin() {
        return new self([ROLE_ADMIN]);
    }
    
    /**
     * Create middleware for teachers
     * @return AuthMiddleware
     */
    public static function teacher() {
        return new self([ROLE_ADMIN, ROLE_TEACHER]);
    }
    
    /**
     * Create middleware for students
     * @return AuthMiddleware
     */
    public static function student() {
        return new self([ROLE_ADMIN, ROLE_TEACHER, ROLE_STUDENT]);
    }
    
    /**
     * Check if current user can access resource
     * @param int $resourceOwnerId
     * @return bool
     */
    public static function canAccessResource($resourceOwnerId = null) {
        $session = Session::getInstance();
        
        if (!$session->validate()) {
            return false;
        }
        
        $userRole = $session->getUserRole();
        $userId = $session->getUserId();
        
        // Admin can access everything
        if ($userRole === ROLE_ADMIN) {
            return true;
        }
        
        // Check if user owns the resource
        if ($resourceOwnerId && $userId == $resourceOwnerId) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get current user ID
     * @return int|null
     */
    public static function getCurrentUserId() {
        $session = Session::getInstance();
        return $session->getUserId();
    }
    
    /**
     * Get current user role
     * @return string|null
     */
    public static function getCurrentUserRole() {
        $session = Session::getInstance();
        return $session->getUserRole();
    }
    
    /**
     * Check if current user is admin
     * @return bool
     */
    public static function isAdmin() {
        return self::getCurrentUserRole() === ROLE_ADMIN;
    }
    
    /**
     * Check if current user is teacher
     * @return bool
     */
    public static function isTeacher() {
        return self::getCurrentUserRole() === ROLE_TEACHER;
    }
    
    /**
     * Check if current user is student
     * @return bool
     */
    public static function isStudent() {
        return self::getCurrentUserRole() === ROLE_STUDENT;
    }
    
    /**
     * Require authentication
     * @param callable $callback
     * @return void
     */
    public static function requireAuth($callback) {
        $middleware = new self();
        $middleware->handle($callback);
    }
    
    /**
     * Require specific role
     * @param array $roles
     * @param callable $callback
     * @return void
     */
    public static function requireRole($roles, $callback) {
        $middleware = new self($roles);
        $middleware->handle($callback);
    }
    
    /**
     * Rate limiting middleware
     * @param int $maxRequests
     * @param int $timeWindow
     * @return bool
     */
    public static function rateLimit($maxRequests = 60, $timeWindow = 60) {
        $session = Session::getInstance();
        $clientIP = getClientIP();
        $key = 'rate_limit_' . md5($clientIP);
        
        $requests = $session->get($key, []);
        $now = time();
        
        // Remove old requests
        $requests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
            return $now - $timestamp < $timeWindow;
        });
        
        // Check if limit exceeded
        if (count($requests) >= $maxRequests) {
            if (isAjaxRequest()) {
                jsonResponse(null, HTTP_TOO_MANY_REQUESTS, 'Too many requests. Please try again later.');
            } else {
                http_response_code(429);
                echo '<h1>Too Many Requests</h1><p>Please try again later.</p>';
            }
            exit;
        }
        
        // Add current request
        $requests[] = $now;
        $session->set($key, $requests);
        
        return true;
    }
}
