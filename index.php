<?php
session_start();
require_once 'db_connect.php';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Define constants for event expiry
define('EXPIRY_WARNING_DAYS', 7); // Show warning when event is within 7 days of expiry

// Auto-update expired events
$today = date('Y-m-d');
$update_expired = "UPDATE events SET expired = 1 WHERE event_date < '$today' AND expired = 0";
$conn->query($update_expired);

// Get active (non-expired) events for display
$query = "SELECT * FROM events WHERE expired = 0 ORDER BY event_date ASC";
$result = $conn->query($query);

// Get upcoming events that are nearly expired (for notifications)
$warning_date = date('Y-m-d', strtotime("+".EXPIRY_WARNING_DAYS." days"));
$soon_expiring = "SELECT * FROM events WHERE event_date BETWEEN '$today' AND '$warning_date' AND expired = 0";
$soon_expiring_result = $conn->query($soon_expiring);

// Get count of expired events
$expired_count_query = "SELECT COUNT(*) as count FROM events WHERE expired = 1";
$expired_count_result = $conn->query($expired_count_query);
$expired_count = $expired_count_result->fetch_assoc()['count'];

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

// Set flag for showing expiring events modal
$show_expiry_modal = false;
if ($soon_expiring_result->num_rows > 0) {
    $show_expiry_modal = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $theme = $conn->real_escape_string($_POST['theme']);
    $batch_year = $conn->real_escape_string($_POST['batch_year']);
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $venue = $conn->real_escape_string($_POST['venue']);
    
    // Insert data with expired status (default 0 = not expired)
    $query = "INSERT INTO events (theme, batch_year, event_date, venue, expired) 
              VALUES ('$theme', '$batch_year', '$event_date', '$venue', 0)";
    
    if ($conn->query($query)) {
        // Store message in session for toast
        $_SESSION['toast_message'] = 'Event added successfully!';
        $_SESSION['toast_type'] = 'success';
        // Redirect to prevent form resubmission on refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $toast_message = 'Error: ' . $conn->error;
        $toast_type = 'danger';
    }
}

// Delete event if requested
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM events WHERE id = $id")) {
        // Store message in session for toast
        $_SESSION['toast_message'] = 'Event deleted successfully!';
        $_SESSION['toast_type'] = 'success';
        // Redirect to prevent issues with refreshing
        header("Location: index.php");
        exit();
    } else {
        $toast_message = 'Error deleting event: ' . $conn->error;
        $toast_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alumni Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Custom styles for expiry modal */
        .expiry-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1060;
            max-width: 450px;
            width: 90%;
            display: none;
        }
        
        .expiry-modal-content {
            background: rgba(255, 215, 0, 0.95);
            color: #002147;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 20px;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        
        .expiry-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1050;
            display: none;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.9); }
        }
        
        .fade-out {
            animation: fadeOut 0.5s;
        }
    </style>
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
                            <a href="index.php" class="sidebar-link active">
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
                            <a href="archived_events.php" class="sidebar-link">
                                <i class="fas fa-archive me-2"></i>
                                Archived Events
                                <?php if ($expired_count > 0): ?>
                                <span class="badge-counter"><?php echo $expired_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="comments.php" class="sidebar-link">
                                <i class="fas fa-comments me-2"></i>
                                Comments
                                <?php 
                                // Get unread comments count
                                $unread_comments_query = "SELECT COUNT(*) as count FROM comments WHERE is_read = 0";
                                $unread_comments_result = $conn->query($unread_comments_query);
                                $unread_comments = $unread_comments_result->fetch_assoc()['count'];
                                if ($unread_comments > 0):
                                ?>
                                <span class="badge-counter"><?php echo $unread_comments; ?></span>
                                <?php endif; ?>
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
                
                <h2 class="mb-4 text-navy-blue">Alumni Events Dashboard</h2>
                
                <!-- Toast container for notifications -->
                <div class="toast-container position-fixed top-0 end-0 p-3"></div>
                
                <div class="row">
                    <!-- Add Event Form -->
                    <div class="col-md-4 mb-4">
                        <div class="form-container">
                            <h4 class="mb-3 text-navy-blue">Add New Alumni Event</h4>
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="theme" class="form-label">Event Theme</label>
                                    <input type="text" class="form-control" id="theme" name="theme" required>
                                </div>
                                <div class="mb-3">
                                    <label for="batch_year" class="form-label">Batch Year</label>
                                    <input type="text" class="form-control" id="batch_year" name="batch_year" required>
                                </div>
                                <div class="mb-3">
                                    <label for="event_date" class="form-label">Event Date</label>
                                    <input type="date" class="form-control" id="event_date" name="event_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="venue" class="form-label">Venue</label>
                                    <input type="text" class="form-control" id="venue" name="venue" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Event
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Event List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0" style="color: black;">Active Events</h4>
                            </div>
                            <!-- Search functionality -->
                            <div class="card-body pb-0" style="background-color: white;">
                                <form id="searchForm" class="mb-3">
                                    <div class="input-group">
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search events by theme, batch, venue...">
                                        <button class="btn btn-primary" type="button" id="searchButton">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                        <button class="btn btn-secondary" type="button" id="resetButton">
                                            <i class="fas fa-sync"></i> Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="card-body pt-0" style="background-color: white;">
                                <div class="table-responsive">
                                    <table class="table table-hover" style="color: black">
                                        <thead style="color: yellow;">
                                            <tr>
                                                <th style="background: var(--yellow); color: black;">Theme</th>
                                                <th style="background: var(--yellow); color: black;">Batch Year</th>
                                                <th style="background: var(--yellow); color: black;">Date</th>
                                                <th style="background: var(--yellow); color: black;">Venue</th>
                                                <th style="background: var(--yellow); color: black;">Status</th>
                                                <th style="background: var(--yellow); color: black;">Comments</th>  
                                                <th style="background: var(--yellow); color: black;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result->num_rows > 0): ?>
                                                <?php while ($row = $result->fetch_assoc()): 
                                                    $days_until = (strtotime($row['event_date']) - strtotime($today)) / (60 * 60 * 24);
                                                    $is_expiring_soon = $days_until <= EXPIRY_WARNING_DAYS && $days_until >= 0;
                                                ?>
                                                    <tr <?php echo $is_expiring_soon ? 'class="bg-light"' : ''; ?>>
                                                        <td><?php echo htmlspecialchars($row['theme']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['batch_year']); ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                                                        <td><?php echo htmlspecialchars($row['venue']); ?></td>
                                                        <td>
                                                            <?php if ($is_expiring_soon): ?>
                                                                <span class="badge badge-expiring">
                                                                    Expires in <?php echo floor($days_until); ?> days
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            // Get comment count for this event
                                                            $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE event_id = {$row['id']}";
                                                            $comment_count_result = $conn->query($comment_count_query);
                                                            $comment_count = $comment_count_result->fetch_assoc()['count'];
                                                            ?>
                                                            <a href="comments.php?event_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                                                <i class="fas fa-comments me-1"></i> <?php echo $comment_count; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="edit_event.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $row['id']; ?>" data-theme="<?php echo htmlspecialchars($row['theme']); ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No active events found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dashboard Summary -->
                <div class="row mt-4">
                    <div class="col-md-3 mb-3">
                        <div class="card dashboard-card bg-navy-blue text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Events</h5>
                                        <?php
                                        $total = $conn->query("SELECT COUNT(*) as count FROM events");
                                        $total_count = $total->fetch_assoc()['count'];
                                        ?>
                                        <h2 class="mb-0"><?php echo $total_count; ?></h2>
                                    </div>
                                    <i class="fas fa-calendar-alt fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card dashboard-card bg-yellow text-navy-blue h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Active Events</h5>
                                        <h2 class="mb-0"><?php echo $result->num_rows; ?></h2>
                                    </div>
                                    <i class="fas fa-calendar-check fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card dashboard-card bg-warning text-navy-blue h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Expiring Soon</h5>
                                        <h2 class="mb-0"><?php echo $soon_expiring_result->num_rows; ?></h2>
                                    </div>
                                    <i class="fas fa-hourglass-end fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card dashboard-card bg-navy-blue text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Archived Events</h5>
                                        <h2 class="mb-0"><?php echo $expired_count; ?></h2>
                                    </div>
                                    <i class="fas fa-archive fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Event Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-navy-blue text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the event "<span id="eventTheme"></span>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiry Alert Modal -->
    <div class="expiry-modal" id="expiryModal">
        <div class="expiry-modal-content">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-exclamation-triangle fa-3x me-3"></i>
                <h4 class="mb-0">Attention!</h4>
            </div>
            <p class="fs-5 mb-3">You have <?php echo $soon_expiring_result->num_rows; ?> event<?php echo $soon_expiring_result->num_rows > 1 ? 's' : ''; ?> that will expire soon.</p>
            <button type="button" class="btn btn-navy-blue" id="closeExpiryModal">
                <i class="fas fa-check me-2"></i>Acknowledge
            </button>
        </div>
    </div>
    <div class="expiry-modal-backdrop" id="expiryModalBackdrop"></div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript for Toast and Modal -->
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
    
    // Handle delete modal
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const theme = button.getAttribute('data-theme');
            
            document.getElementById('eventTheme').textContent = theme;
            document.getElementById('confirmDelete').href = 'index.php?delete=' + id;
        });
    }
    
    // Expiry Modal functionality
    function showExpiryModal() {
        const expiryModal = document.getElementById('expiryModal');
        const expiryModalBackdrop = document.getElementById('expiryModalBackdrop');
        
        if (expiryModal && expiryModalBackdrop) {
            expiryModal.style.display = 'block';
            expiryModalBackdrop.style.display = 'block';
            
            // Auto-close after 7 seconds
            setTimeout(function() {
                closeExpiryModal();
            }, 7000);
        }
    }
    
    function closeExpiryModal() {
        const expiryModal = document.getElementById('expiryModal');
        const expiryModalBackdrop = document.getElementById('expiryModalBackdrop');
        
        if (expiryModal && expiryModalBackdrop) {
            expiryModal.classList.add('fade-out');
            expiryModalBackdrop.classList.add('fade-out');
            
            // Remove the modal after animation completes
            setTimeout(function() {
                expiryModal.style.display = 'none';
                expiryModalBackdrop.style.display = 'none';
                expiryModal.classList.remove('fade-out');
                expiryModalBackdrop.classList.remove('fade-out');
            }, 500);
        }
    }
    
    // Search functionality for events table
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const resetButton = document.getElementById('resetButton');
        const tableRows = document.querySelectorAll('table tbody tr');
        const closeExpiryModalBtn = document.getElementById('closeExpiryModal');
        
        // Function to filter table rows based on search input
        function filterTable() {
            const searchText = searchInput.value.toLowerCase();
            
            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show message if no results found
            const visibleRows = document.querySelectorAll('table tbody tr:not([style="display: none;"])');
            const noResultsRow = document.getElementById('noResultsRow');
            
            if (visibleRows.length === 0) {
                if (!noResultsRow) {
                    const tbody = document.querySelector('table tbody');
                    const newRow = document.createElement('tr');
                    newRow.id = 'noResultsRow';
                    newRow.innerHTML = `<td colspan="7" class="text-center">No events found matching "${searchText}"</td>`;
                    tbody.appendChild(newRow);
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }
        }
        
        // Function to reset the search
        function resetSearch() {
            searchInput.value = '';
            tableRows.forEach(row => {
                row.style.display = '';
            });
            
            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
        
        // Event listeners
        if (searchButton) {
            searchButton.addEventListener('click', filterTable);
        }
        
        if (resetButton) {
            resetButton.addEventListener('click', resetSearch);
        }
        
        if (searchInput) {
            searchInput.addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    filterTable();
                    event.preventDefault();
                }
            });
        }
        
        if (closeExpiryModalBtn) {
            closeExpiryModalBtn.addEventListener('click', closeExpiryModal);
        }
        
        <?php if ($toast_message): ?>
            showToast('<?php echo $toast_message; ?>', '<?php echo $toast_type; ?>');
        <?php endif; ?>
        
        <?php if ($show_expiry_modal): ?>
            // Show expiry modal after a short delay
            setTimeout(function() {
                showExpiryModal();
            }, 500);
        <?php endif; ?>
    });
</script>
</body>
</html>