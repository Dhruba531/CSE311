<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Verify Code
    if (isset($_POST['code'])) {
        $input_code = trim($_POST['code']);
        if ($input_code == $_SESSION['reset_otp']) {
            // Code Correct - Show Password Reset Form
            $_SESSION['otp_verified'] = true;
        } else {
            header("Location: reset_code.php?error=Invalid Code");
            exit;
        }
    }
    
    // 2. Handle Password Update
    if (isset($_POST['new_password'])) {
        if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
            die("Unauthorized access");
        }
        
        $new_pass = $_POST['new_password'];
        $email = $_SESSION['reset_email'];
        
        // Update DB
        // Determine if input was username or email to find correct user
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
            $update->execute([$hash, $user['user_id']]);
            
            // Clear session and redirect
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);
            
            header("Location: login.php?msg=Password Updated");
            exit;
        } else {
            $error = "User not found.";
        }
    }
} else {
    // If accessing directly without POST
    if (!isset($_SESSION['otp_verified'])) {
        header("Location: forgot_password.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password - XTrade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-screen flex flex-col bg-zinc-50 overflow-hidden">
    
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-[400px] p-8 bg-white border border-zinc-100 rounded-[2rem] shadow-2xl shadow-zinc-200/50 animate-in fade-in zoom-in-95 duration-500">
            


            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold tracking-tight mb-1 uppercase">
                    Reset Password
                </h1>
                <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-[0.4em]">
                    Create New Password
                </p>
            </div>

            <form method="POST" action="verify_reset.php" class="space-y-4">
                <div class="group">
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                        New Password
                    </label>
                    <input
                        type="password"
                        name="new_password"
                        class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all duration-300 text-sm font-medium outline-none"
                        placeholder="••••••••"
                        required
                        autofocus
                    />
                </div>

                <div class="group">
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                        Confirm Password
                    </label>
                    <input
                        type="password"
                        name="confirm_password"
                        class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all duration-300 text-sm font-medium outline-none"
                        placeholder="••••••••"
                        required
                    />
                </div>

                <button
                    type="submit"
                    class="w-full py-4 bg-black text-white rounded-xl font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-zinc-800 transition-all duration-300 flex items-center justify-center space-x-2 mt-2"
                >
                    <span>Update Password</span>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
