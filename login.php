<?php
session_start();
require 'config.php';
require 'includes/csrf.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($full_name) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE full_name = ?");
            $stmt->execute([$full_name]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid credentials. Please try again.';
            }
        } else {
            $error = 'Please fill in all fields.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stock Trading</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">📈 StockTrader</h1>
            <h2>Login to Your Account</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="auth-form">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</body>

</html>