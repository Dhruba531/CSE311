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
        $ticker_symbol = trim($_POST['ticker_symbol'] ?? '');
        $num_shares = (int) ($_POST['num_shares'] ?? 0);
        $cost_per_share = (float) ($_POST['cost_per_share'] ?? 0);
        $exchange_id = (int) ($_POST['exchange_id'] ?? 0);
        $account_id = (int) ($_POST['account_id'] ?? 0);

        // Validate action - must be explicitly 'buy' or 'sell'
        if ($action !== 'buy' && $action !== 'sell') {
            $error = 'Invalid action. Must be either "buy" or "sell".';
        } elseif (empty($ticker_symbol) || $num_shares <= 0 || $cost_per_share <= 0 || $exchange_id <= 0 || $account_id <= 0) {
            $error = 'Please fill in all fields correctly.';
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

                $total_cost = $num_shares * $cost_per_share;

                if ($action === 'buy') {
                    // Check balance before transaction (trigger will also validate, but this provides better UX)
                    if ($account['balance'] < $total_cost) {
                        throw new Exception('Insufficient balance.');
                    }
                } else { // sell
                    // Check if user has enough shares (trigger will also validate, but this provides better UX)
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

                // Record transaction (trigger will automatically update balance)
                $is_buy = ($action === 'buy') ? 1 : 0;
                $stmt = $pdo->prepare("
                    INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares, exchange_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $account_id, $ticker_symbol, $is_buy, $cost_per_share, $num_shares, $exchange_id]);

                $pdo->commit();
                $message = ucfirst($action) . ' order executed successfully!';
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

// Get stocks
$stmt = $pdo->query("SELECT * FROM Stock ORDER BY company_name");
$stocks = $stmt->fetchAll();

// Get exchanges
$stmt = $pdo->query("SELECT * FROM Exchange ORDER BY exchange_name");
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

<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">📈 StockTrader</h1>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="stocks.php">Stocks</a></li>
                <li><a href="buy_sell.php" class="active">Trade</a></li>
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
        <h2>Buy / Sell Stocks</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="content-grid">
            <div class="card">
                <h3>Place Order</h3>
                <form method="POST" action="buy_sell.php" class="form">
                    <?php csrf_field(); ?>
                    <div class="form-group">
                        <label for="action">Action</label>
                        <select id="action" name="action" required onchange="updateForm()">
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="account_id">Account</label>
                        <select id="account_id" name="account_id" required>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?php echo $acc['account_id']; ?>">
                                    Account #<?php echo $acc['account_id']; ?> -
                                    $<?php echo number_format($acc['balance'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ticker_symbol">Stock Symbol</label>
                        <select id="ticker_symbol" name="ticker_symbol" required>
                            <option value="">Select a stock</option>
                            <?php foreach ($stocks as $stock): ?>
                                <option value="<?php echo htmlspecialchars($stock['ticker_symbol']); ?>"
                                    data-shares="<?php echo $holdings[$stock['ticker_symbol']] ?? 0; ?>">
                                    <?php echo htmlspecialchars($stock['ticker_symbol']); ?> -
                                    <?php echo htmlspecialchars($stock['company_name']); ?>
                                    <?php if (isset($holdings[$stock['ticker_symbol']])): ?>
                                        (You own: <?php echo $holdings[$stock['ticker_symbol']]; ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="exchange_id">Exchange</label>
                        <select id="exchange_id" name="exchange_id" required>
                            <option value="">Select an exchange</option>
                            <?php foreach ($exchanges as $exchange): ?>
                                <option value="<?php echo $exchange['exchange_id']; ?>">
                                    <?php echo htmlspecialchars($exchange['exchange_name']); ?>
                                    (<?php echo htmlspecialchars($exchange['short_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="num_shares">Number of Shares</label>
                        <input type="number" id="num_shares" name="num_shares" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="cost_per_share">Price per Share ($)</label>
                        <input type="number" id="cost_per_share" name="cost_per_share" step="0.01" min="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Total Cost</label>
                        <input type="text" id="total_cost" readonly class="readonly-input">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn">Place Order</button>
                </form>
            </div>

            <div class="card">
                <h3>Your Holdings</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Shares Owned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($holdings)): ?>
                                <tr>
                                    <td colspan="2" class="empty-state">No holdings yet</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($holdings as $symbol => $shares): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($symbol); ?></strong></td>
                                        <td><?php echo number_format($shares); ?></td>
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
        function updateForm() {
            const action = document.getElementById('action').value;
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.textContent = action === 'buy' ? 'Buy Stock' : 'Sell Stock';
            submitBtn.className = action === 'buy' ? 'btn btn-primary btn-block' : 'btn btn-danger btn-block';
        }

        // Calculate total cost
        document.getElementById('num_shares').addEventListener('input', calculateTotal);
        document.getElementById('cost_per_share').addEventListener('input', calculateTotal);

        function calculateTotal() {
            const shares = parseFloat(document.getElementById('num_shares').value) || 0;
            const price = parseFloat(document.getElementById('cost_per_share').value) || 0;
            const total = shares * price;
            document.getElementById('total_cost').value = '$' + total.toFixed(2);
        }
    </script>
</body>

</html>