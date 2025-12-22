<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id, full_name, password_hash FROM Users WHERE full_name = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - StockTrader</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="auth-page">
    <div class="auth-card fade-in-up">
        <div class="auth-header">
            <div class="auth-logo">
                <!-- Zigzag icon similar to reference -->
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
            </div>
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 2rem; border-radius: 0.5rem; text-align: center; font-size: 0.9rem">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="auth-form-group">
                <label for="username">Full Name (Username)</label>
                <input type="text" id="username" name="username" class="auth-input" required autofocus value="Dhruba Saha">
            </div>

            <div class="auth-form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="auth-input" required placeholder="••••••">
            </div>

            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>
</body>
</html>
