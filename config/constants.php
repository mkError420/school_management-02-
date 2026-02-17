<?php
/**
 * Application Constants
 * School Management System
 */

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STUDENT', 'student');

// User statuses
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_GRADUATED', 'graduated');

// Enrollment statuses
define('ENROLLMENT_ACTIVE', 'active');
define('ENROLLMENT_TRANSFERRED', 'transferred');
define('ENROLLMENT_COMPLETED', 'completed');

// Attendance statuses
define('ATTENDANCE_PRESENT', 'present');
define('ATTENDANCE_ABSENT', 'absent');
define('ATTENDANCE_LATE', 'late');
define('ATTENDANCE_EXCUSED', 'excused');

// Exam types
define('EXAM_MIDTERM', 'midterm');
define('EXAM_FINAL', 'final');
define('EXAM_QUIZ', 'quiz');
define('EXAM_ASSIGNMENT', 'assignment');

// Exam statuses
define('EXAM_DRAFT', 'draft');
define('EXAM_PUBLISHED', 'published');
define('EXAM_COMPLETED', 'completed');

// Gender options
define('GENDER_MALE', 'male');
define('GENDER_FEMALE', 'female');
define('GENDER_OTHER', 'other');

// HTTP status codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_INTERNAL_SERVER_ERROR', 500);

// Response messages
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// Common messages
define('MSG_LOGIN_SUCCESS', 'Login successful!');
define('MSG_LOGIN_FAILED', 'Invalid username or password!');
define('MSG_LOGOUT_SUCCESS', 'You have been logged out successfully!');
define('MSG_ACCESS_DENIED', 'Access denied!');
define('MSG_INVALID_REQUEST', 'Invalid request!');
define('MSG_OPERATION_SUCCESS', 'Operation completed successfully!');
define('MSG_OPERATION_FAILED', 'Operation failed!');
define('MSG_DATA_NOT_FOUND', 'Data not found!');
define('MSG_REQUIRED_FIELD', 'This field is required!');
define('MSG_INVALID_EMAIL', 'Please enter a valid email address!');
define('MSG_PASSWORD_MISMATCH', 'Passwords do not match!');
define('MSG_FILE_TOO_LARGE', 'File size exceeds maximum limit!');
define('MSG_INVALID_FILE_TYPE', 'Invalid file type!');

// Grade boundaries
define('GRADE_A_PLUS', 90);
define('GRADE_A', 80);
define('GRADE_B_PLUS', 70);
define('GRADE_B', 60);
define('GRADE_C_PLUS', 50);
define('GRADE_C', 40);
define('GRADE_D', 33);
define('GRADE_F', 0);

// Grade labels
$grade_labels = [
    'A+' => 'Excellent',
    'A' => 'Very Good',
    'B+' => 'Good',
    'B' => 'Satisfactory',
    'C+' => 'Average',
    'C' => 'Below Average',
    'D' => 'Poor',
    'F' => 'Fail'
];

// Navigation menus
$admin_menu = [
    'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
    'students' => ['title' => 'Students', 'icon' => 'fas fa-user-graduate'],
    'teachers' => ['title' => 'Teachers', 'icon' => 'fas fa-chalkboard-teacher'],
    'classes' => ['title' => 'Classes', 'icon' => 'fas fa-school'],
    'subjects' => ['title' => 'Subjects', 'icon' => 'fas fa-book'],
    'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check'],
    'results' => ['title' => 'Results', 'icon' => 'fas fa-chart-line'],
    'reports' => ['title' => 'Reports', 'icon' => 'fas fa-file-alt'],
    'settings' => ['title' => 'Settings', 'icon' => 'fas fa-cog']
];

$teacher_menu = [
    'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
    'my_classes' => ['title' => 'My Classes', 'icon' => 'fas fa-users'],
    'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check'],
    'marks' => ['title' => 'Marks', 'icon' => 'fas fa-pen'],
    'students' => ['title' => 'Students', 'icon' => 'fas fa-user-graduate'],
    'reports' => ['title' => 'Reports', 'icon' => 'fas fa-file-alt']
];

$student_menu = [
    'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
    'profile' => ['title' => 'Profile', 'icon' => 'fas fa-user'],
    'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check'],
    'results' => ['title' => 'Results', 'icon' => 'fas fa-chart-line'],
    'schedule' => ['title' => 'Schedule', 'icon' => 'fas fa-calendar-alt']
];

// Academic years
function getAcademicYears() {
    $currentYear = date('Y');
    $years = [];
    
    for ($i = 0; $i < 5; $i++) {
        $year = $currentYear - $i;
        $years[] = ($year - 1) . '-' . $year;
    }
    
    return $years;
}

// Get grade from marks
function getGrade($marks) {
    if ($marks >= GRADE_A_PLUS) return 'A+';
    if ($marks >= GRADE_A) return 'A';
    if ($marks >= GRADE_B_PLUS) return 'B+';
    if ($marks >= GRADE_B) return 'B';
    if ($marks >= GRADE_C_PLUS) return 'C+';
    if ($marks >= GRADE_C) return 'C';
    if ($marks >= GRADE_D) return 'D';
    return 'F';
}

// Get grade point from grade
function getGradePoint($grade) {
    $gradePoints = [
        'A+' => 4.0,
        'A' => 4.0,
        'B+' => 3.5,
        'B' => 3.0,
        'C+' => 2.5,
        'C' => 2.0,
        'D' => 1.0,
        'F' => 0.0
    ];
    
    return $gradePoints[$grade] ?? 0.0;
}

// Format date
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Calculate age from date of birth
function calculateAge($dob) {
    if (empty($dob)) return '';
    
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($dobDate)->y;
    
    return $age;
}

// Generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Redirect with message
function redirect($url, $message = '', $type = MSG_INFO) {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    header("Location: $url");
    exit();
}

// Display flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? MSG_INFO;
        
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>";
        echo $message;
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
        echo "</div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}
