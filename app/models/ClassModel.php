<?php
/**
 * Class Model
 * School Management System
 */

class ClassModel {
    private $db;
    
    public function __construct() {
        $this->db = new QueryBuilder();
    }
    
    /**
     * Get class by ID
     * @param int $id
     * @return object|null
     */
    public function findById($id) {
        return $this->db->table('classes')
            ->where('id', $id)
            ->first();
    }
    
    /**
     * Create new class
     * @param array $data
     * @return bool
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table('classes')->insert($data);
    }
    
    /**
     * Update class
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('classes')
            ->where('id', $id)
            ->update($data)
            ->execute();
    }
    
    /**
     * Delete class
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        return $this->db->table('classes')
            ->where('id', $id)
            ->delete()
            ->execute();
    }
    
    /**
     * Get all classes
     * @return array
     */
    public function getAll() {
        return $this->db->table('classes')
            ->where('status', STATUS_ACTIVE)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get class with sections
     * @param int $id
     * @return object|null
     */
    public function getWithSections($id) {
        $class = $this->findById($id);
        
        if ($class) {
            $class->sections = $this->db->table('sections')
                ->leftJoin('teachers', 'teachers.id', '=', 'sections.teacher_id')
                ->leftJoin('users', 'users.id', '=', 'teachers.user_id')
                ->where('sections.class_id', $id)
                ->where('sections.status', STATUS_ACTIVE)
                ->select('sections.*, teachers.first_name as teacher_first_name, teachers.last_name as teacher_last_name, users.username as teacher_username')
                ->orderBy('sections.name')
                ->get();
        }
        
        return $class;
    }
    
    /**
     * Get classes by grade level
     * @param int $gradeLevel
     * @return array
     */
    public function getByGradeLevel($gradeLevel) {
        return $this->db->table('classes')
            ->where('grade_level', $gradeLevel)
            ->where('status', STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get total student count for class
     * @param int $classId
     * @return int
     */
    public function getStudentCount($classId) {
        return $this->db->table('students')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
            ->where('sections.class_id', $classId)
            ->where('enrollments.status', ENROLLMENT_ACTIVE)
            ->count();
    }
    
    /**
     * Get total section count for class
     * @param int $classId
     * @return int
     */
    public function getSectionCount($classId) {
        return $this->db->table('sections')
            ->where('class_id', $classId)
            ->where('status', STATUS_ACTIVE)
            ->count();
    }
    
    /**
     * Get class statistics
     * @param int $classId
     * @return array
     */
    public function getStatistics($classId) {
        $stats = [];
        
        // Total students
        $stats['total_students'] = $this->getStudentCount($classId);
        
        // Total sections
        $stats['total_sections'] = $this->getSectionCount($classId);
        
        // Assigned teachers
        $stats['total_teachers'] = $this->db->table('sections')
            ->leftJoin('teachers', 'teachers.id', '=', 'sections.teacher_id')
            ->where('sections.class_id', $classId)
            ->where('sections.status', STATUS_ACTIVE)
            ->whereNotNull('sections.teacher_id')
            ->count();
        
        // Average attendance (last 30 days)
        $stats['average_attendance'] = $this->getAverageAttendance($classId);
        
        return $stats;
    }
    
    /**
     * Get average attendance for class
     * @param int $classId
     * @return float
     */
    private function getAverageAttendance($classId) {
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        
        $attendanceData = $this->db->table('attendance')
            ->leftJoin('sections', 'sections.id', '=', 'attendance.section_id')
            ->where('sections.class_id', $classId)
            ->where('attendance.date', '>=', $thirtyDaysAgo)
            ->select('attendance.status')
            ->get();
        
        if (empty($attendanceData)) {
            return 0.0;
        }
        
        $total = count($attendanceData);
        $present = 0;
        
        foreach ($attendanceData as $attendance) {
            if ($attendance->status === ATTENDANCE_PRESENT) {
                $present++;
            }
        }
        
        return round(($present / $total) * 100, 2);
    }
    
    /**
     * Search classes
     * @param string $search
     * @return array
     */
    public function search($search) {
        return $this->db->table('classes')
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('description', 'LIKE', "%{$search}%")
            ->orWhere('grade_level', 'LIKE', "%{$search}%")
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Update class status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        return $this->db->table('classes')
            ->where('id', $id)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->execute();
    }
    
    /**
     * Get total class count
     * @return int
     */
    public function getTotalCount() {
        return $this->db->table('classes')->count();
    }
    
    /**
     * Get active class count
     * @return int
     */
    public function getActiveCount() {
        return $this->db->table('classes')->where('status', STATUS_ACTIVE)->count();
    }
    
    /**
     * Check if class name exists
     * @param string $name
     * @param int|null $excludeId
     * @return bool
     */
    public function nameExists($name, $excludeId = null) {
        $query = $this->db->table('classes')->where('name', $name);
        
        if ($excludeId) {
            $query->andWhere('id', '!=', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * Get classes with enrollment data
     * @return array
     */
    public function getWithEnrollmentData() {
        $classes = $this->getAll();
        
        foreach ($classes as $class) {
            $class->student_count = $this->getStudentCount($class->id);
            $class->section_count = $this->getSectionCount($class->id);
            $class->teacher_count = $this->db->table('sections')
                ->where('class_id', $class->id)
                ->where('status', STATUS_ACTIVE)
                ->whereNotNull('teacher_id')
                ->count();
        }
        
        return $classes;
    }
    
    /**
     * Get class performance data
     * @param int $classId
     * @param string $academicYear
     * @return array
     */
    public function getPerformanceData($classId, $academicYear = '') {
        $query = $this->db->table('results')
            ->leftJoin('students', 'students.id', '=', 'results.student_id')
            ->leftJoin('enrollments', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->where('sections.class_id', $classId)
            ->select('results.marks_obtained, results.grade, exams.exam_type');
        
        if (!empty($academicYear)) {
            $query->andWhere('exams.exam_date', '>=', $academicYear . '-01-01')
                  ->andWhere('exams.exam_date', '<=', $academicYear . '-12-31');
        }
        
        $results = $query->get();
        
        $performance = [
            'total_exams' => count($results),
            'average_marks' => 0,
            'grade_distribution' => []
        ];
        
        if (!empty($results)) {
            $totalMarks = 0;
            foreach ($results as $result) {
                $totalMarks += $result->marks_obtained;
                
                $grade = $result->grade;
                if (!isset($performance['grade_distribution'][$grade])) {
                    $performance['grade_distribution'][$grade] = 0;
                }
                $performance['grade_distribution'][$grade]++;
            }
            
            $performance['average_marks'] = round($totalMarks / count($results), 2);
        }
        
        return $performance;
    }
    
    /**
     * Get class attendance data
     * @param int $classId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAttendanceData($classId, $startDate = '', $endDate = '') {
        $query = $this->db->table('attendance')
            ->leftJoin('sections', 'sections.id', '=', 'attendance.section_id')
            ->where('sections.class_id', $classId)
            ->select('attendance.date, attendance.status');
        
        if (!empty($startDate)) {
            $query->andWhere('attendance.date', '>=', $startDate);
        }
        
        if (!empty($endDate)) {
            $query->andWhere('attendance.date', '<=', $endDate);
        }
        
        $attendanceRecords = $query->get();
        
        $attendanceData = [
            'total_days' => 0,
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'excused_days' => 0,
            'attendance_rate' => 0
        ];
        
        if (!empty($attendanceRecords)) {
            $attendanceData['total_days'] = count($attendanceRecords);
            
            foreach ($attendanceRecords as $record) {
                $status = $record->status . '_days';
                if (isset($attendanceData[$status])) {
                    $attendanceData[$status]++;
                }
            }
            
            if ($attendanceData['total_days'] > 0) {
                $attendanceData['attendance_rate'] = round(($attendanceData['present_days'] / $attendanceData['total_days']) * 100, 2);
            }
        }
        
        return $attendanceData;
    }
}
