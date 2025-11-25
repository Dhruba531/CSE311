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

// Handle alert operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $ticker_symbol = strtoupper(trim($_POST['ticker_symbol'] ?? ''));
            $alert_type = $_POST['alert_type'] ?? '';
            $target_price = (float) ($_POST['target_price'] ?? 0);
            $target_percent = (float) ($_POST['target_percent'] ?? 0);

            if (empty($ticker_symbol) || empty($alert_type)) {
                $error = 'Please fill in all required fields.';
            } elseif ($alert_type !== 'CHANGE_PERCENT' && $target_price <= 0) {
                $error = 'Target price must be greater than 0.';
            } else {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO PriceAlert (user_id, ticker_symbol, alert_type, target_price, target_percent)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $user_id,
                        $ticker_symbol,
                        $alert_type,
                        $alert_type === 'CHANGE_PERCENT' ? NULL : $target_price,
                        $alert_type === 'CHANGE_PERCENT' ? $target_percent : NULL
                    ]);
                    $message = 'Price alert created successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to create alert.';
                }
            }
        } elseif ($action === 'toggle') {
            $alert_id = (int) ($_POST['alert_id'] ?? 0);
            $is_active = (int) ($_POST['is_active'] ?? 0);
            if ($alert_id > 0) {
                try {
                    $stmt = $pdo->prepare("UPDATE PriceAlert SET is_active = ? WHERE alert_id = ? AND user_id = ?");
                    $stmt->execute([$is_active, $alert_id, $user_id]);
                    $message = 'Alert updated.';
                } catch (PDOException $e) {
                    $error = 'Failed to update alert.';
                }
            }
        } elseif ($action === 'delete') {
            $alert_id = (int) ($_POST['alert_id'] ?? 0);
            if ($alert_id > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM PriceAlert WHERE alert_id = ? AND user_id = ?");
                    $stmt->execute([$alert_id, $user_id]);
                    $message = 'Alert deleted.';
                } catch (PDOException $e) {
                    $error = 'Failed to delete alert.';
                }
            }
        }
    }
}


// Get user's alerts
$stmt = $pdo->prepare("
    SELECT pa.*, s.company_name, sp.current_price
    FROM PriceAlert pa
    JOIN Stock s ON pa.ticker_symbol = s.ticker_symbol
    LEFT JOIN StockPrice sp ON pa.ticker_symbol = sp.ticker_symbol
    WHERE pa.user_id = ?
    ORDER BY pa.created_date DESC
");
$stmt->execute([$user_id]);
$alerts = $stmt->fetchAll();

// Get all stocks
$stmt = $pdo->query("SELECT ticker_symbol, company_name FROM Stock ORDER BY company_name");
$stocks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Alerts - Stock Trading</title>
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
                <li><a href="alerts.php" class="active">Alerts</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="account.php">Account</a></li>
                <li><a href="friends.php">Friends</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Price Alerts</h2>
            <button class="btn btn-primary" onclick="openModal()">+ Create Alert</button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <?php if (empty($alerts)): ?>
                <div class="empty-state">No price alerts set. Create alerts to get notified when stocks reach your target
                    prices!</div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Company</th>
                                <th>Current Price</th>
                                <th>Alert Type</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th>Triggered</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alerts as $alert): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($alert['ticker_symbol']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($alert['company_name']); ?></td>
                                    <td>$<?php echo number_format($alert['current_price'] ?? 0, 2); ?></td>
                                    <td>
                                        <span class="badge badge-buy">
                                            <?php
                                            echo $alert['alert_type'] === 'ABOVE' ? 'Above' :
                                                ($alert['alert_type'] === 'BELOW' ? 'Below' : 'Change %');
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        if ($alert['alert_type'] === 'CHANGE_PERCENT') {
                                            echo number_format($alert['target_percent'], 2) . '%';
                                        } else {
                                            echo '$' . number_format($alert['target_price'], 2);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($alert['is_triggered']): ?>
                                            <span class="badge badge-sell">Triggered</span>
                                        <?php elseif ($alert['is_active']): ?>
                                            <span class="badge badge-buy">Active</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #6b7280;">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $alert['triggered_date'] ? date('M d, Y H:i', strtotime($alert['triggered_date'])) : '-'; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($alert['created_date'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="alert_id" value="<?php echo $alert['alert_id']; ?>">
                                            <input type="hidden" name="is_active"
                                                value="<?php echo $alert['is_active'] ? 0 : 1; ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                <?php echo $alert['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;"
                                            onsubmit="return confirm('Delete this alert?');">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="alert_id" value="<?php echo $alert['alert_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
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
            <h3>Create Price Alert</h3>
            <form method="POST" action="alerts.php" id="alert-form">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="create">

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
                    <label for="alert_type">Alert Type</label>
                    <select id="alert_type" name="alert_type" required onchange="updateAlertForm()">
                        <option value="">Select alert type</option>
                        <option value="ABOVE">Alert when price goes ABOVE</option>
                        <option value="BELOW">Alert when price goes BELOW</option>
                        <option value="CHANGE_PERCENT">Alert on % change</option>
                    </select>
                </div>

                <div class="form-group" id="price-group">
                    <label for="target_price">Target Price ($)</label>
                    <input type="number" id="target_price" name="target_price" step="0.01" min="0.01"
                        placeholder="Enter target price">
                </div>

                <div class="form-group" id="percent-group" style="display:none;">
                    <label for="target_percent">Target Change (%)</label>
                    <input type="number" id="target_percent" name="target_percent" step="0.01"
                        placeholder="Enter percentage change">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Alert</button>
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
            document.getElementById('alert-form').reset();
        }

        function updateAlertForm() {
            const alertType = document.getElementById('alert_type').value;
            const priceGroup = document.getElementById('price-group');
            const percentGroup = document.getElementById('percent-group');

            if (alertType === 'CHANGE_PERCENT') {
                priceGroup.style.display = 'none';
                percentGroup.style.display = 'block';
                document.getElementById('target_price').removeAttribute('required');
                document.getElementById('target_percent').setAttribute('required', 'required');
            } else {
                priceGroup.style.display = 'block';
                percentGroup.style.display = 'none';
                document.getElementById('target_price').setAttribute('required', 'required');
                document.getElementById('target_percent').removeAttribute('required');
            }
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