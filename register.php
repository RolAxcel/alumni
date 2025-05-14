<?php
session_start();
require_once 'db_connect.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['is_admin']) {
        header("Location: index.php");
    } else {
        header("Location: public_homepage.php");
    }
    exit();
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $graduation_year = $conn->real_escape_string($_POST['graduation_year']);
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if username already exists
        $check_query = "SELECT id FROM users WHERE username = '$username'";
        $result = $conn->query($check_query);
        
        if ($result->num_rows > 0) {
            $error = "Username already exists. Please choose a different one.";
        } else {
            // Check if email already exists
            $check_email_query = "SELECT id FROM users WHERE email = '$email'";
            $email_result = $conn->query($check_email_query);
            
            if ($email_result->num_rows > 0) {
                $error = "Email already registered. Please use a different email.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $insert_query = "INSERT INTO users (username, email, password, graduation_year, is_admin) 
                                VALUES ('$username', '$email', '$hashed_password', '$graduation_year', 0)";
                
                if ($conn->query($insert_query) === TRUE) {
                    $success = "Registration successful! You can now login.";
                    
                    // Optional: Auto-login after registration
                    // $_SESSION['user_id'] = $conn->insert_id;
                    // $_SESSION['username'] = $username;
                    // $_SESSION['is_admin'] = 0;
                    // header("Location: public_homepage.php");
                    // exit();
                } else {
                    $error = "Error: " . $conn->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Alumni Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            max-width: 500px;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i {
            font-size: 40px;
            color: #0d6efd;
        }
        .logo h3 {
            font-weight: bold;
            margin-top: 10px;
        }
        .btn-register {
            width: 100%;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <h3>Alumni Portal</h3>
            <p class="text-muted">Create Your Account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <div class="mt-2">
                    <a href="login.php" class="btn btn-sm btn-primary">Login Now</a>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="graduation_year" class="form-label">Graduation Year</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    <input type="number" class="form-control" id="graduation_year" name="graduation_year" min="1900" max="<?php echo date('Y'); ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-text">Password must be at least 8 characters long.</div>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-register">
                <i class="fas fa-user-plus me-2"></i> Register
            </button>
        </form>
        
        <div class="text-center mt-4">
            Already have an account? <a href="login.php" class="text-decoration-none">Login here</a>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>