<?php
/**
 * Authentication Controller
 * School Management System
 */

class AuthController {
    private $userModel;
    private $session;
    
    public function __construct() {
        require_once INCLUDES_PATH . '/Session.php';
        require_once APP_PATH . '/models/User.php';
        
        $this->userModel = new User();
        $this->session = Session::getInstance();
    }
    
    /**
     * Show login page
     */
    public function showLogin() {
        // Redirect if already logged in
        if ($this->session->isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        
        include APP_PATH . '/views/auth/login.php';
    }
    
    /**
     * Process login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/School management/public/login');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('/School management/public/login');
            return;
        }
        
        // Get and sanitize input
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->session->setFlash('Please enter username and password!', 'error');
            redirect('/School management/public/login');
            return;
        }
        
        // Find user
        $user = $this->userModel->findByUsernameOrEmail($username);
        
        if (!$user || !$this->userModel->verifyPassword($password, $user->password)) {
            $this->session->setFlash('Invalid username or password!', 'error');
            redirect('/School management/public/login');
            return;
        }
        
        // Check if user is active
        if ($user->status !== STATUS_ACTIVE) {
            $this->session->setFlash('Your account is inactive. Please contact administrator!', 'error');
            redirect('/School management/public/login');
            return;
        }
        
        // Login user
        $this->session->login($user, $remember);
        
        // Update last login
        // Temporarily disabled to isolate SQL parameter error
        // $this->userModel->updateLastLogin($user->id);
        
        // Redirect to dashboard
        $this->redirectToDashboard();
    }
    
    /**
     * Logout user
     */
    public function logout() {
        $this->session->logout();
        $this->session->setFlash('You have been logged out successfully!', 'success');
        redirect('/School management/public/login');
    }
    
    /**
     * Show forgot password page
     */
    public function showForgotPassword() {
        if ($this->session->isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        
        include APP_PATH . '/views/auth/forgot_password.php';
    }
    
    /**
     * Process forgot password
     */
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('forgot-password');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('forgot-password');
            return;
        }
        
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($email) || !isValidEmail($email)) {
            $this->session->setFlash('Please enter a valid email address!', 'error');
            redirect('forgot-password');
            return;
        }
        
        // Find user by email
        $user = $this->userModel->findByUsernameOrEmail($email);
        
        if ($user) {
            // Generate temporary password
            $newPassword = generatePassword(8);
            
            // Update password
            if ($this->userModel->resetPassword($user->id, $newPassword)) {
                // In a real application, send email here
                // For demo, show the password
                $this->session->setFlash("Your password has been reset. New password: <strong>{$newPassword}</strong>", 'info');
                redirect('login');
                return;
            }
        }
        
        // Always show success message for security
        $this->session->setFlash('If your email exists in our system, you will receive a password reset link.', 'info');
        redirect('forgot-password');
    }
    
    /**
     * Show profile page
     */
    public function showProfile() {
        if (!$this->session->isLoggedIn()) {
            $this->session->setFlash('Please login to access this page!', 'error');
            redirect('login');
            return;
        }
        
        $userId = $this->session->getUserId();
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $this->session->setFlash('User not found!', 'error');
            $this->logout();
            return;
        }
        
        // Get additional user data based on role
        $additionalData = $this->getUserAdditionalData($user);
        
        include APP_PATH . '/views/auth/profile.php';
    }
    
    /**
     * Update profile
     */
    public function updateProfile() {
        if (!$this->session->isLoggedIn()) {
            jsonResponse(null, HTTP_UNAUTHORIZED, 'Please login to access this page');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(null, HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid request');
            return;
        }
        
        $userId = $this->session->getUserId();
        $data = [
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'address' => sanitize($_POST['address'] ?? '')
        ];
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone' => 'phone',
            'address' => 'max:255'
        ];
        
        if (!$validator->validate($data, $rules)) {
            jsonResponse($validator->getErrors(), HTTP_BAD_REQUEST, 'Validation failed');
            return;
        }
        
        // Update user
        if ($this->userModel->update($userId, $data)) {
            // Update session data
            $user = $this->userModel->findById($userId);
            $this->session->set('user', (array) $user);
            
            jsonResponse(['user' => $user], HTTP_OK, 'Profile updated successfully');
        } else {
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to update profile');
        }
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        if (!$this->session->isLoggedIn()) {
            jsonResponse(null, HTTP_UNAUTHORIZED, 'Please login to access this page');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(null, HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid request');
            return;
        }
        
        $userId = $this->session->getUserId();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'All fields are required');
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'New passwords do not match');
            return;
        }
        
        // Check password strength
        $passwordCheck = checkPasswordStrength($newPassword);
        if ($passwordCheck['strength'] < 3) {
            jsonResponse(['feedback' => $passwordCheck['feedback']], HTTP_BAD_REQUEST, 'Password is too weak');
            return;
        }
        
        // Get current user
        $user = $this->userModel->findById($userId);
        if (!$user || !$this->userModel->verifyPassword($currentPassword, $user->password)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Current password is incorrect');
            return;
        }
        
        // Update password
        if ($this->userModel->resetPassword($userId, $newPassword)) {
            jsonResponse(null, HTTP_OK, 'Password changed successfully');
        } else {
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to change password');
        }
    }
    
    /**
     * Redirect to appropriate dashboard based on user role
     */
    private function redirectToDashboard() {
        $role = $this->session->getUserRole();
        
        switch ($role) {
            case ROLE_ADMIN:
                redirect('admin/dashboard');
                break;
            case ROLE_TEACHER:
                redirect('teacher/dashboard');
                break;
            case ROLE_STUDENT:
                redirect('student/dashboard');
                break;
            default:
                redirect('login');
                break;
        }
    }
    
    /**
     * Get additional user data based on role
     * @param object $user
     * @return object|null
     */
    private function getUserAdditionalData($user) {
        switch ($user->role) {
            case ROLE_ADMIN:
                return null; // Admins don't have additional data
            case ROLE_TEACHER:
                // Get teacher data
                $teacher = $this->db->table('teachers')->where('user_id', $user->id)->first();
                return $teacher;
            case ROLE_STUDENT:
                // Get student data
                $student = $this->db->table('students')->where('user_id', $user->id)->first();
                return $student;
            default:
                return null;
        }
    }
    
    /**
     * Check if user is authenticated
     * @return bool
     */
    public function isAuthenticated() {
        return $this->session->validate();
    }
    
    /**
     * Get current user
     * @return object|null
     */
    public function getCurrentUser() {
        if ($this->isAuthenticated()) {
            return (object) $this->session->getUser();
        }
        return null;
    }
    
    /**
     * Check user permission
     * @param string $requiredRole
     * @return bool
     */
    public function hasPermission($requiredRole) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        // Admin has access to everything
        if ($user->role === ROLE_ADMIN) return true;
        
        // Check role hierarchy
        $roleHierarchy = [
            ROLE_ADMIN => 3,
            ROLE_TEACHER => 2,
            ROLE_STUDENT => 1
        ];
        
        $userLevel = $roleHierarchy[$user->role] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
}
