-- School Management System Database Schema
-- Created for PHP 8+ with MySQL 5.7+

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `school_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `school_management`;

-- Drop tables if they exist (for fresh installation)
SET FOREIGN_KEY_CHECKS = 0;
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
SET FOREIGN_KEY_CHECKS = 1;

-- Users table - Central authentication table
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

-- Classes table
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

-- Sections table
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

-- Subjects table
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

-- Students table
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

-- Teachers table
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

-- Enrollments table - Student-class relationships
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

-- Exams table
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

-- Attendance table
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

-- Results table
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

-- Create indexes for better performance
CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_students_name ON students(first_name, last_name);
CREATE INDEX idx_teachers_name ON teachers(first_name, last_name);
CREATE INDEX idx_attendance_date_status ON attendance(date, status);
CREATE INDEX idx_results_marks ON results(marks_obtained);
CREATE INDEX idx_enrollments_student ON enrollments(student_id, academic_year);
