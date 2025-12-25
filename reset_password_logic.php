<?php
// Placeholder for password reset logic
// In a real application, this would verify the user and send an email.

// For now, let's just simulate a success message and redirect back to login.
$email = htmlspecialchars($_POST['email'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Link Sent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-screen flex items-center justify-center bg-zinc-50">
    <div class="text-center p-8 bg-white border border-zinc-100 rounded-[2rem] shadow-xl max-w-sm">
        <div class="w-16 h-16 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
            <i class="fa-solid fa-check"></i>
        </div>
        <h2 class="text-xl font-bold mb-2">Check your Email</h2>
        <p class="text-sm text-zinc-500 mb-6">
            We've sent a reset link to <strong><?php echo $email; ?></strong>.<br>
            Please check your inbox and spam folder.
        </p>
        <a href="login.php" class="block w-full py-3 bg-black text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-zinc-800 transition-all">
            Return to Login
        </a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
