<?php
/**
 * Subject Model
 * School Management System
 */

class Subject {
    private $db;
    
    public function __construct() {
        $this->db = new QueryBuilder();
    }
    
    /**
     * Get subject by ID
     * @param int $id
     * @return object|null
     */
    public function findById($id) {
        return $this->db->table('subjects')
            ->where('id', $id)
            ->first();
    }
    
    /**
     * Get subject by code
     * @param string $code
     * @return object|null
     */
    public function findByCode($code) {
        return $this->db->table('subjects')
            ->where('code', $code)
            ->first();
    }
    
    /**
     * Create new subject
     * @param array $data
     * @return bool
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table('subjects')->insert($data);
    }
    
    /**
     * Update subject
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');
        return $this->db->table('subjects')
            ->where('id', $id)
            ->update($data)
            ->execute();
    }
    
    /**
     * Delete subject
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        return $this->db->table('subjects')
            ->where('id', $id)
            ->delete()
            ->execute();
    }
    
    /**
     * Get all subjects
     * @return array
     */
    public function getAll() {
        return $this->db->table('subjects')
            ->where('status', STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get subjects with pagination
     * @param int $page
     * @param string $search
     * @return array
     */
    public function getAllWithPagination($page = 1, $search = '') {
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $query = $this->db->table('subjects');
        
        // Apply search filter
        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
        }
        
        // Get total count
        $totalQuery = clone $query;
        $total = $totalQuery->count();
        
        // Get results
        $subjects = $query->orderBy('name')
            ->limit($limit, $offset)
            ->get();
        
        return [
            'subjects' => $subjects,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get subjects by class
     * @param int $classId
     * @return array
     */
    public function getByClass($classId) {
        // This would typically come from a class-subject assignment table
        // For now, return all active subjects
        return $this->getAll();
    }
    
    /**
     * Get subjects with exam count
     * @return array
     */
    public function getWithExamCount() {
        $subjects = $this->getAll();
        
        foreach ($subjects as $subject) {
            $subject->exam_count = $this->db->table('exams')
                ->where('subject_id', $subject->id)
                ->count();
        }
        
        return $subjects;
    }
    
    /**
     * Get subject statistics
     * @param int $subjectId
     * @return array
     */
    public function getStatistics($subjectId) {
        $stats = [];
        
        // Total exams
        $stats['total_exams'] = $this->db->table('exams')
            ->where('subject_id', $subjectId)
            ->count();
        
        // Total results
        $stats['total_results'] = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->where('exams.subject_id', $subjectId)
            ->count();
        
        // Average marks
        $avgMarks = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->where('exams.subject_id', $subjectId)
            ->select('AVG(results.marks_obtained) as average')
            ->first();
        
        $stats['average_marks'] = round($avgMarks->average ?? 0, 2);
        
        // Grade distribution
        $gradeStats = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->where('exams.subject_id', $subjectId)
            ->select('results.grade, COUNT(*) as count')
            ->groupBy('results.grade')
            ->get();
        
        $stats['grade_distribution'] = [];
        foreach ($gradeStats as $grade) {
            $stats['grade_distribution'][$grade->grade] = $grade->count;
        }
        
        return $stats;
    }
    
    /**
     * Search subjects
     * @param string $search
     * @return array
     */
    public function search($search) {
        return $this->db->table('subjects')
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('code', 'LIKE', "%{$search}%")
            ->orWhere('description', 'LIKE', "%{$search}%")
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Update subject status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        return $this->db->table('subjects')
            ->where('id', $id)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->execute();
    }
    
    /**
     * Get total subject count
     * @return int
     */
    public function getTotalCount() {
        return $this->db->table('subjects')->count();
    }
    
    /**
     * Get active subject count
     * @return int
     */
    public function getActiveCount() {
        return $this->db->table('subjects')->where('status', STATUS_ACTIVE)->count();
    }
    
    /**
     * Check if subject code exists
     * @param string $code
     * @param int|null $excludeId
     * @return bool
     */
    public function codeExists($code, $excludeId = null) {
        $query = $this->db->table('subjects')->where('code', $code);
        
        if ($excludeId) {
            $query->andWhere('id', '!=', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * Check if subject name exists
     * @param string $name
     * @param int|null $excludeId
     * @return bool
     */
    public function nameExists($name, $excludeId = null) {
        $query = $this->db->table('subjects')->where('name', $name);
        
        if ($excludeId) {
            $query->andWhere('id', '!=', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * Get subject performance data
     * @param int $subjectId
     * @param string $academicYear
     * @return array
     */
    public function getPerformanceData($subjectId, $academicYear = '') {
        $query = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->where('exams.subject_id', $subjectId)
            ->select('results.marks_obtained, results.grade, exams.exam_type, exams.exam_date');
        
        if (!empty($academicYear)) {
            $query->andWhere('exams.exam_date', '>=', $academicYear . '-01-01')
                  ->andWhere('exams.exam_date', '<=', $academicYear . '-12-31');
        }
        
        $results = $query->get();
        
        $performance = [
            'total_exams' => count($results),
            'average_marks' => 0,
            'highest_marks' => 0,
            'lowest_marks' => 100,
            'grade_distribution' => [],
            'exam_type_performance' => []
        ];
        
        if (!empty($results)) {
            $totalMarks = 0;
            $marks = [];
            
            foreach ($results as $result) {
                $totalMarks += $result->marks_obtained;
                $marks[] = $result->marks_obtained;
                
                // Grade distribution
                $grade = $result->grade;
                if (!isset($performance['grade_distribution'][$grade])) {
                    $performance['grade_distribution'][$grade] = 0;
                }
                $performance['grade_distribution'][$grade]++;
                
                // Exam type performance
                $examType = $result->exam_type;
                if (!isset($performance['exam_type_performance'][$examType])) {
                    $performance['exam_type_performance'][$examType] = [
                        'total' => 0,
                        'marks' => []
                    ];
                }
                $performance['exam_type_performance'][$examType]['total']++;
                $performance['exam_type_performance'][$examType]['marks'][] = $result->marks_obtained;
            }
            
            $performance['average_marks'] = round($totalMarks / count($results), 2);
            $performance['highest_marks'] = max($marks);
            $performance['lowest_marks'] = min($marks);
            
            // Calculate averages for each exam type
            foreach ($performance['exam_type_performance'] as $examType => $data) {
                if (!empty($data['marks'])) {
                    $performance['exam_type_performance'][$examType]['average'] = round(array_sum($data['marks']) / count($data['marks']), 2);
                } else {
                    $performance['exam_type_performance'][$examType]['average'] = 0;
                }
                unset($performance['exam_type_performance'][$examType]['marks']);
            }
        }
        
        return $performance;
    }
    
    /**
     * Get subject trends over time
     * @param int $subjectId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getTrends($subjectId, $startDate = '', $endDate = '') {
        $query = $this->db->table('results')
            ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
            ->where('exams.subject_id', $subjectId)
            ->select('AVG(results.marks_obtained) as average, DATE(exams.exam_date) as exam_date, COUNT(*) as student_count')
            ->groupBy('DATE(exams.exam_date)');
        
        if (!empty($startDate)) {
            $query->andWhere('exams.exam_date', '>=', $startDate);
        }
        
        if (!empty($endDate)) {
            $query->andWhere('exams.exam_date', '<=', $endDate);
        }
        
        return $query->orderBy('exam_date')->get();
    }
    
    /**
     * Get subjects assigned to teacher
     * @param int $teacherId
     * @return array
     */
    public function getByTeacher($teacherId) {
        // This would typically come from a teacher-subject assignment table
        // For now, return all active subjects
        return $this->getAll();
    }
    
    /**
     * Get subject with recent results
     * @param int $subjectId
     * @param int $limit
     * @return array
     */
    public function getWithRecentResults($subjectId, $limit = 10) {
        $subject = $this->findById($subjectId);
        
        if ($subject) {
            $subject->recent_results = $this->db->table('results')
                ->leftJoin('exams', 'exams.id', '=', 'results.exam_id')
                ->leftJoin('students', 'students.id', '=', 'results.student_id')
                ->where('exams.subject_id', $subjectId)
                ->select('results.*, exams.title as exam_title, exams.exam_date, students.first_name, students.last_name, students.student_id')
                ->orderBy('exams.exam_date', 'DESC')
                ->limit($limit)
                ->get();
        }
        
        return $subject;
    }
}
