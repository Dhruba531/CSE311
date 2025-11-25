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

// Handle buy/sell transaction
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $order_type = strtoupper($_POST['order_type'] ?? 'MARKET');
        $ticker_symbol = trim($_POST['ticker_symbol'] ?? '');
        $num_shares = (float) ($_POST['num_shares'] ?? 0);
        $cost_per_share = (float) ($_POST['cost_per_share'] ?? 0);
        $limit_price = (float) ($_POST['limit_price'] ?? 0);
        $stop_price = (float) ($_POST['stop_price'] ?? 0);
        $exchange_id = (int) ($_POST['exchange_id'] ?? 0);
        $account_id = (int) ($_POST['account_id'] ?? 0);
        $expiry_date = $_POST['expiry_date'] ?? null;

        // Validate action - must be explicitly 'buy' or 'sell'
        if ($action !== 'buy' && $action !== 'sell') {
            $error = 'Invalid action. Must be either "buy" or "sell".';
        } elseif (empty($ticker_symbol) || $num_shares <= 0 || $exchange_id <= 0 || $account_id <= 0) {
            $error = 'Please fill in all required fields correctly.';
        } elseif (!in_array($order_type, ['MARKET', 'LIMIT', 'STOP_LOSS', 'STOP_LIMIT'])) {
            $error = 'Invalid order type.';
        } elseif (($order_type === 'LIMIT' || $order_type === 'STOP_LIMIT') && $limit_price <= 0) {
            $error = 'Limit price is required for LIMIT and STOP_LIMIT orders.';
        } elseif (($order_type === 'STOP_LOSS' || $order_type === 'STOP_LIMIT') && $stop_price <= 0) {
            $error = 'Stop price is required for STOP_LOSS and STOP_LIMIT orders.';
        } elseif ($order_type === 'MARKET' && $cost_per_share <= 0) {
            $error = 'Price per share is required for MARKET orders.';
        } else {
            try {
                $pdo->beginTransaction();

                // Get account balance
                $stmt = $pdo->prepare("SELECT balance FROM Account WHERE account_id = ? AND user_id = ?");
                $stmt->execute([$account_id, $user_id]);
                $account = $stmt->fetch();

                if (!$account) {
                    throw new Exception('Invalid account.');
                }

                // For MARKET orders, execute immediately
                if ($order_type === 'MARKET') {
                    $total_cost = $num_shares * $cost_per_share;

                    if ($action === 'buy') {
                        // Check balance before transaction
                        if ($account['balance'] < $total_cost) {
                            throw new Exception('Insufficient balance.');
                        }
                    } else { // sell
                        // Check if user has enough shares
                        $stmt = $pdo->prepare("
                            SELECT SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END) as shares_held
                            FROM TransactionRecord
                            WHERE user_id = ? AND ticker_symbol = ?
                            GROUP BY ticker_symbol
                        ");
                        $stmt->execute([$user_id, $ticker_symbol]);
                        $holding = $stmt->fetch();

                        if (!$holding || $holding['shares_held'] < $num_shares) {
                            throw new Exception('Insufficient shares to sell.');
                        }
                    }

                    // Execute market order immediately
                    $is_buy = ($action === 'buy') ? 1 : 0;
                    $stmt = $pdo->prepare("
                        INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares, exchange_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$user_id, $account_id, $ticker_symbol, $is_buy, $cost_per_share, $num_shares, $exchange_id]);

                    $pdo->commit();
                    $message = ucfirst($action) . ' order executed successfully!';
                } else {
                    // For advanced orders, create pending order
                    $action_type = strtoupper($action);
                    $expiry_datetime = null;
                    if ($expiry_date) {
                        $expiry_datetime = date('Y-m-d H:i:s', strtotime($expiry_date));
                    }

                    // For LIMIT orders, use limit_price; for STOP orders, use stop_price
                    $price_to_check = ($order_type === 'LIMIT' || $order_type === 'STOP_LIMIT') ? $limit_price : $stop_price;

                    // Validate sufficient balance/shares for pending orders
                    if ($action === 'buy') {
                        $total_cost = $num_shares * $price_to_check;
                        if ($account['balance'] < $total_cost) {
                            throw new Exception('Insufficient balance for this order.');
                        }
                    } else {
                        $stmt = $pdo->prepare("
                            SELECT SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END) as shares_held
                            FROM TransactionRecord
                            WHERE user_id = ? AND ticker_symbol = ?
                            GROUP BY ticker_symbol
                        ");
                        $stmt->execute([$user_id, $ticker_symbol]);
                        $holding = $stmt->fetch();

                        if (!$holding || $holding['shares_held'] < $num_shares) {
                            throw new Exception('Insufficient shares for this order.');
                        }
                    }

                    // Note: OrderType table doesn't have exchange_id column
                    // Create pending order
                    $stmt = $pdo->prepare("
                        INSERT INTO OrderType (user_id, account_id, ticker_symbol, order_type, action_type, quantity, limit_price, stop_price, expiry_date, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')
                    ");
                    $stmt->execute([
                        $user_id,
                        $account_id,
                        $ticker_symbol,
                        $order_type,
                        $action_type,
                        $num_shares,
                        ($order_type === 'LIMIT' || $order_type === 'STOP_LIMIT') ? $limit_price : null,
                        ($order_type === 'STOP_LOSS' || $order_type === 'STOP_LIMIT') ? $stop_price : null,
                        $expiry_datetime
                    ]);

                    $pdo->commit();
                    $message = ucfirst($action) . ' ' . str_replace('_', ' ', strtolower($order_type)) . ' order placed successfully! It will execute when conditions are met.';
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        }
    }
}

// Get user accounts
$stmt = $pdo->prepare("SELECT * FROM Account WHERE user_id = ?");
$stmt->execute([$user_id]);
$accounts = $stmt->fetchAll();

// Get stocks with current prices (USA only)
$stmt = $pdo->query("
    SELECT s.*, sp.current_price, b.region_id, r.region_name
    FROM Stock s 
    LEFT JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol 
    LEFT JOIN Business b ON s.business_id = b.business_id
    LEFT JOIN Region r ON b.region_id = r.region_id
    WHERE r.region_name = 'North America'
    ORDER BY s.company_name
");
$stocks = $stmt->fetchAll();

// Get exchanges (USA only)
$stmt = $pdo->query("
    SELECT e.* 
    FROM Exchange e 
    JOIN Region r ON e.region_id = r.region_id 
    WHERE r.region_name = 'North America'
    ORDER BY e.exchange_name
");
$exchanges = $stmt->fetchAll();

// Get current holdings
$stmt = $pdo->prepare("
    SELECT ticker_symbol, SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END) as shares_held
    FROM TransactionRecord
    WHERE user_id = ?
    GROUP BY ticker_symbol
    HAVING shares_held > 0
");
$stmt->execute([$user_id]);
$holdings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy/Sell Stocks - Stock Trading</title>
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
                <li><a href="buy_sell.php" class="active">Trade</a></li>
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
        <div class="page-header">
            <h2>Trade Stocks</h2>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="trade-layout">
            <!-- Left Column: Order Form -->
            <div class="trade-form-section">
                <div class="card trade-form-card">
                    <div class="form-header">
                        <div class="form-header-left">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="header-icon">
                                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <h3>Place Order</h3>
                        </div>
                        <div class="action-toggle">
                            <button type="button"
                                class="action-btn <?php echo (!isset($_POST['action']) || $_POST['action'] === 'buy') ? 'active' : ''; ?>"
                                onclick="setAction('buy')" id="buyBtn">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                                <span>Buy</span>
                            </button>
                            <button type="button"
                                class="action-btn <?php echo (isset($_POST['action']) && $_POST['action'] === 'sell') ? 'active sell-active' : ''; ?>"
                                onclick="setAction('sell')" id="sellBtn">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                                <span>Sell</span>
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="buy_sell.php" class="form compact-form" id="orderForm">
                        <?php csrf_field(); ?>
                        <input type="hidden" id="action" name="action" value="buy">

                        <div class="form-row">
                            <div class="form-group compact">
                                <label for="order_type">Order Type</label>
                                <select id="order_type" name="order_type" required onchange="updateOrderType()">
                                    <option value="MARKET">Market</option>
                                    <option value="LIMIT">Limit</option>
                                    <option value="STOP_LOSS">Stop-Loss</option>
                                    <option value="STOP_LIMIT">Stop-Limit</option>
                                </select>
                            </div>

                            <div class="form-group compact">
                                <label for="account_id">Account</label>
                                <select id="account_id" name="account_id" required>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?php echo $acc['account_id']; ?>">
                                            #<?php echo $acc['account_id']; ?> -
                                            $<?php echo number_format($acc['balance'], 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group compact">
                            <label for="ticker_symbol">Stock</label>
                            <select id="ticker_symbol" name="ticker_symbol" required>
                                <option value="">Select stock</option>
                                <?php foreach ($stocks as $stock): ?>
                                    <option value="<?php echo htmlspecialchars($stock['ticker_symbol']); ?>"
                                        data-shares="<?php echo $holdings[$stock['ticker_symbol']] ?? 0; ?>"
                                        data-price="<?php echo $stock['current_price'] ?? 0; ?>">
                                        <?php echo htmlspecialchars($stock['ticker_symbol']); ?> -
                                        <?php echo htmlspecialchars($stock['company_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group compact">
                                <label for="exchange_id">Exchange</label>
                                <select id="exchange_id" name="exchange_id" required>
                                    <option value="">Select exchange</option>
                                    <?php foreach ($exchanges as $exchange): ?>
                                        <option value="<?php echo $exchange['exchange_id']; ?>">
                                            <?php echo htmlspecialchars($exchange['short_code']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group compact">
                                <label for="num_shares">Shares</label>
                                <input type="number" id="num_shares" name="num_shares" step="0.000001" min="0.000001"
                                    required placeholder="0.00">
                            </div>
                        </div>

                        <!-- Market Order Price -->
                        <div class="form-group compact" id="market_price_group">
                            <label for="cost_per_share">Price ($)</label>
                            <input type="number" id="cost_per_share" name="cost_per_share" step="0.01" min="0.01"
                                placeholder="0.00">
                        </div>

                        <!-- Limit Price -->
                        <div class="form-group compact" id="limit_price_group" style="display:none;">
                            <label for="limit_price">Limit Price ($)</label>
                            <input type="number" id="limit_price" name="limit_price" step="0.01" min="0.01"
                                placeholder="0.00">
                        </div>

                        <!-- Stop Price -->
                        <div class="form-group compact" id="stop_price_group" style="display:none;">
                            <label for="stop_price">Stop Price ($)</label>
                            <input type="number" id="stop_price" name="stop_price" step="0.01" min="0.01"
                                placeholder="0.00">
                        </div>

                        <!-- Expiry Date -->
                        <div class="form-group compact" id="expiry_group" style="display:none;">
                            <label for="expiry_date">Expiry (Optional)</label>
                            <input type="datetime-local" id="expiry_date" name="expiry_date">
                        </div>

                        <div class="order-summary">
                            <div class="summary-item">
                                <span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                        <path
                                            d="M12 2V22M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    Estimated Total:
                                </span>
                                <strong id="total_cost">$0.00</strong>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-large submit-order-btn"
                            id="submitBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="btn-icon">
                                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span id="submitText">Place Buy Order</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Holdings & Quick Info -->
            <div class="trade-sidebar">
                <div class="card holdings-card">
                    <h3>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                            <path d="M3 3V21H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path d="M7 16L12 11L16 15L21 10" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M21 10H17V14H21V10Z" fill="currentColor" />
                        </svg>
                        Your Holdings
                    </h3>
                    <?php if (empty($holdings)): ?>
                        <div class="empty-state-small">No holdings yet</div>
                    <?php else: ?>
                        <div class="holdings-list">
                            <?php foreach ($holdings as $symbol => $shares): ?>
                                <div class="holding-item"
                                    onclick="quickSelectStock('<?php echo htmlspecialchars($symbol); ?>')">
                                    <div class="holding-symbol"><?php echo htmlspecialchars($symbol); ?></div>
                                    <div class="holding-shares"><?php echo number_format($shares, 2); ?> shares</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Info Card -->
                <div class="card info-card">
                    <h3>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                            <path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                        Quick Info
                    </h3>
                    <div class="info-item">
                        <span class="info-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                <path d="M3 3V21H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                <path d="M7 16L12 11L16 15L21 10" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Stock:
                        </span>
                        <span class="info-value" id="selectedStock">-</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                <path
                                    d="M12 2V22M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            Price:
                        </span>
                        <span class="info-value price-value" id="currentPrice">-</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                <path
                                    d="M20 7H4C2.89543 7 2 7.89543 2 9V19C2 20.1046 2.89543 21 4 21H20C21.1046 21 22 20.1046 22 19V9C22 7.89543 21.1046 7 20 7Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M16 21V5C16 4.46957 15.7893 3.96086 15.4142 3.58579C15.0391 3.21071 14.5304 3 14 3H10C9.46957 3 8.96086 3.21071 8.58579 3.58579C8.21071 3.96086 8 4.46957 8 5V21"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            You Own:
                        </span>
                        <span class="info-value" id="youOwn">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setAction(action) {
            document.getElementById('action').value = action;
            const buyBtn = document.getElementById('buyBtn');
            const sellBtn = document.getElementById('sellBtn');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');

            if (action === 'buy') {
                buyBtn.classList.add('active');
                sellBtn.classList.remove('active');
                sellBtn.classList.remove('sell-active');
                submitBtn.className = 'btn btn-primary btn-block btn-large submit-order-btn';
                submitBtn.style.background = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
                submitBtn.style.boxShadow = '0 4px 16px rgba(59, 130, 246, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
                submitText.textContent = 'Place Buy Order';
            } else {
                sellBtn.classList.add('active');
                sellBtn.classList.add('sell-active');
                buyBtn.classList.remove('active');
                submitBtn.className = 'btn btn-danger btn-block btn-large submit-order-btn';
                submitBtn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                submitBtn.style.boxShadow = '0 4px 16px rgba(239, 68, 68, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
                submitText.textContent = 'Place Sell Order';
            }
            updateOrderType();
        }

        function quickSelectStock(symbol) {
            document.getElementById('ticker_symbol').value = symbol;
            const option = document.getElementById('ticker_symbol').querySelector(`option[value="${symbol}"]`);
            if (option) {
                const price = option.getAttribute('data-price');
                const shares = option.getAttribute('data-shares');

                if (price && price > 0) {
                    const orderType = document.getElementById('order_type').value;
                    if (orderType === 'MARKET') {
                        document.getElementById('cost_per_share').value = parseFloat(price).toFixed(2);
                    } else if (orderType === 'LIMIT') {
                        document.getElementById('limit_price').value = parseFloat(price).toFixed(2);
                    }
                    calculateTotal();
                }

                // Update quick info
                document.getElementById('selectedStock').textContent = symbol;
                document.getElementById('currentPrice').textContent = price ? '$' + parseFloat(price).toFixed(2) : '-';
                document.getElementById('youOwn').textContent = shares > 0 ? shares + ' shares' : 'None';
            }
        }

        function updateForm() {
            const action = document.getElementById('action').value;
            setAction(action);
        }

        function updateOrderType() {
            const orderType = document.getElementById('order_type').value;
            const marketPriceGroup = document.getElementById('market_price_group');
            const limitPriceGroup = document.getElementById('limit_price_group');
            const stopPriceGroup = document.getElementById('stop_price_group');
            const expiryGroup = document.getElementById('expiry_group');
            const orderTypeHelp = document.getElementById('order_type_help');
            const costPerShare = document.getElementById('cost_per_share');
            const limitPrice = document.getElementById('limit_price');
            const stopPrice = document.getElementById('stop_price');

            // Reset all fields
            costPerShare.removeAttribute('required');
            limitPrice.removeAttribute('required');
            stopPrice.removeAttribute('required');

            if (orderType === 'MARKET') {
                marketPriceGroup.style.display = 'block';
                limitPriceGroup.style.display = 'none';
                stopPriceGroup.style.display = 'none';
                expiryGroup.style.display = 'none';
                costPerShare.setAttribute('required', 'required');
                orderTypeHelp.textContent = 'Market orders execute immediately at current price.';
            } else if (orderType === 'LIMIT') {
                marketPriceGroup.style.display = 'none';
                limitPriceGroup.style.display = 'block';
                stopPriceGroup.style.display = 'none';
                expiryGroup.style.display = 'block';
                limitPrice.setAttribute('required', 'required');
                orderTypeHelp.textContent = 'Limit orders execute at your specified price or better.';
            } else if (orderType === 'STOP_LOSS') {
                marketPriceGroup.style.display = 'none';
                limitPriceGroup.style.display = 'none';
                stopPriceGroup.style.display = 'block';
                expiryGroup.style.display = 'block';
                stopPrice.setAttribute('required', 'required');
                orderTypeHelp.textContent = 'Stop-loss orders trigger when price reaches stop level, then execute as market order.';
            } else if (orderType === 'STOP_LIMIT') {
                marketPriceGroup.style.display = 'none';
                limitPriceGroup.style.display = 'block';
                stopPriceGroup.style.display = 'block';
                expiryGroup.style.display = 'block';
                limitPrice.setAttribute('required', 'required');
                stopPrice.setAttribute('required', 'required');
                orderTypeHelp.textContent = 'Stop-limit orders trigger at stop price, then execute as limit order at limit price.';
            }

            calculateTotal();
        }

        // Calculate total cost
        document.getElementById('num_shares').addEventListener('input', calculateTotal);
        document.getElementById('cost_per_share').addEventListener('input', calculateTotal);
        document.getElementById('limit_price').addEventListener('input', calculateTotal);
        document.getElementById('stop_price').addEventListener('input', calculateTotal);

        // Auto-populate price when stock is selected
        document.getElementById('ticker_symbol').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const shares = selectedOption.getAttribute('data-shares');
            const symbol = selectedOption.value;
            const orderType = document.getElementById('order_type').value;

            // Update quick info
            document.getElementById('selectedStock').textContent = symbol || '-';
            document.getElementById('currentPrice').textContent = price && price > 0 ? '$' + parseFloat(price).toFixed(2) : '-';
            document.getElementById('youOwn').textContent = shares > 0 ? parseFloat(shares).toFixed(2) + ' shares' : 'None';

            if (price && price > 0) {
                if (orderType === 'MARKET') {
                    document.getElementById('cost_per_share').value = parseFloat(price).toFixed(2);
                } else if (orderType === 'LIMIT') {
                    document.getElementById('limit_price').value = parseFloat(price).toFixed(2);
                } else if (orderType === 'STOP_LOSS' || orderType === 'STOP_LIMIT') {
                    document.getElementById('stop_price').value = parseFloat(price).toFixed(2);
                    if (orderType === 'STOP_LIMIT') {
                        document.getElementById('limit_price').value = parseFloat(price).toFixed(2);
                    }
                }
                calculateTotal();
            }
        });

        function calculateTotal() {
            const shares = parseFloat(document.getElementById('num_shares').value) || 0;
            const orderType = document.getElementById('order_type').value;
            let price = 0;

            if (orderType === 'MARKET') {
                price = parseFloat(document.getElementById('cost_per_share').value) || 0;
            } else if (orderType === 'LIMIT') {
                price = parseFloat(document.getElementById('limit_price').value) || 0;
            } else if (orderType === 'STOP_LOSS') {
                price = parseFloat(document.getElementById('stop_price').value) || 0;
            } else if (orderType === 'STOP_LIMIT') {
                price = parseFloat(document.getElementById('limit_price').value) || 0;
            }

            const total = shares * price;
            document.getElementById('total_cost').textContent = total > 0 ? '$' + total.toFixed(2) : '$0.00';
        }

        // Initialize on page load
        updateOrderType();
        setAction('buy');
    </script>
</body>

</html>