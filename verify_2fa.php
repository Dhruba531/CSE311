<?php
session_start();
require 'config.php';
require 'includes/GoogleAuthenticator.php';

$error = '';

if (!isset($_SESSION['partial_user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code']);
    $uid = $_SESSION['partial_user_id'];
    
    // Fetch secret
    $stmt = $pdo->prepare("SELECT two_factor_secret FROM Users WHERE user_id = ?");
    $stmt->execute([$uid]);
    $secret = $stmt->fetchColumn();
    
    $g2fa = new PHPGangsta_GoogleAuthenticator();
    if ($g2fa->verifyCode($secret, $code)) {
        // Success
        $_SESSION['user_id'] = $uid;
        $_SESSION['full_name'] = $_SESSION['partial_full_name'];
        unset($_SESSION['partial_user_id']);
        unset($_SESSION['partial_full_name']);
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify 2FA - StockTrader</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .code-input {
            letter-spacing: 0.5rem;
            font-size: 2rem;
            text-align: center;
            width: 100%;
        }
    </style>
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f1f5f9;">

    <div class="card auth-card fade-in-up" style="max-width: 400px; text-align: center;">
        <h2>Two-Factor Authentication</h2>
        <p class="subtitle">Enter the 6-digit code from your app</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" style="margin-top: 2rem;">
            <div class="form-group">
                <input type="text" name="code" class="code-input" maxlength="6" pattern="[0-9]*" inputmode="numeric" required autofocus placeholder="000000">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Verify</button>
        </form>
        
        <div style="margin-top: 1.5rem;">
            <a href="login.php" style="color: var(--text-muted); font-size: 0.9rem;">Back to Login</a>
        </div>
    </div>

</body>
</html>
