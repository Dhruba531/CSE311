<?php
session_start();
require 'config.php';
require_login();

$uid = $_SESSION['user_id'];
$msg = '';

// Handle deposit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deposit') {
    $amt = (float)$_POST['amount'];
    $trx = $_POST['trx_id'];
    
    $stmt = $pdo->prepare("UPDATE Account SET balance = balance + ? WHERE user_id = ?");
    $stmt->execute([$amt, $uid]);
    
    $stmt = $pdo->prepare("INSERT INTO FundTransaction(user_id, amount, transaction_reference_id) VALUES(?, ?, ?)");
    $stmt->execute([$uid, $amt, $trx]);
    
    $msg = 'Deposit successful!';
}

// Get user details
$stmt = $pdo->prepare("SELECT full_name, workplace FROM Users WHERE user_id = ?");
$stmt->execute([$uid]);
$u = $stmt->fetch();

// Get account balance
$stmt = $pdo->prepare("SELECT balance FROM Account WHERE user_id = ?");
$stmt->execute([$uid]);
$bal = $stmt->fetchColumn();

// Get share statistics
$stmt = $pdo->prepare("
    SELECT SUM(IF(is_buy, num_shares, 0)) bought,
           SUM(IF(NOT is_buy, num_shares, 0)) sold
    FROM TransactionRecord 
    WHERE user_id = ?
");
$stmt->execute([$uid]);
$st = $stmt->fetch();

// Get current holdings
$stmt = $pdo->prepare("
    SELECT s.ticker_symbol, s.company_name,
           SUM(IF(t.is_buy, t.num_shares, -t.num_shares)) shares,
           sp.current_price
    FROM TransactionRecord t
    JOIN Stock s ON t.ticker_symbol = s.ticker_symbol
    LEFT JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol
    WHERE t.user_id = ?
    GROUP BY s.ticker_symbol
    HAVING shares > 0
");
$stmt->execute([$uid]);
$h = $stmt->fetchAll();

// Get deposit history
$stmt = $pdo->prepare("
    SELECT * FROM FundTransaction 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$uid]);
$d = $stmt->fetchAll();

// Get stock transaction history
$stmt = $pdo->prepare("
    SELECT t.*, s.company_name 
    FROM TransactionRecord t
    JOIN Stock s ON t.ticker_symbol = s.ticker_symbol
    WHERE t.user_id = ? 
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$uid]);
$ht = $stmt->fetchAll();

// Calculate total portfolio value
$tv = 0;
foreach ($h as $x) {
    $tv += $x['shares'] * $x['current_price'];
}

$page_title = 'My Portfolio';
require 'includes/header.php';
require 'includes/nav.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h2>My Portfolio</h2>
    </div>

    <?php if ($msg): ?>
        <div class='alert alert-success'><?= $msg ?></div>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="card fade-in-up" style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem">
        <div style="width: 80px; height: 80px; background: #eff6ff; border-radius: 50%; 
                    display: flex; align-items: center; justify-content: center; 
                    font-size: 2rem; color: var(--primary); font-weight: bold">
            <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
        </div>
        <div style="flex: 1">
            <h2 style="margin-bottom: .25rem"><?= htmlspecialchars($u['full_name']) ?></h2>
            <p style="color: var(--text-muted); margin-bottom: 1rem">
                <span style="display: inline-flex; align-items: center; gap: .5rem">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                    <?= htmlspecialchars($u['workplace'] ?? 'Unknown') ?>
                </span>
            </p>
            <div style="display: flex; gap: 2rem">
                <div>
                    <div style="font-size: .875rem; color: var(--text-muted)">Total Shares Bought</div>
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--success)">
                        +<?= number_format($st['bought'] ?? 0) ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: .875rem; color: var(--text-muted)">Total Shares Sold</div>
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--danger)">
                        -<?= number_format($st['sold'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align: right">
            <div style="font-size: .875rem; color: var(--text-muted); margin-bottom: .25rem">Current Balance</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary)">
                $<?= number_format($bal, 2) ?>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>Available Balance</h3>
                <p class="stat-value">$<?= number_format($bal, 2) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💼</div>
            <div class="stat-info">
                <h3>Total Holdings Value</h3>
                <p class="stat-value">$<?= number_format($tv, 2) ?></p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem">
        <!-- Left Column -->
        <div>
            <!-- Current Holdings -->
            <div class="card">
                <h3>Current Holdings</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Company</th>
                                <th>Shares</th>
                                <th>Current Price</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($h)): ?>
                                <tr>
                                    <td colspan="5" class="empty-state">No stocks owned yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($h as $x): ?>
                                    <tr>
                                        <td><strong><?= $x['ticker_symbol'] ?></strong></td>
                                        <td><?= $x['company_name'] ?></td>
                                        <td><?= $x['shares'] ?></td>
                                        <td>$<?= number_format($x['current_price'], 2) ?></td>
                                        <td>$<?= number_format($x['shares'] * $x['current_price'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Deposit History -->
            <div class="card">
                <h3>Deposit History</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($d)): ?>
                                <tr>
                                    <td colspan="3" class="empty-state">No deposits yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($d as $x): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($x['created_at'])) ?></td>
                                        <td><code><?= $x['transaction_reference_id'] ?></code></td>
                                        <td style="color: var(--success)">+$<?= number_format($x['amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stock Transaction History -->
            <div class="card" style="margin-top: 2rem">
                <h3>Stock Transaction History</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Symbol</th>
                                <th>Shares</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ht)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state">No trades yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ht as $t): ?>
                                    <?php
                                    $ib = $t['is_buy'];
                                    $tot = $t['num_shares'] * $t['cost_per_share'];
                                    ?>
                                    <tr>
                                        <td><?= date('M d, H:i', strtotime($t['transaction_date'])) ?></td>
                                        <td>
                                            <span class="badge <?= $ib ? 'badge-blue' : 'badge-gray' ?>" 
                                                  style="color: <?= $ib ? 'var(--success)' : 'var(--danger)' ?>">
                                                <?= $ib ? 'BUY' : 'SELL' ?>
                                            </span>
                                        </td>
                                        <td><strong><?= $t['ticker_symbol'] ?></strong></td>
                                        <td><?= $t['num_shares'] ?></td>
                                        <td>$<?= number_format($t['cost_per_share'], 2) ?></td>
                                        <td style="font-weight: 600">$<?= number_format($tot, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Deposit Form -->
        <div>
            <div class="card">
                <h3>Deposit Funds</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="deposit">
                    
                    <div class="form-group">
                        <label for="amount">Amount ($)</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="1" 
                               required placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label for="trx_id">Transaction ID</label>
                        <input type="text" id="trx_id" name="trx_id" required placeholder="e.g., TRX-12345">
                        <small style="color: var(--text-muted); display: block; margin-top: .25rem">
                            Enter the reference ID from your bank transfer.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%">Deposit Funds</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
