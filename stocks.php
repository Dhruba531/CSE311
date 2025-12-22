<?php
session_start();
require 'config.php';
require_login();

$uid = $_SESSION['user_id'];
$msg = '';

// Get session messages
if (isset($_SESSION['message'])) {
    $msg = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle stock CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $a = $_POST['action'];
    $t = strtoupper(trim($_POST['ticker_symbol']));
    $c = trim($_POST['company_name']);
    $b = $_POST['business_id'];
    
    // ADMIN ONLY: Handle stock CRUD operations
    if ($uid == 1 && in_array($a, ['create', 'update', 'delete'])) {
        if ($a == 'create') {
            $stmt = $pdo->prepare("INSERT INTO Stock(ticker_symbol, company_name, business_id) VALUES(?, ?, ?)");
            $stmt->execute([$t, $c, $b]);
        }
        if ($a == 'update') {
            $stmt = $pdo->prepare("UPDATE Stock SET company_name=?, business_id=? WHERE ticker_symbol=?");
            $stmt->execute([$c, $b, $t]);
        }
        if ($a == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM Stock WHERE ticker_symbol=?");
            $stmt->execute([$t]);
        }
    } elseif ($a == 'toggle_watchlist') {
        // Toggle Watchlist
        $stmt = $pdo->prepare("SELECT watchlist_id FROM Watchlist WHERE user_id = ? AND ticker_symbol = ?");
        $stmt->execute([$uid, $t]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("DELETE FROM Watchlist WHERE watchlist_id = ?");
            $stmt->execute([$exists['watchlist_id']]);
            $msg = "Removed $t from watchlist.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO Watchlist(user_id, ticker_symbol) VALUES(?, ?)");
            $stmt->execute([$uid, $t]);
            $msg = "Added $t to watchlist.";
        }
        $_SESSION['message'] = $msg; // Persist message after post-redirect-get if used, or just show
    } else {
        // Prevent non-admins from doing this via POST request manipulation
        $_SESSION['message'] = "Unauthorized action.";
    }
}

// Get search and sort parameters
$s = $_GET['search'] ?? '';
$so = $_GET['sort'] ?? 'name';

// Fetch current user's watchlist
$stmt = $pdo->prepare("SELECT ticker_symbol FROM Watchlist WHERE user_id = ?");
$stmt->execute([$uid]);
$my_watchlist = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build stock query
$sql = "SELECT s.ticker_symbol, s.company_name, b.business_name, r.region_name,
               sp.current_price, sp.previous_close
        FROM Stock s
        LEFT JOIN Business b ON s.business_id = b.business_id
        LEFT JOIN Region r ON b.region_id = r.region_id
        LEFT JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol
        WHERE r.region_name = 'North America'";

if ($s) {
    $sql .= " AND (s.ticker_symbol LIKE '%$s%' 
              OR s.company_name LIKE '%$s%' 
              OR b.business_name LIKE '%$s%')";
}

if ($so == 'ticker') {
    $sql .= " ORDER BY s.ticker_symbol";
} elseif ($so == 'price') {
    $sql .= " ORDER BY (sp.current_price - sp.previous_close) DESC";
} else {
    $sql .= " ORDER BY s.company_name";
}

$st = $pdo->query($sql)->fetchAll();

// Get businesses for dropdown
$bz = $pdo->query("SELECT business_id, business_name FROM Business ORDER BY business_name")->fetchAll();

$page_title = 'Market Overview';
require 'includes/header.php';
require 'includes/nav.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h2>Market Overview</h2>
            <p class="subtitle">Live market data and personalized watchlist.</p>
        </div>
        <!-- Search embedded in header for cleaner look -->
        <form method="GET" class="search-form" style="margin:0">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Search stocks..." 
                       value="<?= htmlspecialchars($s) ?>" style="padding-top:0.6rem; padding-bottom:0.6rem; font-size: 0.9rem">
            </div>
            <select name="sort" onchange="this.form.submit()" style="width: auto; padding-top:0.6rem; padding-bottom:0.6rem; font-size: 0.9rem">
                <option value="name" <?= $so == 'name' ? 'selected' : '' ?>>Name</option>
                <option value="ticker" <?= $so == 'ticker' ? 'selected' : '' ?>>Ticker</option>
                <option value="price" <?= $so == 'price' ? 'selected' : '' ?>>% Change</option>
            </select>
        </form>
    </div>

    <?php if ($msg): ?>
        <div class='alert alert-success'><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Stocks Panel -->
    <div class="card fade-in-up" style="padding: 0; box-shadow: var(--shadow); border: 1px solid var(--border)">
        <div class="table-container" style="border:none">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%">Company</th>
                        <th style="width: 20%">Price</th>
                        <th style="width: 20%; text-align: right">Change (1D)</th>
                        <th style="width: 20%; text-align: right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($st)): ?>
                        <tr>
                            <td colspan="4" class="empty-state">No stocks found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($st as $x): ?>
                            <?php
                            $p = $x['current_price'] ?? 0;
                            $o = $x['previous_close'] ?? 0;
                            $ch = $p - $o;
                            $pc = $o > 0 ? ($ch / $o) * 100 : 0;
                            $up = $ch >= 0;
                            ?>
                            <tr>
                                <td>
                                    <div class="stock-row" style="gap: 1rem">
                                        <form method="POST" style="margin-right: 0.25rem">
                                            <input type="hidden" name="action" value="toggle_watchlist">
                                            <input type="hidden" name="ticker_symbol" value="<?= htmlspecialchars($x['ticker_symbol']) ?>">
                                            <button type="submit" style="background:none; border:none; cursor:pointer; color: <?= in_array($x['ticker_symbol'], $my_watchlist) ? '#ffbf00' : '#d1d5db' ?>; font-size: 1.25rem; line-height: 1">
                                                <?= in_array($x['ticker_symbol'], $my_watchlist) ? '★' : '☆' ?>
                                            </button>
                                        </form>
                                        
                                        <div class="stock-icon" style="width:36px; height:36px; font-size:0.9rem; background: #f3f4f6; color: var(--text-main); border-radius: 6px">
                                            <?= htmlspecialchars(substr($x['ticker_symbol'], 0, 1)) ?>
                                        </div>
                                        
                                        <div class="stock-info">
                                            <h4 style="font-size: 0.95rem; margin-bottom: 0.2rem"><?= htmlspecialchars($x['ticker_symbol']) ?></h4>
                                            <span style="font-size: 0.8rem; color: var(--text-muted)"><?= htmlspecialchars($x['company_name']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.95rem">$<?= number_format($p, 2) ?></div>
                                </td>
                                <td style="text-align: right">
                                    <span style="font-weight: 600; color: <?= $up ? 'var(--success)' : 'var(--danger)' ?>; font-size: 0.9rem">
                                        <?= number_format($pc, 2) ?>%
                                    </span>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.1rem">
                                        <?= $up ? '+' : '' ?><?= number_format($ch, 2) ?>
                                    </div>
                                </td>
                                <td style="text-align: right">
                                    <button onclick="openTradeModal('buy', '<?= htmlspecialchars($x['ticker_symbol']) ?>', <?= $p ?>)" 
                                            class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem">
                                        Buy
                                    </button>
                                    <button onclick="openTradeModal('sell', '<?= htmlspecialchars($x['ticker_symbol']) ?>', <?= $p ?>)" 
                                            class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem; color: var(--danger); border-color: var(--border)">
                                        Sell
                                    </button>
                                    <?php if ($uid == 1): ?>
                                         <button onclick="openModal('edit', '<?= htmlspecialchars($x['ticker_symbol']) ?>', '<?= htmlspecialchars(addslashes($x['company_name'])) ?>', <?= (int)($x['business_id'] ?? 0) ?>)" 
                                            style="background:none; border:none; color: var(--text-muted); cursor:pointer; padding: 0.4rem">
                                            Edit
                                         </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Stock -->
<div id="modal" class="modal" hidden>
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 id="modal-title">Add New Stock</h3>
        <form method="POST">
            <input type="hidden" name="action" id="form-action" value="create">
            
            <div class="form-group">
                <label for="ticker">Ticker Symbol</label>
                <input type="text" name="ticker_symbol" id="ticker" required placeholder="e.g. AAPL">
            </div>
            
            <div class="form-group">
                <label for="company">Company Name</label>
                <input type="text" name="company_name" id="company" required placeholder="e.g. Apple Inc.">
            </div>
            
            <div class="form-group">
                <label for="business">Business Sector</label>
                <select name="business_id" id="business" required>
                    <option value="">Select Sector...</option>
                    <?php foreach ($bz as $b): ?>
                        <option value="<?= $b['business_id'] ?>"><?= htmlspecialchars($b['business_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Stock</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Trade -->
<div id="trade-modal" class="modal" hidden>
    <div class="modal-content">
        <span class="close" onclick="closeTradeModal()">&times;</span>
        <h3 id="trade-title">Buy Stock</h3>
        <form method="POST" action="trade.php">
            <input type="hidden" name="action" id="trade-action">
            <input type="hidden" name="ticker_symbol" id="trade-ticker">
            
            <div style="background: var(--background); padding: 1rem; border-radius: .5rem; margin-bottom: 1.5rem; 
                        display: flex; justify-content: space-between; align-items: center">
                <span style="color: var(--text-muted)">Current Price</span>
                <strong id="trade-price" style="font-size: 1.25rem">$0.00</strong>
            </div>
            
            <div class="form-group">
                <label>Order Type</label>
                <div style="display:flex; gap:1rem; margin-bottom:1rem">
                    <label style="display:inline-flex; align-items:center; gap:0.5rem; font-weight:normal">
                        <input type="radio" name="order_type" value="market" checked onchange="toggleLimitInput(false)"> Market
                    </label>
                    <label style="display:inline-flex; align-items:center; gap:0.5rem; font-weight:normal">
                        <input type="radio" name="order_type" value="limit" onchange="toggleLimitInput(true)"> Limit
                    </label>
                </div>
            </div>

            <div class="form-group" id="limit-price-group" hidden>
                <label for="limit_price">Limit Price ($)</label>
                <input type="number" id="limit_price" name="limit_price" step="0.01" min="0.01" placeholder="Target Price">
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" id="quantity" min="1" required 
                       placeholder="Number of shares" oninput="calcTotal()">
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; 
                        padding-top: 1rem; border-top: 1px solid var(--border)">
                <span>Total Cost</span>
                <strong id="trade-total" style="color: var(--primary); font-size: 1.25rem">$0.00</strong>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeTradeModal()">Cancel</button>
                <button type="submit" id="trade-btn" class="btn btn-primary">Confirm Trade</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(t, k = '', c = '', b = '') {
    const m = document.getElementById('modal');
    const h = document.getElementById('modal-title');
    const a = document.getElementById('form-action');
    const ti = document.getElementById('ticker');
    const co = document.getElementById('company');
    const bi = document.getElementById('business');
    
    m.removeAttribute('hidden');
    a.value = t === 'create' ? 'create' : 'update';
    h.textContent = t === 'create' ? 'Add New Stock' : 'Edit Stock';
    ti.value = k;
    co.value = c;
    bi.value = b;
    
    if (t === 'edit') {
        ti.setAttribute('readonly', 'readonly');
        ti.style.backgroundColor = '#f1f5f9';
    } else {
        ti.removeAttribute('readonly');
        ti.style.backgroundColor = '';
    }
}

function closeModal() {
    document.getElementById('modal').setAttribute('hidden', '');
}

let priceNow = 0;

function openTradeModal(t, k, p) {
    const m = document.getElementById('trade-modal');
    const h = document.getElementById('trade-title');
    const a = document.getElementById('trade-action');
    const ti = document.getElementById('trade-ticker');
    const pd = document.getElementById('trade-price');
    const btn = document.getElementById('trade-btn');
    const q = document.getElementById('quantity');
    const tot = document.getElementById('trade-total');
    
    priceNow = p;
    m.removeAttribute('hidden');
    a.value = t;
    ti.value = k;
    pd.textContent = '$' + p.toFixed(2);
    h.textContent = (t === 'buy' ? 'Buy' : 'Sell') + ' ' + k;
    btn.textContent = t === 'buy' ? 'Confirm Purchase' : 'Confirm Sale';
    q.value = '';
    tot.textContent = '$0.00';
    
    if (t === 'sell') {
        btn.style.backgroundColor = 'var(--danger)';
    } else {
        btn.style.backgroundColor = '';
    }
}

function closeTradeModal() {
    document.getElementById('trade-modal').setAttribute('hidden', '');
}

function toggleLimitInput(isLimit) {
    const g = document.getElementById('limit-price-group');
    const p = document.getElementById('limit_price');
    if (isLimit) {
        g.removeAttribute('hidden');
        p.setAttribute('required', 'required');
    } else {
        g.setAttribute('hidden', '');
        p.removeAttribute('required');
    }
}

function calcTotal() {
    let q = document.getElementById('quantity').value || 0;
    // Use limit price if set and visible, otherwise market price
    let isLimit = document.querySelector('input[name="order_type"]:checked').value === 'limit';
    let p = isLimit ? (document.getElementById('limit_price').value || 0) : priceNow;
    
    document.getElementById('trade-total').textContent = '$' + (q * p).toFixed(2);
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.setAttribute('hidden', '');
    }
}
</script>

<?php require 'includes/footer.php'; ?>