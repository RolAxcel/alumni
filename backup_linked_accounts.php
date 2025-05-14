<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Function to generate SQL backup of all database tables
function generateLinkedAccountsBackup($conn, $user_id) {
    // Header information
    $sql_backup = "-- Complete SQL Backup for user ID: $user_id\n";
    $sql_backup .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $sql_backup .= "-- This backup includes all tables in the database\n\n";
    
    // Get a list of all tables in the database
    $tables_result = $conn->query("SHOW TABLES");
    $tables = [];
    
    while ($table_row = $tables_result->fetch_row()) {
        $tables[] = $table_row[0];
    }
    
    // For each table, add structure and data related to this user
    foreach ($tables as $table) {
        try {
            // Get table structure first
            $structure_result = $conn->query("SHOW CREATE TABLE `$table`");
            if ($structure_row = $structure_result->fetch_assoc()) {
                $table_structure = $structure_row['Create Table'];
                $sql_backup .= "-- Table structure for table `$table`\n";
                $sql_backup .= "DROP TABLE IF EXISTS `$table`;\n";
                $sql_backup .= "$table_structure;\n\n";
            }
            
            // Check if this table has a user_id column to filter by user
            $columns_result = $conn->query("DESCRIBE `$table`");
            $has_user_id = false;
            
            while ($column = $columns_result->fetch_assoc()) {
                if ($column['Field'] == 'user_id') {
                    $has_user_id = true;
                    break;
                }
            }
            
            // Get data for this user if possible, otherwise skip data
            if ($has_user_id) {
                $stmt = $conn->prepare("SELECT * FROM `$table` WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $sql_backup .= "-- Data for table `$table` filtered by user_id = $user_id\n";
                    
                    while ($row = $result->fetch_assoc()) {
                        // For user table, mask sensitive data
                        if ($table == 'users') {
                            if (isset($row['password'])) $row['password'] = '***REMOVED***';
                            if (isset($row['password_hash'])) $row['password_hash'] = '***REMOVED***';
                        }
                        
                        $columns = implode("`, `", array_keys($row));
                        $sql_backup .= "INSERT INTO `$table` (`$columns`) VALUES (";
                        
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = "NULL";
                            } else {
                                $values[] = "'" . $conn->real_escape_string($value) . "'";
                            }
                        }
                        
                        $sql_backup .= implode(", ", $values);
                        $sql_backup .= ");\n";
                    }
                    
                    $sql_backup .= "\n";
                } else {
                    $sql_backup .= "-- No data found in table `$table` for user_id = $user_id\n\n";
                }
            } else if ($table != 'users') {
                // For tables without user_id, note that we're skipping them
                $sql_backup .= "-- Table `$table` doesn't have a user_id column, skipping data export for security\n\n";
            } else {
                // Special case for users table if it doesn't have user_id column
                $stmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $sql_backup .= "-- Data for table `$table` for user with id = $user_id\n";
                    
                    while ($row = $result->fetch_assoc()) {
                        // Mask sensitive data
                        if (isset($row['password'])) $row['password'] = '***REMOVED***';
                        if (isset($row['password_hash'])) $row['password_hash'] = '***REMOVED***';
                        
                        $columns = implode("`, `", array_keys($row));
                        $sql_backup .= "INSERT INTO `$table` (`$columns`) VALUES (";
                        
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = "NULL";
                            } else {
                                $values[] = "'" . $conn->real_escape_string($value) . "'";
                            }
                        }
                        
                        $sql_backup .= implode(", ", $values);
                        $sql_backup .= ");\n";
                    }
                    
                    $sql_backup .= "\n";
                }
            }
        } catch (Exception $e) {
            $sql_backup .= "-- Error processing table `$table`: " . $e->getMessage() . "\n\n";
            continue;
        }
    }
    
    // Save SQL backup to a file in the backups directory
    $backup_dir = 'backups';
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $filename = 'complete_db_backup_user_' . $user_id . '_' . date('Y-m-d_H-i-s') . '.sql';
    $file_path = $backup_dir . '/' . $filename;
    
    if (file_put_contents($file_path, $sql_backup)) {
        // Record this backup in the history
        try {
            $table_check = $conn->query("SHOW TABLES LIKE 'backup_history'");
            if ($table_check->num_rows > 0) {
                $stmt = $conn->prepare("INSERT INTO backup_history 
                                    (user_id, data_type, execution_time, status, file_path, file_size) 
                                    VALUES (?, 'complete_db', NOW(), 'success', ?, ?)");
                $file_size = filesize($file_path);
                $stmt->bind_param("isi", $user_id, $filename, $file_size);
                $stmt->execute();
            }
        } catch (Exception $e) {
            // Ignore errors with recording history
        }
        
        // Set headers for download
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output the file
        readfile($file_path);
        exit;
    } else {
        throw new Exception("Failed to write backup file");
    }
}

// Function to schedule backup
function scheduleBackup($conn, $user_id, $frequency) {
    // First, check if the backup_schedules table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'backup_schedules'");
    if ($table_check->num_rows == 0) {
        // Table doesn't exist, create it
        $conn->query("CREATE TABLE IF NOT EXISTS `backup_schedules` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `data_type` varchar(50) NOT NULL,
            `frequency` enum('daily','weekly','monthly') NOT NULL,
            `last_backup` datetime DEFAULT NULL,
            `next_backup` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_data_type` (`user_id`,`data_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
    
    // Check if backup_history table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'backup_history'");
    if ($table_check->num_rows == 0) {
        // Table doesn't exist, create it
        $conn->query("CREATE TABLE IF NOT EXISTS `backup_history` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `data_type` varchar(50) NOT NULL,
            `execution_time` datetime NOT NULL,
            `status` enum('success','failed') NOT NULL,
            `file_path` varchar(255) DEFAULT NULL,
            `file_size` int(11) DEFAULT NULL,
            `error_message` text,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_data_type` (`user_id`,`data_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // Update or insert backup schedule
    $stmt = $conn->prepare("INSERT INTO backup_schedules (user_id, data_type, frequency, last_backup, next_backup) 
                          VALUES (?, 'user_data', ?, NOW(), ?) 
                          ON DUPLICATE KEY UPDATE frequency = ?, next_backup = ?");
    
    $next_backup = null;
    $current_date = new DateTime();
    
    switch($frequency) {
        case 'daily':
            $next_backup = $current_date->modify('+1 day')->format('Y-m-d H:i:s');
            break;
        case 'weekly':
            $next_backup = $current_date->modify('+1 week')->format('Y-m-d H:i:s');
            break;
        case 'monthly':
            $next_backup = $current_date->modify('+1 month')->format('Y-m-d H:i:s');
            break;
        default:
            $next_backup = null;
    }
    
    $stmt->bind_param("issss", $user_id, $frequency, $next_backup, $frequency, $next_backup);
    $stmt->execute();
    
    return [
        'success' => true,
        'message' => 'Backup schedule updated successfully',
        'frequency' => $frequency,
        'next_backup' => $next_backup
    ];
}

// Handle request
$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check action
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'generate_backup':
                try {
                    generateLinkedAccountsBackup($conn, $user_id);
                } catch (Exception $e) {
                    $response = ['success' => false, 'message' => 'Error generating backup: ' . $e->getMessage()];
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;
                    } else {
                        $_SESSION['backup_error'] = $response['message'];
                        header('Location: settings.php#linkedaccounts');
                        exit;
                    }
                }
                break;
                
            case 'schedule_backup':
                if (isset($_POST['frequency'])) {
                    $frequency = $_POST['frequency'];
                    try {
                        $response = scheduleBackup($conn, $user_id, $frequency);
                    } catch (Exception $e) {
                        $response = ['success' => false, 'message' => 'Error scheduling backup: ' . $e->getMessage()];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Backup frequency not specified'];
                }
                break;
                
            case 'delete_schedule':
                try {
                    $table_check = $conn->query("SHOW TABLES LIKE 'backup_schedules'");
                    if ($table_check->num_rows > 0) {
                        $stmt = $conn->prepare("DELETE FROM backup_schedules WHERE user_id = ? AND data_type = 'user_data'");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                    }
                    $response = ['success' => true, 'message' => 'Backup schedule deleted successfully'];
                } catch (Exception $e) {
                    $response = ['success' => false, 'message' => 'Error deleting schedule: ' . $e->getMessage()];
                }
                break;
        }
    }
}

// Return JSON response for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Redirect back to settings page for regular requests
header('Location: settings.php#linkedaccounts');
exit;
?>