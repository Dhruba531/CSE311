<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT tr.*, s.company_name, e.exchange_name, e.short_code, a.account_id
    FROM TransactionRecord tr
    JOIN Stock s ON tr.ticker_symbol = s.ticker_symbol
    JOIN Exchange e ON tr.exchange_id = e.exchange_id
    JOIN Account a ON tr.account_id = a.account_id
    WHERE tr.user_id = ?
    ORDER BY tr.transaction_time DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();

// Calculate totals
$total_bought = 0;
$total_sold = 0;
foreach ($history as $tx) {
    $value = $tx['num_shares'] * $tx['cost_per_share'];
    if ($tx['is_buy']) {
        $total_bought += $value;
    } else {
        $total_sold += $value;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Stock Trading</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">📈 StockTrader</h1>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="stocks.php">Stocks</a></li>
                <li><a href="buy_sell.php">Trade</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
                <li><a href="history.php" class="active">History</a></li>
                <li><a href="watchlist.php">Watchlist</a></li>
                <li><a href="alerts.php">Alerts</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="account.php">Account</a></li>
                <li><a href="friends.php">Friends</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Transaction History</h2>

        <div class="history-summary">
            <div class="summary-card">
                <h3>Total Bought</h3>
                <p class="summary-value text-success">$<?php echo number_format($total_bought, 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Total Sold</h3>
                <p class="summary-value text-danger">$<?php echo number_format($total_sold, 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Total Transactions</h3>
                <p class="summary-value"><?php echo count($history); ?></p>
            </div>
        </div>

        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Symbol</th>
                            <th>Company</th>
                            <th>Exchange</th>
                            <th>Shares</th>
                            <th>Price/Share</th>
                            <th>Total Value</th>
                            <th>Account</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr><td colspan="9" class="empty-state">No transaction history yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($history as $tx): ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i:s', strtotime($tx['transaction_time'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $tx['is_buy'] ? 'badge-buy' : 'badge-sell'; ?>">
                                            <?php echo $tx['is_buy'] ? 'BUY' : 'SELL'; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($tx['ticker_symbol']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($tx['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($tx['short_code']); ?></td>
                                    <td><?php echo number_format($tx['num_shares']); ?></td>
                                    <td>$<?php echo number_format($tx['cost_per_share'], 2); ?></td>
                                    <td><strong>$<?php echo number_format($tx['num_shares'] * $tx['cost_per_share'], 2); ?></strong></td>
                                    <td>#<?php echo $tx['account_id']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
