<?php
// add_comment.php - Handles comment submissions for events

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER']));
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['event_id']) || empty($_POST['comment'])) {
        $_SESSION['error_message'] = "Missing required fields";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Get form data
    $event_id = (int)$_POST['event_id'];
    $user_id = (int)$_SESSION['user_id'];
    $comment = trim($_POST['comment']);
    
    // Additional input validation
    if (strlen($comment) < 1 || strlen($comment) > 500) {
        $_SESSION['error_message'] = "Comment must be between 1 and 500 characters";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Connect to database
    require_once 'db_connect.php';
    
    // Prepare and execute the query
    $stmt = $conn->prepare("INSERT INTO comments (event_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $event_id, $user_id, $comment);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Comment added successfully";
    } else {
        $_SESSION['error_message'] = "Error adding comment: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    
    // Redirect back to the referring page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    // If not a POST request, redirect to home
    header('Location: index.php');
    exit;
}
?>