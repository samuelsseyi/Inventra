<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$msg = '';
$err = '';

try {
    $stmt = $conn->prepare('SELECT id, username, email, is_verified FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        throw new Exception('User not found.');
    }
    if ((int)$user['is_verified'] === 1) {
        $msg = 'Your email is already verified.';
    } else {
        // Invalidate previous tokens for this user
        $stmt = $conn->prepare('DELETE FROM email_verifications WHERE user_id = ? AND verified_at IS NULL');
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();

        // Create new token
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTime('+1 day'))->format('Y-m-d H:i:s');
        $stmt = $conn->prepare('INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $user['id'], $token, $expiresAt);
        $stmt->execute();
        $conn->query('UPDATE users SET verification_sent_at = NOW() WHERE id = ' . (int)$user['id']);

        // Build verify URL
        $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $verifyUrl = $base . $basePath . '/verify.php?token=' . urlencode($token);

        $subject = 'Verify your Inventra account';
        $message = '<p>Hello ' . htmlspecialchars($user['username']) . ',</p>' .
                   '<p>Please verify your email to activate your account.</p>' .
                   '<p><a href="' . $verifyUrl . '">Click here to verify</a></p>' .
                   '<p>This link will expire in 24 hours.</p>';
        sendEmail($user['email'], $subject, $message);
        $msg = 'Verification email has been resent.';
    }
} catch (Exception $e) {
    $err = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification - Inventra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-3">Email Verification</h5>
                        <?php if ($err): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
                        <?php else: ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
                        <?php endif; ?>
                        <a href="../index.php?page=profile" class="btn btn-primary mt-2"><i class="fas fa-arrow-left me-1"></i>Back to Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


