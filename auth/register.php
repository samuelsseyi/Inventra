<?php
/*
* Inventra - Registration Page
* Handles new business registration
*/

session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $business_name = trim($_POST['business_name']);
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($business_name)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email address is already registered.";
        } else {
            // Attempt to register
            $new_user_id = registerUser($username, $email, $password, $business_name);
            if ($new_user_id) {
                // Auto-login after successful registration
                $stmt2 = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt2->bind_param("i", $new_user_id);
                $stmt2->execute();
                $user = $stmt2->get_result()->fetch_assoc();
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['business_name'] = $user['business_name'];
                    $_SESSION['business_code'] = $user['business_code'];
                    $_SESSION['user_role'] = $user['role'];
                    header("Location: ../index.php?page=dashboard");
                    exit();
                } else {
                    $success = "Registration successful. Verification email sent. Please login.";
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Inventra</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="text-center mb-4">
                <img src="../assets/images/logo-white.png" alt="Inventra Logo" class="auth-logo light-logo">
                <img src="../assets/images/logo-black.png" alt="Inventra Logo" class="auth-logo dark-logo">
                <h1>Create Account</h1>
                <p class="subtitle">Start managing your inventory today</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="business_name">
                        <i class="fas fa-store"></i>
                        Business Name
                    </label>
                    <input type="text" id="business_name" name="business_name" required 
                           placeholder="Enter your business name"
                           value="<?php echo isset($_POST['business_name']) ? htmlspecialchars($_POST['business_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter your full name"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required 
                               placeholder="Create a password">
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Confirm Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm your password">
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </div>
                
                <div class="auth-links">
                    <span>Already have an account?</span>
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt"></i>
                        Login Here
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Toggle password visibility for both password fields
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html> 