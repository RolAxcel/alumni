<?php
session_start();

// Include database connection
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If no user found, redirect to login
if (!$user) {
    $_SESSION = array();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Alumni Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #003366; /* Dark blue for sidebar from image */
            --primary-yellow: #FFD700; /* Yellow for header bar from image */
            --text-light: #FFFFFF;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: var(--primary-blue) !important;
            padding: 15px 0;
        }

        .header-bar {
            background-color: var(--primary-yellow);
            padding: 10px 20px;
            color: #000;
        }

        .section-header {
            background-color: var(--primary-yellow);
            padding: 15px;
            margin-bottom: 20px;
            color: #000;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .btn-primary:hover {
            background-color: #00264d;
            border-color: #00264d;
        }

        .footer {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
        }

        .social-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            color: white;
            transition: color 0.3s;
        }

        .social-icon:hover {
            color: var(--primary-yellow);
        }

        .settings-nav .nav-link {
            color: #333;
            border-radius: 0;
            padding: 12px 15px;
            border-left: 4px solid transparent;
        }

        .settings-nav .nav-link.active {
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary-blue);
            font-weight: bold;
        }

        .settings-nav .nav-link:hover:not(.active) {
            background-color: #f1f1f1;
            border-left: 4px solid #ccc;
        }

        .settings-nav .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 5px;
        }

        .tab-content {
            padding: 20px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0 0.25rem 0.25rem 0.25rem;
        }

        .page-header {
            background-color: #f5f5f5;
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .notification-option {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .notification-option:last-child {
            border-bottom: none;
        }
        
        .backup-option {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .backup-option:hover {
            background-color: #f8f9fa;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .progress-bar-striped {
            background-size: 1rem 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="public_homepage.php">
                <i class="fas fa-graduation-cap me-2"></i>
                Alumni Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> Username
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold">Account Settings</h1>
                    <p class="text-muted mb-0">Manage your account preferences and settings</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end mb-0">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Settings Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Settings Navigation -->
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Settings Menu</h5>
                        </div>
                        <div class="card-body p-0">
                            <nav class="settings-nav nav flex-column">
                                <a class="nav-link active" id="general-tab" data-bs-toggle="pill" href="#general">
                                    <i class="fas fa-cog"></i> General
                                </a>
                                <a class="nav-link" id="security-tab" data-bs-toggle="pill" href="#security">
                                    <i class="fas fa-lock"></i> Security
                                </a>
                                <a class="nav-link" id="notifications-tab" data-bs-toggle="pill" href="#notifications">
                                    <i class="fas fa-bell"></i> Notifications
                                </a>
                                <a class="nav-link" id="privacy-tab" data-bs-toggle="pill" href="#privacy">
                                    <i class="fas fa-user-shield"></i> Privacy
                                </a>
                                <a class="nav-link" id="linkedaccounts-tab" data-bs-toggle="pill" href="#linkedaccounts">
                                    <i class="fas fa-link"></i> Linked Accounts
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="col-lg-9">
                    <div class="tab-content">
                        <!-- General Settings Tab -->
                        <div class="tab-pane fade show active" id="general">
                            <h4 class="mb-4">General Settings</h4>
                            <form action="update_settings.php" method="post">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Account Information</h5>
                                        <div class="mb-3 row">
                                            <label for="username" class="col-sm-3 col-form-label">Username</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                                <small class="text-muted">Username cannot be changed</small>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="email" class="col-sm-3 col-form-label">Email Address</label>
                                            <div class="col-sm-9">
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="gradYear" class="col-sm-3 col-form-label">Graduation Year</label>
                                            <div class="col-sm-9">
                                                <select class="form-select" id="gradYear" name="grad_year">
                                                    <?php 
                                                    $currentYear = date('Y');
                                                    for ($year = $currentYear; $year >= $currentYear - 100; $year--) {
                                                        $selected = ($user['grad_year'] == $year) ? 'selected' : '';
                                                        echo "<option value=\"$year\" $selected>$year</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="timezone" class="col-sm-3 col-form-label">Timezone</label>
                                            <div class="col-sm-9">
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <?php
                                                    $timezones = [
                                                        'America/New_York' => 'Eastern Time (US & Canada)',
                                                        'America/Chicago' => 'Central Time (US & Canada)',
                                                        'America/Denver' => 'Mountain Time (US & Canada)',
                                                        'America/Los_Angeles' => 'Pacific Time (US & Canada)',
                                                        'America/Anchorage' => 'Alaska',
                                                        'Pacific/Honolulu' => 'Hawaii',
                                                        'Europe/London' => 'London',
                                                        'Europe/Paris' => 'Paris',
                                                        'Asia/Tokyo' => 'Tokyo',
                                                        'Australia/Sydney' => 'Sydney'
                                                    ];
                                                    
                                                    foreach ($timezones as $tz_value => $tz_name) {
                                                        $selected = ($user['timezone'] == $tz_value) ? 'selected' : '';
                                                        echo "<option value=\"$tz_value\" $selected>$tz_name</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Language & Locale</h5>
                                        <div class="mb-3 row">
                                            <label for="language" class="col-sm-3 col-form-label">Language</label>
                                            <div class="col-sm-9">
                                                <select class="form-select" id="language" name="language">
                                                    <option value="en" <?php echo ($user['language'] == 'en') ? 'selected' : ''; ?>>English</option>
                                                    <option value="es" <?php echo ($user['language'] == 'es') ? 'selected' : ''; ?>>Español</option>
                                                    <option value="fr" <?php echo ($user['language'] == 'fr') ? 'selected' : ''; ?>>Français</option>
                                                    <option value="de" <?php echo ($user['language'] == 'de') ? 'selected' : ''; ?>>Deutsch</option>
                                                    <option value="zh" <?php echo ($user['language'] == 'zh') ? 'selected' : ''; ?>>中文</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="dateFormat" class="col-sm-3 col-form-label">Date Format</label>
                                            <div class="col-sm-9">
                                                <select class="form-select" id="dateFormat" name="date_format">
                                                    <option value="MM/DD/YYYY" <?php echo ($user['date_format'] == 'MM/DD/YYYY') ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                                    <option value="DD/MM/YYYY" <?php echo ($user['date_format'] == 'DD/MM/YYYY') ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                                    <option value="YYYY-MM-DD" <?php echo ($user['date_format'] == 'YYYY-MM-DD') ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" name="update_general" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Settings Tab -->
                        <div class="tab-pane fade" id="security">
                            <h4 class="mb-4">Security Settings</h4>
                            <form action="update_security.php" method="post">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Change Password</h5>
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                        </div>
                                        <button type="submit" name="update_password" class="btn btn-primary">
                                            <i class="fas fa-key me-2"></i> Change Password
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Two-Factor Authentication</h5>
                                    <p class="card-text">Enhance your account security by enabling two-factor authentication.</p>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="twoFactorToggle" <?php echo ($user['two_factor'] == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="twoFactorToggle">Enable Two-Factor Authentication</label>
                                    </div>
                                    <div id="twoFactorOptions" class="<?php echo ($user['two_factor'] == 1) ? '' : 'd-none'; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Verification Method</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="twoFactorMethod" id="twoFactorSMS" value="sms" <?php echo ($user['two_factor_method'] == 'sms') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="twoFactorSMS">
                                                    SMS Text Message
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="twoFactorMethod" id="twoFactorApp" value="app" <?php echo ($user['two_factor_method'] == 'app') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="twoFactorApp">
                                                    Authentication App
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary" id="twoFactorSetup">
                                            <i class="fas fa-cog me-2"></i> Setup Two-Factor Authentication
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Login Sessions</h5>
                                    <p class="card-text">View and manage your active login sessions.</p>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Device</th>
                                                    <th>Location</th>
                                                    <th>Last Activity</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><i class="fas fa-laptop me-2"></i> Windows PC</td>
                                                    <td>New York, USA</td>
                                                    <td>Current Session</td>
                                                    <td>
                                                        <span class="badge bg-success">Current</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-mobile-alt me-2"></i> iPhone</td>
                                                    <td>New York, USA</td>
                                                    <td>2 days ago</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-sign-out-alt"></i> Logout
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-tablet-alt me-2"></i> iPad</td>
                                                    <td>Boston, USA</td>
                                                    <td>1 week ago</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-sign-out-alt"></i> Logout
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout of All Other Sessions
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications Settings Tab -->
                        <div class="tab-pane fade" id="notifications">
                            <h4 class="mb-4">Notification Settings</h4>
                            <form action="update_notifications.php" method="post">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Email Notifications</h5>
                                        <div class="notification-option">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="emailEvents" name="notify_events" <?php echo ($user['notify_events'] == 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailEvents">
                                                    <strong>Event Updates</strong>
                                                </label>
                                            </div>
                                            <p class="text-muted ms-4 mb-0">Receive emails about new events, changes, or cancellations</p>
                                        </div>
                                        <div class="notification-option">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="emailNews" name="notify_news" <?php echo ($user['notify_news'] == 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailNews">
                                                    <strong>News & Announcements</strong>
                                                </label>
                                            </div>
                                            <p class="text-muted ms-4 mb-0">Receive emails about news and announcements from the institution</p>
                                        </div>
                                        <div class="notification-option">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="emailComments" name="notify_comments" <?php echo ($user['notify_comments'] == 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailComments">
                                                    <strong>Comment Notifications</strong>
                                                </label>
                                            </div>
                                            <p class="text-muted ms-4 mb-0">Receive emails when someone replies to your comments</p>
                                        </div>
                                        <div class="notification-option">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="emailJobs" name="notify_jobs" <?php echo ($user['notify_jobs'] == 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailJobs">
                                                    <strong>Job Opportunities</strong>
                                                </label>
                                            </div>
                                            <p class="text-muted ms-4 mb-0">Receive emails about new job opportunities</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Notification Frequency</h5>
                                        <div class="mb-3">
                                            <label class="form-label">How often would you like to receive non-urgent emails?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="email_frequency" id="emailInstant" value="instant" <?php echo ($user['email_frequency'] == 'instant') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailInstant">
                                                    Immediately
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="email_frequency" id="emailDaily" value="daily" <?php echo ($user['email_frequency'] == 'daily') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailDaily">
                                                    Daily Digest
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="email_frequency" id="emailWeekly" value="weekly" <?php echo ($user['email_frequency'] == 'weekly') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailWeekly">
                                                    Weekly Digest
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" name="update_notifications" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Privacy Settings Tab -->
                        <div class="tab-pane fade" id="privacy">
                            <h4 class="mb-4">Privacy Settings</h4>
                            <form action="update_privacy.php" method="post">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Profile Visibility</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Who can see your profile?</label>
                                            <select class="form-select" name="profile_visibility">
                                                <option value="public" <?php echo ($user['profile_visibility'] == 'public') ? 'selected' : ''; ?>>Everyone (Public)</option>
                                                <option value="alumni" <?php echo ($user['profile_visibility'] == 'alumni') ? 'selected' : ''; ?>>Alumni Only</option>
                                                <option value="batch" <?php echo ($user['profile_visibility'] == 'batch') ? 'selected' : ''; ?>>My Batch Only</option>
                                                <option value="none" <?php echo ($user['profile_visibility'] == 'none') ? 'selected' : ''; ?>>Nobody (Private)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Information Visibility</h5>
                                        <p>Control what information is visible to others</p>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email Address</label>
                                            <select class="form-select" name="email_visibility">
                                                <option value="public" <?php echo ($user['email_visibility'] == 'public') ? 'selected' : ''; ?>>Everyone</option>
                                                <option value="alumni" <?php echo ($user['email_visibility'] == 'alumni') ? 'selected' : ''; ?>>Alumni Only</option>
                                                <option value="batch" <?php echo ($user['email_visibility'] == 'batch') ? 'selected' : ''; ?>>My Batch Only</option>
                                                <option value="none" <?php echo ($user['email_visibility'] == 'none') ? 'selected' : ''; ?>>Nobody</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <select class="form-select" name="phone_visibility">
                                                <option value="public" <?php echo ($user['phone_visibility'] == 'public') ? 'selected' : ''; ?>>Everyone</option>
                                                <option value="alumni" <?php echo ($user['phone_visibility'] == 'alumni') ? 'selected' : ''; ?>>Alumni Only</option>
                                                <option value="batch" <?php echo ($user['phone_visibility'] == 'batch') ? 'selected' : ''; ?>>My Batch Only</option>
                                                <option value="none" <?php echo ($user['phone_visibility'] == 'none') ? 'selected' : ''; ?>>Nobody</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Employment Information</label>
                                            <select class="form-select" name="employment_visibility">
                                                <option value="public" <?php echo ($user['employment_visibility'] == 'public') ? 'selected' : ''; ?>>Everyone</option>
                                                <option value="alumni" <?php echo ($user['employment_visibility'] == 'alumni') ? 'selected' : ''; ?>>Alumni Only</option>
                                                <option value="batch" <?php echo ($user['employment_visibility'] == 'batch') ? 'selected' : ''; ?>>My Batch Only</option>
                                                <option value="none" <?php echo ($user['employment_visibility'] == 'none') ? 'selected' : ''; ?>>Nobody</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Directory Listing</h5>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="directoryListing" name="directory_listing" <?php echo ($user['directory_listing'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="directoryListing">
                                                Include my profile in the alumni directory
                                            </label>
                                        </div>
                                        <small class="text-muted">Your profile will be searchable in the alumni directory if enabled</small>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" name="update_privacy" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Linked Accounts Tab -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Backup Linked Accounts</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Manual Backup</h6>
                                        <p class="text-muted small">Download a backup of all your linked accounts data.</p>
                                        <form action="backup_linked_accounts.php" method="post">
                                            <input type="hidden" name="action" value="generate_backup">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-download me-2"></i>Download Backup
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Scheduled Backups</h6>
                                        <p class="text-muted small">Set up automatic backups of your linked accounts data.</p>
                                        
                                        <?php
                                        // Check if the user has a backup schedule
                                        $schedule_stmt = $conn->prepare("SELECT frequency, next_backup FROM backup_schedules 
                                                                        WHERE user_id = ? AND data_type = 'linked_accounts'");
                                        $schedule_stmt->bind_param("i", $_SESSION['user_id']);
                                        $schedule_stmt->execute();
                                        $schedule_result = $schedule_stmt->get_result();
                                        
                                        if ($schedule_result->num_rows > 0) {
                                            $schedule = $schedule_result->fetch_assoc();
                                            ?>
                                            <div class="alert alert-info">
                                                <p><strong>Current Schedule:</strong> <?php echo ucfirst($schedule['frequency']); ?> backups</p>
                                                <p><strong>Next Backup:</strong> <?php echo date('F j, Y, g:i a', strtotime($schedule['next_backup'])); ?></p>
                                                
                                                <form action="backup_linked_accounts.php" method="post" class="mt-3">
                                                    <input type="hidden" name="action" value="delete_schedule">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash me-2"></i>Delete Schedule
                                                    </button>
                                                </form>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                            <form action="backup_linked_accounts.php" method="post" id="scheduleBackupForm">
                                                <input type="hidden" name="action" value="schedule_backup">
                                                <div class="form-group mb-3">
                                                    <label for="backupFrequency">Backup Frequency</label>
                                                    <select class="form-control" id="backupFrequency" name="frequency" required>
                                                        <option value="">Select frequency</option>
                                                        <option value="daily">Daily</option>
                                                        <option value="weekly">Weekly</option>
                                                        <option value="monthly">Monthly</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-calendar-check me-2"></i>Schedule Backups
                                                </button>
                                            </form>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h6>Backup History</h6>
                                    <p class="text-muted small">Recent backup activity for your linked accounts.</p>
                                    
                                    <?php
                                    // Get backup history
                                    $history_stmt = $conn->prepare("SELECT execution_time, status, file_path 
                                                                FROM backup_history 
                                                                WHERE user_id = ? AND data_type = 'linked_accounts'
                                                                ORDER BY execution_time DESC LIMIT 5");
                                    $history_stmt->bind_param("i", $_SESSION['user_id']);
                                    $history_stmt->execute();
                                    $history_result = $history_stmt->get_result();
                                    
                                    if ($history_result->num_rows > 0) {
                                    ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($row = $history_result->fetch_assoc()) { ?>
                                                        <tr>
                                                            <td><?php echo date('M j, Y g:i a', strtotime($row['execution_time'])); ?></td>
                                                            <td>
                                                                <?php if ($row['status'] == 'success') { ?>
                                                                    <span class="badge bg-success">Success</span>
                                                                <?php } else { ?>
                                                                    <span class="badge bg-danger">Failed</span>
                                                                <?php } ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($row['status'] == 'success' && !empty($row['file_path'])) { ?>
                                                                    <a href="download_backup.php?file=<?php echo urlencode($row['file_path']); ?>" class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php
                                    } else {
                                        echo '<div class="alert alert-light">No backup history available.</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>About Alumni Portal</h5>
                    <p>Connecting graduates and fostering lifelong relationships with our institution and fellow alumni.</p>
                    <div class="mt-3">
                        <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="#" class="text-white">Events</a></li>
                        <li><a href="#" class="text-white">Gallery</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                        <li><a href="profile.php" class="text-white">Profile</a></li>
                        <li><a href="logout.php" class="text-white">Logout</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Education Street, City</li>
                        <li><i class="fas fa-phone me-2"></i> (123) 456-7890</li>
                        <li><i class="fas fa-envelope me-2"></i> alumni@example.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Alumni Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Two-Factor Authentication toggle script
        document.getElementById('twoFactorToggle').addEventListener('change', function() {
            document.getElementById('twoFactorOptions').classList.toggle('d-none', !this.checked);
        });
    </script>
    <script>
    $(document).ready(function() {
        // Handle schedule backup form submission
        $("#scheduleBackupForm").on("submit", function(e) {
            e.preventDefault();
            
            $.ajax({
                type: "POST",
                url: "backup_linked_accounts.php",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Show success message and refresh the page
                        alert("Backup schedule updated successfully!");
                        location.reload();
                    } else {
                        // Show error message
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while processing your request.");
                }
            });
        });
    });
    </script>
</body>
</html>