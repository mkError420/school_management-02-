<?php
/**
 * Admin Controller
 * School Management System
 */

class AdminController {
    private $userModel;
    private $studentModel;
    private $teacherModel;
    private $classModel;
    private $subjectModel;
    private $session;
    
    public function __construct() {
        // Check authentication and admin role
        AuthMiddleware::requireRole([ROLE_ADMIN], function() {
            $this->init();
        });
    }
    
    private function init() {
        require_once INCLUDES_PATH . '/Session.php';
        require_once APP_PATH . '/models/User.php';
        require_once APP_PATH . '/models/Student.php';
        require_once APP_PATH . '/models/Teacher.php';
        require_once APP_PATH . '/models/ClassModel.php';
        require_once APP_PATH . '/models/Subject.php';
        
        $this->session = Session::getInstance();
        $this->userModel = new User();
        $this->studentModel = new Student();
        $this->teacherModel = new Teacher();
        $this->classModel = new ClassModel();
        $this->subjectModel = new Subject();
    }
    
    /**
     * Admin Dashboard
     */
    public function dashboard() {
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $attendanceOverview = $this->getAttendanceOverview();
        
        include APP_PATH . '/views/admin/dashboard.php';
    }
    
    /**
     * Students Management
     */
    public function students() {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $class = $_GET['class'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $students = $this->studentModel->getAll($page, $search, $class, $status);
        $classes = $this->classModel->getAll();
        
        include APP_PATH . '/views/admin/students.php';
    }
    
    /**
     * Create Student
     */
    public function createStudent() {
        $classes = $this->classModel->getAll();
        $sections = $this->getSections();
        
        include APP_PATH . '/views/admin/create_student.php';
    }
    
    /**
     * Edit Student
     */
    public function editStudent() {
        $id = $_GET['id'] ?? 0;
        $student = $this->studentModel->findById($id);
        
        if (!$student) {
            $this->session->setFlash('Student not found!', 'error');
            redirect('admin/students');
            return;
        }
        
        $classes = $this->classModel->getAll();
        $sections = $this->getSections();
        
        include APP_PATH . '/views/admin/edit_student.php';
    }
    
    /**
     * Store Student
     */
    public function storeStudent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/students');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/students');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'username' => 'required|alpha_num|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|alpha|max:50',
            'last_name' => 'required|alpha|max:50',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'phone' => 'phone',
            'address' => 'max:255',
            'parent_name' => 'required|max:100',
            'parent_phone' => 'required|phone',
            'parent_email' => 'email',
            'enrollment_date' => 'required|date',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            $this->session->setFlash('Validation failed: ' . $validator->getErrorsAsString(), 'error');
            redirect('admin/students/create');
            return;
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Create user account
            $userData = [
                'username' => sanitize($_POST['username']),
                'email' => sanitize($_POST['email']),
                'password' => $_POST['password'],
                'role' => ROLE_STUDENT,
                'status' => STATUS_ACTIVE
            ];
            
            if (!$this->userModel->create($userData)) {
                throw new Exception('Failed to create user account');
            }
            
            $userId = $this->db->lastInsertId();
            
            // Create student profile
            $studentData = [
                'user_id' => $userId,
                'student_id' => $this->generateStudentId(),
                'first_name' => sanitize($_POST['first_name']),
                'last_name' => sanitize($_POST['last_name']),
                'gender' => $_POST['gender'],
                'date_of_birth' => $_POST['date_of_birth'],
                'phone' => sanitize($_POST['phone']),
                'address' => sanitize($_POST['address']),
                'parent_name' => sanitize($_POST['parent_name']),
                'parent_phone' => sanitize($_POST['parent_phone']),
                'parent_email' => sanitize($_POST['parent_email']),
                'enrollment_date' => $_POST['enrollment_date']
            ];
            
            if (!$this->studentModel->create($studentData)) {
                throw new Exception('Failed to create student profile');
            }
            
            $studentId = $this->db->lastInsertId();
            
            // Create enrollment
            $enrollmentData = [
                'student_id' => $studentId,
                'section_id' => $_POST['section_id'],
                'enrollment_date' => $_POST['enrollment_date'],
                'academic_year' => getCurrentAcademicYear()
            ];
            
            if (!$this->createEnrollment($enrollmentData)) {
                throw new Exception('Failed to create enrollment');
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->session->setFlash('Student created successfully!', 'success');
            redirect('admin/students');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('Failed to create student: ' . $e->getMessage(), 'error');
            redirect('admin/students/create');
        }
    }
    
    /**
     * Update Student
     */
    public function updateStudent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/students');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $student = $this->studentModel->findById($id);
        
        if (!$student) {
            $this->session->setFlash('Student not found!', 'error');
            redirect('admin/students');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/students');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'first_name' => 'required|alpha|max:50',
            'last_name' => 'required|alpha|max:50',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'phone' => 'phone',
            'address' => 'max:255',
            'parent_name' => 'required|max:100',
            'parent_phone' => 'required|phone',
            'parent_email' => 'email'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            $this->session->setFlash('Validation failed: ' . $validator->getErrorsAsString(), 'error');
            redirect("admin/students/edit?id=$id");
            return;
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Update user account
            $userData = [
                'email' => sanitize($_POST['email'])
            ];
            
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }
            
            if (!$this->userModel->update($student->user_id, $userData)) {
                throw new Exception('Failed to update user account');
            }
            
            // Update student profile
            $studentData = [
                'first_name' => sanitize($_POST['first_name']),
                'last_name' => sanitize($_POST['last_name']),
                'gender' => $_POST['gender'],
                'date_of_birth' => $_POST['date_of_birth'],
                'phone' => sanitize($_POST['phone']),
                'address' => sanitize($_POST['address']),
                'parent_name' => sanitize($_POST['parent_name']),
                'parent_phone' => sanitize($_POST['parent_phone']),
                'parent_email' => sanitize($_POST['parent_email'])
            ];
            
            if (!$this->studentModel->update($id, $studentData)) {
                throw new Exception('Failed to update student profile');
            }
            
            // Update enrollment if changed
            if (isset($_POST['section_id'])) {
                $this->updateEnrollment($id, $_POST['section_id']);
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->session->setFlash('Student updated successfully!', 'success');
            redirect('admin/students');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('Failed to update student: ' . $e->getMessage(), 'error');
            redirect("admin/students/edit?id=$id");
        }
    }
    
    /**
     * Delete Student
     */
    public function deleteStudent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/students');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $student = $this->studentModel->findById($id);
        
        if (!$student) {
            $this->session->setFlash('Student not found!', 'error');
            redirect('admin/students');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/students');
            return;
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Delete enrollments
            $this->deleteEnrollments($id);
            
            // Delete student profile
            if (!$this->studentModel->delete($id)) {
                throw new Exception('Failed to delete student profile');
            }
            
            // Delete user account
            if (!$this->userModel->delete($student->user_id)) {
                throw new Exception('Failed to delete user account');
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->session->setFlash('Student deleted successfully!', 'success');
            redirect('admin/students');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('Failed to delete student: ' . $e->getMessage(), 'error');
            redirect('admin/students');
        }
    }
    
    /**
     * Teachers Management
     */
    public function teachers() {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $teachers = $this->teacherModel->getAll($page, $search, $status);
        
        include APP_PATH . '/views/admin/teachers.php';
    }
    
    /**
     * Create Teacher
     */
    public function createTeacher() {
        include APP_PATH . '/views/admin/create_teacher.php';
    }
    
    /**
     * Edit Teacher
     */
    public function editTeacher() {
        $id = $_GET['id'] ?? 0;
        $teacher = $this->teacherModel->findById($id);
        
        if (!$teacher) {
            $this->session->setFlash('Teacher not found!', 'error');
            redirect('admin/teachers');
            return;
        }
        
        include APP_PATH . '/views/admin/edit_teacher.php';
    }
    
    /**
     * Store Teacher
     */
    public function storeTeacher() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/teachers');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/teachers');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'username' => 'required|alpha_num|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|alpha|max:50',
            'last_name' => 'required|alpha|max:50',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'phone' => 'phone',
            'address' => 'max:255',
            'qualification' => 'required|max:100',
            'specialization' => 'required|max:100',
            'experience_years' => 'integer|min_value:0',
            'hire_date' => 'required|date',
            'salary' => 'numeric|min_value:0'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            $this->session->setFlash('Validation failed: ' . $validator->getErrorsAsString(), 'error');
            redirect('admin/teachers/create');
            return;
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Create user account
            $userData = [
                'username' => sanitize($_POST['username']),
                'email' => sanitize($_POST['email']),
                'password' => $_POST['password'],
                'role' => ROLE_TEACHER,
                'status' => STATUS_ACTIVE
            ];
            
            if (!$this->userModel->create($userData)) {
                throw new Exception('Failed to create user account');
            }
            
            $userId = $this->db->lastInsertId();
            
            // Create teacher profile
            $teacherData = [
                'user_id' => $userId,
                'employee_id' => $this->generateEmployeeId(),
                'first_name' => sanitize($_POST['first_name']),
                'last_name' => sanitize($_POST['last_name']),
                'gender' => $_POST['gender'],
                'date_of_birth' => $_POST['date_of_birth'],
                'phone' => sanitize($_POST['phone']),
                'address' => sanitize($_POST['address']),
                'qualification' => sanitize($_POST['qualification']),
                'specialization' => sanitize($_POST['specialization']),
                'experience_years' => $_POST['experience_years'],
                'hire_date' => $_POST['hire_date'],
                'salary' => $_POST['salary']
            ];
            
            if (!$this->teacherModel->create($teacherData)) {
                throw new Exception('Failed to create teacher profile');
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->session->setFlash('Teacher created successfully!', 'success');
            redirect('admin/teachers');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('Failed to create teacher: ' . $e->getMessage(), 'error');
            redirect('admin/teachers/create');
        }
    }
    
    /**
     * Update Teacher
     */
    public function updateTeacher() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/teachers');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $teacher = $this->teacherModel->findById($id);
        
        if (!$teacher) {
            $this->session->setFlash('Teacher not found!', 'error');
            redirect('admin/teachers');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/teachers');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'email' => 'required|email|unique:users,email,' . $teacher->user_id,
            'first_name' => 'required|alpha|max:50',
            'last_name' => 'required|alpha|max:50',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'phone' => 'phone',
            'address' => 'max:255',
            'qualification' => 'required|max:100',
            'specialization' => 'required|max:100',
            'experience_years' => 'integer|min_value:0',
            'hire_date' => 'required|date',
            'salary' => 'numeric|min_value:0'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            $this->session->setFlash('Validation failed: ' . $validator->getErrorsAsString(), 'error');
            redirect("admin/teachers/edit?id=$id");
            return;
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Update user account
            $userData = [
                'email' => sanitize($_POST['email'])
            ];
            
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }
            
            if (!$this->userModel->update($teacher->user_id, $userData)) {
                throw new Exception('Failed to update user account');
            }
            
            // Update teacher profile
            $teacherData = [
                'first_name' => sanitize($_POST['first_name']),
                'last_name' => sanitize($_POST['last_name']),
                'gender' => $_POST['gender'],
                'date_of_birth' => $_POST['date_of_birth'],
                'phone' => sanitize($_POST['phone']),
                'address' => sanitize($_POST['address']),
                'qualification' => sanitize($_POST['qualification']),
                'specialization' => sanitize($_POST['specialization']),
                'experience_years' => $_POST['experience_years'],
                'hire_date' => $_POST['hire_date'],
                'salary' => $_POST['salary']
            ];
            
            if (!$this->teacherModel->update($id, $teacherData)) {
                throw new Exception('Failed to update teacher profile');
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->session->setFlash('Teacher updated successfully!', 'success');
            redirect('admin/teachers');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('Failed to update teacher: ' . $e->getMessage(), 'error');
            redirect("admin/teachers/edit?id=$id");
        }
    }
    
    /**
     * Delete Teacher
     */
    public function deleteTeacher() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/teachers');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $teacher = $this->teacherModel->findById($id);
        
        if (!$teacher) {
            $this->session->setFlash('Teacher not found!', 'error');
            redirect('admin/teachers');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/teachers');
            return;
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Delete teacher profile
            if (!$this->teacherModel->delete($id)) {
                throw new Exception('Failed to delete teacher profile');
            }
            
            // Delete user account
            if (!$this->userModel->delete($teacher->user_id)) {
                throw new Exception('Failed to delete user account');
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->session->setFlash('Teacher deleted successfully!', 'success');
            redirect('admin/teachers');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('Failed to delete teacher: ' . $e->getMessage(), 'error');
            redirect('admin/teachers');
        }
    }
    
    /**
     * Classes Management
     */
    public function classes() {
        $classes = $this->classModel->getAll();
        include APP_PATH . '/views/admin/classes.php';
    }
    
    /**
     * Create Class
     */
    public function createClass() {
        include APP_PATH . '/views/admin/create_class.php';
    }
    
    /**
     * Edit Class
     */
    public function editClass() {
        $id = $_GET['id'] ?? 0;
        $class = $this->classModel->findById($id);
        
        if (!$class) {
            $this->session->setFlash('Class not found!', 'error');
            redirect('admin/classes');
            return;
        }
        
        include APP_PATH . '/views/admin/edit_class.php';
    }
    
    /**
     * Store Class
     */
    public function storeClass() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/classes');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/classes');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'name' => 'required|max:50',
            'grade_level' => 'required|integer|min_value:1|max_value:12',
            'description' => 'max:255'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            $this->session->setFlash('Validation failed: ' . $validator->getErrorsAsString(), 'error');
            redirect('admin/classes/create');
            return;
        }
        
        $data = [
            'name' => sanitize($_POST['name']),
            'grade_level' => $_POST['grade_level'],
            'description' => sanitize($_POST['description'])
        ];
        
        if ($this->classModel->create($data)) {
            $this->session->setFlash('Class created successfully!', 'success');
            redirect('admin/classes');
        } else {
            $this->session->setFlash('Failed to create class!', 'error');
            redirect('admin/classes/create');
        }
    }
    
    /**
     * Update Class
     */
    public function updateClass() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/classes');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $class = $this->classModel->findById($id);
        
        if (!$class) {
            $this->session->setFlash('Class not found!', 'error');
            redirect('admin/classes');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/classes');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'name' => 'required|max:50',
            'grade_level' => 'required|integer|min_value:1|max_value:12',
            'description' => 'max:255'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            $this->session->setFlash('Validation failed: ' . $validator->getErrorsAsString(), 'error');
            redirect("admin/classes/edit?id=$id");
            return;
        }
        
        $data = [
            'name' => sanitize($_POST['name']),
            'grade_level' => $_POST['grade_level'],
            'description' => sanitize($_POST['description'])
        ];
        
        if ($this->classModel->update($id, $data)) {
            $this->session->setFlash('Class updated successfully!', 'success');
            redirect('admin/classes');
        } else {
            $this->session->setFlash('Failed to update class!', 'error');
            redirect("admin/classes/edit?id=$id");
        }
    }
    
    /**
     * Delete Class
     */
    public function deleteClass() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/classes');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $class = $this->classModel->findById($id);
        
        if (!$class) {
            $this->session->setFlash('Class not found!', 'error');
            redirect('admin/classes');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/classes');
            return;
        }
        
        if ($this->classModel->delete($id)) {
            $this->session->setFlash('Class deleted successfully!', 'success');
            redirect('admin/classes');
        } else {
            $this->session->setFlash('Failed to delete class!', 'error');
            redirect('admin/classes');
        }
    }
    
    /**
     * Subjects Management
     */
    public function subjects() {
        $subjects = $this->subjectModel->getAll();
        include APP_PATH . '/views/admin/subjects.php';
    }
    
    /**
     * Create Subject
     */
    public function createSubject() {
        include APP_PATH . '/views/admin/create_subject.php';
    }
    
    /**
     * Edit Subject
     */
    public function editSubject() {
        $id = $_GET['id'] ?? 0;
        $subject = $this->subjectModel->findById($id);
        
        if (!$subject) {
            $this->session->setFlash('Subject not found!', 'error');
            redirect('admin/subjects');
            return;
        }
        
        include APP_PATH . '/views/admin/edit_subject.php';
    }
    
    /**
     * Store Subject
     */
    public function storeSubject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/subjects');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/subjects');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'name' => 'required|max:100',
            'code' => 'required|max:20|unique:subjects,code',
            'description' => 'max:255',
            'credits' => 'integer|min_value:1|max_value:10'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            $this->session->setFlash('Validation failed: ' . $validator->getErrorsAsString(), 'error');
            redirect('admin/subjects/create');
            return;
        }
        
        $data = [
            'name' => sanitize($_POST['name']),
            'code' => sanitize($_POST['code']),
            'description' => sanitize($_POST['description']),
            'credits' => $_POST['credits'] ?? 1
        ];
        
        if ($this->subjectModel->create($data)) {
            $this->session->setFlash('Subject created successfully!', 'success');
            redirect('admin/subjects');
        } else {
            $this->session->setFlash('Failed to create subject!', 'error');
            redirect('admin/subjects/create');
        }
    }
    
    /**
     * Update Subject
     */
    public function updateSubject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/subjects');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $subject = $this->subjectModel->findById($id);
        
        if (!$subject) {
            $this->session->setFlash('Subject not found!', 'error');
            redirect('admin/subjects');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/subjects');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'name' => 'required|max:100',
            'code' => 'required|max:20|unique:subjects,code,' . $id,
            'description' => 'max:255',
            'credits' => 'integer|min_value:1|max_value:10'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            $this->session->setFlash('Validation failed: ' . $validator->getErrorsAsString(), 'error');
            redirect("admin/subjects/edit?id=$id");
            return;
        }
        
        $data = [
            'name' => sanitize($_POST['name']),
            'code' => sanitize($_POST['code']),
            'description' => sanitize($_POST['description']),
            'credits' => $_POST['credits'] ?? 1
        ];
        
        if ($this->subjectModel->update($id, $data)) {
            $this->session->setFlash('Subject updated successfully!', 'success');
            redirect('admin/subjects');
        } else {
            $this->session->setFlash('Failed to update subject!', 'error');
            redirect("admin/subjects/edit?id=$id");
        }
    }
    
    /**
     * Delete Subject
     */
    public function deleteSubject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/subjects');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $subject = $this->subjectModel->findById($id);
        
        if (!$subject) {
            $this->session->setFlash('Subject not found!', 'error');
            redirect('admin/subjects');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('Invalid request!', 'error');
            redirect('admin/subjects');
            return;
        }
        
        if ($this->subjectModel->delete($id)) {
            $this->session->setFlash('Subject deleted successfully!', 'success');
            redirect('admin/subjects');
        } else {
            $this->session->setFlash('Failed to delete subject!', 'error');
            redirect('admin/subjects');
        }
    }
    
    /**
     * Attendance Overview
     */
    public function attendance() {
        $date = $_GET['date'] ?? date('Y-m-d');
        $class = $_GET['class'] ?? '';
        
        $attendanceData = $this->getAttendanceData($date, $class);
        $classes = $this->classModel->getAll();
        
        include APP_PATH . '/views/admin/attendance.php';
    }
    
    /**
     * Results Management
     */
    public function results() {
        $exam = $_GET['exam'] ?? '';
        $class = $_GET['class'] ?? '';
        
        $resultsData = $this->getResultsData($exam, $class);
        $exams = $this->getExams();
        $classes = $this->classModel->getAll();
        
        include APP_PATH . '/views/admin/results.php';
    }
    
    /**
     * Reports
     */
    public function reports() {
        include APP_PATH . '/views/admin/reports.php';
    }
    
    /**
     * Settings
     */
    public function settings() {
        include APP_PATH . '/views/admin/settings.php';
    }
    
    // Helper methods
    private function getDashboardStats() {
        return [
            'total_students' => $this->studentModel->getTotalCount(),
            'total_teachers' => $this->teacherModel->getTotalCount(),
            'total_classes' => $this->classModel->getTotalCount(),
            'total_subjects' => $this->subjectModel->getTotalCount(),
            'attendance_today' => $this->getTodayAttendance(),
            'recent_enrollments' => $this->getRecentEnrollments()
        ];
    }
    
    private function getRecentActivities() {
        // Implementation for recent activities
        return [];
    }
    
    private function getAttendanceOverview() {
        // Implementation for attendance overview
        return [];
    }
    
    private function generateStudentId() {
        return 'STU' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    private function generateEmployeeId() {
        return 'EMP' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    private function getSections() {
        // Implementation for getting sections
        return [];
    }
    
    private function createEnrollment($data) {
        // Implementation for creating enrollment
        return true;
    }
    
    private function updateEnrollment($studentId, $sectionId) {
        // Implementation for updating enrollment
        return true;
    }
    
    private function deleteEnrollments($studentId) {
        // Implementation for deleting enrollments
        return true;
    }
    
    private function getAttendanceData($date, $class) {
        // Implementation for attendance data
        return [];
    }
    
    private function getResultsData($exam, $class) {
        // Implementation for results data
        return [];
    }
    
    private function getExams() {
        // Implementation for getting exams
        return [];
    }
    
    private function getTodayAttendance() {
        // Implementation for today's attendance
        return 0;
    }
    
    private function getRecentEnrollments() {
        // Implementation for recent enrollments
        return 0;
    }
}
