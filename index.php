<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    // Show Landing Page if not logged in
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>App - Trade Stocks with Confidence</title>
        <link rel="stylesheet" href="styles.css">
    </head>

    <body class="landing-page">
        <!-- Header -->
        <header class="landing-header">
            <div class="header-container">
                <div class="logo-container">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 18L7 12L11 15L15 8L19 11L21 9" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                    <span class="logo-text">App</span>
                </div>
                <a href="login.php" class="btn-signin">Sign In</a>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-container">
                <h1 class="hero-title">Trade Stocks with Confidence</h1>
                <p class="hero-description">
                    A modern platform for buying and selling stocks. Track your portfolio, manage your watchlist, and
                    execute trades in real-time.
                </p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn-primary">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 18L7 12L11 15L15 8L19 11L21 9" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        </svg>
                        Get Started
                    </a>
                    <a href="learn_more.php" class="btn-secondary">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section id="features" class="features-section">
            <div class="features-container">
                <div class="feature-item">
                    <div class="feature-number">1000+</div>
                    <div class="feature-label">Stocks Available</div>
                </div>
                <div class="feature-item">
                    <div class="feature-number">24/7</div>
                    <div class="feature-label">Market Access</div>
                </div>
                <div class="feature-item">
                    <div class="feature-number">100%</div>
                    <div class="feature-label">Secure</div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="landing-footer">
            <div class="footer-container">
                <p>&copy; 2025 App. All rights reserved.</p>
            </div>
        </footer>
    </body>

    </html>
    <?php
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
    SELECT ticker_symbol,
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

// Get top gainers and losers
$top_gainers = $pdo->query("
    SELECT s.ticker_symbol, s.company_name, sp.current_price, sp.previous_close,
           ((sp.current_price - sp.previous_close) / sp.previous_close * 100) as change_pct,
           (sp.current_price - sp.previous_close) as change_amount
    FROM StockPrice sp
    JOIN Stock s ON sp.ticker_symbol = s.ticker_symbol
    WHERE sp.previous_close > 0 AND sp.current_price IS NOT NULL
    ORDER BY change_pct DESC
    LIMIT 5
")->fetchAll();

$top_losers = $pdo->query("
    SELECT s.ticker_symbol, s.company_name, sp.current_price, sp.previous_close,
           ((sp.current_price - sp.previous_close) / sp.previous_close * 100) as change_pct,
           (sp.current_price - sp.previous_close) as change_amount
    FROM StockPrice sp
    JOIN Stock s ON sp.ticker_symbol = s.ticker_symbol
    WHERE sp.previous_close > 0 AND sp.current_price IS NOT NULL
    ORDER BY change_pct ASC
    LIMIT 5
")->fetchAll();
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

<body class="dashboard-page">
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">
                <span class="logo-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 18L7 12L11 15L15 8L19 11L21 9" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="7" cy="12" r="1.5" fill="currentColor" />
                        <circle cx="11" cy="15" r="1.5" fill="currentColor" />
                        <circle cx="15" cy="8" r="1.5" fill="currentColor" />
                        <circle cx="19" cy="11" r="1.5" fill="currentColor" />
                    </svg>
                </span>
                <span class="logo-text">StockTrader</span>
            </h1>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="stocks.php">Stocks</a></li>
                <li><a href="buy_sell.php">Trade</a></li>
                <li><a href="orders.php">Orders</a></li>
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
        <div class="welcome-section fade-in-up">
            <h2>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p class="subtitle">Manage your investments and track your portfolio</p>
        </div>

        <div class="stats-grid fade-in-up" style="animation-delay: 0.1s;">
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
        <div class="charts-grid fade-in-up" style="animation-delay: 0.2s;">
            <div class="card">
                <h3>Portfolio History</h3>
                <canvas id="historyChart"></canvas>
            </div>
            <div class="card">
                <h3>Asset Allocation</h3>
                <div style="height: 300px; position: relative; display: flex; justify-content: center;">
                    <canvas id="allocationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Market Movers -->
        <div class="charts-grid fade-in-up" style="animation-delay: 0.25s;">
            <div class="card">
                <h3>🚀 Top Gainers</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Price</th>
                                <th>Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_gainers)): ?>
                                <tr>
                                    <td colspan="3" class="empty-state">No data available</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($top_gainers as $stock): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($stock['ticker_symbol']); ?></strong>
                                            <br><small
                                                style="color: #94a3b8;"><?php echo htmlspecialchars($stock['company_name']); ?></small>
                                        </td>
                                        <td>$<?php echo number_format($stock['current_price'], 2); ?></td>
                                        <td style="color: #22c55e; font-weight: 600;">
                                            ▲ $<?php echo number_format($stock['change_amount'], 2); ?>
                                            (<?php echo number_format($stock['change_pct'], 2); ?>%)
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>📉 Top Losers</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Price</th>
                                <th>Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_losers)): ?>
                                <tr>
                                    <td colspan="3" class="empty-state">No data available</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($top_losers as $stock): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($stock['ticker_symbol']); ?></strong>
                                            <br><small
                                                style="color: #94a3b8;"><?php echo htmlspecialchars($stock['company_name']); ?></small>
                                        </td>
                                        <td>$<?php echo number_format($stock['current_price'], 2); ?></td>
                                        <td style="color: #ef4444; font-weight: 600;">
                                            ▼ $<?php echo number_format(abs($stock['change_amount']), 2); ?>
                                            (<?php echo number_format($stock['change_pct'], 2); ?>%)
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="content-grid fade-in-up" style="animation-delay: 0.3s;">
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
                                <tr>
                                    <td colspan="3" class="empty-state">No holdings yet</td>
                                </tr>
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
                                <tr>
                                    <td colspan="5" class="empty-state">No transactions yet</td>
                                </tr>
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
        const historyData = <?php echo json_encode(array_map(function ($item) {
            return ['date' => $item['snapshot_date'], 'value' => $item['total_value']];
        }, $history_data)); ?>;

        const allocationData = <?php echo json_encode(array_map(function ($item) {
            return ['symbol' => $item['ticker_symbol'], 'value' => $item['total_value']];
        }, $top_holdings)); ?>;

        // Common Chart Options for Dark Theme
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';

        // History Chart
        const ctxHistory = document.getElementById('historyChart').getContext('2d');
        const gradientHistory = ctxHistory.createLinearGradient(0, 0, 0, 400);
        gradientHistory.addColorStop(0, 'rgba(37, 99, 235, 0.5)');
        gradientHistory.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        new Chart(ctxHistory, {
            type: 'line',
            data: {
                labels: historyData.map(d => d.date),
                datasets: [{
                    label: 'Portfolio Value',
                    data: historyData.map(d => d.value),
                    borderColor: '#3b82f6',
                    backgroundColor: gradientHistory,
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
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
                        '#3b82f6', '#8b5cf6', '#ec4899', '#f97316', '#22c55e',
                        '#06b6d4', '#6366f1', '#a855f7', '#d946ef', '#eab308'
                    ],
                    borderColor: '#0f172a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { usePointStyle: true, padding: 20 }
                    }
                },
                cutout: '70%',
                maintainAspectRatio: false
            }
        });
    </script>
</body>

</html>