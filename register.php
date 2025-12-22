<?php
session_start();
require 'config.php';


$error = '';
$success = '';

// Fetch regions for dropdown
$regions = $pdo->query("SELECT * FROM Region")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $workplace = trim($_POST['workplace'] ?? '');
    $region_id = (int)($_POST['region_id'] ?? 0);

    if (empty($full_name) || empty($password) || empty($workplace) || $region_id <= 0) {
        $error = 'Please fill in all fields.';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE full_name = ?");
        $stmt->execute([$full_name]);
        if ($stmt->fetch()) {
            $error = 'Username already taken.';
        } else {
            try {
                $pdo->beginTransaction();

                // Create User
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Users (full_name, password_hash, workplace, region_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$full_name, $password_hash, $workplace, $region_id]);
                $user_id = $pdo->lastInsertId();

                // Create Account
                $stmt = $pdo->prepare("INSERT INTO Account (user_id, balance) VALUES (?, 0.00)");
                $stmt->execute([$user_id]);

                $pdo->commit();
                
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                
                header("Location: index.php");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Registration failed. Please try again.';
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
    <title>Create Account - StockTrader</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="auth-page">
    <div class="auth-card fade-in-up">
        <div class="auth-header">
            <div class="auth-logo">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
            </div>
            <h2>Create Account</h2>
            <p class="subtitle">Join the trading platform</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 2rem; border-radius: 0.5rem; text-align: center; font-size: 0.9rem">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="auth-form-group">
                <label for="full_name">Full Name (Username)</label>
                <input type="text" id="full_name" name="full_name" class="auth-input" required placeholder="e.g. John Doe">
            </div>

            <div class="auth-form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="auth-input" required placeholder="••••••">
            </div>

            <div class="auth-form-group">
                <label for="workplace">Workplace</label>
                <input type="text" id="workplace" name="workplace" class="auth-input" required placeholder="e.g. Google">
            </div>

            <div class="auth-form-group">
                <label for="region_id">Region</label>
                <select id="region_id" name="region_id" class="auth-input" required>
                    <option value="">Select Region</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?php echo $region['region_id']; ?>">
                            <?php echo htmlspecialchars($region['region_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="auth-btn">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>
