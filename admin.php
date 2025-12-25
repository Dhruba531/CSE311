<?php
session_start();
require 'config.php';
require_login();

// Simple Admin Check (In a real app, use a role column)
// For now, we assume user_id 1 is admin, or we add a role check if we added that column.
// Let's just check if it's the "admin" user by name or ID.
// Simple Admin Check
$uid = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->execute([$uid]);
$u = $stmt->fetch();

if ($uid != 1) {
    $_SESSION['message'] = "Unauthorized access.";
    header("Location: index.php");
    exit;
}

// Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_money = $pdo->query("SELECT SUM(balance) FROM Accounts")->fetchColumn();
$total_stocks = $pdo->query("SELECT COUNT(*) FROM Instruments")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM Orders WHERE status = 'PENDING'")->fetchColumn();

// Handle Add Instrument (Stock)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_stock') {
    $t = strtoupper(trim($_POST['ticker_symbol']));
    $c = trim($_POST['company_name']);
    $s = trim($_POST['sector']);
    
    // Basic dup check
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Instruments WHERE ticker_symbol = ?");
    $stmt->execute([$t]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        // Insert Instrument
        $stmt = $pdo->prepare("INSERT INTO Instruments(ticker_symbol, name, sector, current_price, exchange_id) VALUES(?, ?, ?, 100.00, 1)");
        $stmt->execute([$t, $c, $s]);
        
        $msg = "Instrument added: " . htmlspecialchars($t);
    } else {
        $error = "Ticker already exists.";
    }
}

// Fetch all stocks for management
$stocks = $pdo->query("SELECT * FROM Instruments ORDER BY ticker_symbol")->fetchAll();
$sectors = ['Technology', 'Healthcare', 'Finance', 'Energy', 'Consumer'];

$page_title = 'Admin Dashboard';
require 'includes/header.php';
require 'includes/nav.php';
?>

<div class="container">
    <div class="page-header">
        <h2>Admin Dashboard</h2>
        <div style="color: var(--text-muted)">Welcome back, Admin</div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3>Total Users</h3>
                <p class="stat-value"><?= number_format($total_users) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>System Funds</h3>
                <p class="stat-value">$<?= number_format($total_money, 2) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
                <h3>Listed Instruments</h3>
                <p class="stat-value"><?= number_format($total_stocks) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3>Pending Orders</h3>
                <p class="stat-value"><?= number_format($pending_orders) ?></p>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Stock Management -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3>Stock Management</h3>
            </div>
            
            <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Ticker</th>
                            <th>Company</th>
                            <th>Sector</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['ticker_symbol']) ?></strong></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['sector'] ?? 'N/A') ?></td>
                            <td>
                                <button class="btn btn-secondary" style="padding:0.25rem 0.5rem; font-size:0.8rem">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Stock Form -->
        <div class="card">
            <h3>Add New Instrument</h3>
            <?php if (isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
            <?php if (isset($error)) echo "<div class='alert alert-error'>$error</div>"; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="create_stock">
                <div class="form-group">
                    <label>Ticker Symbol</label>
                    <input type="text" name="ticker_symbol" required placeholder="e.g. NVDA" maxlength="5" style="text-transform:uppercase">
                </div>
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" required placeholder="e.g. NVIDIA Corp">
                </div>
                <div class="form-group">
                    <label>Sector</label>
                    <select name="sector" required>
                        <?php foreach ($sectors as $b): ?>
                            <option value="<?= $b ?>"><?= $b ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">List Instrument</button>
            </form>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
