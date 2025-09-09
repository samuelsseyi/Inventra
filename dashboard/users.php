<?php
$page_title = 'User Management';
$current_page = 'users';

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied';
    exit();
}

$error = null;
$success = null;

// Debug: Show current user role
$current_user_role = $_SESSION['user_role'] ?? 'not set';

// Handle create user (within same business)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'cashier');
    $password = $_POST['password'] ?? '';

    try {
        if ($username === '' || $email === '' || $password === '') {
            throw new Exception('All fields are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address.');
        }

        // Ensure unique email
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Email already in use.');
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $businessName = $_SESSION['business_name'];
        $businessCode = $_SESSION['business_code'];

        $stmt = $conn->prepare('INSERT INTO users (username, email, password, business_name, business_code, role, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())');
        $stmt->bind_param('ssssss', $username, $email, $hashed, $businessName, $businessCode, $role);
        $stmt->execute();
        $success = 'User created successfully.';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch users in same business
$users = [];
$stmt = $conn->prepare('SELECT id, username, email, role, is_verified, created_at FROM users WHERE business_code = ? ORDER BY created_at DESC');
$stmt->bind_param('s', $_SESSION['business_code']);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

ob_start();
?>
<div class="row g-4">
    <div class="col-md-5">
        <div class="dashboard-card">
            <h5 class="mb-3">Add User</h5>
            <div class="alert alert-info">
                <strong>Debug Info:</strong> Your current role is: <code><?php echo htmlspecialchars($current_user_role); ?></code>
            </div>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role * <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" title="Admin: Full access. Manager: Stock + cashier management. Cashier: Sales only."></i></label>
                    <select class="form-select" name="role" required>
                        <option value="manager">Manager</option>
                        <option value="cashier">Cashier</option>
                    </select>
                    <div class="form-text">
                        <strong>Role Permissions:</strong><br>
                        • <strong>Admin:</strong> Full system access including user management<br>
                        • <strong>Manager:</strong> Stock management + cashier oversight<br>
                        • <strong>Cashier:</strong> Sales recording only
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Temporary Password *</label>
                    <input type="password" class="form-control" name="password" minlength="6" required>
                    <div class="form-text">Share this with the user; they can change it later.</div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i>Create User</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="dashboard-card">
            <h5 class="mb-3">Team Members</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Verified</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($u['role'])); ?></span></td>
                            <td><?php echo ((int)$u['is_verified'] === 1) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>'; ?></td>
                            <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$page_content = ob_get_clean();
require_once 'templates/dashboard_template.php';
?>


