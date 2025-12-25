<?php
session_start();
require 'config.php';
require_login();

$uid = $_SESSION['user_id'];
$msg = '';

// Handle Deposit/Withdraw
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = (float)$_POST['amount'];
    $type = $_POST['type']; // 'DEPOSIT' or 'WITHDRAW'
    
    if ($amount > 0) {
        $pdo->beginTransaction();
        try {
            if ($type == 'DEPOSIT') {
                $pdo->prepare("UPDATE Accounts SET balance = balance + ?, buying_power = buying_power + ? WHERE user_id = ?")->execute([$amount, $amount, $uid]);
            } else {
                // Withdraw logic
                $stmt = $pdo->prepare("SELECT buying_power FROM Accounts WHERE user_id = ?");
                $stmt->execute([$uid]);
                $bp = $stmt->fetchColumn();
                if ($bp < $amount) throw new Exception("Insufficient buying power.");
                
                $pdo->prepare("UPDATE Accounts SET balance = balance - ?, buying_power = buying_power - ? WHERE user_id = ?")->execute([$amount, $amount, $uid]);
            }
            
            // Log to Funds_Log
            $pdo->prepare("INSERT INTO Funds_Log (user_id, amount, transaction_type, status) VALUES (?, ?, ?, 'COMPLETED')")->execute([$uid, $amount, $type]);
            
            $pdo->commit();
            $msg = ucfirst(strtolower($type)) . " successful.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Error: " . $e->getMessage();
        }
    }
}

// Fetch Funds Log
$stmt = $pdo->prepare("SELECT * FROM Funds_Log WHERE user_id = ? ORDER BY date_time DESC LIMIT 20");
$stmt->execute([$uid]);
$logs = $stmt->fetchAll();

// Get Account Info
$stmt = $pdo->prepare("SELECT * FROM Accounts WHERE user_id = ?");
$stmt->execute([$uid]);
$account = $stmt->fetch();

$page_title = 'Funds & Banking';
require 'includes/header.php';
require 'includes/nav.php';
?>

<div class="flex-between mb-4">
    <div>
        <h1>Funds & Banking</h1>
        <p class="text-muted">Manage your deposits and withdrawals.</p>
    </div>
</div>

<?php if ($msg): ?>
<div style="background: #e0f2fe; color: #0284c7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="dashboard-grid">
    <!-- Balance Card -->
    <div class="card-premium">
        <h3>Available Cash</h3>
        <div style="font-size: 2.5rem; font-weight: 800; margin: 10px 0;">
            $<?= number_format($account['balance'] ?? 0, 2) ?>
        </div>
        <p class="text-muted">Buying Power: $<?= number_format($account['buying_power'] ?? 0, 2) ?></p>
    </div>

    <!-- Actions Card -->
    <div class="card-premium">
        <h3>Transfer Funds</h3>
        <form method="POST" style="margin-top: 20px;">
            <div style="display:flex; gap: 10px; margin-bottom: 15px;">
                <label style="flex:1"><input type="radio" name="type" value="DEPOSIT" checked> Deposit</label>
                <label style="flex:1"><input type="radio" name="type" value="WITHDRAW"> Withdraw</label>
            </div>
            
            <input type="number" name="amount" placeholder="Amount ($)" min="1" step="0.01" required
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px;">
            
            <button type="submit" class="btn-primary" style="width:100%">Process Transfer</button>
        </form>
    </div>

    <!-- History Card -->
    <div class="card-premium col-span-2">
        <h3>Transfer History</h3>
        <table class="table-minimal">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= date('M d, Y H:i', strtotime($log['date_time'])) ?></td>
                    <td>
                        <span class="badge" style="background: <?= $log['transaction_type'] == 'DEPOSIT' ? '#dcfce7' : '#fee2e2' ?>; color: <?= $log['transaction_type'] == 'DEPOSIT' ? '#15803d' : '#991b1b' ?>;">
                            <?= $log['transaction_type'] ?>
                        </span>
                    </td>
                    <td><?= $log['status'] ?></td>
                    <td style="text-align:right; font-weight:600;">$<?= number_format($log['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="4" style="text-align:center; padding: 20px; color:#999;">No transfers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
