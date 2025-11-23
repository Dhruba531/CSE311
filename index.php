<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get account balance
$stmt = $pdo->prepare("SELECT SUM(balance) as total_balance FROM Account WHERE user_id = ?");
$stmt->execute([$user_id]);
$account = $stmt->fetch();

// Get portfolio stats
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT ticker_symbol) as total_stocks,
           SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END) as total_shares
    FROM TransactionRecord
    WHERE user_id = ?
    GROUP BY ticker_symbol
    HAVING total_shares > 0
");
$stmt->execute([$user_id]);
$portfolio_stats = $stmt->fetchAll();
$total_stocks = count($portfolio_stats);

// Get recent transactions
$stmt = $pdo->prepare("
    SELECT tr.*, s.company_name, e.exchange_name
    FROM TransactionRecord tr
    JOIN Stock s ON tr.ticker_symbol = s.ticker_symbol
    JOIN Exchange e ON tr.exchange_id = e.exchange_id
    WHERE tr.user_id = ?
    ORDER BY tr.transaction_time DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_transactions = $stmt->fetchAll();

// Get top holdings with value
$stmt = $pdo->prepare("
    SELECT s.ticker_symbol, s.company_name,
           SUM(CASE WHEN t.is_buy THEN t.num_shares ELSE -t.num_shares END) as shares_held,
           sp.current_price,
           (SUM(CASE WHEN t.is_buy THEN t.num_shares ELSE -t.num_shares END) * COALESCE(sp.current_price, 0)) as total_value
    FROM TransactionRecord t
    JOIN Stock s ON t.ticker_symbol = s.ticker_symbol
    LEFT JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol
    WHERE t.user_id = ?
    GROUP BY s.ticker_symbol
    HAVING shares_held > 0
    ORDER BY total_value DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$top_holdings = $stmt->fetchAll();

// Get portfolio history for chart
$stmt = $pdo->prepare("
    SELECT snapshot_date, total_value 
    FROM PortfolioSnapshot 
    WHERE user_id = ? 
    ORDER BY snapshot_date ASC 
    LIMIT 30
");
$stmt->execute([$user_id]);
$history_data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Trading Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">📈 StockTrader</h1>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="stocks.php">Stocks</a></li>
                <li><a href="buy_sell.php">Trade</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
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
        <div class="welcome-section">
            <h2>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p class="subtitle">Manage your investments and track your portfolio</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>Total Balance</h3>
                    <p class="stat-value">$<?php echo number_format($account['total_balance'] ?? 0, 2); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3>Stocks Owned</h3>
                    <p class="stat-value"><?php echo $total_stocks; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-info">
                    <h3>Total Shares</h3>
                    <p class="stat-value"><?php echo array_sum(array_column($portfolio_stats, 'total_shares')); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-info">
                    <h3>Workplace</h3>
                    <p class="stat-value"><?php echo htmlspecialchars($user['workplace'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <div class="card">
                <h3>Portfolio History</h3>
                <canvas id="historyChart"></canvas>
            </div>
            <div class="card">
                <h3>Asset Allocation</h3>
                <canvas id="allocationChart"></canvas>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
                <h3>Top Holdings</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Company</th>
                                <th>Shares</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_holdings)): ?>
                                <tr><td colspan="3" class="empty-state">No holdings yet</td></tr>
                            <?php else: ?>
                                <?php foreach ($top_holdings as $holding): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($holding['ticker_symbol']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($holding['company_name']); ?></td>
                                        <td><?php echo number_format($holding['shares_held']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>Recent Transactions</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Type</th>
                                <th>Shares</th>
                                <th>Price</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_transactions)): ?>
                                <tr><td colspan="5" class="empty-state">No transactions yet</td></tr>
                            <?php else: ?>
                                <?php foreach ($recent_transactions as $tx): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($tx['ticker_symbol']); ?></strong></td>
                                        <td><span class="badge <?php echo $tx['is_buy'] ? 'badge-buy' : 'badge-sell'; ?>">
                                            <?php echo $tx['is_buy'] ? 'BUY' : 'SELL'; ?>
                                        </span></td>
                                        <td><?php echo number_format($tx['num_shares']); ?></td>
                                        <td>$<?php echo number_format($tx['cost_per_share'], 2); ?></td>
                                        <td><?php echo date('M d, H:i', strtotime($tx['transaction_time'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prepare data for charts
        const historyData = <?php echo json_encode(array_map(function($item) {
            return ['date' => $item['snapshot_date'], 'value' => $item['total_value']];
        }, $history_data)); ?>;

        const allocationData = <?php echo json_encode(array_map(function($item) {
            return ['symbol' => $item['ticker_symbol'], 'value' => $item['total_value']];
        }, $top_holdings)); ?>;

        // History Chart
        const ctxHistory = document.getElementById('historyChart').getContext('2d');
        new Chart(ctxHistory, {
            type: 'line',
            data: {
                labels: historyData.map(d => d.date),
                datasets: [{
                    label: 'Portfolio Value',
                    data: historyData.map(d => d.value),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: false }
                }
            }
        });

        // Allocation Chart
        const ctxAllocation = document.getElementById('allocationChart').getContext('2d');
        new Chart(ctxAllocation, {
            type: 'doughnut',
            data: {
                labels: allocationData.map(d => d.symbol),
                datasets: [{
                    data: allocationData.map(d => d.value),
                    backgroundColor: [
                        '#2563eb', '#7c3aed', '#db2777', '#ea580c', '#16a34a',
                        '#0891b2', '#4f46e5', '#9333ea', '#c026d3', '#d97706'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    </script>
</body>
</html>

