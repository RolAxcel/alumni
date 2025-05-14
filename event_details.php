<?php
session_start();
require_once 'db_connect.php';

// Check if event ID is provided
if (!isset($_GET['id'])) {
    header("Location: public_homepage.php");
    exit();
}

$event_id = (int)$_GET['id'];

// Get event details
$event_query = "SELECT * FROM events WHERE id = $event_id";
$event_result = $conn->query($event_query);

if ($event_result->num_rows == 0) {
    // Event not found
    header("Location: public_homepage.php");
    exit();
}

$event = $event_result->fetch_assoc();

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['toast_message'] = 'Please log in to comment.';
        $_SESSION['toast_type'] = 'warning';
        header("Location: login.php?redirect=event_details.php?id=$event_id");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $content = $conn->real_escape_string($_POST['content']);
    
    $insert_query = "INSERT INTO comments (event_id, user_id, content, is_read) 
                     VALUES ($event_id, $user_id, '$content', 0)";
    
    if ($conn->query($insert_query)) {
        $_SESSION['toast_message'] = 'Comment posted successfully!';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_message'] = 'Error posting comment: ' . $conn->error;
        $_SESSION['toast_type'] = 'danger';
    }
    
    // Redirect to prevent form resubmission
    header("Location: event_details.php?id=$event_id");
    exit();
}

// Get comments for this event
$comments_query = "SELECT c.*, u.username 
                  FROM comments c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.event_id = $event_id 
                  AND c.parent_comment_id IS NULL
                  ORDER BY c.created_at DESC";
$comments_result = $conn->query($comments_query);

// Initialize toast message variables
$toast_message = '';
$toast_type = '';

// Check for any messages in the session
if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])) {
    $toast_message = $_SESSION['toast_message'];
    $toast_type = $_SESSION['toast_type'];
    // Clear the message after displaying it
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_type']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['theme']); ?> - Alumni Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Include your public header/navbar here -->
    
    <div class="container mt-4">
        <!-- Toast container for notifications -->
        <div class="toast-