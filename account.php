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

// Handle account operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $initial_balance = (float)($_POST['initial_balance'] ?? 0);
            if ($initial_balance < 0) {
                $error = 'Initial balance cannot be negative.';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO Account (user_id, balance) VALUES (?, ?)");
                    $stmt->execute([$user_id, $initial_balance]);
                    $message = 'Account created successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to create account.';
                }
            }
        } elseif ($action === 'deposit') {
            $account_id = (int)($_POST['account_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            if ($amount <= 0) {
                $error = 'Amount must be greater than 0.';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE Account SET balance = balance + ? WHERE account_id = ? AND user_id = ?");
                    $stmt->execute([$amount, $account_id, $user_id]);
                    if ($stmt->rowCount() > 0) {
                        $message = 'Deposit successful!';
                    } else {
                        $error = 'Invalid account.';
                    }
                } catch (PDOException $e) {
                    $error = 'Deposit failed.';
                }
            }
        } elseif ($action === 'withdraw') {
            $account_id = (int)($_POST['account_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            if ($amount <= 0) {
                $error = 'Amount must be greater than 0.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("SELECT balance FROM Account WHERE account_id = ? AND user_id = ?");
                    $stmt->execute([$account_id, $user_id]);
                    $account = $stmt->fetch();
                    
                    if (!$account) {
                        throw new Exception('Invalid account.');
                    }
                    
                    if ($account['balance'] < $amount) {
                        throw new Exception('Insufficient balance.');
                    }
                    
                    $stmt = $pdo->prepare("UPDATE Account SET balance = balance - ? WHERE account_id = ? AND user_id = ?");
                    $stmt->execute([$amount, $account_id, $user_id]);
                    $pdo->commit();
                    $message = 'Withdrawal successful!';
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = $e->getMessage();
                }
            }
        }
    }
}


// Get user accounts
$stmt = $pdo->prepare("SELECT * FROM Account WHERE user_id = ? ORDER BY account_id");
$stmt->execute([$user_id]);
$accounts = $stmt->fetchAll();

// Get user info
$stmt = $pdo->prepare("SELECT u.*, r.region_name FROM Users u LEFT JOIN Region r ON u.region_id = r.region_id WHERE u.user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$total_balance = array_sum(array_column($accounts, 'balance'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management - Stock Trading</title>
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
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="account.php" class="active">Account</a></li>
                <li><a href="friends.php">Friends</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Account Management</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="content-grid">
            <div class="card">
                <h3>User Information</h3>
                <div class="info-list">
                    <div class="info-item">
                        <strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Workplace:</strong> <?php echo htmlspecialchars($user['workplace'] ?? 'N/A'); ?>
                    </div>
                    <div class="info-item">
                        <strong>Region:</strong> <?php echo htmlspecialchars($user['region_name'] ?? 'N/A'); ?>
                    </div>
                    <div class="info-item">
                        <strong>Total Balance:</strong> <span class="highlight">$<?php echo number_format($total_balance, 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Create New Account</h3>
                <form method="POST" action="account.php" class="form">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="initial_balance">Initial Balance ($)</label>
                        <input type="number" id="initial_balance" name="initial_balance" step="0.01" min="0" value="0" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h3>Your Accounts</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Account ID</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($accounts)): ?>
                            <tr><td colspan="3" class="empty-state">No accounts yet. Create one to start trading!</td></tr>
                        <?php else: ?>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td><strong>#<?php echo $account['account_id']; ?></strong></td>
                                    <td class="highlight">$<?php echo number_format($account['balance'], 2); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="openModal('deposit', <?php echo $account['account_id']; ?>)">Deposit</button>
                                        <button class="btn btn-sm btn-warning" onclick="openModal('withdraw', <?php echo $account['account_id']; ?>)">Withdraw</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modal-title">Deposit</h3>
            <form method="POST" action="account.php" id="account-form">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" id="form-action" value="deposit">
                <input type="hidden" name="account_id" id="form-account-id" value="">
                
                <div class="form-group">
                    <label for="amount">Amount ($)</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(action, accountId) {
            const modal = document.getElementById('modal');
            const title = document.getElementById('modal-title');
            const formAction = document.getElementById('form-action');
            const accountIdInput = document.getElementById('form-account-id');
            
            formAction.value = action;
            accountIdInput.value = accountId;
            title.textContent = action.charAt(0).toUpperCase() + action.slice(1);
            
            document.getElementById('account-form').reset();
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

