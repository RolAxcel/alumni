<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Portal</title>
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

        .user-info {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .user-info i {
            margin-right: 5px;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://via.placeholder.com/1920x1080') no-repeat center center;
            background-size: cover;
            height: 500px;
            display: flex;
            align-items: center;
            color: white;
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--primary-blue);
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
        
        .cta-section {
            background-color: var(--primary-blue);
            color: white;
        }

        /* Comment section styles */
        .comment-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .comment-list {
            max-height: 150px;
            overflow-y: auto;
            margin-bottom: 10px;
            padding-right: 5px;
        }

        .comment-item {
            padding: 8px;
            margin-bottom: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 3px;
        }

        .event-card {
            height: auto;
            display: flex;
            flex-direction: column;
        }

        .event-card .card-body {
            flex: 1 0 auto;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
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
                            <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold">Welcome to the Alumni Portal</h1>
                    <p class="lead">Connecting graduates from all years. Stay in touch with your alma mater and fellow alumni.</p>
                    <a href="#" class="btn btn-primary btn-lg mt-3">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Upcoming Events
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="section-header">
                <h2 class="fw-bold mb-0">Why Join Our Alumni Network?</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-users card-icon"></i>
                            <h4 class="card-title">Networking</h4>
                            <p class="card-text">Connect with former classmates and make valuable professional connections.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-calendar-check card-icon"></i>
                            <h4 class="card-title">Events</h4>
                            <p class="card-text">Participate in reunions, workshops, and special alumni gatherings.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-briefcase card-icon"></i>
                            <h4 class="card-title">Career Opportunities</h4>
                            <p class="card-text">Access exclusive job postings and career advancement resources.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="fw-bold mb-0">Upcoming Alumni Events</h2>
            </div>
            
            <?php
            require_once 'db_connect.php';
            $events_query = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3";
            $events_result = $conn->query($events_query);
            ?>
            
            <div class="row g-4">
                <?php if ($events_result && $events_result->num_rows > 0): ?>
                    <?php while ($event = $events_result->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="card event-card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['theme']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">Batch of <?php echo htmlspecialchars($event['batch_year']); ?></h6>
                                    <p class="card-text">
                                        <i class="fas fa-calendar-day me-2"></i>
                                        <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                    </p>
                                    <p class="card-text">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <?php echo htmlspecialchars($event['venue']); ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="#" class="btn btn-outline-primary w-100 mb-2">Register Now</a>
                                    
                                    <!-- Comment Section -->
                                    <div class="comment-section">
                                        <h6><i class="fas fa-comments me-1"></i> Comments</h6>
                                        <div class="comment-list">
                                            <?php
                                            // Query to fetch comments for this event
                                            $comments_query = "SELECT c.*, u.username FROM comments c 
                                                              JOIN users u ON c.user_id = u.id 
                                                              WHERE c.event_id = " . $event['id'] . " 
                                                              ORDER BY c.created_at DESC";
                                            $comments_result = $conn->query($comments_query);
                                            
                                            if ($comments_result && $comments_result->num_rows > 0) {
                                                while ($comment = $comments_result->fetch_assoc()) {
                                                    echo '<div class="comment-item">';
                                                    echo '<div class="comment-header">';
                                                    echo '<span><i class="fas fa-user-circle me-1"></i>' . htmlspecialchars($comment['username']) . '</span>';
                                                    echo '<span>' . date('M d, Y H:i', strtotime($comment['created_at'])) . '</span>';
                                                    echo '</div>';
                                                    echo '<div class="comment-content">' . htmlspecialchars($comment['content']) . '</div>';
                                                    echo '</div>';
                                                }
                                            } else {
                                                echo '<p class="text-muted small">No comments yet. Be the first to comment!</p>';
                                            }
                                            ?>
                                        </div>
                                        
                                        <!-- Comment Form -->
                                        <form action="add_comment.php" method="post" class="comment-form">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <div class="input-group">
                                                <input type="text" name="comment" class="form-control form-control-sm" placeholder="Add a comment...">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            No upcoming events at the moment. Check back soon!
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="#" class="btn btn-primary">View All Events</a>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 cta-section">
        <div class="container text-center">
            <h2 class="fw-bold">Stay Connected</h2>
            <p class="lead">Don't miss out on alumni news, events, and opportunities.</p>
            <form class="row g-2 justify-content-center mt-4">
                <div class="col-md-6 col-lg-4">
                    <input type="email" class="form-control form-control-lg" placeholder="Your Email Address">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-light btn-lg">Subscribe</button>
                </div>
            </form>
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
                        <li><a href="#" class="text-white">Home</a></li>
                        <li><a href="#" class="text-white">Events</a></li>
                        <li><a href="#" class="text-white">Gallery</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                        <li><a href="#" class="text-white">Profile</a></li>
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
</body>
</html>