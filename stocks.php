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

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $ticker_symbol = strtoupper(trim($_POST['ticker_symbol'] ?? ''));
            $company_name = trim($_POST['company_name'] ?? '');
            $business_id = (int) ($_POST['business_id'] ?? 0);

            if (empty($ticker_symbol) || empty($company_name) || $business_id <= 0) {
                $error = 'Please fill in all fields.';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO Stock (ticker_symbol, company_name, business_id) VALUES (?, ?, ?)");
                    $stmt->execute([$ticker_symbol, $company_name, $business_id]);
                    $message = 'Stock created successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to create stock. Symbol may already exist.';
                }
            }
        } elseif ($action === 'update') {
            $ticker_symbol = strtoupper(trim($_POST['ticker_symbol'] ?? ''));
            $company_name = trim($_POST['company_name'] ?? '');
            $business_id = (int) ($_POST['business_id'] ?? 0);

            if (empty($ticker_symbol) || empty($company_name) || $business_id <= 0) {
                $error = 'Please fill in all fields.';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE Stock SET company_name = ?, business_id = ? WHERE ticker_symbol = ?");
                    $stmt->execute([$company_name, $business_id, $ticker_symbol]);
                    $message = 'Stock updated successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to update stock.';
                }
            }
        } elseif ($action === 'delete') {
            $ticker_symbol = trim($_POST['ticker_symbol'] ?? '');
            if (!empty($ticker_symbol)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM Stock WHERE ticker_symbol = ?");
                    $stmt->execute([$ticker_symbol]);
                    $message = 'Stock deleted successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to delete stock. It may have existing transactions.';
                }
            }
        }
    }
}


// Get all stocks with business info
// Handle Search and Sort
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'name_asc';

$query = "
    SELECT s.*, b.company_name as business_name, b.year_established, r.region_name,
           sp.current_price, sp.previous_close
    FROM Stock s
    LEFT JOIN Business b ON s.business_id = b.business_id
    LEFT JOIN Region r ON b.region_id = r.region_id
    LEFT JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol
    WHERE r.region_name = 'North America'
";

$params = [];
if (!empty($search)) {
    $query .= " AND (s.ticker_symbol LIKE ? OR s.company_name LIKE ? OR b.company_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

switch ($sort) {
    case 'name_desc':
        $query .= " ORDER BY s.company_name DESC";
        break;
    case 'ticker_asc':
        $query .= " ORDER BY s.ticker_symbol ASC";
        break;
    case 'ticker_desc':
        $query .= " ORDER BY s.ticker_symbol DESC";
        break;
    case 'year_asc':
        $query .= " ORDER BY b.year_established ASC";
        break;
    case 'year_desc':
        $query .= " ORDER BY b.year_established DESC";
        break;
    case 'price_change_high':
        $query .= " ORDER BY (sp.current_price - sp.previous_close) DESC";
        break;
    case 'price_change_low':
        $query .= " ORDER BY (sp.current_price - sp.previous_close) ASC";
        break;
    default: // name_asc
        $query .= " ORDER BY s.company_name ASC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$stocks = $stmt->fetchAll();

// Get businesses for dropdown
$stmt = $pdo->query("SELECT * FROM Business ORDER BY company_name");
$businesses = $stmt->fetchAll();

// Get exchanges for each stock
$stock_exchanges = [];
foreach ($stocks as $stock) {
    $stmt = $pdo->prepare("
        SELECT e.exchange_name, e.short_code
        FROM traded_on t
        JOIN Exchange e ON t.exchange_id = e.exchange_id
        WHERE t.ticker_symbol = ?
    ");
    $stmt->execute([$stock['ticker_symbol']]);
    $stock_exchanges[$stock['ticker_symbol']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stocks Management - Stock Trading</title>
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
                <li><a href="stocks.php" class="active">Stocks</a></li>
                <li><a href="buy_sell.php">Trade</a></li>
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
            <h2>Stocks Management</h2>
        </div>

        <div class="search-filter-card">
            <form method="GET" action="stocks.php" class="search-form">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="search" placeholder="Search stocks..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="filter-group">
                    <select name="sort" onchange="this.form.submit()" class="custom-select">
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)
                        </option>
                        <option value="ticker_asc" <?php echo $sort === 'ticker_asc' ? 'selected' : ''; ?>>Ticker (A-Z)
                        </option>
                        <option value="ticker_desc" <?php echo $sort === 'ticker_desc' ? 'selected' : ''; ?>>Ticker (Z-A)
                        </option>
                        <option value="year_asc" <?php echo $sort === 'year_asc' ? 'selected' : ''; ?>>Year (Oldest First)
                        </option>
                        <option value="year_desc" <?php echo $sort === 'year_desc' ? 'selected' : ''; ?>>Year (Newest
                            First)</option>
                        <option value="price_change_high" <?php echo $sort === 'price_change_high' ? 'selected' : ''; ?>>
                            Price Change (High to Low)</option>
                        <option value="price_change_low" <?php echo $sort === 'price_change_low' ? 'selected' : ''; ?>>
                            Price Change (Low to High)</option>
                    </select>

                    <button type="submit" class="btn btn-primary search-btn">Search</button>

                    <?php if (!empty($search)): ?>
                        <a href="stocks.php" class="btn btn-secondary clear-btn" title="Clear Search">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Ticker Symbol</th>
                            <th>Company Name</th>
                            <th>Business</th>
                            <th>Year Established</th>
                            <th>Region</th>
                            <th>Exchanges</th>
                            <th>Price Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stocks)): ?>
                            <tr>
                                <td colspan="7" class="empty-state">No stocks found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stocks as $stock): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($stock['ticker_symbol']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($stock['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($stock['business_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo $stock['year_established'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($stock['region_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $exchanges = $stock_exchanges[$stock['ticker_symbol']] ?? [];
                                        if (empty($exchanges)) {
                                            echo 'None';
                                        } else {
                                            echo implode(', ', array_map(function ($e) {
                                                return htmlspecialchars($e['short_code']);
                                            }, $exchanges));
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($stock['current_price'] && $stock['previous_close']) {
                                            $change = $stock['current_price'] - $stock['previous_close'];
                                            $changePercent = ($change / $stock['previous_close']) * 100;

                                            if ($change > 0) {
                                                $color = '#22c55e'; // Green
                                                $arrow = '▲';
                                            } elseif ($change < 0) {
                                                $color = '#ef4444'; // Red
                                                $arrow = '▼';
                                            } else {
                                                $color = '#94a3b8'; // Gray
                                                $arrow = '━';
                                            }

                                            echo '<span style="color: ' . $color . '; font-weight: 600;">';
                                            echo $arrow . ' $' . number_format(abs($change), 2);
                                            echo ' (' . number_format(abs($changePercent), 2) . '%)';
                                            echo '</span>';
                                        } else {
                                            echo '<span style="color: #64748b;">N/A</span>';
                                        }
                                        ?>
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
            <h3 id="modal-title">Add New Stock</h3>
            <form method="POST" action="stocks.php" id="stock-form">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" id="form-action" value="create">

                <div class="form-group">
                    <label for="ticker_symbol">Ticker Symbol</label>
                    <input type="text" id="ticker_symbol" name="ticker_symbol" required placeholder="e.g., AAPL"
                        maxlength="10" style="text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" required placeholder="Enter company name">
                </div>

                <div class="form-group">
                    <label for="business_id">Business</label>
                    <select id="business_id" name="business_id" required>
                        <option value="">Select a business</option>
                        <?php foreach ($businesses as $business): ?>
                            <option value="<?php echo $business['business_id']; ?>">
                                <?php echo htmlspecialchars($business['company_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(action, ticker = '', company = '', businessId = 0) {
            const modal = document.getElementById('modal');
            const form = document.getElementById('stock-form');
            const title = document.getElementById('modal-title');
            const tickerInput = document.getElementById('ticker_symbol');

            document.getElementById('form-action').value = action;

            if (action === 'create') {
                title.textContent = 'Add New Stock';
                form.reset();
                tickerInput.removeAttribute('readonly');
            } else {
                title.textContent = 'Edit Stock';
                tickerInput.value = ticker;
                tickerInput.setAttribute('readonly', 'readonly');
                document.getElementById('company_name').value = company;
                document.getElementById('business_id').value = businessId;
            }

            modal.style.display = 'flex';
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