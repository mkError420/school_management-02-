-- School Management System Seed Data
-- Insert sample data for testing

USE `school_management`;

-- Insert Admin user
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('admin', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert sample subjects
INSERT INTO `subjects` (`name`, `code`, `description`, `credits`) VALUES
('Mathematics', 'MATH101', 'Mathematics for Grade 10', 4),
('English', 'ENG101', 'English Language and Literature', 3),
('Science', 'SCI101', 'General Science', 3),
('History', 'HIS101', 'World History', 2),
('Computer Science', 'CS101', 'Introduction to Computer Science', 3),
('Physical Education', 'PE101', 'Physical Education and Sports', 1);

-- Insert sample classes
INSERT INTO `classes` (`name`, `grade_level`, `description`) VALUES
('Grade 10', 10, 'Tenth Grade Class'),
('Grade 9', 9, 'Ninth Grade Class'),
('Grade 8', 8, 'Eighth Grade Class');

-- Insert sample sections
INSERT INTO `sections` (`class_id`, `name`, `room_number`, `max_students`) VALUES
(1, 'A', '101', 35),
(1, 'B', '102', 35),
(2, 'A', '201', 30),
(2, 'B', '202', 30),
(3, 'A', '301', 32);

-- Insert sample teachers
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('teacher1', 'john.smith@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('teacher2', 'mary.johnson@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('teacher3', 'robert.wilson@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active');

INSERT INTO `teachers` (`user_id`, `employee_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `phone`, `qualification`, `specialization`, `experience_years`, `hire_date`) VALUES
(2, 'T001', 'John', 'Smith', 'male', '1985-05-15', '123-456-7890', 'M.Ed Mathematics', 'Mathematics', 10, '2014-08-01'),
(3, 'T002', 'Mary', 'Johnson', 'female', '1988-08-22', '123-456-7891', 'M.A. English', 'English Literature', 8, '2016-08-01'),
(4, 'T003', 'Robert', 'Wilson', 'male', '1982-03-10', '123-456-7892', 'M.Sc. Physics', 'Science', 12, '2012-08-01');

-- Assign teachers to sections
UPDATE `sections` SET `teacher_id` = 1 WHERE `id` = 1;
UPDATE `sections` SET `teacher_id` = 2 WHERE `id` = 2;
UPDATE `sections` SET `teacher_id` = 3 WHERE `id` = 3;

-- Insert sample students
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('student1', 'alice.brown@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student2', 'bob.davis@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student3', 'charlie.miller@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student4', 'diana.wilson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student5', 'edward.moore@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active');

INSERT INTO `students` (`user_id`, `student_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `phone`, `address`, `parent_name`, `parent_phone`, `parent_email`, `enrollment_date`) VALUES
(5, 'STU001', 'Alice', 'Brown', 'female', '2008-03-15', '555-0101', '123 Main St, City', 'James Brown', '555-0102', 'james.brown@email.com', '2023-08-01'),
(6, 'STU002', 'Bob', 'Davis', 'male', '2008-07-22', '555-0103', '456 Oak Ave, City', 'Robert Davis', '555-0104', 'robert.davis@email.com', '2023-08-01'),
(7, 'STU003', 'Charlie', 'Miller', 'male', '2008-11-30', '555-0105', '789 Pine Rd, City', 'Susan Miller', '555-0106', 'susan.miller@email.com', '2023-08-01'),
(8, 'STU004', 'Diana', 'Wilson', 'female', '2008-01-18', '555-0107', '321 Elm St, City', 'Michael Wilson', '555-0108', 'michael.wilson@email.com', '2023-08-01'),
(9, 'STU005', 'Edward', 'Moore', 'male', '2008-09-25', '555-0109', '654 Maple Dr, City', 'Jennifer Moore', '555-0110', 'jennifer.moore@email.com', '2023-08-01');

-- Enroll students in sections
INSERT INTO `enrollments` (`student_id`, `section_id`, `enrollment_date`, `academic_year`) VALUES
(1, 1, '2023-08-01', '2023-2024'),
(2, 1, '2023-08-01', '2023-2024'),
(3, 2, '2023-08-01', '2023-2024'),
(4, 2, '2023-08-01', '2023-2024'),
(5, 3, '2023-08-01', '2023-2024');

-- Insert sample exams
INSERT INTO `exams` (`title`, `description`, `exam_type`, `class_id`, `subject_id`, `total_marks`, `exam_date`, `duration_minutes`, `status`, `created_by`) VALUES
('Mathematics Midterm', 'Midterm examination for Mathematics', 'midterm', 1, 1, 100.00, '2024-01-15', 90, 'completed', 1),
('English Quiz 1', 'First quiz for English', 'quiz', 1, 2, 50.00, '2024-01-20', 45, 'completed', 2),
('Science Final', 'Final examination for Science', 'final', 1, 3, 100.00, '2024-02-10', 120, 'published', 3);

-- Insert sample attendance records
INSERT INTO `attendance` (`student_id`, `section_id`, `date`, `status`, `marked_by`) VALUES
(1, 1, '2024-01-01', 'present', 1),
(2, 1, '2024-01-01', 'present', 1),
(1, 1, '2024-01-02', 'present', 1),
(2, 1, '2024-01-02', 'absent', 1),
(3, 2, '2024-01-01', 'present', 2),
(4, 2, '2024-01-01', 'late', 2),
(3, 2, '2024-01-02', 'present', 2),
(4, 2, '2024-01-02', 'present', 2);

-- Insert sample results
INSERT INTO `results` (`student_id`, `exam_id`, `marks_obtained`, `grade`, `created_by`) VALUES
(1, 1, 85.00, 'A', 1),
(2, 1, 72.00, 'B', 1),
(1, 2, 45.00, 'A', 2),
(2, 2, 38.00, 'B', 2),
(3, 1, 78.00, 'B', 1),
(4, 1, 91.00, 'A+', 1);

-- Note: All passwords are 'password123' hashed with PASSWORD_DEFAULT
-- Admin credentials: admin / password123
-- Teacher credentials: teacher1 / password123
-- Student credentials: student1 / password123
