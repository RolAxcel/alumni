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
if (isset($_POST['update_general'])) {
    // Get form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $grad_year = filter_input(INPUT_POST, 'grad_year', FILTER_SANITIZE_NUMBER_INT);
    $timezone = filter_input(INPUT_POST, 'timezone', FILTER_SANITIZE_SPECIAL_CHARS);
    $language = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_SPECIAL_CHARS);
    $date_format = filter_input(INPUT_POST, 'date_format', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header('Location: settings.php');
        exit;
    }
    
    // Validate grad year (must be a year and not in the future)
    $current_year = date('Y');
    if (!is_numeric($grad_year) || $grad_year > $current_year) {
        $_SESSION['error'] = "Invalid graduation year";
        header('Location: settings.php');
        exit;
    }
    
    // Check if email already exists for another user
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $_SESSION['user_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email address is already in use by another account";
        header('Location: settings.php');
        exit;
    }
    
    // Update the user record
    $stmt = $conn->prepare("UPDATE users SET email = ?, grad_year = ?, timezone = ?, language = ?, date_format = ? WHERE id = ?");
    $stmt->bind_param("sisssi", $email, $grad_year, $timezone, $language, $date_format, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "General settings updated successfully";
    } else {
        $_SESSION['error'] = "Error updating settings: " . $conn->error;
    }
    
    $stmt->close();
    header('Location: settings.php');
    exit;
} else {
    // If form was not submitted, redirect back to settings page
    header('Location: settings.php');
    exit;
}
?>