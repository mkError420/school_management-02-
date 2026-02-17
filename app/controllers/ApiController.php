<?php
/**
 * API Controller
 * School Management System
 */

class ApiController {
    private $db;
    private $session;
    
    public function __construct() {
        // Set headers for API responses
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        // Initialize database and session
        $this->db = new QueryBuilder();
        $this->session = Session::getInstance();
        
        // Rate limiting
        $this->applyRateLimiting();
    }
    
    /**
     * Apply rate limiting
     */
    private function applyRateLimiting() {
        $clientIP = getClientIP();
        $key = 'api_rate_limit_' . md5($clientIP);
        $maxRequests = 100; // per hour
        $timeWindow = 3600; // 1 hour
        
        $requests = $this->session->get($key, []);
        $now = time();
        
        // Remove old requests
        $requests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
            return $now - $timestamp < $timeWindow;
        });
        
        // Check if limit exceeded
        if (count($requests) >= $maxRequests) {
            jsonResponse(null, HTTP_TOO_MANY_REQUESTS, 'Rate limit exceeded. Please try again later.');
        }
        
        // Add current request
        $requests[] = $now;
        $this->session->set($key, $requests);
    }
    
    /**
     * Get Students API
     */
    public function getStudents() {
        $this->requireAuth();
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $class = $_GET['class'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $studentModel = new Student();
        $result = $studentModel->getAll($page, $search, $class, $status);
        
        jsonResponse($result, HTTP_OK, 'Students retrieved successfully');
    }
    
    /**
     * Create Student API
     */
    public function createStudent() {
        $this->requireAuth([ROLE_ADMIN]);
        $this->validateCSRF();
        
        $data = $this->getJsonInput();
        
        // Validate required fields
        $required = ['username', 'email', 'password', 'first_name', 'last_name', 'gender', 'date_of_birth'];
        $this->validateRequired($data, $required);
        
        // Validate data
        $validator = new Validator();
        $rules = [
            'username' => 'required|alpha_num|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|alpha|max:50',
            'last_name' => 'required|alpha|max:50',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date'
        ];
        
        if (!$validator->validate($data, $rules)) {
            jsonResponse($validator->getErrors(), HTTP_BAD_REQUEST, 'Validation failed');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create user
            $userData = [
                'username' => sanitize($data['username']),
                'email' => sanitize($data['email']),
                'password' => $data['password'],
                'role' => ROLE_STUDENT,
                'status' => STATUS_ACTIVE
            ];
            
            $userModel = new User();
            if (!$userModel->create($userData)) {
                throw new Exception('Failed to create user');
            }
            
            $userId = $this->db->lastInsertId();
            
            // Create student
            $studentData = [
                'user_id' => $userId,
                'student_id' => $this->generateStudentId(),
                'first_name' => sanitize($data['first_name']),
                'last_name' => sanitize($data['last_name']),
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'phone' => sanitize($data['phone'] ?? ''),
                'address' => sanitize($data['address'] ?? ''),
                'parent_name' => sanitize($data['parent_name'] ?? ''),
                'parent_phone' => sanitize($data['parent_phone'] ?? ''),
                'parent_email' => sanitize($data['parent_email'] ?? ''),
                'enrollment_date' => $data['enrollment_date'] ?? date('Y-m-d')
            ];
            
            $studentModel = new Student();
            if (!$studentModel->create($studentData)) {
                throw new Exception('Failed to create student');
            }
            
            $this->db->commit();
            
            $student = $studentModel->findById($this->db->lastInsertId());
            jsonResponse($student, HTTP_CREATED, 'Student created successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to create student: ' . $e->getMessage());
        }
    }
    
    /**
     * Update Student API
     */
    public function updateStudent() {
        $this->requireAuth([ROLE_ADMIN]);
        $this->validateCSRF();
        
        $id = $_GET['id'] ?? 0;
        $data = $this->getJsonInput();
        
        if (empty($id)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Student ID is required');
        }
        
        $studentModel = new Student();
        $student = $studentModel->findById($id);
        
        if (!$student) {
            jsonResponse(null, HTTP_NOT_FOUND, 'Student not found');
        }
        
        // Validate data
        $validator = new Validator();
        $rules = [
            'email' => 'email|unique:users,email,' . $student->user_id,
            'first_name' => 'alpha|max:50',
            'last_name' => 'alpha|max:50',
            'gender' => 'in:male,female,other',
            'date_of_birth' => 'date'
        ];
        
        if (!$validator->validate($data, $rules)) {
            jsonResponse($validator->getErrors(), HTTP_BAD_REQUEST, 'Validation failed');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update user if email provided
            if (isset($data['email'])) {
                $userData = ['email' => sanitize($data['email'])];
                
                if (isset($data['password']) && !empty($data['password'])) {
                    $userData['password'] = $data['password'];
                }
                
                $userModel = new User();
                if (!$userModel->update($student->user_id, $userData)) {
                    throw new Exception('Failed to update user');
                }
            }
            
            // Update student
            $studentData = [];
            $allowedFields = ['first_name', 'last_name', 'gender', 'date_of_birth', 'phone', 'address', 'parent_name', 'parent_phone', 'parent_email'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $studentData[$field] = sanitize($data[$field]);
                }
            }
            
            if (!empty($studentData) && !$studentModel->update($id, $studentData)) {
                throw new Exception('Failed to update student');
            }
            
            $this->db->commit();
            
            $updatedStudent = $studentModel->findById($id);
            jsonResponse($updatedStudent, HTTP_OK, 'Student updated successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to update student: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete Student API
     */
    public function deleteStudent() {
        $this->requireAuth([ROLE_ADMIN]);
        $this->validateCSRF();
        
        $id = $_GET['id'] ?? 0;
        
        if (empty($id)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Student ID is required');
        }
        
        $studentModel = new Student();
        $student = $studentModel->findById($id);
        
        if (!$student) {
            jsonResponse(null, HTTP_NOT_FOUND, 'Student not found');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Delete enrollments
            $this->db->table('enrollments')->where('student_id', $id)->delete()->execute();
            
            // Delete student
            if (!$studentModel->delete($id)) {
                throw new Exception('Failed to delete student');
            }
            
            // Delete user
            $userModel = new User();
            if (!$userModel->delete($student->user_id)) {
                throw new Exception('Failed to delete user');
            }
            
            $this->db->commit();
            
            jsonResponse(null, HTTP_OK, 'Student deleted successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to delete student: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Teachers API
     */
    public function getTeachers() {
        $this->requireAuth();
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $teacherModel = new Teacher();
        $result = $teacherModel->getAll($page, $search, $status);
        
        jsonResponse($result, HTTP_OK, 'Teachers retrieved successfully');
    }
    
    /**
     * Create Teacher API
     */
    public function createTeacher() {
        $this->requireAuth([ROLE_ADMIN]);
        $this->validateCSRF();
        
        $data = $this->getJsonInput();
        
        // Validate required fields
        $required = ['username', 'email', 'password', 'first_name', 'last_name', 'gender', 'date_of_birth'];
        $this->validateRequired($data, $required);
        
        // Validate data
        $validator = new Validator();
        $rules = [
            'username' => 'required|alpha_num|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|alpha|max:50',
            'last_name' => 'required|alpha|max:50',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date'
        ];
        
        if (!$validator->validate($data, $rules)) {
            jsonResponse($validator->getErrors(), HTTP_BAD_REQUEST, 'Validation failed');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create user
            $userData = [
                'username' => sanitize($data['username']),
                'email' => sanitize($data['email']),
                'password' => $data['password'],
                'role' => ROLE_TEACHER,
                'status' => STATUS_ACTIVE
            ];
            
            $userModel = new User();
            if (!$userModel->create($userData)) {
                throw new Exception('Failed to create user');
            }
            
            $userId = $this->db->lastInsertId();
            
            // Create teacher
            $teacherData = [
                'user_id' => $userId,
                'employee_id' => $this->generateEmployeeId(),
                'first_name' => sanitize($data['first_name']),
                'last_name' => sanitize($data['last_name']),
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'phone' => sanitize($data['phone'] ?? ''),
                'address' => sanitize($data['address'] ?? ''),
                'qualification' => sanitize($data['qualification'] ?? ''),
                'specialization' => sanitize($data['specialization'] ?? ''),
                'experience_years' => $data['experience_years'] ?? 0,
                'hire_date' => $data['hire_date'] ?? date('Y-m-d'),
                'salary' => $data['salary'] ?? 0
            ];
            
            $teacherModel = new Teacher();
            if (!$teacherModel->create($teacherData)) {
                throw new Exception('Failed to create teacher');
            }
            
            $this->db->commit();
            
            $teacher = $teacherModel->findById($this->db->lastInsertId());
            jsonResponse($teacher, HTTP_CREATED, 'Teacher created successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to create teacher: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Classes API
     */
    public function getClasses() {
        $this->requireAuth();
        
        $classModel = new ClassModel();
        $classes = $classModel->getAll();
        
        jsonResponse($classes, HTTP_OK, 'Classes retrieved successfully');
    }
    
    /**
     * Create Class API
     */
    public function createClass() {
        $this->requireAuth([ROLE_ADMIN]);
        $this->validateCSRF();
        
        $data = $this->getJsonInput();
        
        // Validate required fields
        $required = ['name', 'grade_level'];
        $this->validateRequired($data, $required);
        
        // Validate data
        $validator = new Validator();
        $rules = [
            'name' => 'required|max:50',
            'grade_level' => 'required|integer|min_value:1|max_value:12',
            'description' => 'max:255'
        ];
        
        if (!$validator->validate($data, $rules)) {
            jsonResponse($validator->getErrors(), HTTP_BAD_REQUEST, 'Validation failed');
        }
        
        $classData = [
            'name' => sanitize($data['name']),
            'grade_level' => $data['grade_level'],
            'description' => sanitize($data['description'] ?? '')
        ];
        
        $classModel = new ClassModel();
        if ($classModel->create($classData)) {
            $class = $classModel->findById($this->db->lastInsertId());
            jsonResponse($class, HTTP_CREATED, 'Class created successfully');
        } else {
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to create class');
        }
    }
    
    /**
     * Get Subjects API
     */
    public function getSubjects() {
        $this->requireAuth();
        
        $subjectModel = new Subject();
        $subjects = $subjectModel->getAll();
        
        jsonResponse($subjects, HTTP_OK, 'Subjects retrieved successfully');
    }
    
    /**
     * Create Subject API
     */
    public function createSubject() {
        $this->requireAuth([ROLE_ADMIN]);
        $this->validateCSRF();
        
        $data = $this->getJsonInput();
        
        // Validate required fields
        $required = ['name', 'code'];
        $this->validateRequired($data, $required);
        
        // Validate data
        $validator = new Validator();
        $rules = [
            'name' => 'required|max:100',
            'code' => 'required|max:20|unique:subjects,code',
            'description' => 'max:255',
            'credits' => 'integer|min_value:1|max_value:10'
        ];
        
        if (!$validator->validate($data, $rules)) {
            jsonResponse($validator->getErrors(), HTTP_BAD_REQUEST, 'Validation failed');
        }
        
        $subjectData = [
            'name' => sanitize($data['name']),
            'code' => sanitize($data['code']),
            'description' => sanitize($data['description'] ?? ''),
            'credits' => $data['credits'] ?? 1
        ];
        
        $subjectModel = new Subject();
        if ($subjectModel->create($subjectData)) {
            $subject = $subjectModel->findById($this->db->lastInsertId());
            jsonResponse($subject, HTTP_CREATED, 'Subject created successfully');
        } else {
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to create subject');
        }
    }
    
    /**
     * Get Attendance API
     */
    public function getAttendance() {
        $this->requireAuth();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $section = $_GET['section'] ?? '';
        $student = $_GET['student'] ?? '';
        
        $query = $this->db->table('attendance')
            ->leftJoin('students', 'students.id', '=', 'attendance.student_id')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('sections', 'sections.id', '=', 'attendance.section_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->select('attendance.*, students.first_name, students.last_name, students.student_id, users.username, classes.name as class_name, sections.name as section_name');
        
        if (!empty($date)) {
            $query->where('attendance.date', $date);
        }
        
        if (!empty($section)) {
            $query->andWhere('attendance.section_id', $section);
        }
        
        if (!empty($student)) {
            $query->andWhere('attendance.student_id', $student);
        }
        
        // Apply role-based filtering
        $userRole = $this->session->getUserRole();
        if ($userRole === ROLE_TEACHER) {
            $teacherId = $this->getTeacherId();
            $query->andWhere('sections.teacher_id', $teacherId);
        } elseif ($userRole === ROLE_STUDENT) {
            $studentId = $this->getStudentId();
            $query->andWhere('attendance.student_id', $studentId);
        }
        
        $attendance = $query->orderBy('attendance.date', 'DESC')->get();
        
        jsonResponse($attendance, HTTP_OK, 'Attendance retrieved successfully');
    }
    
    /**
     * Mark Attendance API
     */
    public function markAttendance() {
        $this->requireAuth([ROLE_ADMIN, ROLE_TEACHER]);
        $this->validateCSRF();
        
        $data = $this->getJsonInput();
        
        // Validate required fields
        $required = ['date', 'section_id', 'attendance'];
        $this->validateRequired($data, $required);
        
        try {
            $this->db->beginTransaction();
            
            foreach ($data['attendance'] as $studentId => $status) {
                // Check if attendance already exists
                $existing = $this->db->table('attendance')
                    ->where('student_id', $studentId)
                    ->where('section_id', $data['section_id'])
                    ->where('date', $data['date'])
                    ->first();
                
                $attendanceData = [
                    'student_id' => $studentId,
                    'section_id' => $data['section_id'],
                    'date' => $data['date'],
                    'status' => $status,
                    'remarks' => $data['remarks'][$studentId] ?? null,
                    'marked_by' => $this->getUserId()
                ];
                
                if ($existing) {
                    // Update existing
                    $this->db->table('attendance')
                        ->where('id', $existing->id)
                        ->update($attendanceData)
                        ->execute();
                } else {
                    // Insert new
                    $attendanceData['created_at'] = date('Y-m-d H:i:s');
                    $this->db->table('attendance')->insert($attendanceData);
                }
            }
            
            $this->db->commit();
            
            jsonResponse(null, HTTP_OK, 'Attendance marked successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to mark attendance: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Results API
     */
    public function getResults() {
        $this->requireAuth();
        
        $exam = $_GET['exam'] ?? '';
        $student = $_GET['student'] ?? '';
        $class = $_GET['class'] ?? '';
        $year = $_GET['year'] ?? getCurrentAcademicYear();
        
        $query = $this->db->table('results')
            ->leftJoin('students', 'students.id', '=', 'results.student_id')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->select('results.*, students.first_name, students.last_name, students.student_id, users.username, exams.title as exam_title, exams.exam_type, exams.total_marks, exams.exam_date, subjects.name as subject_name, subjects.code as subject_code, classes.name as class_name, sections.name as section_name');
        
        if (!empty($exam)) {
            $query->where('results.exam_id', $exam);
        }
        
        if (!empty($student)) {
            $query->andWhere('results.student_id', $student);
        }
        
        if (!empty($class)) {
            $query->andWhere('classes.id', $class);
        }
        
        if (!empty($year)) {
            $query->andWhere('exams.exam_date', '>=', $year . '-01-01')
                  ->andWhere('exams.exam_date', '<=', $year . '-12-31');
        }
        
        // Apply role-based filtering
        $userRole = $this->session->getUserRole();
        if ($userRole === ROLE_TEACHER) {
            $teacherId = $this->getTeacherId();
            $query->andWhere('sections.teacher_id', $teacherId);
        } elseif ($userRole === ROLE_STUDENT) {
            $studentId = $this->getStudentId();
            $query->andWhere('results.student_id', $studentId);
        }
        
        $results = $query->orderBy('exams.exam_date', 'DESC')->get();
        
        jsonResponse($results, HTTP_OK, 'Results retrieved successfully');
    }
    
    /**
     * Store Result API
     */
    public function storeResult() {
        $this->requireAuth([ROLE_ADMIN, ROLE_TEACHER]);
        $this->validateCSRF();
        
        $data = $this->getJsonInput();
        
        // Validate required fields
        $required = ['student_id', 'exam_id', 'marks_obtained'];
        $this->validateRequired($data, $required);
        
        // Validate marks
        if (!is_numeric($data['marks_obtained']) || $data['marks_obtained'] < 0 || $data['marks_obtained'] > 100) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid marks. Must be between 0 and 100');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Check if result already exists
            $existing = $this->db->table('results')
                ->where('student_id', $data['student_id'])
                ->where('exam_id', $data['exam_id'])
                ->first();
            
            $resultData = [
                'student_id' => $data['student_id'],
                'exam_id' => $data['exam_id'],
                'marks_obtained' => $data['marks_obtained'],
                'grade' => getGrade($data['marks_obtained']),
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $this->getUserId()
            ];
            
            if ($existing) {
                // Update existing
                $resultData['updated_at'] = date('Y-m-d H:i:s');
                $this->db->table('results')
                    ->where('id', $existing->id)
                    ->update($resultData)
                    ->execute();
            } else {
                // Insert new
                $resultData['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('results')->insert($resultData);
            }
            
            $this->db->commit();
            
            jsonResponse(null, HTTP_OK, 'Result stored successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to store result: ' . $e->getMessage());
        }
    }
    
    // Helper methods
    private function requireAuth($roles = []) {
        if (!$this->session->validate()) {
            jsonResponse(null, HTTP_UNAUTHORIZED, 'Authentication required');
        }
        
        if (!empty($roles)) {
            $userRole = $this->session->getUserRole();
            if (!in_array($userRole, $roles)) {
                jsonResponse(null, HTTP_FORBIDDEN, 'Access denied');
            }
        }
    }
    
    private function validateCSRF() {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($token)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid CSRF token');
        }
    }
    
    private function getJsonInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    private function validateRequired($data, $required) {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                jsonResponse(null, HTTP_BAD_REQUEST, "Field '$field' is required");
            }
        }
    }
    
    private function getUserId() {
        return $this->session->getUserId();
    }
    
    private function getTeacherId() {
        $userId = $this->session->getUserId();
        $teacher = $this->db->table('teachers')->where('user_id', $userId)->first();
        return $teacher ? $teacher->id : null;
    }
    
    private function getStudentId() {
        $userId = $this->session->getUserId();
        $student = $this->db->table('students')->where('user_id', $userId)->first();
        return $student ? $student->id : null;
    }
    
    private function generateStudentId() {
        return 'STU' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    private function generateEmployeeId() {
        return 'EMP' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
