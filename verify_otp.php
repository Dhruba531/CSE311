<?php
session_start();
require 'config.php';
require 'includes/csrf.php';

if (!isset($_SESSION['temp_user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $otp_code = trim($_POST['otp_code'] ?? '');

        if (empty($otp_code)) {
            $error = 'Please enter the verification code.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
            $stmt->execute([$_SESSION['temp_user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                if ($user['otp_code'] === $otp_code && strtotime($user['otp_expiry']) > time()) {
                    // OTP Valid
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    unset($_SESSION['temp_user_id']);

                    // Clear OTP
                    $stmt = $pdo->prepare("UPDATE Users SET otp_code = NULL, otp_expiry = NULL WHERE user_id = ?");
                    $stmt->execute([$user['user_id']]);

                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid or expired verification code.';
                }
            } else {
                $error = 'User not found.';
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
    <title>Verify OTP - Stock Trading</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">🔐 Two-Factor Auth</h1>
            <p style="text-align: center; margin-bottom: 20px;">
                Please enter the 6-digit code sent to your email (simulated in <code>email_log.txt</code>).
            </p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="verify_otp.php" class="auth-form">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="otp_code">Verification Code</label>
                    <input type="text" id="otp_code" name="otp_code" required placeholder="123456" maxlength="6"
                        pattern="\d{6}" style="letter-spacing: 4px; font-size: 1.2rem; text-align: center;">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Verify</button>
            </form>

            <p class="auth-footer">
                <a href="login.php">Back to Login</a>
            </p>
        </div>
    </div>
</body>

</html>