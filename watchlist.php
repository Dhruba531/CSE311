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

// Handle watchlist operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $ticker_symbol = strtoupper(trim($_POST['ticker_symbol'] ?? ''));
            $notes = trim($_POST['notes'] ?? '');

            if (empty($ticker_symbol)) {
                $error = 'Please enter a stock symbol.';
            } else {
                try {
                    // Check if stock exists
                    $stmt = $pdo->prepare("SELECT ticker_symbol FROM Stock WHERE ticker_symbol = ?");
                    $stmt->execute([$ticker_symbol]);
                    if (!$stmt->fetch()) {
                        $error = 'Stock symbol not found.';
                    } else {
                        // Check if already in watchlist
                        $stmt = $pdo->prepare("SELECT watchlist_id FROM Watchlist WHERE user_id = ? AND ticker_symbol = ?");
                        $stmt->execute([$user_id, $ticker_symbol]);
                        if ($stmt->fetch()) {
                            $error = 'Stock already in watchlist.';
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO Watchlist (user_id, ticker_symbol, notes) VALUES (?, ?, ?)");
                            $stmt->execute([$user_id, $ticker_symbol, $notes]);
                            $message = 'Stock added to watchlist!';
                        }
                    }
                } catch (PDOException $e) {
                    $error = 'Failed to add to watchlist.';
                }
            }
        } elseif ($action === 'remove') {
            $watchlist_id = (int) ($_POST['watchlist_id'] ?? 0);
            if ($watchlist_id > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM Watchlist WHERE watchlist_id = ? AND user_id = ?");
                    $stmt->execute([$watchlist_id, $user_id]);
                    $message = 'Removed from watchlist.';
                } catch (PDOException $e) {
                    $error = 'Failed to remove from watchlist.';
                }
            }
        }
    }
}


// Get watchlist with current prices
$stmt = $pdo->prepare("
    SELECT w.*, s.company_name, sp.current_price, sp.previous_close, 
           sp.day_change, sp.day_change_percent, sp.volume
    FROM Watchlist w
    JOIN Stock s ON w.ticker_symbol = s.ticker_symbol
    LEFT JOIN StockPrice sp ON w.ticker_symbol = sp.ticker_symbol
    WHERE w.user_id = ?
    ORDER BY w.added_date DESC
");
$stmt->execute([$user_id]);
$watchlist = $stmt->fetchAll();

// Get all stocks for dropdown
$stmt = $pdo->query("SELECT ticker_symbol, company_name FROM Stock ORDER BY company_name");
$stocks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watchlist - Stock Trading</title>
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
                <li><a href="watchlist.php" class="active">Watchlist</a></li>
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
            <h2>My Watchlist</h2>
            <button class="btn btn-primary" onclick="openModal()">+ Add to Watchlist</button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <?php if (empty($watchlist)): ?>
                <div class="empty-state">Your watchlist is empty. Add stocks to track their prices!</div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Company</th>
                                <th>Current Price</th>
                                <th>Change</th>
                                <th>Change %</th>
                                <th>Volume</th>
                                <th>Notes</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($watchlist as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['ticker_symbol']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['company_name']); ?></td>
                                    <td>$<?php echo number_format($item['current_price'] ?? 0, 2); ?></td>
                                    <td class="<?php echo ($item['day_change'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ($item['day_change'] ?? 0) >= 0 ? '+' : ''; ?>
                                        $<?php echo number_format($item['day_change'] ?? 0, 2); ?>
                                    </td>
                                    <td
                                        class="<?php echo ($item['day_change_percent'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ($item['day_change_percent'] ?? 0) >= 0 ? '+' : ''; ?>
                                        <?php echo number_format($item['day_change_percent'] ?? 0, 2); ?>%
                                    </td>
                                    <td><?php echo number_format($item['volume'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars($item['notes'] ?? '-'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($item['added_date'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;"
                                            onsubmit="return confirm('Remove from watchlist?');">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="watchlist_id"
                                                value="<?php echo $item['watchlist_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Add to Watchlist</h3>
            <form method="POST" action="watchlist.php">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="ticker_symbol">Stock Symbol</label>
                    <select id="ticker_symbol" name="ticker_symbol" required>
                        <option value="">Select a stock</option>
                        <?php foreach ($stocks as $stock): ?>
                            <option value="<?php echo htmlspecialchars($stock['ticker_symbol']); ?>">
                                <?php echo htmlspecialchars($stock['ticker_symbol']); ?> -
                                <?php echo htmlspecialchars($stock['company_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="3"
                        placeholder="Add any notes about this stock..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Watchlist</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        window.onclick = function (event) {
            const modal = document.getElementById('modal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>