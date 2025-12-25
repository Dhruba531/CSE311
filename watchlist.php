<?php
session_start();
require 'config.php';
require_login();

$page_title = 'My Watchlist';
require 'includes/header.php';
require 'includes/nav.php';
?>
<?php
// Handle Remove Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_ticker'])) {
    $r_ticker = $_POST['remove_ticker'];
    $stmt = $pdo->prepare("DELETE FROM Watchlist WHERE user_id = ? AND ticker_symbol = ?");
    $stmt->execute([$_SESSION['user_id'], $r_ticker]);
    $msg = "Removed $r_ticker from watchlist.";
}

// Fetch Watchlist
$stmt = $pdo->prepare("
    SELECT w.ticker_symbol, s.stock_name, s.current_price, s.sector 
    FROM Watchlist w
    JOIN Stocks s ON w.ticker_symbol = s.ticker_symbol
    WHERE w.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$watchlist = $stmt->fetchAll();
?>

<div class="flex-between mb-4">
    <h1>My Watchlist</h1>
    <a href="stocks.php" class="btn-primary"><i class="fas fa-plus"></i> Add Symbols</a>
</div>

<?php if (isset($msg)): ?>
    <div style="background: #dcfce7; color: #166534; padding: 12px 20px; border-radius: 1rem; margin-bottom: 20px; font-weight: 600;">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="card-premium">
    <?php if (empty($watchlist)): ?>
        <div style="text-align:center; padding: 40px; color: var(--text-secondary);">
            <i class="fas fa-list-ul" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <p class="mb-4">Your watchlist is empty.</p>
            <a href="stocks.php" class="btn-primary" style="display:inline-block;">Browse Market</a>
        </div>
    <?php else: ?>
        <table class="table-minimal">
            <thead>
                <tr>
                    <th>Symbol</th>
                    <th>Company</th>
                    <th>Price</th>
                    <th>Sector</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($watchlist as $w): ?>
                <tr class="hover:bg-zinc-50">
                    <td style="font-weight: 700;"><?= htmlspecialchars($w['ticker_symbol']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($w['stock_name']) ?></td>
                    <td style="font-family:'Inter'; font-weight:600;">$<?= number_format($w['current_price'], 2) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($w['sector']) ?></span></td>
                    <td style="text-align: right;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="remove_ticker" value="<?= htmlspecialchars($w['ticker_symbol']) ?>">
                            <button type="submit" style="background:none; border:none; color:var(--danger); cursor:pointer; padding:5px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require 'includes/footer.php'; ?>
