<?php
// This file should be created to handle downloading backup files 
// from the backup history system

// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Get user ID and requested file
$user_id = $_SESSION['user_id'];
$file_path = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($file_path)) {
    die('No file specified.');
}

// Verify this file belongs to the user
$stmt = $conn->prepare("SELECT * FROM backup_history 
                        WHERE user_id = ? AND file_path = ? AND status = 'success'");
$stmt->bind_param("is", $user_id, $file_path);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('File not found or unauthorized access.');
}

// Get file information
$file_info = $result->fetch_assoc();

// Make sure the file exists on the server
$full_path = 'backups/' . $file_path;
if (!file_exists($full_path)) {
    die('Backup file no longer exists on the server.');
}

// Set headers and output file contents
$filename = basename($file_path);
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file and exit
readfile($full_path);
exit;
?>