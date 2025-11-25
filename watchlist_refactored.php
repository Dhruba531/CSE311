<?php
session_start();
require 'config.php';
require 'includes/csrf.php';
require 'includes/db_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle add to watchlist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_watchlist'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $ticker = trim($_POST['ticker_symbol'] ?? '');
        if (empty($ticker)) {
            $error = 'Please select a stock';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO Watchlist (user_id, ticker_symbol) VALUES (?, ?)");
                $stmt->execute([$user_id, $ticker]);
                $message = 'Stock added to watchlist!';
            } catch (PDOException $e) {
                $error = $e->getCode() == 23000 ? 'Stock already in watchlist' : 'Error adding to watchlist';
            }
        }
    }
}

// Handle remove from watchlist
if (isset($_GET['remove'])) {
    $watchlist_id = (int) $_GET['remove'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Watchlist WHERE watchlist_id = ? AND user_id = ?");
        $stmt->execute([$watchlist_id, $user_id]);
        $message = 'Stock removed from watchlist';
    } catch (PDOException $e) {
        $error = 'Error removing from watchlist';
    }
}

// Get data using helper functions
$watchlist = getUserWatchlist($pdo, $user_id);
$stocks = getUSStocks($pdo);

$page_title = 'Watchlist - Stock Trading';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container">
    <h2>Watchlist</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="content-grid">
        <div class="card">
            <h3>Your Watchlist</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Symbol</th>
                            <th>Company</th>
                            <th>Current Price</th>
                            <th>Change</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($watchlist)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">No stocks in watchlist</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($watchlist as $item): ?>
                                <?php
                                $change = ($item['current_price'] ?? 0) - ($item['previous_close'] ?? 0);
                                $changePercent = $item['previous_close'] > 0
                                    ? (($change / $item['previous_close']) * 100)
                                    : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['ticker_symbol']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['company_name']); ?></td>
                                    <td><?php echo formatCurrency($item['current_price'] ?? 0); ?></td>
                                    <td style="color: <?php echo getPriceChangeColor($change); ?>; font-weight: 600;">
                                        <?php echo getPriceChangeArrow($change); ?>
                                        <?php echo formatCurrency(abs($change)); ?>
                                        (<?php echo formatPercentage($changePercent); ?>)
                                    </td>
                                    <td>
                                        <a href="?remove=<?php echo $item['watchlist_id']; ?>" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Remove from watchlist?')">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3>Add to Watchlist</h3>
            <form method="POST" class="form" id="addWatchlistForm">
                <?php csrf_field(); ?>
                <input type="hidden" name="add_watchlist" value="1">
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
                <button type="submit" class="btn btn-primary btn-block">Add to Watchlist</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>