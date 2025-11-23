<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT s.ticker_symbol, s.company_name, 
           SUM(CASE WHEN t.is_buy THEN t.num_shares ELSE -t.num_shares END) as shares_held,
           AVG(CASE WHEN t.is_buy THEN t.cost_per_share END) as avg_buy_price
    FROM TransactionRecord t
    JOIN Stock s ON t.ticker_symbol = s.ticker_symbol
    WHERE t.user_id = ?
    GROUP BY s.ticker_symbol
    HAVING shares_held > 0
    ORDER BY shares_held DESC
");
$stmt->execute([$user_id]);
$portfolio = $stmt->fetchAll();

// Calculate total portfolio value (simulated current prices)
$total_value = 0;
foreach ($portfolio as &$holding) {
    // Simulate current price (in real app, fetch from API)
    $current_price = ($holding['avg_buy_price'] ?? 100) * (0.8 + (rand(0, 40) / 100)); // Random price variation
    $holding['current_price'] = round($current_price, 2);
    $holding['total_value'] = round($holding['shares_held'] * $current_price, 2);
    $holding['gain_loss'] = round($holding['total_value'] - ($holding['shares_held'] * ($holding['avg_buy_price'] ?? 0)), 2);
    $holding['gain_loss_pct'] = $holding['avg_buy_price'] > 0 
        ? round((($current_price - $holding['avg_buy_price']) / $holding['avg_buy_price']) * 100, 2)
        : 0;
    $total_value += $holding['total_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - Stock Trading</title>
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
                <li><a href="portfolio.php" class="active">Portfolio</a></li>
                <li><a href="history.php">History</a></li>
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
        <h2>My Portfolio</h2>

        <div class="portfolio-summary">
            <div class="summary-card">
                <h3>Total Portfolio Value</h3>
                <p class="summary-value">$<?php echo number_format($total_value, 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Total Holdings</h3>
                <p class="summary-value"><?php echo count($portfolio); ?></p>
            </div>
        </div>

        <div class="card">
            <h3>Holdings</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Symbol</th>
                            <th>Company</th>
                            <th>Shares</th>
                            <th>Avg Buy Price</th>
                            <th>Current Price</th>
                            <th>Total Value</th>
                            <th>Gain/Loss</th>
                            <th>% Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($portfolio)): ?>
                            <tr><td colspan="8" class="empty-state">No holdings yet. Start trading to build your portfolio!</td></tr>
                        <?php else: ?>
                            <?php foreach ($portfolio as $holding): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($holding['ticker_symbol']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($holding['company_name']); ?></td>
                                    <td><?php echo number_format($holding['shares_held']); ?></td>
                                    <td>$<?php echo number_format($holding['avg_buy_price'] ?? 0, 2); ?></td>
                                    <td>$<?php echo number_format($holding['current_price'], 2); ?></td>
                                    <td>$<?php echo number_format($holding['total_value'], 2); ?></td>
                                    <td class="<?php echo $holding['gain_loss'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        $<?php echo number_format($holding['gain_loss'], 2); ?>
                                    </td>
                                    <td class="<?php echo $holding['gain_loss_pct'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $holding['gain_loss_pct'] >= 0 ? '+' : ''; ?><?php echo number_format($holding['gain_loss_pct'], 2); ?>%
                                    </td>
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
