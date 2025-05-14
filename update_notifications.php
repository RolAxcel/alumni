<?php
session_start();

// Include database connection
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if form is submitted
if (isset($_POST['update_notifications'])) {
    // Initialize variables with defaults (0 = off)
    $notify_events = 0;
    $notify_news = 0;
    $notify_comments = 0;
    $notify_jobs = 0;
    $email_frequency = 'instant'; // Default frequency
    
    // Get and sanitize form data
    if (isset($_POST['notify_events'])) {
        $notify_events = 1; // Checkbox is checked
    }
    
    if (isset($_POST['notify_news'])) {
        $notify_news = 1; // Checkbox is checked
    }
    
    if (isset($_POST['notify_comments'])) {
        $notify_comments = 1; // Checkbox is checked
    }
    
    if (isset($_POST['notify_jobs'])) {
        $notify_jobs = 1; // Checkbox is checked
    }
    
    // Validate email frequency
    if (isset($_POST['email_frequency']) && in_array($_POST['email_frequency'], ['instant', 'daily', 'weekly'])) {
        $email_frequency = $_POST['email_frequency'];
    }
    
    // Update notification settings in the database
    $stmt = $conn->prepare("UPDATE users SET 
                           notify_events = ?, 
                           notify_news = ?, 
                           notify_comments = ?, 
                           notify_jobs = ?, 
                           email_frequency = ? 
                           WHERE id = ?");
    
    $stmt->bind_param("iiiisi", 
                     $notify_events, 
                     $notify_news, 
                     $notify_comments, 
                     $notify_jobs, 
                     $email_frequency, 
                     $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Notification settings updated successfully";
    } else {
        $_SESSION['error'] = "Error updating notification settings: " . $conn->error;
    }
    
    $stmt->close();
    
    // Redirect back to the notifications tab
    header('Location: settings.php#notifications');
    exit;
} else {
    // If form was not submitted properly, redirect back to settings page
    header('Location: settings.php');
    exit;
}
?>