<?php
session_start();
require 'config.php';
require_login();

$msg = '';
$uid = $_SESSION['user_id'];

// Handle Toggle
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $newState = ($action === 'enable') ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE Users SET is_2fa_enabled = ? WHERE user_id = ?");
    $stmt->execute([$newState, $uid]);
    
    $_SESSION['message'] = "2FA has been " . ($newState ? "enabled" : "disabled") . ".";
    header("Location: setup_2fa.php");
    exit;
}

// Get Current Status
$stmt = $pdo->prepare("SELECT is_2fa_enabled FROM Users WHERE user_id = ?");
$stmt->execute([$uid]);
$status = $stmt->fetchColumn();
$isEnabled = (bool)$status;

$page_title = 'Security Settings';
require 'includes/header.php';
require 'includes/nav.php';
?>

<div class="flex-between mb-4">
    <h1>Security Settings</h1>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <div style="background:#dcfce7; color:#166534; padding:15px; border-radius:1rem; margin-bottom:20px; font-weight:600;">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<div class="card-premium" style="max-width: 600px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <div>
            <h3 style="margin-bottom:0.5rem; font-size:1.2rem;">Email 2-Step Verification</h3>
            <p style="color:var(--text-secondary); font-size:0.9rem;">
                Receive a 6-digit code via email every time you log in.
            </p>
        </div>
        
        <form method="POST">
            <?php if ($isEnabled): ?>
                <input type="hidden" name="action" value="disable">
                <button class="btn-primary" style="background:var(--danger);">Disable 2FA</button>
            <?php else: ?>
                <input type="hidden" name="action" value="enable">
                <button class="btn-primary" style="background:#000;">Enable 2FA</button>
            <?php endif; ?>
        </form>
    </div>
    
    <div style="background: #f4f4f5; padding: 1rem; border-radius: 0.75rem; font-size: 0.85rem; color: var(--text-secondary);">
        <strong>status:</strong> <?= $isEnabled ? '<span style="color:green; font-weight:bold;">ACTIVE</span>' : 'Inactive' ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
