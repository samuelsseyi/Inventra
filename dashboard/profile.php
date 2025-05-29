<?php
$page_title = 'Profile';
$current_page = 'profile';

require_once 'config/database.php';
require_once 'includes/functions.php';

$error = null;
$success = null;

// Get user data
$stmt = $conn->prepare("SELECT username, email, business_name, business_code FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $business_name = trim($_POST['business_name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    try {
        // Verify current password if trying to change password
        if (!empty($new_password)) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $stored_password = $result->fetch_assoc()['password'];
            
            if (!password_verify($current_password, $stored_password)) {
                throw new Exception("Current password is incorrect.");
            }
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Update basic info
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, business_name = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $business_name, $_SESSION['user_id']);
        $stmt->execute();
        
        // Update password if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            $stmt->execute();
        }
        
        $conn->commit();
        $success = "Profile updated successfully!";
        
        // Update session data
        $_SESSION['username'] = $username;
        
        // Refresh user data
        $stmt = $conn->prepare("SELECT username, email, business_name, business_code FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Start output buffering
ob_start();
?>

<!-- Profile Content -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="dashboard-card">
            <h5 class="mb-4">Profile Settings</h5>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Business Name</label>
                    <input type="text" class="form-control" name="business_name" value="<?php echo htmlspecialchars($user['business_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Business Code</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['business_code']); ?>" readonly>
                </div>

                <hr class="my-4">

                <h6 class="mb-3">Change Password</h6>

                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" name="current_password">
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password">
                    <div class="form-text">Leave blank to keep current password</div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php
$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?> 