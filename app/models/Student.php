<?php
/**
 * Student Model
 * School Management System
 */

class Student {
    private $db;
    
    public function __construct() {
        $this->db = new QueryBuilder();
    }
    
    /**
     * Get student by ID
     * @param int $id
     * @return object|null
     */
    public function findById($id) {
        return $this->db->table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->where('students.id', $id)
            ->select('students.*, users.username, users.email, users.status')
            ->first();
    }
    
    /**
     * Get student by user ID
     * @param int $userId
     * @return object|null
     */
    public function findByUserId($userId) {
        return $this->db->table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->where('students.user_id', $userId)
            ->select('students.*, users.username, users.email, users.status')
            ->first();
    }
    
    /**
     * Get student by student ID
     * @param string $studentId
     * @return object|null
     */
    public function findByStudentId($studentId) {
        return $this->db->table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->where('students.student_id', $studentId)
            ->select('students.*, users.username, users.email, users.status')
            ->first();
    }
    
    /**
     * Create new student
     * @param array $data
     * @return bool
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table('students')->insert($data);
    }
    
    /**
     * Update student
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('students')
            ->where('id', $id)
            ->update($data)
            ->execute();
    }
    
    /**
     * Delete student
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        return $this->db->table('students')
            ->where('id', $id)
            ->delete()
            ->execute();
    }
    
    /**
     * Get all students with pagination
     * @param int $page
     * @param string $search
     * @param int $classId
     * @param string $status
     * @return array
     */
    public function getAll($page = 1, $search = '', $classId = '', $status = '') {
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $query = $this->db->table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->select('students.*, users.username, users.email, users.status as user_status, classes.name as class_name, sections.name as section_name');
        
        // Apply filters
        if (!empty($search)) {
            $query->where('students.first_name', 'LIKE', "%{$search}%")
                  ->orWhere('students.last_name', 'LIKE', "%{$search}%")
                  ->orWhere('students.student_id', 'LIKE', "%{$search}%")
                  ->orWhere('users.username', 'LIKE', "%{$search}%")
                  ->orWhere('users.email', 'LIKE', "%{$search}%");
        }
        
        if (!empty($classId)) {
            $query->andWhere('classes.id', $classId);
        }
        
        if (!empty($status)) {
            $query->andWhere('students.status', $status);
        }
        
        // Get total count
        $totalQuery = clone $query;
        $total = $totalQuery->count();
        
        // Get results
        $students = $query->orderBy('students.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get();
        
        return [
            'students' => $students,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get students by class/section
     * @param int $sectionId
     * @return array
     */
    public function getBySection($sectionId) {
        return $this->db->table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', 'students.id')
            ->where('enrollments.section_id', $sectionId)
            ->where('enrollments.status', ENROLLMENT_ACTIVE)
            ->select('students.*, users.username, users.email')
            ->orderBy('students.first_name')
            ->get();
    }
    
    /**
     * Get student attendance
     * @param int $studentId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAttendance($studentId, $startDate = '', $endDate = '') {
        $query = $this->db->table('attendance')
            ->leftJoin('subjects', 'subjects.id', '=', 'attendance.subject_id')
            ->leftJoin('sections', 'sections.id', '=', 'attendance.section_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->where('attendance.student_id', $studentId)
            ->select('attendance.*, subjects.name as subject_name, classes.name as class_name, sections.name as section_name');
        
        if (!empty($startDate)) {
            $query->andWhere('attendance.date', '>=', $startDate);
        }
        
        if (!empty($endDate)) {
            $query->andWhere('attendance.date', '<=', $endDate);
        }
        
        return $query->orderBy('attendance.date', 'DESC')->get();
    }
    
    /**
     * Get student results
     * @param int $studentId
     * @param string $academicYear
     * @return array
     */
    public function getResults($studentId, $academicYear = '') {
        $query = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->leftJoin('classes', 'classes.id', '=', 'exams.class_id')
            ->where('results.student_id', $studentId)
            ->select('results.*, exams.title as exam_title, exams.exam_type, exams.total_marks, exams.exam_date, subjects.name as subject_name, subjects.code as subject_code, classes.name as class_name');
        
        if (!empty($academicYear)) {
            $query->andWhere('exams.exam_date', '>=', $academicYear . '-01-01')
                  ->andWhere('exams.exam_date', '<=', $academicYear . '-12-31');
        }
        
        return $query->orderBy('exams.exam_date', 'DESC')->get();
    }
    
    /**
     * Get student statistics
     * @param int $studentId
     * @return array
     */
    public function getStatistics($studentId) {
        $stats = [];
        
        // Attendance statistics
        $attendanceStats = $this->db->table('attendance')
            ->where('student_id', $studentId)
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        $stats['attendance'] = [
            'total' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0
        ];
        
        foreach ($attendanceStats as $stat) {
            $stats['attendance']['total'] += $stat->count;
            $stats['attendance'][$stat->status] = $stat->count;
        }
        
        if ($stats['attendance']['total'] > 0) {
            $stats['attendance']['percentage'] = round(($stats['attendance']['present'] / $stats['attendance']['total']) * 100, 2);
        } else {
            $stats['attendance']['percentage'] = 0;
        }
        
        // Results statistics
        $resultsStats = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->where('results.student_id', $studentId)
            ->select('AVG(results.marks_obtained) as average, MAX(results.marks_obtained) as highest, MIN(results.marks_obtained) as lowest, COUNT(*) as total_exams')
            ->first();
        
        $stats['results'] = [
            'average' => round($resultsStats->average ?? 0, 2),
            'highest' => $resultsStats->highest ?? 0,
            'lowest' => $resultsStats->lowest ?? 0,
            'total_exams' => $resultsStats->total_exams ?? 0
        ];
        
        // Grade distribution
        $gradeStats = $this->db->table('results')
            ->where('student_id', $studentId)
            ->select('grade, COUNT(*) as count')
            ->groupBy('grade')
            ->get();
        
        $stats['grades'] = [];
        foreach ($gradeStats as $grade) {
            $stats['grades'][$grade->grade] = $grade->count;
        }
        
        return $stats;
    }
    
    /**
     * Get total student count
     * @return int
     */
    public function getTotalCount() {
        return $this->db->table('students')->count();
    }
    
    /**
     * Get active student count
     * @return int
     */
    public function getActiveCount() {
        return $this->db->table('students')->where('status', STATUS_ACTIVE)->count();
    }
    
    /**
     * Search students
     * @param string $search
     * @return array
     */
    public function search($search) {
        return $this->db->table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->where('students.first_name', 'LIKE', "%{$search}%")
            ->orWhere('students.last_name', 'LIKE', "%{$search}%")
            ->orWhere('students.student_id', 'LIKE', "%{$search}%")
            ->orWhere('users.username', 'LIKE', "%{$search}%")
            ->orWhere('users.email', 'LIKE', "%{$search}%")
            ->select('students.*, users.username, users.email')
            ->orderBy('students.first_name')
            ->get();
    }
    
    /**
     * Update student status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        return $this->db->table('students')
            ->where('id', $id)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->execute();
    }
    
    /**
     * Get students by academic year
     * @param string $academicYear
     * @return array
     */
    public function getByAcademicYear($academicYear) {
        return $this->db->table('students')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->where('enrollments.academic_year', $academicYear)
            ->where('enrollments.status', ENROLLMENT_ACTIVE)
            ->select('students.*, classes.name as class_name, sections.name as section_name')
            ->orderBy('students.first_name')
            ->get();
    }
    
    /**
     * Get student class and section
     * @param int $studentId
     * @return object|null
     */
    public function getClassSection($studentId) {
        return $this->db->table('enrollments')
            ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->where('enrollments.student_id', $studentId)
            ->where('enrollments.status', ENROLLMENT_ACTIVE)
            ->select('classes.name as class_name, classes.grade_level, sections.name as section_name, sections.id as section_id')
            ->first();
    }
    
    /**
     * Check if student ID exists
     * @param string $studentId
     * @param int|null $excludeId
     * @return bool
     */
    public function studentIdExists($studentId, $excludeId = null) {
        $query = $this->db->table('students')->where('student_id', $studentId);
        
        if ($excludeId) {
            $query->andWhere('id', '!=', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * Get student age
     * @param int $studentId
     * @return int|null
     */
    public function getAge($studentId) {
        $student = $this->db->table('students')
            ->where('id', $studentId)
            ->select('date_of_birth')
            ->first();
        
        if ($student) {
            return calculateAge($student->date_of_birth);
        }
        
        return null;
    }
    
    /**
     * Get student GPA
     * @param int $studentId
     * @param string $academicYear
     * @return float
     */
    public function getGPA($studentId, $academicYear = '') {
        $query = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->where('results.student_id', $studentId);
        
        if (!empty($academicYear)) {
            $query->andWhere('exams.exam_date', '>=', $academicYear . '-01-01')
                  ->andWhere('exams.exam_date', '<=', $academicYear . '-12-31');
        }
        
        $results = $query->get();
        
        if (empty($results)) {
            return 0.0;
        }
        
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($results as $result) {
            $gradePoint = getGradePoint($result->grade);
            $credits = $this->getSubjectCredits($result->exam_id);
            
            $totalPoints += $gradePoint * $credits;
            $totalCredits += $credits;
        }
        
        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }
    
    /**
     * Get subject credits for exam
     * @param int $examId
     * @return int
     */
    private function getSubjectCredits($examId) {
        $exam = $this->db->table('exams')
            ->leftJoin('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->where('exams.id', $examId)
            ->select('subjects.credits')
            ->first();
        
        return $exam->credits ?? 1;
    }
}
