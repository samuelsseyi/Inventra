<?php
/*
* Email Verification Endpoint
* Verifies user accounts using token links sent via email
*/

session_start();
require_once '../config/database.php';

$message = '';
$isSuccess = false;

try {
    $token = isset($_GET['token']) ? trim($_GET['token']) : '';
    if ($token === '') {
        throw new Exception('Invalid verification link.');
    }

    // Find verification record
    $stmt = $conn->prepare("SELECT ev.id, ev.user_id, ev.expires_at, ev.verified_at, u.is_verified FROM email_verifications ev JOIN users u ON u.id = ev.user_id WHERE ev.token = ? LIMIT 1");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $verification = $stmt->get_result()->fetch_assoc();

    if (!$verification) {
        throw new Exception('Verification token not found or already used.');
    }
    if (!empty($verification['verified_at'])) {
        $message = 'This verification link has already been used. You can login now.';
        $isSuccess = true;
    } else {
        $now = new DateTime();
        $expiresAt = new DateTime($verification['expires_at']);
        if ($now > $expiresAt) {
            throw new Exception('Verification link has expired. Please request a new one.');
        }

        // Mark verified
        $conn->begin_transaction();
        $stmt1 = $conn->prepare("UPDATE users SET is_verified = 1, verified_at = NOW() WHERE id = ?");
        $stmt1->bind_param('i', $verification['user_id']);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE email_verifications SET verified_at = NOW() WHERE id = ?");
        $stmt2->bind_param('i', $verification['id']);
        $stmt2->execute();

        $conn->commit();
        $message = 'Your account has been verified successfully. You can login now.';
        $isSuccess = true;
    }
} catch (Exception $e) {
    if ($conn && $conn->errno) {
        $conn->rollback();
    }
    $message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Inventra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    <style>
    .verify-container { max-width: 520px; margin: 10vh auto; background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; }
    .verify-container .status { font-size: 18px; margin-top: 8px; }
    .verify-container .status.success { color: #198754; }
    .verify-container .status.error { color: #dc3545; }
    .verify-container a.btn { display: inline-block; margin-top: 16px; padding: 10px 16px; border-radius: 8px; background: #0d6efd; color: #fff; text-decoration: none; }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    
</head>
<body style="background: linear-gradient(135deg,#0b132b,#1c2541 60%,#3a506b); min-height: 100vh;">
    <div class="verify-container">
        <i class="fa-solid fa-envelope-circle-check fa-3x" style="color:#0d6efd"></i>
        <h3 class="mt-3">Email Verification</h3>
        <p class="status <?php echo $isSuccess ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></p>
        <a class="btn" href="login.php">Go to Login</a>
    </div>
</body>
</html>


