<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get portfolio analytics using stored procedure
try {
    $stmt = $pdo->prepare("CALL sp_get_portfolio_analytics(?)");
    $stmt->execute([$user_id]);
    $analytics = $stmt->fetch();
    $stmt->closeCursor();
} catch (PDOException $e) {
    $analytics = null;
}

// Get top performing stocks
$stmt = $pdo->prepare("
    SELECT s.ticker_symbol, s.company_name,
           SUM(CASE WHEN t.is_buy THEN t.num_shares ELSE -t.num_shares END) as shares_held,
           AVG(CASE WHEN t.is_buy THEN t.cost_per_share END) as avg_price,
           sp.current_price,
           (sp.current_price - AVG(CASE WHEN t.is_buy THEN t.cost_per_share END)) as gain_per_share,
           ((sp.current_price - AVG(CASE WHEN t.is_buy THEN t.cost_per_share END)) / 
            AVG(CASE WHEN t.is_buy THEN t.cost_per_share END) * 100) as gain_percent
    FROM TransactionRecord t
    JOIN Stock s ON t.ticker_symbol = s.ticker_symbol
    LEFT JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol
    WHERE t.user_id = ?
    GROUP BY s.ticker_symbol
    HAVING shares_held > 0
    ORDER BY gain_percent DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$top_performers = $stmt->fetchAll();

// Get worst performers
$stmt = $pdo->prepare("
    SELECT s.ticker_symbol, s.company_name,
           SUM(CASE WHEN t.is_buy THEN t.num_shares ELSE -t.num_shares END) as shares_held,
           AVG(CASE WHEN t.is_buy THEN t.cost_per_share END) as avg_price,
           sp.current_price,
           (sp.current_price - AVG(CASE WHEN t.is_buy THEN t.cost_per_share END)) as gain_per_share,
           ((sp.current_price - AVG(CASE WHEN t.is_buy THEN t.cost_per_share END)) / 
            AVG(CASE WHEN t.is_buy THEN t.cost_per_share END) * 100) as gain_percent
    FROM TransactionRecord t
    JOIN Stock s ON t.ticker_symbol = s.ticker_symbol
    LEFT JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol
    WHERE t.user_id = ?
    GROUP BY s.ticker_symbol
    HAVING shares_held > 0
    ORDER BY gain_percent ASC
    LIMIT 5
");
$stmt->execute([$user_id]);
$worst_performers = $stmt->fetchAll();

// Get portfolio snapshot history
$stmt = $pdo->prepare("
    SELECT * FROM PortfolioSnapshot
    WHERE user_id = ?
    ORDER BY snapshot_date DESC
    LIMIT 30
");
$stmt->execute([$user_id]);
$snapshots = $stmt->fetchAll();

// Get transaction statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN is_buy = 1 THEN 1 ELSE 0 END) as buy_count,
        SUM(CASE WHEN is_buy = 0 THEN 1 ELSE 0 END) as sell_count,
        SUM(CASE WHEN is_buy = 1 THEN num_shares * cost_per_share ELSE 0 END) as total_bought,
        SUM(CASE WHEN is_buy = 0 THEN num_shares * cost_per_share ELSE 0 END) as total_sold,
        MIN(transaction_time) as first_transaction,
        MAX(transaction_time) as last_transaction
    FROM TransactionRecord
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$tx_stats = $stmt->fetch();

// Get account balance
$stmt = $pdo->prepare("SELECT SUM(balance) as total_balance FROM Account WHERE user_id = ?");
$stmt->execute([$user_id]);
$account = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Stock Trading</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="dashboard-page">
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">
                <span class="logo-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 18L7 12L11 15L15 8L19 11L21 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="7" cy="12" r="1.5" fill="currentColor"/>
                        <circle cx="11" cy="15" r="1.5" fill="currentColor"/>
                        <circle cx="15" cy="8" r="1.5" fill="currentColor"/>
                        <circle cx="19" cy="11" r="1.5" fill="currentColor"/>
                    </svg>
                </span>
                <span class="logo-text">StockTrader</span>
            </h1>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="stocks.php">Stocks</a></li>
                <li><a href="buy_sell.php">Trade</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
                <li><a href="history.php">History</a></li>
                <li><a href="watchlist.php">Watchlist</a></li>
                <li><a href="alerts.php">Alerts</a></li>
                <li><a href="analytics.php" class="active">Analytics</a></li>
                <li><a href="account.php">Account</a></li>
                <li><a href="friends.php">Friends</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Portfolio Analytics</h2>

        <?php if ($analytics): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3>Total Stocks</h3>
                        <p class="stat-value"><?php echo $analytics['total_stocks'] ?? 0; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>Portfolio Value</h3>
                        <p class="stat-value">$<?php echo number_format($analytics['total_value'] ?? 0, 2); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💵</div>
                    <div class="stat-info">
                        <h3>Total Cost</h3>
                        <p class="stat-value">$<?php echo number_format($analytics['total_cost'] ?? 0, 2); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-info">
                        <h3>Gain/Loss</h3>
                        <p
                            class="stat-value <?php echo ($analytics['total_gain_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            $<?php echo number_format($analytics['total_gain_loss'] ?? 0, 2); ?>
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📉</div>
                    <div class="stat-info">
                        <h3>Gain/Loss %</h3>
                        <p
                            class="stat-value <?php echo ($analytics['gain_loss_percent'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ($analytics['gain_loss_percent'] ?? 0) >= 0 ? '+' : ''; ?>
                            <?php echo number_format($analytics['gain_loss_percent'] ?? 0, 2); ?>%
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💳</div>
                    <div class="stat-info">
                        <h3>Cash Balance</h3>
                        <p class="stat-value">$<?php echo number_format($account['total_balance'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="content-grid">
            <div class="card">
                <h3>Top Performers</h3>
                <div class="table-container">
                    <table id="topPerformersTable">
                        <thead>
                            <tr>
                                <th class="sortable" onclick="sortTable(0, 'topPerformersTable')">Symbol</th>
                                <th class="sortable" onclick="sortTable(1, 'topPerformersTable')">Company</th>
                                <th class="sortable" onclick="sortTable(2, 'topPerformersTable')">Shares</th>
                                <th class="sortable" onclick="sortTable(3, 'topPerformersTable')">Avg Price</th>
                                <th class="sortable" onclick="sortTable(4, 'topPerformersTable')">Current</th>
                                <th class="sortable" onclick="sortTable(5, 'topPerformersTable')">Gain/Loss</th>
                                <th class="sortable" onclick="sortTable(6, 'topPerformersTable')">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_performers)): ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No holdings yet</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($top_performers as $stock): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($stock['ticker_symbol']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($stock['company_name']); ?></td>
                                        <td><?php echo number_format($stock['shares_held']); ?></td>
                                        <td>$<?php echo number_format($stock['avg_price'] ?? 0, 2); ?></td>
                                        <td>$<?php echo number_format($stock['current_price'] ?? 0, 2); ?></td>
                                        <td
                                            class="<?php echo ($stock['gain_per_share'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            $<?php echo number_format($stock['gain_per_share'] ?? 0, 2); ?>
                                        </td>
                                        <td
                                            class="<?php echo ($stock['gain_percent'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($stock['gain_percent'] ?? 0) >= 0 ? '+' : ''; ?>
                                            <?php echo number_format($stock['gain_percent'] ?? 0, 2); ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>Worst Performers</h3>
                <div class="table-container">
                    <table id="worstPerformersTable">
                        <thead>
                            <tr>
                                <th class="sortable" onclick="sortTable(0, 'worstPerformersTable')">Symbol</th>
                                <th class="sortable" onclick="sortTable(1, 'worstPerformersTable')">Company</th>
                                <th class="sortable" onclick="sortTable(2, 'worstPerformersTable')">Shares</th>
                                <th class="sortable" onclick="sortTable(3, 'worstPerformersTable')">Avg Price</th>
                                <th class="sortable" onclick="sortTable(4, 'worstPerformersTable')">Current</th>
                                <th class="sortable" onclick="sortTable(5, 'worstPerformersTable')">Gain/Loss</th>
                                <th class="sortable" onclick="sortTable(6, 'worstPerformersTable')">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($worst_performers)): ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No holdings yet</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($worst_performers as $stock): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($stock['ticker_symbol']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($stock['company_name']); ?></td>
                                        <td><?php echo number_format($stock['shares_held']); ?></td>
                                        <td>$<?php echo number_format($stock['avg_price'] ?? 0, 2); ?></td>
                                        <td>$<?php echo number_format($stock['current_price'] ?? 0, 2); ?></td>
                                        <td
                                            class="<?php echo ($stock['gain_per_share'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            $<?php echo number_format($stock['gain_per_share'] ?? 0, 2); ?>
                                        </td>
                                        <td
                                            class="<?php echo ($stock['gain_percent'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($stock['gain_percent'] ?? 0) >= 0 ? '+' : ''; ?>
                                            <?php echo number_format($stock['gain_percent'] ?? 0, 2); ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Transaction Statistics</h3>
            <div class="info-list">
                <div class="info-item">
                    <strong>Total Transactions:</strong>
                    <?php echo number_format($tx_stats['total_transactions'] ?? 0); ?>
                </div>
                <div class="info-item">
                    <strong>Buy Orders:</strong> <?php echo number_format($tx_stats['buy_count'] ?? 0); ?>
                </div>
                <div class="info-item">
                    <strong>Sell Orders:</strong> <?php echo number_format($tx_stats['sell_count'] ?? 0); ?>
                </div>
                <div class="info-item">
                    <strong>Total Bought:</strong> $<?php echo number_format($tx_stats['total_bought'] ?? 0, 2); ?>
                </div>
                <div class="info-item">
                    <strong>Total Sold:</strong> $<?php echo number_format($tx_stats['total_sold'] ?? 0, 2); ?>
                </div>
                <div class="info-item">
                    <strong>First Transaction:</strong>
                    <?php echo $tx_stats['first_transaction'] ? date('M d, Y H:i', strtotime($tx_stats['first_transaction'])) : 'N/A'; ?>
                </div>
                <div class="info-item">
                    <strong>Last Transaction:</strong>
                    <?php echo $tx_stats['last_transaction'] ? date('M d, Y H:i', strtotime($tx_stats['last_transaction'])) : 'N/A'; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($snapshots)): ?>
            <div class="card">
                <h3>Portfolio Performance History (Last 30 Days)</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Value</th>
                                <th>Total Cost</th>
                                <th>Gain/Loss</th>
                                <th>Gain/Loss %</th>
                                <th>Cash Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($snapshots as $snapshot): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($snapshot['snapshot_date'])); ?></td>
                                    <td>$<?php echo number_format($snapshot['total_value'], 2); ?></td>
                                    <td>$<?php echo number_format($snapshot['total_cost'], 2); ?></td>
                                    <td
                                        class="<?php echo $snapshot['total_gain_loss'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        $<?php echo number_format($snapshot['total_gain_loss'], 2); ?>
                                    </td>
                                    <td
                                        class="<?php echo $snapshot['gain_loss_percent'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $snapshot['gain_loss_percent'] >= 0 ? '+' : ''; ?>
                                        <?php echo number_format($snapshot['gain_loss_percent'], 2); ?>%
                                    </td>
                                    <td>$<?php echo number_format($snapshot['cash_balance'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function sortTable(n, tableId) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById(tableId);
            switching = true;
            dir = "asc";

            var headers = table.getElementsByTagName("th");
            for (i = 0; i < headers.length; i++) {
                headers[i].classList.remove("asc", "desc");
            }

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];

                    var xVal = x.textContent.replace(/[$,%]/g, '').trim();
                    var yVal = y.textContent.replace(/[$,%]/g, '').trim();

                    if (!isNaN(parseFloat(xVal)) && !isNaN(parseFloat(yVal))) {
                        xVal = parseFloat(xVal);
                        yVal = parseFloat(yVal);
                    } else {
                        xVal = xVal.toLowerCase();
                        yVal = yVal.toLowerCase();
                    }

                    if (dir == "asc") {
                        if (xVal > yVal) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (xVal < yVal) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
            headers[n].classList.add(dir);
        }
    </script>
</body>

</html>