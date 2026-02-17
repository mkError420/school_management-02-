-- School Management System Database Schema (Fixed Order)
-- Created for PHP 8+ with MySQL 5.7+

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `school_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `school_management`;

-- Set foreign key checks to 0 for creation
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they exist (for fresh installation)
DROP TABLE IF EXISTS `results`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `enrollments`;
DROP TABLE IF EXISTS `exam_subjects`;
DROP TABLE IF EXISTS `exams`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `sections`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `teachers`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;

-- Users table - Central authentication table (NO DEPENDENCIES)
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('admin','teacher','student') NOT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `last_login` datetime DEFAULT NULL,
    `remember_token` varchar(255) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`),
    KEY `role` (`role`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Classes table (NO DEPENDENCIES)
CREATE TABLE `classes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `grade_level` int(11) NOT NULL,
    `description` text DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `grade_level` (`grade_level`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects table (NO DEPENDENCIES)
CREATE TABLE `subjects` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `code` varchar(20) NOT NULL,
    `description` text DEFAULT NULL,
    `credits` int(11) DEFAULT 1,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    KEY `name` (`name`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Students table (DEPENDS ON users)
CREATE TABLE `students` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `student_id` varchar(20) NOT NULL,
    `first_name` varchar(50) NOT NULL,
    `last_name` varchar(50) NOT NULL,
    `gender` enum('male','female','other') NOT NULL,
    `date_of_birth` date NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `parent_name` varchar(100) DEFAULT NULL,
    `parent_phone` varchar(20) DEFAULT NULL,
    `parent_email` varchar(100) DEFAULT NULL,
    `enrollment_date` date DEFAULT NULL,
    `status` enum('active','inactive','graduated') DEFAULT 'active',
    `profile_picture` varchar(255) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id` (`user_id`),
    UNIQUE KEY `student_id` (`student_id`),
    KEY `first_name` (`first_name`),
    KEY `last_name` (`last_name`),
    KEY `status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teachers table (DEPENDS ON users)
CREATE TABLE `teachers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `employee_id` varchar(20) NOT NULL,
    `first_name` varchar(50) NOT NULL,
    `last_name` varchar(50) NOT NULL,
    `gender` enum('male','female','other') NOT NULL,
    `date_of_birth` date NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `qualification` varchar(100) DEFAULT NULL,
    `specialization` varchar(100) DEFAULT NULL,
    `experience_years` int(11) DEFAULT 0,
    `hire_date` date DEFAULT NULL,
    `salary` decimal(10,2) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `profile_picture` varchar(255) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id` (`user_id`),
    UNIQUE KEY `employee_id` (`employee_id`),
    KEY `first_name` (`first_name`),
    KEY `last_name` (`last_name`),
    KEY `status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sections table (DEPENDS ON classes, teachers)
CREATE TABLE `sections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `class_id` int(11) NOT NULL,
    `name` varchar(20) NOT NULL,
    `teacher_id` int(11) DEFAULT NULL,
    `room_number` varchar(20) DEFAULT NULL,
    `max_students` int(11) DEFAULT 40,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `class_id` (`class_id`),
    KEY `teacher_id` (`teacher_id`),
    KEY `status` (`status`),
    FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enrollments table (DEPENDS ON students, sections)
CREATE TABLE `enrollments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `student_id` int(11) NOT NULL,
    `section_id` int(11) NOT NULL,
    `enrollment_date` date NOT NULL,
    `status` enum('active','transferred','completed') DEFAULT 'active',
    `academic_year` varchar(20) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `student_section_year` (`student_id`,`section_id`,`academic_year`),
    KEY `student_id` (`student_id`),
    KEY `section_id` (`section_id`),
    KEY `academic_year` (`academic_year`),
    KEY `status` (`status`),
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exams table (DEPENDS ON classes, subjects)
CREATE TABLE `exams` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `exam_type` enum('midterm','final','quiz','assignment') NOT NULL,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `total_marks` decimal(5,2) NOT NULL DEFAULT 100.00,
    `exam_date` date NOT NULL,
    `duration_minutes` int(11) DEFAULT 60,
    `status` enum('draft','published','completed') DEFAULT 'draft',
    `created_by` int(11) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `class_id` (`class_id`),
    KEY `subject_id` (`subject_id`),
    KEY `exam_date` (`exam_date`),
    KEY `status` (`status`),
    KEY `created_by` (`created_by`),
    FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance table (DEPENDS ON students, sections, subjects)
CREATE TABLE `attendance` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `student_id` int(11) NOT NULL,
    `section_id` int(11) NOT NULL,
    `subject_id` int(11) DEFAULT NULL,
    `date` date NOT NULL,
    `status` enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
    `remarks` text DEFAULT NULL,
    `marked_by` int(11) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `student_section_date` (`student_id`,`section_id`,`date`),
    KEY `student_id` (`student_id`),
    KEY `section_id` (`section_id`),
    KEY `subject_id` (`subject_id`),
    KEY `date` (`date`),
    KEY `status` (`status`),
    KEY `marked_by` (`marked_by`),
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`marked_by`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Results table (DEPENDS ON students, exams)
CREATE TABLE `results` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `student_id` int(11) NOT NULL,
    `exam_id` int(11) NOT NULL,
    `marks_obtained` decimal(5,2) NOT NULL,
    `grade` varchar(2) DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `student_exam` (`student_id`,`exam_id`),
    KEY `student_id` (`student_id`),
    KEY `exam_id` (`exam_id`),
    KEY `grade` (`grade`),
    KEY `created_by` (`created_by`),
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Create indexes for better performance
CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_students_name ON students(first_name, last_name);
CREATE INDEX idx_teachers_name ON teachers(first_name, last_name);
CREATE INDEX idx_attendance_date_status ON attendance(date, status);
CREATE INDEX idx_results_marks ON results(marks_obtained);
CREATE INDEX idx_enrollments_student ON enrollments(student_id, academic_year);

-- Insert sample data
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('admin', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

INSERT INTO `classes` (`name`, `grade_level`, `description`) VALUES
('Grade 10', 10, 'Tenth Grade Class'),
('Grade 9', 9, 'Ninth Grade Class'),
('Grade 8', 8, 'Eighth Grade Class');

INSERT INTO `subjects` (`name`, `code`, `description`, `credits`) VALUES
('Mathematics', 'MATH101', 'Mathematics for Grade 10', 4),
('English', 'ENG101', 'English Language and Literature', 3),
('Science', 'SCI101', 'General Science', 3),
('History', 'HIS101', 'World History', 2),
('Computer Science', 'CS101', 'Introduction to Computer Science', 3),
('Physical Education', 'PE101', 'Physical Education and Sports', 1);

INSERT INTO `sections` (`class_id`, `name`, `room_number`, `max_students`) VALUES
(1, 'A', '101', 35),
(1, 'B', '102', 35),
(2, 'A', '201', 30),
(2, 'B', '202', 30),
(3, 'A', '301', 32);

-- Insert sample users for teachers and students
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('teacher1', 'john.smith@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('teacher2', 'mary.johnson@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('teacher3', 'robert.wilson@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('student1', 'alice.brown@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student2', 'bob.davis@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student3', 'charlie.miller@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student4', 'diana.wilson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student5', 'edward.moore@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active');

INSERT INTO `teachers` (`user_id`, `employee_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `phone`, `qualification`, `specialization`, `experience_years`, `hire_date`) VALUES
(2, 'T001', 'John', 'Smith', 'male', '1985-05-15', '123-456-7890', 'M.Ed Mathematics', 'Mathematics', 10, '2014-08-01'),
(3, 'T002', 'Mary', 'Johnson', 'female', '1988-08-22', '123-456-7891', 'M.A. English', 'English Literature', 8, '2016-08-01'),
(4, 'T003', 'Robert', 'Wilson', 'male', '1982-03-10', '123-456-7892', 'M.Sc. Physics', 'Science', 12, '2012-08-01');

INSERT INTO `students` (`user_id`, `student_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `phone`, `address`, `parent_name`, `parent_phone`, `parent_email`, `enrollment_date`) VALUES
(5, 'STU001', 'Alice', 'Brown', 'female', '2008-03-15', '555-0101', '123 Main St, City', 'James Brown', '555-0102', 'james.brown@email.com', '2023-08-01'),
(6, 'STU002', 'Bob', 'Davis', 'male', '2008-07-22', '555-0103', '456 Oak Ave, City', 'Robert Davis', '555-0104', 'robert.davis@email.com', '2023-08-01'),
(7, 'STU003', 'Charlie', 'Miller', 'male', '2008-11-30', '555-0105', '789 Pine Rd, City', 'Susan Miller', '555-0106', 'susan.miller@email.com', '2023-08-01'),
(8, 'STU004', 'Diana', 'Wilson', 'female', '2008-01-18', '555-0107', '321 Elm St, City', 'Michael Wilson', '555-0108', 'michael.wilson@email.com', '2023-08-01'),
(9, 'STU005', 'Edward', 'Moore', 'male', '2008-09-25', '555-0109', '654 Maple Dr, City', 'Jennifer Moore', '555-0110', 'jennifer.moore@email.com', '2023-08-01');

-- Assign teachers to sections
UPDATE `sections` SET `teacher_id` = 1 WHERE `id` = 1;
UPDATE `sections` SET `teacher_id` = 2 WHERE `id` = 2;
UPDATE `sections` SET `teacher_id` = 3 WHERE `id` = 3;

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
