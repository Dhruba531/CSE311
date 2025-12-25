<?php
session_start();
require 'config.php';

// Ensure we have a pending login
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code']);
    $uid = $_SESSION['temp_user_id'];
    
    // Check code in DB
    $stmt = $pdo->prepare("SELECT user_id, full_name, two_factor_code, two_factor_expires_at FROM Users WHERE user_id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['two_factor_code'] === $code) {
            if (strtotime($user['two_factor_expires_at']) > time()) {
                // Success!
                // Clear code
                $p = $pdo->prepare("UPDATE Users SET two_factor_code = NULL, two_factor_expires_at = NULL WHERE user_id = ?");
                $p->execute([$uid]);
                
                // Set Real Session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                unset($_SESSION['temp_user_id']); // clear temp
                
                header("Location: portfolio.php");
                exit;
            } else {
                $error = 'Code has expired. Please login again.';
            }
        } else {
            $error = 'Invalid verification code.';
        }
    } else {
        $error = 'User not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2-Step Verification - XTrade</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #000; --bg: #f4f4f5; --card: #fff; --text: #18181b; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .card { background: var(--card); padding: 2.5rem; border-radius: 1.5rem; width: 100%; max-width: 400px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); text-align:center; }
        h1 { margin-bottom: 0.5rem; font-weight: 800; font-size: 1.5rem; }
        p { color: #71717a; margin-bottom: 2rem; font-size: 0.9rem; }
        input { width: 100%; padding: 1rem; border: 1px solid #e4e4e7; border-radius: 0.75rem; font-size: 1.5rem; text-align: center; letter-spacing: 0.5em; margin-bottom: 1.5rem; outline:none; font-weight:700; }
        input:focus { border-color: black; }
        button { background: black; color: white; border: none; padding: 1rem; width: 100%; border-radius: 0.75rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s; }
        button:hover { opacity: 0.9; }
        .error { color: #ef4444; font-size: 0.875rem; margin-bottom: 1rem; font-weight: 600; }
        .back { display:block; margin-top: 1.5rem; color: #71717a; text-decoration: none; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Verify It's You</h1>
        <p>Enter the 6-digit code sent to your email.</p>
        
        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="code" maxlength="6" placeholder="000000" autofocus required autocomplete="off">
            <button type="submit">Verify</button>
        </form>
        
        <a href="login.php" class="back">Back to Login</a>
    </div>
</body>
</html>
