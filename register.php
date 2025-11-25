<?php
session_start();
require 'config.php';
require 'includes/csrf.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $workplace = trim($_POST['workplace'] ?? '');
        $region_id = (int) ($_POST['region_id'] ?? 0);

        if (empty($full_name) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all required fields.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE full_name = ?");
            $stmt->execute([$full_name]);
            if ($stmt->fetch()) {
                $error = 'User with this name already exists.';
            } else {
                try {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO Users (full_name, password_hash, workplace, region_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$full_name, $password_hash, $workplace, $region_id]);

                    $user_id = $pdo->lastInsertId();
                    $pdo->prepare("INSERT INTO Account (user_id, balance) VALUES (?, 10000.00)")->execute([$user_id]);

                    $success = 'Registration successful! You can now login.';
                } catch (PDOException $e) {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

// Get regions for dropdown
$stmt = $pdo->query("SELECT * FROM Region ORDER BY region_name");
$regions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Stock Trading</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="auth-page dark-theme">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-container">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 12L12 3L21 12L12 21L3 12Z" stroke="currentColor" stroke-width="2" fill="none"/>
                    </svg>
                    <span class="logo-text">StockTrader</span>
                </div>
                <h2>Create Your Account</h2>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <p class="auth-footer">
                    <a href="login.php">Go to Login</a>
                </p>
            <?php else: ?>
                <form method="POST" action="register.php" class="auth-form">
                    <?php csrf_field(); ?>
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required placeholder="At least 6 characters"
                            minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                            placeholder="Re-enter your password" minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="workplace">Workplace</label>
                        <input type="text" id="workplace" name="workplace" placeholder="Enter your workplace"
                            value="<?php echo htmlspecialchars($_POST['workplace'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="region_id">Region</label>
                        <select id="region_id" name="region_id" required>
                            <option value="">Select a region</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo $region['region_id']; ?>" <?php echo (isset($_POST['region_id']) && $_POST['region_id'] == $region['region_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($region['region_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>

                <p class="auth-footer">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>