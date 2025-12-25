<?php
session_start();
$email = $_POST['email'] ?? '';

if (empty($email)) {
    header("Location: forgot_password.php");
    exit;
}

// Generate Demo OTP
$otp = rand(100000, 999999);
$_SESSION['reset_otp'] = $otp;
$_SESSION['reset_email'] = $email;

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - XTrade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-screen flex flex-col bg-zinc-50 overflow-hidden">
    
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-[400px] p-8 bg-white border border-zinc-100 rounded-[2rem] shadow-2xl shadow-zinc-200/50 animate-in fade-in zoom-in-95 duration-500">
            
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-black tracking-tighter mb-1 uppercase">
                    Security Check
                </h1>
                <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-[0.4em]">
                    Verify Identity
                </p>
            </div>

            <!-- Demo OTP Display -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-100 rounded-xl flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-yellow-600 uppercase tracking-wider">Demo Access Code</p>
                    <p class="text-2xl font-mono font-bold text-yellow-800 tracking-widest mt-1"><?php echo $otp; ?></p>
                </div>
                <div class="text-[10px] text-yellow-600/70 max-w-[100px] text-right leading-tight">
                    *Use this code below to proceed
                </div>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 text-center text-xs text-red-500 font-bold uppercase tracking-wide">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="verify_reset.php" class="space-y-4">
                <div class="group">
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                        Enter 6-Digit Code
                    </label>
                    <input
                        type="text"
                        name="code"
                        maxlength="6"
                        class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all duration-300 text-center font-mono text-xl font-bold tracking-[0.5em] outline-none placeholder:tracking-normal placeholder:font-sans placeholder:text-zinc-300"
                        placeholder="000000"
                        required
                        autofocus
                    />
                </div>

                <button
                    type="submit"
                    class="w-full py-4 bg-black text-white rounded-xl font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-zinc-800 transition-all duration-300 flex items-center justify-center space-x-2 mt-2"
                >
                    <span>Verify & Continue</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="forgot_password.php" class="text-[9px] text-zinc-400 font-bold uppercase tracking-widest hover:text-black transition-colors hover:underline">
                    Resend Code
                </a>
            </div>
        </div>
    </div>

</body>
</html>
