<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - XTrade</title>
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
                    Account Recovery
                </p>
            </div>

            <div class="mb-8 text-center px-4">
                <p class="text-xs text-zinc-500 font-medium leading-relaxed">
                    Enter your email address or username linked to your account. We will send you a secure link to reset your password.
                </p>
                <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
                    <i class="fa-solid fa-shield-halved text-blue-500 mt-0.5 text-xs"></i>
                    <p class="text-[10px] text-blue-600 text-left font-semibold">
                        For 2-Step Verification users, you will need to verify your identity using your backup codes after resetting.
                    </p>
                </div>
            </div>

            <!-- Recovery Form -->
            <form method="POST" action="reset_code.php" class="space-y-4">
                <div class="group">
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1 ml-1 transition-colors group-focus-within:text-black">
                        Identity / Email
                    </label>
                    <input
                        type="text"
                        name="email"
                        class="w-full px-4 py-3 bg-zinc-50 border border-zinc-100 rounded-xl focus:border-black focus:bg-white focus:ring-1 focus:ring-black/5 transition-all duration-300 text-sm font-medium tracking-tight outline-none placeholder:text-zinc-300"
                        placeholder="terminal@tradex.com"
                        required
                    />
                </div>

                <button
                    type="submit"
                    class="w-full py-4 bg-black text-white rounded-xl font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-zinc-800 hover:shadow-lg hover:shadow-black/10 hover:-translate-y-0.5 transition-all duration-300 active:scale-[0.98] flex items-center justify-center space-x-2 mt-2"
                >
                    <span>Send Reset LinK</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="login.php" class="text-[9px] text-zinc-400 font-bold uppercase tracking-widest hover:text-black transition-colors hover:underline">
                    Return to Login
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 text-center text-[9px] text-zinc-300 font-bold uppercase tracking-widest">
        TradeX Security &copy; 2024
    </footer>

</body>
</html>
