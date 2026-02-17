<?php
/**
 * Teacher Controller
 * School Management System
 */

class TeacherController {
    private $userModel;
    private $teacherModel;
    private $studentModel;
    private $classModel;
    private $subjectModel;
    private $session;
    private $currentTeacher;
    
    public function __construct() {
        // Check authentication and teacher role
        AuthMiddleware::requireRole([ROLE_ADMIN, ROLE_TEACHER], function() {
            $this->init();
        });
    }
    
    private function init() {
        require_once INCLUDES_PATH . '/Session.php';
        require_once APP_PATH . '/models/User.php';
        require_once APP_PATH . '/models/Teacher.php';
        require_once APP_PATH . '/models/Student.php';
        require_once APP_PATH . '/models/ClassModel.php';
        require_once APP_PATH . '/models/Subject.php';
        
        $this->session = Session::getInstance();
        $this->userModel = new User();
        $this->teacherModel = new Teacher();
        $this->studentModel = new Student();
        $this->classModel = new ClassModel();
        $this->subjectModel = new Subject();
        
        // Get current teacher
        $userId = $this->session->getUserId();
        $this->currentTeacher = $this->teacherModel->findByUserId($userId);
    }
    
    /**
     * Teacher Dashboard
     */
    public function dashboard() {
        if (!$this->currentTeacher) {
            $this->session->setFlash('Teacher profile not found!', 'error');
            redirect('login');
            return;
        }
        
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $upcomingExams = $this->getUpcomingExams();
        $attendanceOverview = $this->getAttendanceOverview();
        
        include APP_PATH . '/views/teacher/dashboard.php';
    }
    
    /**
     * My Classes
     */
    public function classes() {
        if (!$this->currentTeacher) {
            redirect('login');
            return;
        }
        
        $assignedClasses = $this->teacherModel->getAssignedClasses($this->currentTeacher->id);
        
        include APP_PATH . '/views/teacher/classes.php';
    }
    
    /**
     * Attendance Management
     */
    public function attendance() {
        if (!$this->currentTeacher) {
            redirect('login');
            return;
        }
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $sectionId = $_GET['section'] ?? '';
        
        $assignedClasses = $this->teacherModel->getAssignedClasses($this->currentTeacher->id);
        $attendanceRecords = [];
        $students = [];
        
        if ($sectionId) {
            $students = $this->teacherModel->getStudents($this->currentTeacher->id, $sectionId);
            $attendanceRecords = $this->teacherModel->getAttendanceRecords($this->currentTeacher->id, $date, $sectionId);
        }
        
        include APP_PATH . '/views/teacher/attendance.php';
    }
    
    /**
     * Mark Attendance
     */
    public function markAttendance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(null, HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
            return;
        }
        
        if (!$this->currentTeacher) {
            jsonResponse(null, HTTP_UNAUTHORIZED, 'Teacher not found');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid request');
            return;
        }
        
        $date = $_POST['date'] ?? date('Y-m-d');
        $sectionId = $_POST['section_id'] ?? 0;
        $attendanceData = $_POST['attendance'] ?? [];
        
        if (empty($sectionId) || empty($attendanceData)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Missing required data');
            return;
        }
        
        // Validate date
        if (!strtotime($date)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid date');
            return;
        }
        
        // Prepare attendance data
        $attendanceRecords = [];
        foreach ($attendanceData as $studentId => $status) {
            $attendanceRecords[] = [
                'student_id' => $studentId,
                'section_id' => $sectionId,
                'date' => $date,
                'status' => $status,
                'remarks' => $_POST['remarks'][$studentId] ?? null,
                'marked_by' => $this->currentTeacher->id
            ];
        }
        
        // Mark attendance
        if ($this->teacherModel->markAttendance($attendanceRecords)) {
            jsonResponse(null, HTTP_OK, 'Attendance marked successfully');
        } else {
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to mark attendance');
        }
    }
    
    /**
     * Marks Management
     */
    public function marks() {
        if (!$this->currentTeacher) {
            redirect('login');
            return;
        }
        
        $examId = $_GET['exam'] ?? '';
        $sectionId = $_GET['section'] ?? '';
        
        $assignedClasses = $this->teacherModel->getAssignedClasses($this->currentTeacher->id);
        $exams = $this->getTeacherExams();
        $students = [];
        $examResults = [];
        
        if ($examId && $sectionId) {
            $students = $this->teacherModel->getStudents($this->currentTeacher->id, $sectionId);
            $examResults = $this->teacherModel->getExamResults($this->currentTeacher->id, $examId);
        }
        
        include APP_PATH . '/views/teacher/marks.php';
    }
    
    /**
     * Store Marks
     */
    public function storeMarks() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(null, HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
            return;
        }
        
        if (!$this->currentTeacher) {
            jsonResponse(null, HTTP_UNAUTHORIZED, 'Teacher not found');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid request');
            return;
        }
        
        $examId = $_POST['exam_id'] ?? 0;
        $marksData = $_POST['marks'] ?? [];
        
        if (empty($examId) || empty($marksData)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Missing required data');
            return;
        }
        
        // Validate exam belongs to teacher
        if (!$this->isTeacherExam($examId)) {
            jsonResponse(null, HTTP_FORBIDDEN, 'Access denied');
            return;
        }
        
        $success = true;
        $errors = [];
        
        foreach ($marksData as $studentId => $marks) {
            // Validate marks
            if (!is_numeric($marks) || $marks < 0 || $marks > 100) {
                $errors[] = "Invalid marks for student ID: $studentId";
                $success = false;
                continue;
            }
            
            $resultData = [
                'student_id' => $studentId,
                'exam_id' => $examId,
                'marks_obtained' => $marks,
                'remarks' => $_POST['remarks'][$studentId] ?? null,
                'created_by' => $this->currentTeacher->id
            ];
            
            if (!$this->teacherModel->storeResult($resultData)) {
                $errors[] = "Failed to store marks for student ID: $studentId";
                $success = false;
            }
        }
        
        if ($success) {
            jsonResponse(null, HTTP_OK, 'Marks stored successfully');
        } else {
            jsonResponse(['errors' => $errors], HTTP_INTERNAL_SERVER_ERROR, 'Some marks could not be stored');
        }
    }
    
    /**
     * Update Marks
     */
    public function updateMarks() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(null, HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
            return;
        }
        
        if (!$this->currentTeacher) {
            jsonResponse(null, HTTP_UNAUTHORIZED, 'Teacher not found');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid request');
            return;
        }
        
        $resultId = $_POST['result_id'] ?? 0;
        $marks = $_POST['marks'] ?? 0;
        $remarks = $_POST['remarks'] ?? '';
        
        if (empty($resultId)) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Missing result ID');
            return;
        }
        
        // Validate marks
        if (!is_numeric($marks) || $marks < 0 || $marks > 100) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid marks');
            return;
        }
        
        // Check if result belongs to teacher's exam
        $result = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->where('results.id', $resultId)
            ->where('sections.teacher_id', $this->currentTeacher->id)
            ->first();
        
        if (!$result) {
            jsonResponse(null, HTTP_FORBIDDEN, 'Access denied');
            return;
        }
        
        // Update result
        $updateData = [
            'marks_obtained' => $marks,
            'grade' => getGrade($marks),
            'remarks' => $remarks,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->db->table('results')->where('id', $resultId)->update($updateData)->execute()) {
            jsonResponse(null, HTTP_OK, 'Marks updated successfully');
        } else {
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to update marks');
        }
    }
    
    /**
     * Students List
     */
    public function students() {
        if (!$this->currentTeacher) {
            redirect('login');
            return;
        }
        
        $sectionId = $_GET['section'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $assignedClasses = $this->teacherModel->getAssignedClasses($this->currentTeacher->id);
        $students = [];
        
        if ($sectionId) {
            $students = $this->teacherModel->getStudents($this->currentTeacher->id, $sectionId);
            
            // Apply search filter
            if (!empty($search)) {
                $filteredStudents = [];
                foreach ($students as $student) {
                    if (stripos($student->first_name, $search) !== false ||
                        stripos($student->last_name, $search) !== false ||
                        stripos($student->student_id, $search) !== false ||
                        stripos($student->username, $search) !== false) {
                        $filteredStudents[] = $student;
                    }
                }
                $students = $filteredStudents;
            }
        }
        
        include APP_PATH . '/views/teacher/students.php';
    }
    
    /**
     * Student Details
     */
    public function studentDetails() {
        if (!$this->currentTeacher) {
            redirect('login');
            return;
        }
        
        $studentId = $_GET['id'] ?? 0;
        
        if (empty($studentId)) {
            $this->session->setFlash('Student ID not provided!', 'error');
            redirect('teacher/students');
            return;
        }
        
        // Check if student belongs to teacher's class
        $student = $this->db->table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->where('students.id', $studentId)
            ->where('sections.teacher_id', $this->currentTeacher->id)
            ->where('enrollments.status', ENROLLMENT_ACTIVE)
            ->select('students.*, users.username, users.email, classes.name as class_name, sections.name as section_name')
            ->first();
        
        if (!$student) {
            $this->session->setFlash('Student not found or not in your class!', 'error');
            redirect('teacher/students');
            return;
        }
        
        // Get student statistics
        $statistics = $this->studentModel->getStatistics($studentId);
        
        // Get recent attendance
        $attendance = $this->studentModel->getAttendance($studentId, date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
        
        // Get recent results
        $results = $this->studentModel->getResults($studentId, getCurrentAcademicYear());
        
        include APP_PATH . '/views/teacher/student_details.php';
    }
    
    /**
     * Reports
     */
    public function reports() {
        if (!$this->currentTeacher) {
            redirect('login');
            return;
        }
        
        $reportType = $_GET['type'] ?? 'attendance';
        $sectionId = $_GET['section'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $assignedClasses = $this->teacherModel->getAssignedClasses($this->currentTeacher->id);
        $reportData = [];
        
        if ($sectionId) {
            switch ($reportType) {
                case 'attendance':
                    $reportData = $this->getAttendanceReport($sectionId, $startDate, $endDate);
                    break;
                case 'results':
                    $reportData = $this->getResultsReport($sectionId, $startDate, $endDate);
                    break;
                case 'performance':
                    $reportData = $this->getPerformanceReport($sectionId, $startDate, $endDate);
                    break;
            }
        }
        
        include APP_PATH . '/views/teacher/reports.php';
    }
    
    // Helper methods
    private function getDashboardStats() {
        return $this->teacherModel->getStatistics($this->currentTeacher->id);
    }
    
    private function getRecentActivities() {
        // Get recent activities for teacher
        return [
            'recent_attendance' => $this->getRecentAttendance(),
            'recent_marks' => $this->getRecentMarks(),
            'recent_exams' => $this->getRecentExams()
        ];
    }
    
    private function getUpcomingExams() {
        return $this->db->table('exams')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->where('sections.teacher_id', $this->currentTeacher->id)
            ->where('exams.status', EXAM_PUBLISHED)
            ->where('exams.exam_date', '>=', date('Y-m-d'))
            ->select('exams.*, subjects.name as subject_name, sections.name as section_name')
            ->orderBy('exams.exam_date')
            ->limit(5)
            ->get();
    }
    
    private function getAttendanceOverview() {
        $today = date('Y-m-d');
        $sections = $this->teacherModel->getAssignedClasses($this->currentTeacher->id);
        
        $overview = [];
        foreach ($sections as $section) {
            $attendance = $this->db->table('attendance')
                ->where('section_id', $section->id)
                ->where('date', $today)
                ->select('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();
            
            $total = 0;
            $present = 0;
            foreach ($attendance as $record) {
                $total += $record->count;
                if ($record->status === ATTENDANCE_PRESENT) {
                    $present += $record->count;
                }
            }
            
            $overview[] = [
                'section' => $section,
                'total' => $total,
                'present' => $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0
            ];
        }
        
        return $overview;
    }
    
    private function getTeacherExams() {
        return $this->db->table('exams')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->where('sections.teacher_id', $this->currentTeacher->id)
            ->select('exams.*, subjects.name as subject_name, sections.name as section_name')
            ->orderBy('exams.exam_date', 'DESC')
            ->get();
    }
    
    private function isTeacherExam($examId) {
        return $this->db->table('exams')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->where('exams.id', $examId)
            ->where('sections.teacher_id', $this->currentTeacher->id)
            ->count() > 0;
    }
    
    private function getRecentAttendance() {
        return $this->db->table('attendance')
            ->leftJoin('sections', 'sections.id', '=', 'attendance.section_id')
            ->where('sections.teacher_id', $this->currentTeacher->id)
            ->where('attendance.date', '>=', date('Y-m-d', strtotime('-7 days')))
            ->select('attendance.date, COUNT(*) as total_students')
            ->groupBy('attendance.date')
            ->orderBy('attendance.date', 'DESC')
            ->limit(5)
            ->get();
    }
    
    private function getRecentMarks() {
        return $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->where('sections.teacher_id', $this->currentTeacher->id)
            ->where('results.created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->select('results.created_at, COUNT(*) as total_marks')
            ->groupBy('DATE(results.created_at)')
            ->orderBy('results.created_at', 'DESC')
            ->limit(5)
            ->get();
    }
    
    private function getRecentExams() {
        return $this->db->table('exams')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->where('sections.teacher_id', $this->currentTeacher->id)
            ->where('exams.status', EXAM_COMPLETED)
            ->where('exams.exam_date', '>=', date('Y-m-d', strtotime('-30 days')))
            ->select('exams.title, exams.exam_date')
            ->orderBy('exams.exam_date', 'DESC')
            ->limit(5)
            ->get();
    }
    
    private function getAttendanceReport($sectionId, $startDate, $endDate) {
        return $this->db->table('attendance')
            ->leftJoin('students', 'students.id', '=', 'attendance.student_id')
            ->leftJoin('sections', 'sections.id', '=', 'attendance.section_id')
            ->where('sections.id', $sectionId)
            ->where('attendance.date', '>=', $startDate)
            ->where('attendance.date', '<=', $endDate)
            ->select('attendance.date, attendance.status, students.first_name, students.last_name, students.student_id')
            ->orderBy('attendance.date')
            ->orderBy('students.first_name')
            ->get();
    }
    
    private function getResultsReport($sectionId, $startDate, $endDate) {
        return $this->db->table('results')
            ->leftJoin('students', 'students.id', '=', 'results.student_id')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->where('sections.id', $sectionId)
            ->where('exams.exam_date', '>=', $startDate)
            ->where('exams.exam_date', '<=', $endDate)
            ->select('results.*, students.first_name, students.last_name, students.student_id, exams.title as exam_title, exams.exam_date')
            ->orderBy('exams.exam_date')
            ->orderBy('students.first_name')
            ->get();
    }
    
    private function getPerformanceReport($sectionId, $startDate, $endDate) {
        // Get performance summary for the section
        $students = $this->teacherModel->getStudents($this->currentTeacher->id, $sectionId);
        $performanceData = [];
        
        foreach ($students as $student) {
            $stats = $this->studentModel->getStatistics($student->id);
            $performanceData[] = [
                'student' => $student,
                'stats' => $stats
            ];
        }
        
        return $performanceData;
    }
}
