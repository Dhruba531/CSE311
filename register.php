<?php
session_start();
require 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');

    if (empty($full_name) || empty($password) || empty($email) || empty($username)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check if user exists (by email or username)
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = 'Username or Email already taken.';
        } else {
            try {
                $pdo->beginTransaction();

                // Create User
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO Users (username, full_name, email, password_hash, is_2fa_enabled) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $full_name, $email, $password_hash, $enable_2fa]);
                $user_id = $pdo->lastInsertId();

                // Create Account with default balance
                $stmt = $pdo->prepare("INSERT INTO Accounts (user_id, balance, buying_power) VALUES (?, 10000.00, 10000.00)"); 
                $stmt->execute([$user_id]);

                $pdo->commit();
                
                if ($enable_2fa) {
                    // Start 2FA Flow
                    $code = rand(100000, 999999);
                    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                    
                    $stmt = $pdo->prepare("UPDATE Users SET two_factor_code = ?, two_factor_expires_at = ? WHERE user_id = ?");
                    $stmt->execute([$code, $expires, $user_id]);
                    
                    // Send Verification Email
                    require_once 'includes/email_helper.php';
                    $subject = "Welcome to XTrade - Verify Your Account";
                    $body = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; background: #f4f4f5; text-align: center;'>
                        <div style='background: white; padding: 30px; border-radius: 10px; max-width: 400px; margin: auto;'>
                            <h2 style='color: #000; margin-bottom: 10px;'>Verify It's You</h2>
                            <p style='color: #666; font-size: 14px;'>Use the code below to complete your registration.</p>
                            <div style='background: #000; color: #fff; font-size: 24px; font-weight: bold; padding: 15px; border-radius: 5px; margin: 20px 0; letter-spacing: 5px;'>
                                $code
                            </div>
                            <p style='color: #999; font-size: 12px;'>Valid for 10 minutes.</p>
                        </div>
                    </div>";
                    
                    send_email($email, $subject, $body);
                    
                    $_SESSION['temp_user_id'] = $user_id;
                    header("Location: verify_2fa.php");
                } else {
                    // Standard Login
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['full_name'] = $full_name;
                    header("Location: portfolio.php");
                }
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['mock_social'])) {
    // Handle Mock Social Login Link
    $provider = $_GET['mock_social']; // google or apple
    $email = "user_$provider" . rand(100,999) . "@example.com";
    $username = "$provider" . "_user_" . rand(100,999);
    $full_name = ucfirst($provider) . " User";
    
    // Check if exists or create
    // Simple mock: just create a new random one for demo purposes or reuse if email collision (unlikely with rand)
    // Actually, let's just create one.
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO Users (username, full_name, email, password_hash) VALUES (?, ?, ?, ?)");
        // Dummy hash
        $stmt->execute([$username, $full_name, $email, password_hash('social_pw', PASSWORD_DEFAULT)]);
        $uid = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO Accounts (user_id, balance, buying_power) VALUES (?, 10000.00, 10000.00)")->execute([$uid]);
        $pdo->commit();
        
        $_SESSION['user_id'] = $uid;
        $_SESSION['full_name'] = $full_name;
        header("Location: portfolio.php");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Social Login Error: " . $e->getMessage();
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XTrade - New Account Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen flex flex-col bg-zinc-50 overflow-hidden">
    
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-[400px] p-8 bg-white border border-zinc-100 rounded-[2rem] shadow-2xl shadow-zinc-200/50 animate-in fade-in zoom-in-95 duration-500">
            
            <!-- Header -->
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-black tracking-tighter mb-1 uppercase">
                    Trade<span class="text-zinc-300">X</span>
                </h1>
                <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-[0.4em]">
                    Create New Account
                </p>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-600 rounded-xl text-[10px] font-bold text-center uppercase tracking-wide">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="space-y-4">
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="group">
                        <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                            Full Name
                        </label>
                        <input type="text" name="full_name" class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all text-sm font-medium outline-none" required placeholder="John Doe">
                    </div>
                    <div class="group">
                         <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                            Username
                        </label>
                        <input type="text" name="username" class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all text-sm font-medium outline-none" required placeholder="jdoe">
                    </div>
                </div>

                <div class="group">
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                        Email Address
                    </label>
                    <input type="email" name="email" class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all text-sm font-medium outline-none" required placeholder="john@example.com">
                </div>

                <div class="group">
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                        Password
                    </label>
                    <input type="password" name="password" class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all text-sm font-medium outline-none" required placeholder="••••••••">
                </div>

                <div class="flex items-center space-x-2 pl-1">
                    <input type="checkbox" name="enable_2fa" id="enable_2fa" class="w-4 h-4 border-zinc-300 rounded focus:ring-black">
                    <label for="enable_2fa" class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 cursor-pointer select-none">
                        Enable 2-Step Verification
                    </label>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full py-4 bg-black text-white rounded-xl font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-zinc-800 transition-all duration-300 flex items-center justify-center space-x-2"
                    >
                        <span>Create Account</span>
                    </button>
                </div>

                <!-- Social Login -->
                <div class="relative flex py-4 items-center">
                    <div class="flex-grow border-t border-zinc-200"></div>
                    <span class="flex-shrink-0 mx-4 text-[9px] font-bold text-zinc-400 uppercase tracking-widest">Or continue with</span>
                    <div class="flex-grow border-t border-zinc-200"></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <a href="?mock_social=google" class="flex items-center justify-center w-full py-3 bg-white border border-zinc-200 rounded-xl hover:bg-zinc-50 transition-colors cursor-pointer">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
                    </a>
                     <a href="?mock_social=apple" class="flex items-center justify-center w-full py-3 bg-white border border-zinc-200 rounded-xl hover:bg-zinc-50 transition-colors cursor-pointer">
                        <i class="fab fa-apple text-xl"></i>
                    </a>
                </div>

            </form>

            <div class="mt-6 text-center">
                <a href="login.php" class="text-[9px] text-zinc-400 font-bold uppercase tracking-widest hover:text-black transition-colors hover:underline">
                    Already have access? Sign In
                </a>
            </div>

        </div>
    </div>
</body>
</html>
