<?php
// This file handles initialization tasks like creating the admin account
require_once 'db_connect.php';

// Function to check if admin account exists
function adminAccountExists($conn) {
    $query = "SELECT id FROM users WHERE username = 'admin' AND is_admin = 1";
    $result = $conn->query($query);
    return ($result && $result->num_rows > 0);
}

// Function to create admin account
function createAdminAccount($conn) {
    $admin_username = 'admin';
    $admin_password = 'admin123'; // In production, use a stronger password
    $admin_email = 'admin@alumnisystem.com';
    
    // Hash the password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Check if the users table exists, create it if not
    $check_table = "SHOW TABLES LIKE 'users'";
    $table_exists = $conn->query($check_table);
    
    if ($table_exists->num_rows == 0) {
        // Create users table
        $create_table = "CREATE TABLE users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            graduation_year INT(4),
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_admin TINYINT(1) DEFAULT 0
        )";
        
        if (!$conn->query($create_table)) {
            die("Error creating users table: " . $conn->error);
        }
    }
    
    // Insert admin user
    $insert_admin = "INSERT INTO users (username, email, password, is_admin) 
                    VALUES ('$admin_username', '$admin_email', '$hashed_password', 1)";
    
    if ($conn->query($insert_admin)) {
        return true;
    } else {
        return false;
    }
}

// Run initialization
if (!adminAccountExists($conn)) {
    createAdminAccount($conn);
}
?>