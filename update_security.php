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
if (isset($_POST['update_password'])) {
    // Get form data
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All password fields are required";
        header('Location: settings.php#security');
        exit;
    }
    
    // Check if new password and confirmation match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
        header('Location: settings.php#security');
        exit;
    }
    
    // Validate password strength
    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header('Location: settings.php#security');
        exit;
    }
    
    if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $_SESSION['error'] = "Password must include at least one uppercase letter, one lowercase letter, and one number";
        header('Location: settings.php#security');
        exit;
    }
    
    // Get current user's password from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = "Current password is incorrect";
            header('Location: settings.php#security');
            exit;
        }
        
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Password updated successfully";
            
            // Log the password change action for security audit
            $log_stmt = $conn->prepare("INSERT INTO user_activity_log (user_id, activity_type, ip_address) VALUES (?, 'password_change', ?)");
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_stmt->bind_param("is", $_SESSION['user_id'], $ip);
            $log_stmt->execute();
            $log_stmt->close();
        } else {
            $_SESSION['error'] = "Error updating password: " . $conn->error;
        }
        
        $update_stmt->close();
    } else {
        $_SESSION['error'] = "User not found";
    }
    
    $stmt->close();
    header('Location: settings.php#security');
    exit;
} 
// Handle two-factor authentication update via AJAX (for the toggle switch)
else if (isset($_POST['update_two_factor'])) {
    $enable = filter_input(INPUT_POST, 'enable', FILTER_VALIDATE_BOOLEAN);
    $method = filter_input(INPUT_POST, 'method', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Validate method if enabling
    if ($enable && !in_array($method, ['sms', 'app'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid authentication method']);
        exit;
    }
    
    // Update the database
    $stmt = $conn->prepare("UPDATE users SET two_factor = ?, two_factor_method = ? WHERE id = ?");
    $two_factor = $enable ? 1 : 0;
    $stmt->bind_param("isi", $two_factor, $method, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
        
        // Log the 2FA change for security audit
        $log_stmt = $conn->prepare("INSERT INTO user_activity_log (user_id, activity_type, ip_address) VALUES (?, ?, ?)");
        $activity = $enable ? 'two_factor_enabled' : 'two_factor_disabled';
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt->bind_param("iss", $_SESSION['user_id'], $activity, $ip);
        $log_stmt->execute();
        $log_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    
    $stmt->close();
    exit;
}
// Handle session termination
else if (isset($_POST['terminate_session'])) {
    $session_id = filter_input(INPUT_POST, 'session_id', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (!empty($session_id)) {
        $stmt = $conn->prepare("DELETE FROM user_sessions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("si", $session_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Session terminated successfully";
        } else {
            $_SESSION['error'] = "Error terminating session";
        }
        
        $stmt->close();
    }
    
    header('Location: settings.php#security');
    exit;
}
// Handle termination of all other sessions
else if (isset($_POST['terminate_all_sessions'])) {
    $current_session_id = session_id();
    
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_id != ?");
    $stmt->bind_param("is", $_SESSION['user_id'], $current_session_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "All other sessions terminated successfully";
    } else {
        $_SESSION['error'] = "Error terminating sessions";
    }
    
    $stmt->close();
    header('Location: settings.php#security');
    exit;
} else {
    // If form was not submitted properly, redirect back to settings page
    header('Location: settings.php');
    exit;
}
?>