<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "alumni";

// First connect without selecting a database to check/create it
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Close the connection
$conn->close();

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure proper handling of all characters
$conn->set_charset("utf8mb4");

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `users` (
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
)";

if (!$conn->query($sql)) {
    die("Error creating users table: " . $conn->error);
}

// Create linked_accounts table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `linked_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `provider` varchar(100) NOT NULL,
  `provider_user_id` varchar(255) NOT NULL,
  `linked_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `linked_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    die("Error creating linked_accounts table: " . $conn->error);
}

// Create events table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme` varchar(255) NOT NULL,
  `batch_year` varchar(20) NOT NULL,
  `event_date` date NOT NULL,
  `venue` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expired` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
)";

if (!$conn->query($sql)) {
    die("Error creating events table: " . $conn->error);
}

// Create comments table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `comments` (
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
)";

if (!$conn->query($sql)) {
    die("Error creating comments table: " . $conn->error);
}

// Create backup_schedules table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `backup_schedules` (
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
)";

if (!$conn->query($sql)) {
    die("Error creating backup_schedules table: " . $conn->error);
}

// Create backup_history table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `backup_history` (
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
)";

if (!$conn->query($sql)) {
    die("Error creating backup_history table: " . $conn->error);
}

// Connection successfully established with all required tables
// echo "Database and tables created/verified successfully";
?>