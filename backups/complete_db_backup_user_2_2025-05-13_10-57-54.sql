-- Complete SQL Backup for user ID: 2
-- Generated on: 2025-05-13 10:57:54
-- This backup includes all tables in the database

-- Table structure for table `backup_history`
DROP TABLE IF EXISTS `backup_history`;
CREATE TABLE `backup_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `data_type` varchar(50) NOT NULL,
  `execution_time` datetime NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_data_type` (`user_id`,`data_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `backup_history` filtered by user_id = 2
INSERT INTO `backup_history` (`id`, `user_id`, `data_type`, `execution_time`, `status`, `file_path`, `file_size`, `error_message`, `created_at`) VALUES ('1', '2', 'user_data', '2025-05-13 16:54:22', 'success', 'user_2_backup_2025-05-13_10-54-22.sql', '2229', NULL, '2025-05-13 16:54:22');

-- Table structure for table `backup_schedules`
DROP TABLE IF EXISTS `backup_schedules`;
CREATE TABLE `backup_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `data_type` varchar(50) NOT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL,
  `last_backup` datetime DEFAULT NULL,
  `next_backup` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_data_type` (`user_id`,`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- No data found in table `backup_schedules` for user_id = 2

-- Table structure for table `comments`
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `parent_comment_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_comment_id` (`parent_comment_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No data found in table `comments` for user_id = 2

-- Table structure for table `events`
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme` varchar(255) NOT NULL,
  `batch_year` varchar(20) NOT NULL,
  `event_date` date NOT NULL,
  `venue` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expired` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `events` doesn't have a user_id column, skipping data export for security

-- Table structure for table `linked_accounts`
DROP TABLE IF EXISTS `linked_accounts`;
CREATE TABLE `linked_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `provider` varchar(100) NOT NULL,
  `provider_user_id` varchar(255) NOT NULL,
  `linked_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `linked_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- No data found in table `linked_accounts` for user_id = 2

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `email` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `graduation_year` year(4) DEFAULT NULL,
  `grad_year` int(4) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'America/New_York',
  `language` varchar(10) DEFAULT 'en',
  `date_format` varchar(20) DEFAULT 'MM/DD/YYYY',
  `two_factor` tinyint(1) DEFAULT 0,
  `two_factor_method` varchar(10) DEFAULT NULL,
  `notify_events` tinyint(1) DEFAULT 1,
  `notify_news` tinyint(1) DEFAULT 1,
  `notify_comments` tinyint(1) DEFAULT 1,
  `notify_jobs` tinyint(1) DEFAULT 1,
  `email_frequency` varchar(20) DEFAULT 'instant',
  `profile_visibility` varchar(20) DEFAULT 'alumni',
  `email_visibility` varchar(20) DEFAULT 'alumni',
  `phone_visibility` varchar(20) DEFAULT 'batch',
  `employment_visibility` varchar(20) DEFAULT 'alumni',
  `directory_listing` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users` for user with id = 2
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `created_at`, `is_admin`, `email`, `active`, `graduation_year`, `grad_year`, `timezone`, `language`, `date_format`, `two_factor`, `two_factor_method`, `notify_events`, `notify_news`, `notify_comments`, `notify_jobs`, `email_frequency`, `profile_visibility`, `email_visibility`, `phone_visibility`, `employment_visibility`, `directory_listing`) VALUES ('2', 'axcel', '***REMOVED***', 'yeah', '2025-05-13 09:12:14', '0', NULL, '1', NULL, NULL, 'America/New_York', 'en', 'MM/DD/YYYY', '0', NULL, '1', '1', '1', '1', 'instant', 'alumni', 'alumni', 'batch', 'alumni', '1');

