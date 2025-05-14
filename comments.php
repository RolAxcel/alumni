<?php
session_start();
require_once 'db_connect.php';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get comments for a specific event if event_id is provided
if (isset($_GET['event_id'])) {
    $event_id = (int)$_GET['event_id'];
    
    // Get event details
    $event_query = "SELECT * FROM events WHERE id = $event_id";
    $event_result = $conn->query($event_query);
    $event = $event_result->fetch_assoc();
    
    // Get comments for this event
    $comments_query = "SELECT c.*, u.username 
                      FROM comments c 
                      JOIN users u ON c.user_id = u.id 
                      WHERE c.event_id = $event_id 
                      ORDER BY c.created_at DESC";
    $comments_result = $conn->query($comments_query);
} else {
    // Get all comments (for admin dashboard)
    $comments_query = "SELECT c.*, u.username, e.theme as event_theme 
                      FROM comments c 
                      JOIN users u ON c.user_id = u.id 
                      JOIN events e ON c.event_id = e.id 
                      ORDER BY c.created_at DESC";
    $comments_result = $conn->query($comments_query);
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
    $comment_id = (int)$_POST['comment_id'];
    $event_id = (int)$_POST['event_id'];
    $content = $conn->real_escape_string($_POST['content']);
    $user_id = $_SESSION['user_id'];
    
    // Insert the reply
    $reply_query = "INSERT INTO comments (event_id, user_id, content, parent_comment_id) 
                    VALUES ($event_id, $user_id, '$content', $comment_id)";
    
    if ($conn->query($reply_query)) {
        $_SESSION['toast_message'] = 'Reply posted successfully!';
        $_SESSION['toast_type'] = 'success';
        // Redirect to prevent form resubmission
        header("Location: comments.php" . (isset($_GET['event_id']) ? "?event_id=$event_id" : ""));
        exit();
    } else {
        $_SESSION['toast_message'] = 'Error posting reply: ' . $conn->error;
        $_SESSION['toast_type'] = 'danger';
    }
}

// Handle comment deletion
if (isset($_GET['delete_comment'])) {
    $comment_id = (int)$_GET['delete_comment'];
    $event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
    
    if ($conn->query("DELETE FROM comments WHERE id = $comment_id")) {
        $_SESSION['toast_message'] = 'Comment deleted successfully!';
        $_SESSION['toast_type'] = 'success';
        // Redirect to prevent refresh issues
        header("Location: comments.php" . ($event_id ? "?event_id=$event_id" : ""));
        exit();
    } else {
        $_SESSION['toast_message'] = 'Error deleting comment: ' . $conn->error;
        $_SESSION['toast_type'] = 'danger';
    }
}

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
    <title>Comments - Alumni Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 p-0 sidebar">
                <div class="d-flex flex-column">
                    <a href="#" class="navbar-brand d-flex align-items-center justify-content-center p-3 mb-3 border-bottom">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Alumni Portal
                    </a>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="index.php" class="sidebar-link">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="events.php" class="sidebar-link">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="comments.php" class="sidebar-link active">
                                <i class="fas fa-comments me-2"></i>
                                Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="archived_events.php" class="sidebar-link">
                                <i class="fas fa-archive me-2"></i>
                                Archived Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="sidebar-link">
                                <i class="fas fa-cog me-2"></i>
                                Settings
                            </a>
                        </li>
                        <li class="nav-item mt-auto">
                            <a href="logout.php" class="sidebar-link">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item" style="display: flex;">
                                    <a href="public_homepage.php" class="nav-link">
                                        <i class="fas fa-users me-2"></i>
                                        Users
                                    </a>
                                    <span class="nav-link">
                                        <i class="fas fa-user me-1"></i>
                                        Welcome, <?php echo $_SESSION['username']; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                
                <?php if (isset($event)): ?>
                <h2 class="mb-4 text-navy-blue">Comments for "<?php echo htmlspecialchars($event['theme']); ?>"</h2>
                <div class="mb-3">
                    <a href="comments.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to All Comments
                    </a>
                </div>
                <?php else: ?>
                <h2 class="mb-4 text-navy-blue">Comments Dashboard</h2>
                <?php endif; ?>
                
                <!-- Toast container for notifications -->
                <div class="toast-container position-fixed top-0 end-0 p-3"></div>
                
                <!-- Comments Section -->
                <div class="card">
                    <div class="card-header bg-navy-blue text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-comments me-2"></i>
                            <?php echo isset($event) ? 'Event Comments' : 'All Comments'; ?>
                        </h4>
                    </div>
                    <div class="card-body" style="background-color: white;">
                        <div class="comments-list">
                            <?php if ($comments_result->num_rows > 0): ?>
                                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                                    <div class="comment-card mb-3" id="comment-<?php echo $comment['id']; ?>">
                                        <div class="comment-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="comment-author">
                                                        <i class="fas fa-user-circle me-1"></i>
                                                        <?php echo htmlspecialchars($comment['username']); ?>
                                                    </span>
                                                    <?php if (!isset($event)): ?>
                                                    <span class="comment-event">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <a href="comments.php?event_id=<?php echo $comment['event_id']; ?>">
                                                            <?php echo htmlspecialchars($comment['event_theme']); ?>
                                                        </a>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="comment-date">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo date('M d, Y g:i A', strtotime($comment['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="comment-body">
                                            <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                        </div>
                                        <div class="comment-actions">
                                            <button class="btn btn-sm btn-primary reply-btn" data-comment-id="<?php echo $comment['id']; ?>">
                                                <i class="fas fa-reply me-1"></i> Reply
                                            </button>
                                            <a href="comments.php?delete_comment=<?php echo $comment['id']; ?><?php echo isset($event) ? '&event_id='.$event['id'] : ''; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this comment?');">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </a>
                                        </div>
                                        <!-- Reply Form (Hidden by Default) -->
                                        <div class="reply-form mt-2" id="reply-form-<?php echo $comment['id']; ?>" style="display: none;">
                                            <form method="post" action="">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                <input type="hidden" name="event_id" value="<?php echo $comment['event_id']; ?>">
                                                <div class="form-group">
                                                    <textarea class="form-control" name="content" rows="3" required placeholder="Write your reply here..."></textarea>
                                                </div>
                                                <div class="mt-2">
                                                    <button type="submit" name="reply" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-paper-plane me-1"></i> Post Reply
                                                    </button>
                                                    <button type="button" class="btn btn-secondary btn-sm cancel-reply" data-comment-id="<?php echo $comment['id']; ?>">
                                                        <i class="fas fa-times me-1"></i> Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        
                                        <!-- Fetch and display replies -->
                                        <?php
                                        $reply_query = "SELECT r.*, u.username 
                                                       FROM comments r 
                                                       JOIN users u ON r.user_id = u.id 
                                                       WHERE r.parent_comment_id = {$comment['id']} 
                                                       ORDER BY r.created_at ASC";
                                        $replies = $conn->query($reply_query);
                                        if ($replies->num_rows > 0):
                                        ?>
                                        <div class="replies-section mt-3">
                                            <h6 class="replies-title">
                                                <i class="fas fa-reply-all me-1"></i> Replies 
                                                <span class="badge rounded-pill bg-navy-blue"><?php echo $replies->num_rows; ?></span>
                                            </h6>
                                            <?php while ($reply = $replies->fetch_assoc()): ?>
                                                <div class="reply-card">
                                                    <div class="reply-header">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="reply-author">
                                                                <i class="fas fa-user-circle me-1"></i>
                                                                <?php echo htmlspecialchars($reply['username']); ?>
                                                                <span class="admin-badge">Admin</span>
                                                            </span>
                                                            <span class="reply-date">
                                                                <i class="fas fa-clock me-1"></i>
                                                                <?php echo date('M d, Y g:i A', strtotime($reply['created_at'])); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="reply-body">
                                                        <p><?php echo nl2br(htmlspecialchars($reply['content'])); ?></p>
                                                    </div>
                                                    <div class="reply-actions">
                                                        <a href="comments.php?delete_comment=<?php echo $reply['id']; ?><?php echo isset($event) ? '&event_id='.$event['id'] : ''; ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this reply?');">
                                                            <i class="fas fa-trash me-1"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No comments found.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Comments Functionality -->
    <script>
        // Show toast notification
        function showToast(message, type) {
            const toastContainer = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            
            // Map standard Bootstrap types to our custom colors
            let bgColor = 'bg-navy-blue';
            let textColor = 'text-white';
            
            if (type === 'success') {
                bgColor = 'bg-navy-blue';
            } else if (type === 'danger') {
                bgColor = 'bg-danger';
            } else if (type === 'warning') {
                bgColor = 'bg-yellow';
                textColor = 'text-navy-blue';
            }
            
            toast.className = `toast align-items-center ${textColor} ${bgColor} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            const toastContent = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            toast.innerHTML = toastContent;
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 3000
            });
            
            bsToast.show();
            
            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', function() {
                toastContainer.removeChild(toast);
            });
        }
        
        // Reply functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Show reply form
            const replyButtons = document.querySelectorAll('.reply-btn');
            replyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const commentId = this.getAttribute('data-comment-id');
                    document.getElementById('reply-form-' + commentId).style.display = 'block';
                    this.style.display = 'none';
                });
            });
            
            // Cancel reply
            const cancelButtons = document.querySelectorAll('.cancel-reply');
            cancelButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const commentId = this.getAttribute('data-comment-id');
                    document.getElementById('reply-form-' + commentId).style.display = 'none';
                    document.querySelector(`.reply-btn[data-comment-id="${commentId}"]`).style.display = 'inline-block';
                });
            });
            
            <?php if ($toast_message): ?>
                showToast('<?php echo $toast_message; ?>', '<?php echo $toast_type; ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html>