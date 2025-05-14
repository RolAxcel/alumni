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
if (isset($_POST['update_privacy'])) {
    // Sanitize form data
    $profile_visibility = filter_input(INPUT_POST, 'profile_visibility', FILTER_SANITIZE_SPECIAL_CHARS);
    $email_visibility = filter_input(INPUT_POST, 'email_visibility', FILTER_SANITIZE_SPECIAL_CHARS);
    $phone_visibility = filter_input(INPUT_POST, 'phone_visibility', FILTER_SANITIZE_SPECIAL_CHARS);
    $employment_visibility = filter_input(INPUT_POST, 'employment_visibility', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Initialize directory_listing with default (0 = off)
    $directory_listing = 0;
    
    // Check if directory_listing checkbox is checked
    if (isset($_POST['directory_listing'])) {
        $directory_listing = 1; // Checkbox is checked
    }
    
    // Validate visibility settings
    $valid_visibility_options = ['public', 'alumni', 'batch', 'none'];
    
    if (!in_array($profile_visibility, $valid_visibility_options) || 
        !in_array($email_visibility, $valid_visibility_options) || 
        !in_array($phone_visibility, $valid_visibility_options) || 
        !in_array($employment_visibility, $valid_visibility_options)) {
        
        $_SESSION['error'] = "Invalid privacy settings selected";
        header('Location: settings.php#privacy');
        exit;
    }
    
    // Update privacy settings in the database
    $stmt = $conn->prepare("UPDATE users SET 
                           profile_visibility = ?, 
                           email_visibility = ?, 
                           phone_visibility = ?, 
                           employment_visibility = ?, 
                           directory_listing = ? 
                           WHERE id = ?");
    
    $stmt->bind_param("ssssis", 
                     $profile_visibility, 
                     $email_visibility, 
                     $phone_visibility, 
                     $employment_visibility, 
                     $directory_listing, 
                     $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Privacy settings updated successfully";
    } else {
        $_SESSION['error'] = "Error updating privacy settings: " . $conn->error;
    }
    
    $stmt->close();
    
    // Redirect back to the privacy tab
    header('Location: settings.php#privacy');
    exit;
} else {
    // If form was not submitted properly, redirect back to settings page
    header('Location: settings.php');
    exit;
}
?>