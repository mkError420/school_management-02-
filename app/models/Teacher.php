<?php
/**
 * Teacher Model
 * School Management System
 */

class Teacher {
    private $db;
    
    public function __construct() {
        $this->db = new QueryBuilder();
    }
    
    /**
     * Get teacher by ID
     * @param int $id
     * @return object|null
     */
    public function findById($id) {
        return $this->db->table('teachers')
            ->leftJoin('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.id', $id)
            ->select('teachers.*, users.username, users.email, users.status')
            ->first();
    }
    
    /**
     * Get teacher by user ID
     * @param int $userId
     * @return object|null
     */
    public function findByUserId($userId) {
        return $this->db->table('teachers')
            ->leftJoin('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.user_id', $userId)
            ->select('teachers.*, users.username, users.email, users.status')
            ->first();
    }
    
    /**
     * Get teacher by employee ID
     * @param string $employeeId
     * @return object|null
     */
    public function findByEmployeeId($employeeId) {
        return $this->db->table('teachers')
            ->leftJoin('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.employee_id', $employeeId)
            ->select('teachers.*, users.username, users.email, users.status')
            ->first();
    }
    
    /**
     * Create new teacher
     * @param array $data
     * @return bool
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table('teachers')->insert($data);
    }
    
    /**
     * Update teacher
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('teachers')
            ->where('id', $id)
            ->update($data)
            ->execute();
    }
    
    /**
     * Delete teacher
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        return $this->db->table('teachers')
            ->where('id', $id)
            ->delete()
            ->execute();
    }
    
    /**
     * Get all teachers with pagination
     * @param int $page
     * @param string $search
     * @param string $status
     * @return array
     */
    public function getAll($page = 1, $search = '', $status = '') {
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $query = $this->db->table('teachers')
            ->leftJoin('users', 'users.id', '=', 'teachers.user_id')
            ->select('teachers.*, users.username, users.email, users.status as user_status');
        
        // Apply filters
        if (!empty($search)) {
            $query->where('teachers.first_name', 'LIKE', "%{$search}%")
                  ->orWhere('teachers.last_name', 'LIKE', "%{$search}%")
                  ->orWhere('teachers.employee_id', 'LIKE', "%{$search}%")
                  ->orWhere('users.username', 'LIKE', "%{$search}%")
                  ->orWhere('users.email', 'LIKE', "%{$search}%");
        }
        
        if (!empty($status)) {
            $query->andWhere('teachers.status', $status);
        }
        
        // Get total count
        $totalQuery = clone $query;
        $total = $totalQuery->count();
        
        // Get results
        $teachers = $query->orderBy('teachers.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get();
        
        return [
            'teachers' => $teachers,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get teacher's assigned classes
     * @param int $teacherId
     * @return array
     */
    public function getAssignedClasses($teacherId) {
        return $this->db->table('sections')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->where('sections.teacher_id', $teacherId)
            ->where('sections.status', STATUS_ACTIVE)
            ->select('sections.*, classes.name as class_name, classes.grade_level')
            ->orderBy('classes.grade_level')
            ->orderBy('sections.name')
            ->get();
    }
    
    /**
     * Get teacher's students
     * @param int $teacherId
     * @param int $sectionId
     * @return array
     */
    public function getStudents($teacherId, $sectionId = null) {
        $query = $this->db->table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->where('sections.teacher_id', $teacherId)
            ->where('enrollments.status', ENROLLMENT_ACTIVE)
            ->select('students.*, users.username, users.email, classes.name as class_name, sections.name as section_name');
        
        if ($sectionId) {
            $query->andWhere('sections.id', $sectionId);
        }
        
        return $query->orderBy('students.first_name')->get();
    }
    
    /**
     * Get teacher's subjects
     * @param int $teacherId
     * @return array
     */
    public function getSubjects($teacherId) {
        // Get subjects from teacher's assigned classes
        $sections = $this->getAssignedClasses($teacherId);
        $subjectIds = [];
        
        foreach ($sections as $section) {
            // This would typically come from a teacher-subject assignment table
            // For now, we'll return all subjects
        }
        
        return $this->db->table('subjects')
            ->where('status', STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Mark attendance for students
     * @param array $attendanceData
     * @return bool
     */
    public function markAttendance($attendanceData) {
        try {
            $this->db->beginTransaction();
            
            foreach ($attendanceData as $data) {
                // Check if attendance already exists
                $existing = $this->db->table('attendance')
                    ->where('student_id', $data['student_id'])
                    ->where('section_id', $data['section_id'])
                    ->where('date', $data['date'])
                    ->first();
                
                if ($existing) {
                    // Update existing attendance
                    $this->db->table('attendance')
                        ->where('id', $existing->id)
                        ->update([
                            'status' => $data['status'],
                            'remarks' => $data['remarks'] ?? null,
                            'marked_by' => $data['marked_by'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ])
                        ->execute();
                } else {
                    // Insert new attendance
                    $this->db->table('attendance')->insert([
                        'student_id' => $data['student_id'],
                        'section_id' => $data['section_id'],
                        'subject_id' => $data['subject_id'] ?? null,
                        'date' => $data['date'],
                        'status' => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                        'marked_by' => $data['marked_by'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get attendance records for teacher's classes
     * @param int $teacherId
     * @param string $date
     * @param int $sectionId
     * @return array
     */
    public function getAttendanceRecords($teacherId, $date, $sectionId = null) {
        $query = $this->db->table('attendance')
            ->leftJoin('students', 'students.id', '=', 'attendance.student_id')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('sections', 'sections.id', '=', 'attendance.section_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'attendance.subject_id')
            ->where('sections.teacher_id', $teacherId)
            ->where('attendance.date', $date)
            ->select('attendance.*, students.first_name, students.last_name, students.student_id, users.username, classes.name as class_name, sections.name as section_name, subjects.name as subject_name');
        
        if ($sectionId) {
            $query->andWhere('sections.id', $sectionId);
        }
        
        return $query->orderBy('students.first_name')->get();
    }
    
    /**
     * Store student marks/results
     * @param array $resultData
     * @return bool
     */
    public function storeResult($resultData) {
        try {
            $this->db->beginTransaction();
            
            // Check if result already exists
            $existing = $this->db->table('results')
                ->where('student_id', $resultData['student_id'])
                ->where('exam_id', $resultData['exam_id'])
                ->first();
            
            if ($existing) {
                // Update existing result
                $this->db->table('results')
                    ->where('id', $existing->id)
                    ->update([
                        'marks_obtained' => $resultData['marks_obtained'],
                        'grade' => getGrade($resultData['marks_obtained']),
                        'remarks' => $resultData['remarks'] ?? null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ])
                    ->execute();
            } else {
                // Insert new result
                $this->db->table('results')->insert([
                    'student_id' => $resultData['student_id'],
                    'exam_id' => $resultData['exam_id'],
                    'marks_obtained' => $resultData['marks_obtained'],
                    'grade' => getGrade($resultData['marks_obtained']),
                    'remarks' => $resultData['remarks'] ?? null,
                    'created_by' => $resultData['created_by'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get teacher's exam results
     * @param int $teacherId
     * @param int $examId
     * @return array
     */
    public function getExamResults($teacherId, $examId) {
        return $this->db->table('results')
            ->leftJoin('students', 'students.id', '=', 'results.student_id')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->where('sections.teacher_id', $teacherId)
            ->where('results.exam_id', $examId)
            ->select('results.*, students.first_name, students.last_name, students.student_id, users.username, exams.title as exam_title, exams.total_marks')
            ->orderBy('students.first_name')
            ->get();
    }
    
    /**
     * Get teacher statistics
     * @param int $teacherId
     * @return array
     */
    public function getStatistics($teacherId) {
        $stats = [];
        
        // Total assigned classes
        $stats['total_classes'] = $this->db->table('sections')
            ->where('teacher_id', $teacherId)
            ->where('status', STATUS_ACTIVE)
            ->count();
        
        // Total students
        $stats['total_students'] = $this->db->table('students')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
            ->where('sections.teacher_id', $teacherId)
            ->where('enrollments.status', ENROLLMENT_ACTIVE)
            ->count();
        
        // Today's attendance
        $today = date('Y-m-d');
        $stats['attendance_today'] = $this->db->table('attendance')
            ->leftJoin('sections', 'sections.id', '=', 'attendance.section_id')
            ->where('sections.teacher_id', $teacherId)
            ->where('attendance.date', $today)
            ->count();
        
        // Pending exams
        $stats['pending_exams'] = $this->db->table('exams')
            ->leftJoin('sections', 'sections.id', '=', 'exams.class_id')
            ->where('sections.teacher_id', $teacherId)
            ->where('exams.status', EXAM_PUBLISHED)
            ->where('exams.exam_date', '>=', $today)
            ->count();
        
        return $stats;
    }
    
    /**
     * Get total teacher count
     * @return int
     */
    public function getTotalCount() {
        return $this->db->table('teachers')->count();
    }
    
    /**
     * Get active teacher count
     * @return int
     */
    public function getActiveCount() {
        return $this->db->table('teachers')->where('status', STATUS_ACTIVE)->count();
    }
    
    /**
     * Search teachers
     * @param string $search
     * @return array
     */
    public function search($search) {
        return $this->db->table('teachers')
            ->leftJoin('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.first_name', 'LIKE', "%{$search}%")
            ->orWhere('teachers.last_name', 'LIKE', "%{$search}%")
            ->orWhere('teachers.employee_id', 'LIKE', "%{$search}%")
            ->orWhere('users.username', 'LIKE', "%{$search}%")
            ->orWhere('users.email', 'LIKE', "%{$search}%")
            ->select('teachers.*, users.username, users.email')
            ->orderBy('teachers.first_name')
            ->get();
    }
    
    /**
     * Update teacher status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        return $this->db->table('teachers')
            ->where('id', $id)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->execute();
    }
    
    /**
     * Check if employee ID exists
     * @param string $employeeId
     * @param int|null $excludeId
     * @return bool
     */
    public function employeeIdExists($employeeId, $excludeId = null) {
        $query = $this->db->table('teachers')->where('employee_id', $employeeId);
        
        if ($excludeId) {
            $query->andWhere('id', '!=', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * Get teacher's schedule
     * @param int $teacherId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSchedule($teacherId, $startDate = '', $endDate = '') {
        // This would typically come from a schedule/timetable table
        // For now, return empty array
        return [];
    }
    
    /**
     * Get teacher's salary information
     * @param int $teacherId
     * @return object|null
     */
    public function getSalaryInfo($teacherId) {
        return $this->db->table('teachers')
            ->where('id', $teacherId)
            ->select('salary, hire_date, experience_years')
            ->first();
    }
    
    /**
     * Update teacher profile picture
     * @param int $id
     * @param string $filename
     * @return bool
     */
    public function updateProfilePicture($id, $filename) {
        return $this->db->table('teachers')
            ->where('id', $id)
            ->update(['profile_picture' => $filename, 'updated_at' => date('Y-m-d H:i:s')])
            ->execute();
    }
}
