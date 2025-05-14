<?php
session_start();
require_once 'db_connect.php';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get expired events for display
$query = "SELECT * FROM events WHERE expired = 1 ORDER BY event_date DESC";
$result = $conn->query($query);

// Initialize toast message variable
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

// Delete event if requested
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM events WHERE id = $id")) {
        // Store message in session for toast
        $_SESSION['toast_message'] = 'Event deleted successfully!';
        $_SESSION['toast_type'] = 'success';
        // Redirect to prevent issues with refreshing
        header("Location: archived_events.php");
        exit();
    } else {
        $_SESSION['toast_message'] = 'Error deleting event: ' . $conn->error;
        $_SESSION['toast_type'] = 'danger';
        header("Location: archived_events.php");
        exit();
    }
}

// Renew and restore event if requested
if (isset($_POST['renew_event'])) {
    $id = (int)$_POST['event_id'];
    $new_date = $_POST['new_event_date'];
    
    // Update the event date and restore it (set expired to 0)
    $update_query = "UPDATE events SET event_date = ?, expired = 0 WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_date, $id);
    
    if ($stmt->execute()) {
        $_SESSION['toast_message'] = 'Event renewed and restored successfully!';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_message'] = 'Error renewing event: ' . $conn->error;
        $_SESSION['toast_type'] = 'danger';
    }
    
    $stmt->close();
    header("Location: archived_events.php");
    exit();
}

// Get count of expired events
$expired_count_query = "SELECT COUNT(*) as count FROM events WHERE expired = 1";
$expired_count_result = $conn->query($expired_count_query);
$expired_count = $expired_count_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Events - Alumni Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --navy-blue: #003366;
            --yellow: #FFD700;
            --white: #FFFFFF;
            --danger: #dc3545;
            --warning: #ffc107;
        }
        
        body {
            background-color: var(--white);
            color: #333;
        }
        
        /* Sidebar styles */
        .sidebar {
            background-color: var(--navy-blue);
            color: var(--white);
            min-height: 100vh;
        }
        
        .sidebar-link {
            color: var(--white);
            padding: 0.8rem 1rem;
            display: block;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--yellow);
        }
        
        .navbar-brand {
            color: var(--white);
            text-decoration: none;
        }
        
        /* Navbar styles */
        .navbar {
            background-color: var(--yellow) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar .nav-link {
            color: var(--navy-blue) !important;
            font-weight: 500;
        }
        
        /* Card styles */
        .dashboard-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .bg-navy-blue {
            background-color: var(--navy-blue);
            color: var(--white);
        }
        
        .bg-yellow {
            background-color: var(--yellow);
            color: var(--navy-blue);
        }
        
        /* Table styles */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background-color: var(--navy-blue);
            color: var(--white);
            border-bottom: none;
        }
        
        .table-hover tbody tr:hover {
            color: white;
        }
        
        /* Card header */
        .card-header {
            background-color: var(--yellow);
            color: var(--white);
        }
        
        /* Badge counter */
        .badge-counter {
            position: relative;
            top: -1px;
            margin-left: 5px;
            padding: 0.25em 0.6em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            background-color: var(--danger);
            color: white;
        }
        
        /* Badge archived */
        .badge-archived {
            background-color: #6c757d;
            color: white;
        }
        
        /* Form container */
        .form-container {
            background-color: var(--white);
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-top: 5px solid var(--yellow);
        }

        .card {
            border-top: 5px solid var(--yellow);
        }
        
        /* Toast customization */
        .toast {
            position: relative;
            min-width: 350px;
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
                            <a href="archived_events.php" class="sidebar-link active">
                                <i class="fas fa-archive me-2"></i>
                                Archived Events
                                <?php if ($expired_count > 0): ?>
                                <span class="badge-counter"><?php echo $expired_count; ?></span>
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
                
                <h2 class="mb-4 text-navy-blue">Archived Alumni Events</h2>
                
                <!-- Toast container for notifications -->
                <div class="toast-container position-fixed top-0 end-0 p-3"></div>
                
                <!-- Archived Events List -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0" style="color: black;">Expired Events</h4>
                    </div>
                    <div class="card-body" style="background-color: white;">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                These events have expired and been archived automatically. You can renew and restore events if needed.
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover" style="color: black">
                                    <thead style="color: yellow;">
                                        <tr>
                                            <th style="background: var(--yellow); color: black;">Theme</th>
                                            <th style="background: var(--yellow); color: black;">Batch Year</th>
                                            <th style="background: var(--yellow); color: black;">Date</th>
                                            <th style="background: var(--yellow); color: black;">Venue</th>
                                            <th style="background: var(--yellow); color: black;">Status</th>
                                            <th style="background: var(--yellow); color: black;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['theme']); ?></td>
                                                <td><?php echo htmlspecialchars($row['batch_year']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($row['venue']); ?></td>
                                                <td>
                                                    <span class="badge badge-archived">Archived</span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" 
                                                        data-bs-target="#renewModal" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-theme="<?php echo htmlspecialchars($row['theme']); ?>"
                                                        data-date="<?php echo date('Y-m-d', strtotime($row['event_date'])); ?>">
                                                        <i class="fas fa-sync-alt"></i> Renew
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $row['id']; ?>" data-theme="<?php echo htmlspecialchars($row['theme']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary">
                                <i class="fas fa-info-circle me-2"></i>
                                No archived events found. When events expire, they will appear here.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Archives Summary -->
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="card dashboard-card bg-navy-blue text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Archived Events</h5>
                                        <h2 class="mb-0"><?php echo $expired_count; ?></h2>
                                    </div>
                                    <i class="fas fa-archive fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card dashboard-card bg-yellow text-navy-blue h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Active Events</h5>
                                        <?php
                                        $active = $conn->query("SELECT COUNT(*) as count FROM events WHERE expired = 0");
                                        $active_count = $active->fetch_assoc()['count'];
                                        ?>
                                        <h2 class="mb-0"><?php echo $active_count; ?></h2>
                                    </div>
                                    <i class="fas fa-calendar-check fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Renew Event Modal -->
    <div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-navy-blue text-white">
                    <h5 class="modal-title" id="renewModalLabel">Renew Event</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="archived_events.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="event_id" id="renewEventId">
                        <p>You are about to renew and restore the event: <strong><span id="renewEventTheme"></span></strong></p>
                        <div class="mb-3">
                            <label for="new_event_date" class="form-label">New Event Date</label>
                            <input type="date" class="form-control" id="new_event_date" name="new_event_date" required>
                            <small class="text-muted">Select a future date for the renewed event.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="renew_event" class="btn btn-success">Renew & Restore</button>
                    </div>
                </form>
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
                    Are you sure you want to permanently delete the archived event "<span id="eventTheme"></span>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete Permanently</a>
                </div>
            </div>
        </div>
    </div>

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
                document.getElementById('confirmDelete').href = 'archived_events.php?delete=' + id;
            });
        }
        
        // Handle renew modal
        const renewModal = document.getElementById('renewModal');
        if (renewModal) {
            renewModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const theme = button.getAttribute('data-theme');
                const date = button.getAttribute('data-date');
                
                document.getElementById('renewEventTheme').textContent = theme;
                document.getElementById('renewEventId').value = id;
                
                // Set minimum date to today
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                const formattedToday = `${yyyy}-${mm}-${dd}`;
                
                const newDateInput = document.getElementById('new_event_date');
                newDateInput.setAttribute('min', formattedToday);
                
                // Set the default new date to 1 year from the original date
                const originalDate = new Date(date);
                originalDate.setFullYear(originalDate.getFullYear() + 1);
                const newDefaultDate = originalDate.toISOString().split('T')[0];
                newDateInput.value = newDefaultDate;
            });
        }
        
        // Show toast on page load if there's a message
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($toast_message): ?>
                showToast('<?php echo $toast_message; ?>', '<?php echo $toast_type; ?>');
            <?php endif; ?>
            
            // Convert any alert messages to toasts
            const alerts = document.querySelectorAll('.alert:not(.alert-info):not(.alert-secondary)');
            alerts.forEach(alert => {
                let type = 'success';
                if (alert.classList.contains('alert-danger')) {
                    type = 'danger';
                } else if (alert.classList.contains('alert-warning')) {
                    type = 'warning';
                }
                
                showToast(alert.innerText, type);
                alert.remove();
            });
        });
    </script>
</body>
</html>