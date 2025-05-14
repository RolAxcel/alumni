<?php
require_once 'db_connect.php';

// Check if the parent_comment_id column already exists
$check_column = "SHOW COLUMNS FROM comments LIKE 'parent_comment_id'";
$column_exists = $conn->query($check_column);

if ($column_exists->num_rows == 0) {
    // Add parent_comment_id column
    $alter_table = "ALTER TABLE comments 
                   ADD COLUMN parent_comment_id INT NULL,
                   ADD CONSTRAINT fk_parent_comment 
                   FOREIGN KEY (parent_comment_id) 
                   REFERENCES comments(id) 
                   ON DELETE CASCADE";
                   
    if ($conn->query($alter_table)) {
        echo "Database updated successfully!";
    } else {
        echo "Error updating database: " . $conn->error;
    }
} else {
    echo "Column already exists. No changes made.";
}

// Add is_read column for tracking unread comments if it doesn't exist
$check_read_column = "SHOW COLUMNS FROM comments LIKE 'is_read'";
$read_column_exists = $conn->query($check_read_column);

if ($read_column_exists->num_rows == 0) {
    $add_read_column = "ALTER TABLE comments 
                       ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0";
                       
    if ($conn->query($add_read_column)) {
        echo "<br>Added is_read column successfully!";
    } else {
        echo "<br>Error adding is_read column: " . $conn->error;
    }
}

$conn->close();
?>