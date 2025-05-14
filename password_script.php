<?php
// Direct script to update the admin password in the database
require_once 'db_connect.php';

// Hash the default password
$default_password = 'admin123';
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Update the admin user password
$query = "UPDATE users SET password = '$hashed_password' WHERE username = 'admin'";

if ($conn->query($query)) {
    echo "Password reset successful! You can now login with:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<a href='login.php'>Go to Login Page</a>";
} else {
    echo "Error resetting password: " . $conn->error;
}
?>