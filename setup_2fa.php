<?php
session_start();
require 'config.php';

if (!isset($_SESSION['2fa_new_secret'])) {
    header("Location: index.php");
    exit;
}

$secret = $_SESSION['2fa_new_secret'];
$qr = $_SESSION['2fa_qr'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup 2FA - StockTrader</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f1f5f9;">

    <div class="card auth-card fade-in-up" style="text-align:center; max-width: 500px;">
        <h2>Secure Your Account</h2>
        <p class="subtitle">Scan this QR code with Google Authenticator</p>
        
        <div style="margin: 2rem 0;">
            <img src="<?php echo $qr; ?>" alt="2FA QR Code" style="border:1px solid #ddd; padding:0.5rem; border-radius:0.5rem; background:white;">
            <p style="margin-top:1rem; font-family:monospace; font-size:1.2rem; background:#eee; padding:0.5rem; border-radius:0.25rem;">
                <?php echo $secret; ?>
            </p>
        </div>
        
        <a href="index.php" class="btn btn-primary" style="width:100%">I Have Scanned It</a>
    </div>

</body>
</html>
