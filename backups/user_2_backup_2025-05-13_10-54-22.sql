-- SQL Backup for user ID: 2
-- Generated on: 2025-05-13 10:54:22

-- Table structure for table `linked_accounts`
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

-- Table structure for table `users`
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

-- Data for table `users` (sensitive data removed)
INSERT INTO `users` VALUES
('2', 'axcel', '***REMOVED***', 'yeah', '2025-05-13 09:12:14', '0', NULL, '1', NULL, NULL, 'America/New_York', 'en', 'MM/DD/YYYY', '0', NULL, '1', '1', '1', '1', 'instant', 'alumni', 'alumni', 'batch', 'alumni', '1');

-- No linked accounts found, showing basic user information instead.
