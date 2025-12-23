-- Database Schema for Vorbereitung B1/B2 Quiz Platform
-- PHP-MySQL MVC Backend

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `quiz_results`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;

-- =====================================================
-- Users Table
-- =====================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `surname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `is_active` TINYINT(1) DEFAULT 0 COMMENT 'Requires admin approval',
  `access_b1` TINYINT(1) DEFAULT 0 COMMENT 'Access to B1 quizzes',
  `access_b2` TINYINT(1) DEFAULT 0 COMMENT 'Access to B2 quizzes',
  `device_limit` INT DEFAULT 1 COMMENT 'Maximum number of devices',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Sessions Table
-- =====================================================
CREATE TABLE `sessions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `session_token` VARCHAR(255) NOT NULL UNIQUE,
  `device_fingerprint` VARCHAR(255) NOT NULL COMMENT 'Hash of IP + User Agent',
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT,
  `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_session_token` (`session_token`),
  INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Quiz Results Table
-- =====================================================
CREATE TABLE `quiz_results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `quiz_id` VARCHAR(50) NOT NULL COMMENT 'Quiz file ID (e.g., 1, 10, 100)',
  `quiz_level` ENUM('B1', 'B2') NOT NULL,
  `score` INT NOT NULL COMMENT 'Number of correct answers',
  `total_questions` INT NOT NULL COMMENT 'Total questions in quiz',
  `answers` JSON DEFAULT NULL COMMENT 'User answers JSON',
  `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_quiz_level` (`quiz_level`),
  INDEX `idx_quiz_id` (`quiz_id`),
  INDEX `idx_completed_at` (`completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Settings Table
-- =====================================================
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Initial Settings Data
-- =====================================================
INSERT INTO `settings` (`key`, `value`) VALUES
  ('maintenance_mode', '0'),
  ('logout_timer', '15');
