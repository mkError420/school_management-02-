<?php
/**
 * Student Controller
 * School Management System
 */

class StudentController {
    private $userModel;
    private $studentModel;
    private $session;
    private $currentStudent;
    
    public function __construct() {
        // Check authentication and student role
        AuthMiddleware::requireRole([ROLE_ADMIN, ROLE_TEACHER, ROLE_STUDENT], function() {
            $this->init();
        });
    }
    
    private function init() {
        require_once INCLUDES_PATH . '/Session.php';
        require_once APP_PATH . '/models/User.php';
        require_once APP_PATH . '/models/Student.php';
        
        $this->session = Session::getInstance();
        $this->userModel = new User();
        $this->studentModel = new Student();
        
        // Get current student
        $userId = $this->session->getUserId();
        $this->currentStudent = $this->studentModel->findByUserId($userId);
    }
    
    /**
     * Student Dashboard
     */
    public function dashboard() {
        if (!$this->currentStudent) {
            $this->session->setFlash('Student profile not found!', 'error');
            redirect('login');
            return;
        }
        
        $stats = $this->getDashboardStats();
        $recentResults = $this->getRecentResults();
        $recentAttendance = $this->getRecentAttendance();
        $upcomingExams = $this->getUpcomingExams();
        $classInfo = $this->getClassInfo();
        
        include APP_PATH . '/views/student/dashboard.php';
    }
    
    /**
     * Student Profile
     */
    public function profile() {
        if (!$this->currentStudent) {
            redirect('login');
            return;
        }
        
        $classInfo = $this->getClassInfo();
        $statistics = $this->studentModel->getStatistics($this->currentStudent->id);
        
        include APP_PATH . '/views/student/profile.php';
    }
    
    /**
     * Update Profile
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(null, HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
            return;
        }
        
        if (!$this->currentStudent) {
            jsonResponse(null, HTTP_UNAUTHORIZED, 'Student not found');
            return;
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(null, HTTP_BAD_REQUEST, 'Invalid request');
            return;
        }
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'phone' => 'phone',
            'address' => 'max:255',
            'parent_phone' => 'phone',
            'parent_email' => 'email'
        ];
        
        if (!$validator->validate($_POST, $rules)) {
            jsonResponse($validator->getErrors(), HTTP_BAD_REQUEST, 'Validation failed');
            return;
        }
        
        // Update student profile
        $data = [
            'phone' => sanitize($_POST['phone'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'parent_phone' => sanitize($_POST['parent_phone'] ?? ''),
            'parent_email' => sanitize($_POST['parent_email'] ?? '')
        ];
        
        if ($this->studentModel->update($this->currentStudent->id, $data)) {
            // Update session data
            $updatedStudent = $this->studentModel->findById($this->currentStudent->id);
            $this->session->set('user', (array) $updatedStudent);
            
            jsonResponse(['student' => $updatedStudent], HTTP_OK, 'Profile updated successfully');
        } else {
            jsonResponse(null, HTTP_INTERNAL_SERVER_ERROR, 'Failed to update profile');
        }
    }
    
    /**
     * Attendance View
     */
    public function attendance() {
        if (!$this->currentStudent) {
            redirect('login');
            return;
        }
        
        $month = $_GET['month'] ?? date('Y-m');
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $attendanceRecords = $this->studentModel->getAttendance($this->currentStudent->id, $startDate, $endDate);
        $attendanceStats = $this->getAttendanceStats($attendanceRecords);
        $classInfo = $this->getClassInfo();
        
        include APP_PATH . '/views/student/attendance.php';
    }
    
    /**
     * Results View
     */
    public function results() {
        if (!$this->currentStudent) {
            redirect('login');
            return;
        }
        
        $academicYear = $_GET['year'] ?? getCurrentAcademicYear();
        $subject = $_GET['subject'] ?? '';
        
        $results = $this->studentModel->getResults($this->currentStudent->id, $academicYear);
        $gpa = $this->studentModel->getGPA($this->currentStudent->id, $academicYear);
        $classInfo = $this->getClassInfo();
        
        // Filter by subject if specified
        if (!empty($subject)) {
            $filteredResults = [];
            foreach ($results as $result) {
                if ($result->subject_code === $subject || stripos($result->subject_name, $subject) !== false) {
                    $filteredResults[] = $result;
                }
            }
            $results = $filteredResults;
        }
        
        include APP_PATH . '/views/student/results.php';
    }
    
    /**
     * Schedule View
     */
    public function schedule() {
        if (!$this->currentStudent) {
            redirect('login');
            return;
        }
        
        $classInfo = $this->getClassInfo();
        $schedule = $this->getStudentSchedule();
        
        include APP_PATH . '/views/student/schedule.php';
    }
    
    /**
     * Download Result Card
     */
    public function downloadResultCard() {
        if (!$this->currentStudent) {
            redirect('login');
            return;
        }
        
        $academicYear = $_GET['year'] ?? getCurrentAcademicYear();
        
        // Generate PDF result card
        $this->generateResultCardPDF($academicYear);
    }
    
    /**
     * Download Attendance Report
     */
    public function downloadAttendanceReport() {
        if (!$this->currentStudent) {
            redirect('login');
            return;
        }
        
        $month = $_GET['month'] ?? date('Y-m');
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        // Generate CSV attendance report
        $this->generateAttendanceCSV($startDate, $endDate);
    }
    
    // Helper methods
    private function getDashboardStats() {
        return $this->studentModel->getStatistics($this->currentStudent->id);
    }
    
    private function getRecentResults() {
        return $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->where('results.student_id', $this->currentStudent->id)
            ->select('results.*, exams.title as exam_title, exams.exam_type, exams.exam_date, subjects.name as subject_name, subjects.code as subject_code')
            ->orderBy('exams.exam_date', 'DESC')
            ->limit(5)
            ->get();
    }
    
    private function getRecentAttendance() {
        return $this->studentModel->getAttendance($this->currentStudent->id, date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
    }
    
    private function getUpcomingExams() {
        return $this->db->table('exams')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', $this->currentStudent->id)
            ->where('enrollments.section_id', '=', 'sections.id')
            ->where('exams.status', EXAM_PUBLISHED)
            ->where('exams.exam_date', '>=', date('Y-m-d'))
            ->select('exams.*, sections.name as section_name')
            ->orderBy('exams.exam_date')
            ->limit(5)
            ->get();
    }
    
    private function getClassInfo() {
        return $this->studentModel->getClassSection($this->currentStudent->id);
    }
    
    private function getAttendanceStats($attendanceRecords) {
        $stats = [
            'total' => count($attendanceRecords),
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
            'percentage' => 0
        ];
        
        foreach ($attendanceRecords as $record) {
            $status = $record->status . '_';
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }
        
        if ($stats['total'] > 0) {
            $stats['percentage'] = round(($stats['present'] / $stats['total']) * 100, 2);
        }
        
        return $stats;
    }
    
    private function getStudentSchedule() {
        // This would typically come from a schedule/timetable table
        // For now, return a sample schedule
        $classInfo = $this->getClassInfo();
        
        if (!$classInfo) {
            return [];
        }
        
        // Sample schedule - in real implementation, this would come from database
        $schedule = [
            [
                'day' => 'Monday',
                'periods' => [
                    ['time' => '08:00-09:00', 'subject' => 'Mathematics', 'teacher' => 'John Smith'],
                    ['time' => '09:00-10:00', 'subject' => 'English', 'teacher' => 'Mary Johnson'],
                    ['time' => '10:15-11:15', 'subject' => 'Science', 'teacher' => 'Robert Wilson'],
                    ['time' => '11:15-12:15', 'subject' => 'History', 'teacher' => 'Sarah Davis'],
                    ['time' => '01:00-02:00', 'subject' => 'Computer Science', 'teacher' => 'Michael Brown']
                ]
            ],
            [
                'day' => 'Tuesday',
                'periods' => [
                    ['time' => '08:00-09:00', 'subject' => 'English', 'teacher' => 'Mary Johnson'],
                    ['time' => '09:00-10:00', 'subject' => 'Mathematics', 'teacher' => 'John Smith'],
                    ['time' => '10:15-11:15', 'subject' => 'Physical Education', 'teacher' => 'James Miller'],
                    ['time' => '11:15-12:15', 'subject' => 'Science', 'teacher' => 'Robert Wilson'],
                    ['time' => '01:00-02:00', 'subject' => 'Art', 'teacher' => 'Emily Taylor']
                ]
            ],
            [
                'day' => 'Wednesday',
                'periods' => [
                    ['time' => '08:00-09:00', 'subject' => 'Science', 'teacher' => 'Robert Wilson'],
                    ['time' => '09:00-10:00', 'subject' => 'History', 'teacher' => 'Sarah Davis'],
                    ['time' => '10:15-11:15', 'subject' => 'Mathematics', 'teacher' => 'John Smith'],
                    ['time' => '11:15-12:15', 'subject' => 'English', 'teacher' => 'Mary Johnson'],
                    ['time' => '01:00-02:00', 'subject' => 'Music', 'teacher' => 'David Anderson']
                ]
            ],
            [
                'day' => 'Thursday',
                'periods' => [
                    ['time' => '08:00-09:00', 'subject' => 'Mathematics', 'teacher' => 'John Smith'],
                    ['time' => '09:00-10:00', 'subject' => 'Computer Science', 'teacher' => 'Michael Brown'],
                    ['time' => '10:15-11:15', 'subject' => 'English', 'teacher' => 'Mary Johnson'],
                    ['time' => '11:15-12:15', 'subject' => 'Science', 'teacher' => 'Robert Wilson'],
                    ['time' => '01:00-02:00', 'subject' => 'Physical Education', 'teacher' => 'James Miller']
                ]
            ],
            [
                'day' => 'Friday',
                'periods' => [
                    ['time' => '08:00-09:00', 'subject' => 'History', 'teacher' => 'Sarah Davis'],
                    ['time' => '09:00-10:00', 'subject' => 'Science', 'teacher' => 'Robert Wilson'],
                    ['time' => '10:15-11:15', 'subject' => 'Mathematics', 'teacher' => 'John Smith'],
                    ['time' => '11:15-12:15', 'subject' => 'English', 'teacher' => 'Mary Johnson'],
                    ['time' => '01:00-02:00', 'subject' => 'Library', 'teacher' => 'Lisa White']
                ]
            ]
        ];
        
        return $schedule;
    }
    
    private function generateResultCardPDF($academicYear) {
        // Generate PDF result card
        // This would use a PDF library like TCPDF or FPDF
        
        $results = $this->studentModel->getResults($this->currentStudent->id, $academicYear);
        $gpa = $this->studentModel->getGPA($this->currentStudent->id, $academicYear);
        $classInfo = $this->getClassInfo();
        
        // For now, just return a simple text response
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="result_card_' . $this->currentStudent->student_id . '.txt"');
        
        echo "RESULT CARD\n";
        echo "============\n\n";
        echo "Student Name: " . $this->currentStudent->first_name . " " . $this->currentStudent->last_name . "\n";
        echo "Student ID: " . $this->currentStudent->student_id . "\n";
        echo "Class: " . ($classInfo->class_name ?? 'N/A') . " - " . ($classInfo->section_name ?? 'N/A') . "\n";
        echo "Academic Year: " . $academicYear . "\n";
        echo "GPA: " . $gpa . "\n\n";
        
        echo "RESULTS:\n";
        echo "--------\n";
        foreach ($results as $result) {
            echo $result->subject_name . " (" . $result->exam_title . "): " . $result->marks_obtained . " - " . $result->grade . "\n";
        }
        
        exit;
    }
    
    private function generateAttendanceCSV($startDate, $endDate) {
        $attendanceRecords = $this->studentModel->getAttendance($this->currentStudent->id, $startDate, $endDate);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_' . $this->currentStudent->student_id . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV header
        fputcsv($output, ['Date', 'Subject', 'Status', 'Remarks']);
        
        // CSV data
        foreach ($attendanceRecords as $record) {
            fputcsv($output, [
                $record->date,
                $record->subject_name ?? 'N/A',
                $record->status,
                $record->remarks ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
}
