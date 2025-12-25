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
    $sector = $_POST['sector']; // Changed from business_id
    
    if ($a == 'toggle_watchlist') {
        // Toggle Watchlist (New Schema: Simple Watchlist table)
        
        $stmt = $pdo->prepare("SELECT 1 FROM Watchlist WHERE user_id = ? AND ticker_symbol = ?");
        $stmt->execute([$uid, $t]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("DELETE FROM Watchlist WHERE user_id = ? AND ticker_symbol = ?");
            $stmt->execute([$uid, $t]);
            $msg = "Removed $t from watchlist.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO Watchlist(user_id, ticker_symbol) VALUES(?, ?)");
            $stmt->execute([$uid, $t]);
            $msg = "Added $t to watchlist.";
        }
        
        $_SESSION['message'] = $msg;
    } else {
        $_SESSION['message'] = "Unauthorized action.";
    }
}

// Get search and sort parameters
$s = $_GET['search'] ?? '';
$so = $_GET['sort'] ?? 'name';

// Fetch current user's watchlist tickers (New Schema)
$stmt = $pdo->prepare("SELECT ticker_symbol FROM Watchlist WHERE user_id = ?");
$stmt->execute([$uid]);
$my_watchlist = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch current user's holdings to determine Sell eligibility
$stmt = $pdo->prepare("SELECT ticker_symbol, quantity FROM Holdings WHERE user_id = ?");
$stmt->execute([$uid]);
$my_holdings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Build stock query (New Schema: Stocks)
// Joining Traded_On -> Exchange if needed, but for now simple select
$sql = "SELECT ticker_symbol, stock_name as company_name, 'General' as sector, current_price
        FROM Stocks
        WHERE 1=1";

if ($s) {
    $sql .= " AND (ticker_symbol LIKE '%$s%' OR stock_name LIKE '%$s%')";
}

if ($so == 'ticker') {
    $sql .= " ORDER BY ticker_symbol";
} elseif ($so == 'price') {
    $sql .= " ORDER BY current_price DESC";
} else {
    $sql .= " ORDER BY stock_name";
}

$st = $pdo->query($sql)->fetchAll();

// Mock sectors for dropdown
$sectors = ['Technology', 'Healthcare', 'Finance', 'Consumer', 'Energy'];

$page_title = 'Market Overview';
require 'includes/header.php';
require 'includes/nav.php';
?>

    <!-- Page Header -->
    <div class="flex-between mb-4">
        <div>
            <h1>Market Overview</h1>
            <p class="text-muted">Live market data and personalized watchlist.</p>
        </div>
        
        <!-- Search & Filter Actions -->
        <form method="GET" class="flex items-center gap-2" style="display:flex; gap:10px;">
            <div style="position:relative;">
                <input type="text" name="search" placeholder="Search stocks..." 
                       value="<?= htmlspecialchars($s) ?>" 
                       style="padding: 12px 20px 12px 40px; border-radius: 999px; border: 1px solid var(--border-color); font-size: 0.9rem; width: 300px; box-shadow: var(--shadow-sm); outline: none;">
                <i class="fas fa-search" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#999;"></i>
            </div>
            
            <select name="sort" onchange="this.form.submit()" 
                    style="padding: 12px 20px; border-radius: 999px; border: 1px solid var(--border-color); background:white; font-size: 0.9rem; cursor: pointer; outline: none; box-shadow: var(--shadow-sm);">
                <option value="name" <?= $so == 'name' ? 'selected' : '' ?>>Sort by Name</option>
                <option value="ticker" <?= $so == 'ticker' ? 'selected' : '' ?>>Sort by Ticker</option>
                <option value="price" <?= $so == 'price' ? 'selected' : '' ?>>Sort by Price</option>
            </select>
        </form>
    </div>

    <?php if ($msg): ?>
        <div style="background: #dcfce7; color: #15803d; padding: 12px 20px; border-radius: 1rem; margin-bottom: 20px; font-weight: 600; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <!-- Stocks Table Card -->
    <div class="card-premium" style="padding: 0; overflow: hidden;">
        <div style="padding: 2rem;">
             <table class="table-minimal">
                <thead>
                    <tr>
                        <th style="width: 40%; background: #f4f4f5; border-radius: 12px 0 0 12px; padding: 16px; font-weight: 700; color: #52525b; border:none;">Instrument</th>
                        <th style="width: 15%; background: #f4f4f5; padding: 16px; font-weight: 700; color: #52525b; border:none;">Sector</th>
                        <th style="width: 15%; background: #f4f4f5; padding: 16px; font-weight: 700; color: #52525b; border:none; text-align: right;">Price</th>
                        <th style="width: 15%; background: #f4f4f5; padding: 16px; font-weight: 700; color: #52525b; border:none; text-align: right;">Change (1D)</th>
                        <th style="width: 15%; background: #f4f4f5; border-radius: 0 12px 12px 0; padding: 16px; font-weight: 700; color: #52525b; border:none; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody style="display: block; margin-top: 10px;"> <!-- Add spacing between header and body -->
                    <!-- Note: For proper alignment with block display, simpler to just keep standard table but space the THs. 
                         Instead of splitting the row, let's just style the THs as floating boxes. -->
                </tbody>
                <!-- Resetting tbody style concept and just styling THs individually with margin effect via border-spacing if needed, 
                     but standard contiguous header is safer. Let's stick to the "Rounded Bar" look for the whole header row 
                     OR individual round boxes if using border-collapse: separate. -->
                     
                <!-- Retry with border-collapse separate to allow spacing between rounded headers? 
                     User said "make a round box EACH colum". So distinct boxes. -->
                
                <style>
                    .table-minimal { border-collapse: separate; border-spacing: 0 8px; } 
                    .table-minimal thead tr th { border-bottom: none; }
                </style>

                <thead>
                    <tr style="margin-bottom: 20px;">
                        <th style="width: 40%; background: #f4f4f5; border-radius: 1rem; padding: 12px 20px; font-weight: 800; color: #71717a; font-size: 0.8rem; text-transform:uppercase; letter-spacing:0.05em; margin-right: 10px;">Instrument</th>
                        <th style="width: 15%; background: #f4f4f5; border-radius: 1rem; padding: 12px 20px; font-weight: 800; color: #71717a; font-size: 0.8rem; text-transform:uppercase; letter-spacing:0.05em; text-align:center;">Sector</th>
                        <th style="width: 15%; background: #f4f4f5; border-radius: 1rem; padding: 12px 20px; font-weight: 800; color: #71717a; font-size: 0.8rem; text-transform:uppercase; letter-spacing:0.05em; text-align: right;">Price</th>
                        <th style="width: 15%; background: #f4f4f5; border-radius: 1rem; padding: 12px 20px; font-weight: 800; color: #71717a; font-size: 0.8rem; text-transform:uppercase; letter-spacing:0.05em; text-align: right;">Change</th>
                        <th style="width: 15%; background: #f4f4f5; border-radius: 1rem; padding: 12px 20px; font-weight: 800; color: #71717a; font-size: 0.8rem; text-transform:uppercase; letter-spacing:0.05em; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($st)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 60px; color: var(--text-secondary);">
                                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i><br>
                                <?php if ($s): ?>
                                    No stocks found matching "<?= htmlspecialchars($s) ?>"
                                <?php else: ?>
                                    <div style="margin-top:10px;">
                                        <p style="margin-bottom:15px;">No market data found.</p>
                                        <a href="setup_db.php" class="btn-primary" style="background:var(--accent); text-decoration:none;">
                                            <i class="fa-solid fa-database"></i> Initialize Database
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($st as $x): ?>
                            <?php
                            $p = $x['current_price'] ?? 0;
                            $o = $x['previous_close'] ?? 0;
                            // Mock change logic if no history
                            if ($o == 0) $o = $p * (1 - (rand(-20, 20) / 1000)); 
                            
                            $ch = $p - $o;
                            $pc = $o > 0 ? ($ch / $o) * 100 : 0;
                            $up = $ch >= 0;
                            
                            // Check if owned
                            $owned_qty = $my_holdings[$x['ticker_symbol']] ?? 0;
                            ?>
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td>
                                    <div style="display:flex; align-items:center; gap: 16px;">
                                        <!-- Watchlist Toggle -->
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="action" value="toggle_watchlist">
                                            <input type="hidden" name="ticker_symbol" value="<?= htmlspecialchars($x['ticker_symbol']) ?>">
                                            <button type="submit" style="background:none; border:none; cursor:pointer; color: <?= in_array($x['ticker_symbol'], $my_watchlist) ? '#fbbf24' : '#e4e4e7' ?>; font-size: 1.2rem; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Avatar -->
                                        <div style="width: 44px; height: 44px; background: <?= $up ? '#dcfce7' : '#fee2e2' ?>; border-radius: 12px; display:flex; align-items:center; justify-content:center; font-weight:800; color: <?= $up ? '#166534' : '#991b1b' ?>; font-size: 0.95rem;">
                                            <?= htmlspecialchars(substr($x['ticker_symbol'], 0, 1)) ?>
                                        </div>
                                        
                                        <!-- Info -->
                                        <div>
                                            <a href="stock_details.php?ticker=<?= htmlspecialchars($x['ticker_symbol']) ?>" style="text-decoration:none; color:inherit; display:block;">
                                                <div style="font-weight: 700; color: var(--text-primary); line-height:1.2; font-size: 0.95rem;"><?= htmlspecialchars($x['ticker_symbol']) ?></div>
                                                <div style="font-size: 0.8rem; color: var(--text-secondary);"><?= htmlspecialchars($x['company_name']) ?></div>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background:#f4f4f5; color:#52525b; border-radius: 999px; padding: 4px 12px; font-size: 0.75rem;">
                                        <?= htmlspecialchars($x['sector'] ?? 'General') ?>
                                    </span>
                                </td>
                                <td style="font-family: 'Inter', monospace; font-weight: 600; font-size: 0.95rem; text-align: right; color: <?= $up ? 'var(--accent)' : 'var(--danger)' ?>;">
                                    $<?= number_format($p, 2) ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="font-weight: 700; color: <?= $up ? 'var(--accent)' : 'var(--danger)' ?>; font-size: 0.9rem;">
                                        <?= $up ? '+' : '' ?><?= number_format($pc, 2) ?>%
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        <?= $up ? '+' : '' ?><?= number_format($ch, 2) ?>
                                    </div>
                                </td>
                                <td style="text-align: right; display:flex; justify-content:flex-end; gap:8px;">
                                    <?php if ($owned_qty > 0): ?>
                                    <button onclick="openTradeModal('sell', '<?= htmlspecialchars($x['ticker_symbol']) ?>', <?= $p ?>)" 
                                            class="btn-primary" style="padding: 8px 20px; font-size: 0.75rem; border-radius: 999px; background: var(--danger);">
                                        Sell
                                    </button>
                                    <?php endif; ?>
                                    <button onclick="openTradeModal('buy', '<?= htmlspecialchars($x['ticker_symbol']) ?>', <?= $p ?>)" 
                                            class="btn-primary" style="padding: 8px 20px; font-size: 0.75rem; border-radius: 999px;">
                                        Buy
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>



<!-- Modal: Trade -->
<div id="trade-modal" class="modal" hidden>
    <div class="modal-content" style="border-radius: 1.5rem; padding: 2rem; max-width: 450px; box-shadow: var(--shadow-card);">
        <div class="flex-between mb-4">
            <h3 id="trade-title" style="font-size: 1.25rem;">Buy Stock</h3>
            <span class="close" onclick="closeTradeModal()" style="cursor:pointer; font-size: 1.5rem; color:#aaa;">&times;</span>
        </div>
        
        <form method="POST" action="trade.php">
            <input type="hidden" name="action" id="trade-action">
            <input type="hidden" name="ticker_symbol" id="trade-ticker">
            
            <div style="background: var(--bg-main); padding: 1.5rem; border-radius: 1rem; margin-bottom: 2rem; 
                        display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color);">
                <span class="text-muted" style="font-weight: 600; font-size: 0.8rem; text-transform: uppercase;">Current Price</span>
                <strong id="trade-price" style="font-size: 1.75rem; color: var(--text-primary); letter-spacing: -1px;">$0.00</strong>
            </div>
            
            <div class="form-group mb-4">
                <label style="display:block; font-size:0.75rem; font-weight:800; color:var(--text-secondary); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing: 0.05em;">Order Type</label>
                <div style="display:flex; gap:1rem; padding: 4px; background: var(--bg-main); border-radius: 999px; display: inline-flex;">
                    <label style="display:inline-flex; align-items:center; gap:0.5rem; font-weight:600; font-size: 0.85rem; cursor:pointer; padding: 8px 16px; background:white; border-radius: 999px; box-shadow: var(--shadow-sm);">
                        <input type="radio" name="order_type" value="market" checked onchange="toggleLimitInput(false)" style="accent-color:black;"> Market
                    </label>
                    <label style="display:inline-flex; align-items:center; gap:0.5rem; font-weight:600; font-size: 0.85rem; cursor:pointer; padding: 8px 16px;">
                        <input type="radio" name="order_type" value="limit" onchange="toggleLimitInput(true)" style="accent-color:black;"> Limit
                    </label>
                </div>
            </div>

            <div class="form-group mb-4" id="limit-price-group" hidden>
                <label for="limit_price" style="display:block; font-size:0.75rem; font-weight:800; color:var(--text-secondary); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing: 0.05em;">Limit Price ($)</label>
                <input type="number" id="limit_price" name="limit_price" step="0.01" min="0.01" placeholder="Target Price"
                       style="width:100%; padding: 1rem; border: 1px solid var(--border-color); border-radius: 1rem; font-family: 'Inter'; font-size: 1rem; outline: none; transition: border-color 0.2s;">
            </div>

            <div class="form-group mb-4">
                <label for="quantity" style="display:block; font-size:0.75rem; font-weight:800; color:var(--text-secondary); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing: 0.05em;">Quantity</label>
                <input type="number" name="quantity" id="quantity" min="1" required 
                       placeholder="0" oninput="calcTotal()"
                       style="width:100%; padding: 1rem; border: 1px solid var(--border-color); border-radius: 1rem; font-family: 'Inter'; font-size: 1rem; outline: none; transition: border-color 0.2s;">
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; 
                        padding-top: 1.5rem; border-top: 1px dashed var(--border-color);">
                <span style="font-weight: 600; color: var(--text-secondary);">Estimated Total</span>
                <strong id="trade-total" style="color: var(--primary); font-size: 1.25rem">$0.00</strong>
            </div>
            
            <button type="submit" id="trade-btn" class="btn-primary" style="width: 100%; padding: 1rem; font-size: 0.9rem;">Confirm Trade</button>
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