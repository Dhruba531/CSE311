<?php
session_start();
require 'config.php';
require 'includes/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle order operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $order_id = (int) ($_POST['order_id'] ?? 0);

        if ($action === 'cancel' && $order_id > 0) {
            try {
                // Check if order belongs to user and is pending
                $stmt = $pdo->prepare("
                    SELECT * FROM OrderType 
                    WHERE order_id = ? AND user_id = ? AND status = 'PENDING'
                ");
                $stmt->execute([$order_id, $user_id]);
                $order = $stmt->fetch();

                if (!$order) {
                    $error = 'Order not found or cannot be cancelled.';
                } else {
                    // Cancel the order
                    $stmt = $pdo->prepare("
                        UPDATE OrderType 
                        SET status = 'CANCELLED' 
                        WHERE order_id = ? AND user_id = ?
                    ");
                    $stmt->execute([$order_id, $user_id]);
                    $message = 'Order cancelled successfully.';
                }
            } catch (PDOException $e) {
                $error = 'Failed to cancel order.';
            }
        }
    }
}

// Get user's orders
$status_filter = $_GET['status'] ?? 'all';
$query = "
    SELECT ot.*, s.company_name, sp.current_price
    FROM OrderType ot
    JOIN Stock s ON ot.ticker_symbol = s.ticker_symbol
    LEFT JOIN StockPrice sp ON ot.ticker_symbol = sp.ticker_symbol
    WHERE ot.user_id = ?
";

$params = [$user_id];

if ($status_filter !== 'all') {
    $query .= " AND ot.status = ?";
    $params[] = strtoupper($status_filter);
}

$query .= " ORDER BY ot.created_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'EXECUTED' THEN 1 ELSE 0 END) as executed_orders,
        SUM(CASE WHEN status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled_orders
    FROM OrderType
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Stock Trading</title>
    <link rel="stylesheet" href="styles.css">
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
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="stocks.php">Stocks</a></li>
                <li><a href="buy_sell.php">Trade</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
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
        <div class="page-header">
            <h2>Order Management</h2>
            <a href="buy_sell.php" class="btn btn-primary">Place New Order</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Order Statistics -->
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3>Total Orders</h3>
                    <p class="stat-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3>Pending</h3>
                    <p class="stat-value"><?php echo number_format($stats['pending_orders'] ?? 0); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>Executed</h3>
                    <p class="stat-value"><?php echo number_format($stats['executed_orders'] ?? 0); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-info">
                    <h3>Cancelled</h3>
                    <p class="stat-value"><?php echo number_format($stats['cancelled_orders'] ?? 0); ?></p>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card" style="margin-bottom: 20px; padding: 15px;">
            <form method="GET" action="orders.php" style="display: flex; gap: 10px; align-items: center;">
                <label for="status" style="font-weight: bold;">Filter by Status:</label>
                <select name="status" id="status" onchange="this.form.submit()" style="flex: 1; max-width: 200px;">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Orders</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="executed" <?php echo $status_filter === 'executed' ? 'selected' : ''; ?>>Executed</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                </select>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <?php if (empty($orders)): ?>
                <div class="empty-state">No orders found. <a href="buy_sell.php">Place your first order</a></div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Symbol</th>
                                <th>Company</th>
                                <th>Type</th>
                                <th>Action</th>
                                <th>Quantity</th>
                                <th>Limit Price</th>
                                <th>Stop Price</th>
                                <th>Current Price</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Expiry</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($order['ticker_symbol']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['company_name']); ?></td>
                                    <td>
                                        <span class="badge badge-buy">
                                            <?php echo str_replace('_', ' ', $order['order_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $order['action_type'] === 'BUY' ? 'badge-buy' : 'badge-sell'; ?>">
                                            <?php echo $order['action_type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($order['quantity'], 6); ?></td>
                                    <td>
                                        <?php echo $order['limit_price'] ? '$' . number_format($order['limit_price'], 2) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo $order['stop_price'] ? '$' . number_format($order['stop_price'], 2) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo $order['current_price'] ? '$' . number_format($order['current_price'], 2) : 'N/A'; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($order['status']) {
                                            case 'PENDING':
                                                $status_class = 'badge-buy';
                                                break;
                                            case 'EXECUTED':
                                                $status_class = 'badge-success';
                                                break;
                                            case 'CANCELLED':
                                            case 'EXPIRED':
                                                $status_class = 'badge-sell';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['created_date'])); ?></td>
                                    <td>
                                        <?php echo $order['expiry_date'] ? date('M d, Y H:i', strtotime($order['expiry_date'])) : 'GTC'; ?>
                                    </td>
                                    <td>
                                        <?php if ($order['status'] === 'PENDING'): ?>
                                            <form method="POST" style="display:inline;" 
                                                onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Order Type Explanations -->
        <div class="card" style="margin-top: 20px;">
            <h3>Order Type Guide</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div>
                    <strong>Market Order</strong>
                    <p style="color: #94a3b8; font-size: 0.9em; margin-top: 5px;">
                        Executes immediately at the current market price.
                    </p>
                </div>
                <div>
                    <strong>Limit Order</strong>
                    <p style="color: #94a3b8; font-size: 0.9em; margin-top: 5px;">
                        Executes only at your specified price or better (lower for buy, higher for sell).
                    </p>
                </div>
                <div>
                    <strong>Stop-Loss Order</strong>
                    <p style="color: #94a3b8; font-size: 0.9em; margin-top: 5px;">
                        Triggers when price reaches stop level, then executes as market order to limit losses.
                    </p>
                </div>
                <div>
                    <strong>Stop-Limit Order</strong>
                    <p style="color: #94a3b8; font-size: 0.9em; margin-top: 5px;">
                        Triggers at stop price, then executes as limit order at your specified limit price.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

