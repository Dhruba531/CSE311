<?php
session_start();
require 'config.php';
require_login();

$page_title = 'My Reports';
require 'includes/header.php';
require 'includes/nav.php';
// Fetch Full Transaction History
$stmt = $pdo->prepare("
    SELECT date_time, ticker_symbol, transaction_type, order_type, quantity, cost, status
    FROM Transactions
    WHERE user_id = ?
    ORDER BY date_time DESC
");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();
?>

<div class="flex-between mb-4">
    <div>
        <h1>Reports & Activity</h1>
        <p class="text-muted">View all your trading activity and download statements.</p>
    </div>
    <button class="btn-primary" onclick="alert('Feature coming soon!')">
        <i class="fa-solid fa-download"></i> Download CSV
    </button>
</div>

<div class="dashboard-grid">
    <div class="card-premium col-span-2"> <!-- Full width -->
        <h3 class="mb-4">Transaction History</h3>
        
        <table class="table-minimal">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Ticker</th>
                    <th>Nature</th>
                    <th>Type</th>
                    <th>Shares</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="7" class="text-center py-8 text-muted">No transactions found.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td style="color:var(--text-secondary); font-size:0.85rem;">
                            <?= date('M d, Y H:i', strtotime($h['date_time'])) ?>
                        </td>
                        <td style="font-weight:700;"><?= $h['ticker_symbol'] ?></td>
                        <td>
                            <span class="badge" style="
                                background: <?= $h['transaction_type'] == 'BUY' ? 'rgba(16, 185, 129, 0.1)' : ($h['transaction_type'] == 'SELL' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(59, 130, 246, 0.1)') ?>;
                                color: <?= $h['transaction_type'] == 'BUY' ? '#10b981' : ($h['transaction_type'] == 'SELL' ? '#ef4444' : '#3b82f6') ?>;
                            ">
                                <?= $h['transaction_type'] ?>
                            </span>
                        </td>
                         <td style="font-size:0.85rem; font-weight:600; text-transform:uppercase;">
                            <?= $h['order_type'] ?: 'MARKET' ?>
                        </td>
                        <td><?= number_format($h['quantity'], 4) ?></td>
                        <td style="font-weight:600;">$<?= number_format($h['cost'], 2) ?></td>
                         <td>
                            <span style="
                                font-size:0.75rem; font-weight:700;
                                color: <?= $h['status'] == 'COMPLETED' ? '#10b981' : ($h['status'] == 'PENDING' ? '#f59e0b' : '#ef4444') ?>;
                            ">
                                <?= $h['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
