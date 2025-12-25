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
        $stmt = $pdo->prepare("SELECT user_id, full_name, email, password_hash, is_2fa_enabled FROM Users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            
            // Check if 2FA is enabled
            if ($user['is_2fa_enabled']) {
                // Generate 6-digit code
                $code = rand(100000, 999999);
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // Store code in DB
                $stmt = $pdo->prepare("UPDATE Users SET two_factor_code = ?, two_factor_expires_at = ? WHERE user_id = ?");
                $stmt->execute([$code, $expiry, $user['user_id']]);
                
                // Send Verification Email
                require_once 'includes/email_helper.php';
                $subject = "XTrade Verification Code";
                $body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background: #f4f4f5; text-align: center;'>
                    <div style='background: white; padding: 30px; border-radius: 10px; max-width: 400px; margin: auto;'>
                        <h2 style='color: #000; margin-bottom: 10px;'>Verify It's You</h2>
                        <p style='color: #666; font-size: 14px;'>Use the code below to sign in to XTrade.</p>
                        <div style='background: #000; color: #fff; font-size: 24px; font-weight: bold; padding: 15px; border-radius: 5px; margin: 20px 0; letter-spacing: 5px;'>
                            $code
                        </div>
                        <p style='color: #999; font-size: 12px;'>Valid for 10 minutes.</p>
                    </div>
                </div>";
                
                send_email($user['email'], $subject, $body);
                
                // Set session for verification
                $_SESSION['temp_user_id'] = $user['user_id'];
                header("Location: verify_2fa.php");
                exit;
            } else {
                // Standard Login
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                header("Location: portfolio.php");
                exit;
            }
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
    <title>XTrade Terminal Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-screen flex flex-col bg-zinc-50 overflow-hidden">
    
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-[400px] p-8 bg-white border border-zinc-100 rounded-[2rem] shadow-2xl shadow-zinc-200/50 animate-in fade-in zoom-in-95 duration-500 hover:shadow-cyan-500/10 transition-shadow duration-500">
            
            <!-- Header -->
            <div class="mb-6 text-center">
                <h1 class="text-4xl font-black tracking-tighter mb-1 uppercase">
                    Trade<span class="text-zinc-300">X</span>
                </h1>
                <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-[0.4em]">
                    Institutional Access
                </p>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-600 rounded-xl text-[10px] font-bold text-center uppercase tracking-wide">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Social Login (Side by Side) -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <button type="button" class="py-3 px-4 border border-zinc-100 rounded-xl flex items-center justify-center space-x-2 hover:bg-zinc-50 hover:border-zinc-200 hover:scale-[1.02] transition-all font-bold text-[9px] uppercase tracking-wider group">
                    <i class="fa-brands fa-google text-sm group-hover:text-red-500 transition-colors"></i>
                    <span>Google</span>
                </button>
                <button type="button" class="py-3 px-4 border border-zinc-100 rounded-xl flex items-center justify-center space-x-2 hover:bg-zinc-50 hover:border-zinc-200 hover:scale-[1.02] transition-all font-bold text-[9px] uppercase tracking-wider group">
                    <i class="fa-brands fa-apple text-sm group-hover:text-black transition-colors"></i>
                    <span>Apple</span>
                </button>
            </div>

            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-zinc-100"></span>
                </div>
                <div class="relative flex justify-center text-[9px] uppercase">
                    <span class="bg-white px-3 text-zinc-300 font-bold tracking-widest">or Login with</span>
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="login.php" class="space-y-4">
                <div class="group">
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                        Identity
                    </label>
                    <input
                        type="text"
                        name="username"
                        class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all duration-300 text-sm font-medium tracking-tight outline-none placeholder:text-zinc-300"
                        placeholder="terminal@tradex.com"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                    />
                </div>

                <div class="group">
                    <div class="flex justify-between items-center mb-1 ml-1">
                        <label class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 transition-colors group-focus-within:text-black">
                            Passcode
                        </label>
                        <a href="forgot_password.php" class="text-[9px] font-bold text-zinc-300 hover:text-black hover:underline uppercase tracking-wider transition-colors">
                            Reset
                        </a>
                    </div>
                    <input
                        type="password"
                        name="password"
                        class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all duration-300 text-sm font-medium outline-none placeholder:text-zinc-300"
                        placeholder="••••••••"
                        required
                    />
                </div>

                <button
                    type="submit"
                    class="w-full py-4 bg-black text-white rounded-xl font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-zinc-800 hover:shadow-lg hover:shadow-black/10 hover:-translate-y-0.5 transition-all duration-300 active:scale-[0.98] flex items-center justify-center space-x-2 mt-2"
                >
                    <span>Authorize</span>
                </button>
            </form>


            <div class="mt-6 text-center">
                <a href="register.php" class="text-[9px] text-zinc-400 font-bold uppercase tracking-widest hover:text-black transition-colors hover:underline">
                    Create New Access ID
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 text-center text-[9px] text-zinc-300 font-bold uppercase tracking-widest">
        TradeX Terminal &copy; 2024
    </footer>

</body>
</html>
