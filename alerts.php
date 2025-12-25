<?php
session_start();
require 'config.php';
require_login();

// Add Alert Login
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $t = $_POST['ticker'];
    $p = $_POST['price'];
    $u = $_SESSION['user_id'];
    
    // Check if stock exists
    $stmt = $pdo->prepare("SELECT 1 FROM Stocks WHERE ticker_symbol = ?");
    $stmt->execute([$t]);
    if ($stmt->fetch()) {
        $pdo->prepare("INSERT INTO Alerts (user_id, ticker_symbol, target_price) VALUES (?, ?, ?)")->execute([$u, $t, $p]);
        $msg = "Alert set for $t at $$p";
    } else {
        $msg = "Invalid ticker.";
    }
}

// Fetch Alerts
$stmt = $pdo->prepare("SELECT * FROM Alerts WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$alerts = $stmt->fetchAll();

$page_title = 'Price Alerts';
require 'includes/header.php';
require 'includes/nav.php';
?>
<div class="flex-between mb-4">
    <h1>Price Alerts</h1>
</div>

<?php if (isset($msg)) echo "<div class='alert'>$msg</div>"; ?>

<div style="display: grid; grid-template-columns: 350px 1fr; gap: 24px; align-items: start;">
    <!-- Create Alert Card -->
    <div class="card-premium">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Create Alert</h3>
        <form method="POST">
            <div class="form-group mb-4">
                <label style="display:block; font-size:0.85rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.5rem;">Ticker Symbol</label>
                <div style="position:relative;">
                    <input type="text" name="ticker" placeholder="e.g. AAPL" required 
                           style="width:100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 0.75rem; font-family: 'Inter'; font-size: 0.95rem; outline: none; transition: all 0.2s;">
                </div>
            </div>
            
            <div class="form-group mb-4">
                <label style="display:block; font-size:0.85rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.5rem;">Target Price ($)</label>
                <div style="position:relative;">
                    <input type="number" name="price" placeholder="0.00" step="0.01" required 
                           style="width:100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 0.75rem; font-family: 'Inter'; font-size: 0.95rem; outline: none; transition: all 0.2s;">
                </div>
            </div>
            
            <button class="btn-primary" style="width:100%; padding: 12px; font-size: 1rem; margin-top: 1rem;">
                <i class="fas fa-bell"></i> Set Alert
            </button>
        </form>
    </div>
    
    <!-- Active Alerts Card -->
    <div class="card-premium">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Active Alerts</h3>
        <?php if (empty($alerts)): ?>
            <div style="text-align:center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                <p>No active alerts set.</p>
            </div>
        <?php else: ?>
            <table class="table-minimal">
                <thead>
                    <tr>
                        <th style="width: 20%">Ticker</th>
                        <th style="width: 30%">Target Price</th>
                        <th style="width: 30%">Current Status</th>
                        <th style="width: 20%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($alerts as $a): ?>
                    <tr>
                        <td style="font-weight: 700;"><?= htmlspecialchars($a['ticker_symbol']) ?></td>
                        <td style="font-family:'Inter';">$<?= number_format($a['target_price'], 2) ?></td>
                        <td>
                            <span class="badge" style="background:#dcfce7; color:#166534; padding:4px 12px; border-radius:99px; font-size:0.75rem; font-weight:700;">
                                <?= htmlspecialchars($a['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button style="border:none; background:none; color:var(--danger); cursor:pointer; font-size:0.9rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
