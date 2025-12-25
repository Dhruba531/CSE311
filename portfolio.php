<?php
session_start();
require 'config.php';
require_login();

$uid = $_SESSION['user_id'];
$msg = '';

// Handle deposit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deposit') {
    $amt = (float)$_POST['amount'];
    if ($amt > 0) {
        $stmt = $pdo->prepare("UPDATE Accounts SET balance = balance + ?, buying_power = buying_power + ? WHERE user_id = ?");
        $stmt->execute([$amt, $amt, $uid]); // Update both balance and buying power
        $msg = 'Funds deposited successfully.';
    }
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'update_profile') {
    $newName = trim($_POST['full_name']);
    $newUser = trim($_POST['username']);
    $newEmail = trim($_POST['email']);
    
    // Check for duplicates
    $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE (username = ? OR email = ?) AND user_id != ?");
    $stmt->execute([$newUser, $newEmail, $uid]);
    if ($stmt->fetch()) {
        $msg = 'Error: Username or Email already exists.';
        $msgType = 'red';
    } else {
        $stmt = $pdo->prepare("UPDATE Users SET full_name = ?, username = ?, email = ? WHERE user_id = ?");
        $stmt->execute([$newName, $newUser, $newEmail, $uid]);
        
        // Update Session
        $_SESSION['full_name'] = $newName;
        $_SESSION['username'] = $newUser; // If you start storing username in session
        
        $msg = 'Profile updated successfully!';
        $msgType = 'green';
        
        // Refresh User Data for display
        $stmt = $pdo->prepare("SELECT full_name, username, email FROM Users WHERE user_id = ?");
        $stmt->execute([$uid]);
        $u = $stmt->fetch();
    }
}

// Always Fetch User Details (for display)
$stmt = $pdo->prepare("SELECT full_name, username, email FROM Users WHERE user_id = ?");
$stmt->execute([$uid]);
$u = $stmt->fetch();

if (!$u) {
    header("Location: logout.php");
    exit;
}

// Get account balance
$stmt = $pdo->prepare("SELECT balance, buying_power, account_id FROM Accounts WHERE user_id = ?");
$stmt->execute([$uid]);
$account = $stmt->fetch();
$bal = $account ? $account['balance'] : 0;
$bp = $account ? $account['buying_power'] : 0;
$accountId = $account ? $account['account_id'] : 0;

// Get Current Holdings
$stmt = $pdo->prepare("
    SELECT s.ticker_symbol, s.stock_name as company_name, s.current_price, 
           h.quantity as shares, h.avg_price as average_entry_price
    FROM Holdings h
    JOIN Stocks s ON h.ticker_symbol = s.ticker_symbol
    WHERE h.user_id = ?
");
$stmt->execute([$uid]);
$h = $stmt->fetchAll();

// Calculate total portfolio value
$tv = 0; // Holdings Value
foreach ($h as $x) {
    if(isset($x['shares'])) {
        $tv += $x['shares'] * $x['current_price'];
    }
}
$total_equity = $bal + $tv; // Net Liquidation Value

// Get Transaction History (Real Data)
$stmt = $pdo->prepare("
    SELECT date_time as timestamp, transaction_type as side, quantity, cost as execution_price, ticker_symbol, status, order_type
    FROM Transactions
    WHERE user_id = ?
    ORDER BY date_time DESC
    LIMIT 10
");
$stmt->execute([$uid]);
$ht = $stmt->fetchAll();

$page_title = 'My Portfolio';
require 'includes/header.php';
require 'includes/nav.php';
?>

    <!-- Page Header -->
    <div class="flex-between mb-4" style="align-items: flex-end;">
        <div>
            <h1>My Portfolio</h1>
            <div style="margin-top: 5px; font-weight: 500; display:flex; align-items:center; gap: 10px;">
                <div>
                    <span style="color: #18181b; font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($u['full_name']) ?></span>
                    <span style="color: #d4d4d8; margin: 0 8px;">&bull;</span>
                    <span style="color: #71717a;">@<?= htmlspecialchars($u['username']) ?></span>
                    <span style="color: #d4d4d8; margin: 0 8px;">&bull;</span>
                    <span style="color: #71717a; font-size: 0.9rem;"><?= htmlspecialchars($u['email']) ?></span>
                </div>
                <button onclick="document.getElementById('editProfileModal').style.display='flex'" style="background:none; border:none; color:var(--accent); cursor:pointer; font-weight:600; font-size:0.8rem;">
                    <i class="fa-solid fa-pen"></i> Edit
                </button>
            </div>
        </div>
        <div>
            <span class="badge badge-green">Pro Account</span>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:30px; border-radius:16px; width:400px; max-width:90%; position:relative;">
            <h3 style="margin-bottom:20px;">Edit Profile</h3>
            <button onclick="document.getElementById('editProfileModal').style.display='none'" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:1.2rem; cursor:pointer;">&times;</button>
            
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:5px; color:#555;">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($u['full_name']) ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:5px; color:#555;">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:5px; color:#555;">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                
                <button type="submit" class="btn-primary" style="width:100%;">Save Changes</button>
            </form>
        </div>
    </div>

    <?php if ($msg): ?>
        <div style="background: #dcfce7; color: #15803d; padding: 12px 20px; border-radius: 1rem; margin-bottom: 20px; font-weight: 600; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        
        <!-- Net Equity Card -->
        <div class="card-premium">
            <h3 class="mb-4" style="font-size: 0.9rem; text-transform: uppercase;">Total Net Equity</h3>
            <div style="font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.5rem;">
                $<?= number_format($total_equity, 2) ?>
            </div>
            <div class="text-small text-muted">
                <span style="color: var(--accent); font-weight: 700;">+0.00%</span> today
            </div>
        </div>

        <!-- Buying Power Card -->
        <div class="card-premium">
            <h3 class="mb-4" style="font-size: 0.9rem; text-transform: uppercase;">Buying Power</h3>
            <div style="font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.5rem;">
                $<?= number_format($bp, 2) ?>
            </div>
             <div class="text-small text-muted">
                Available to trade
            </div>
        </div>

        <!-- Deposit Funds Card -->
        <div class="card-premium">
            <h3 class="mb-4" style="font-size: 0.9rem; text-transform: uppercase;">Quick Deposit</h3>
            <form method="POST" style="display: flex; gap: 10px;">
                <input type="hidden" name="action" value="deposit">
                <div style="flex:1">
                     <input type="number" name="amount" step="100" min="100" required placeholder="$ Amount"
                       style="width:100%; padding: 10px 15px; border-radius: 999px; border: 1px solid var(--border-color); font-weight: 600; outline: none;">
                </div>
                <button type="submit" class="btn-primary" style="padding: 10px 20px; border-radius: 999px; font-size: 0.8rem;">
                    Deposit
                </button>
            </form>
             <div class="text-small text-muted mt-2" style="font-size: 0.75rem;">
                Instant transfer via linked bank.
            </div>
        </div>

        <!-- Holdings Table (Spans 2 columns) -->
        <div class="card-premium col-span-2">
            <div class="flex-between mb-4">
                <h3>Current Holdings</h3>
            </div>
            
            <table class="table-minimal">
                <thead>
                    <tr>
                        <th style="width: 30%">Instrument</th>
                        <th style="width: 15%">Shares</th>
                        <th style="width: 20%; text-align: right">Avg Cost</th>
                        <th style="width: 20%; text-align: right">Current Price</th>
                        <th style="width: 15%; text-align: right">Market Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($h)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">No positions open.</td></tr>
                    <?php else: ?>
                        <?php foreach ($h as $pos): ?>
                            <?php 
                                $val = $pos['shares'] * $pos['current_price']; 
                                $gain = ($pos['current_price'] - $pos['average_entry_price']) * $pos['shares'];
                            ?>
                            <tr class="hover:bg-zinc-50">
                                <td>
                                    <div style="display:flex; align-items:center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; background: #f4f4f5; border-radius: 8px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size: 0.8rem;">
                                            <?= substr($pos['ticker_symbol'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700;"><?= $pos['ticker_symbol'] ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= $pos['company_name'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-weight: 600;"><?= number_format($pos['shares'], 4) ?></td>
                                <td style="text-align: right;">$<?= number_format($pos['average_entry_price'], 2) ?></td>
                                <td style="text-align: right;">$<?= number_format($pos['current_price'], 2) ?></td>
                                <td style="text-align: right;">
                                    <div style="font-weight: 700;">$<?= number_format($val, 2) ?></div>
                                    <div style="font-size: 0.7rem; color: <?= $gain >= 0 ? 'var(--accent)' : 'var(--danger)' ?>;">
                                        <?= $gain >= 0 ? '+' : '' ?><?= number_format($gain, 2) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Activity (Spans 1 column) -->
        <div class="card-premium">
            <h3 class="mb-4">Recent History</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php if (empty($ht)): ?>
                    <p class="text-muted" style="font-size: 0.9rem;">No recent transactions.</p>
                <?php else: ?>
                    <?php foreach ($ht as $t): ?>
                         <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: <?= $t['side'] == 'BUY' ? 'var(--accent)' : 'var(--danger)' ?>;"></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 0.9rem;"><?= $t['ticker_symbol'] ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase;"><?= $t['side'] ?></div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 600; font-size: 0.9rem;">$<?= number_format($t['execution_price'], 2) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= number_format($t['quantity_filled'], 2) ?> shares</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
             <button class="btn-primary" style="width: 100%; margin-top: 20px; background: white; color: black; border: 1px solid var(--border-color);">View All</button>
        </div>

    </div>

    <!-- Market Analysis Section (Transferred from Overview) -->
    <div class="card-premium mt-8">
        <div class="flex-between mb-6">
            <h3 class="text-xl font-bold">Market Analysis</h3>
            <div class="flex items-center gap-3">
                <input type="text" id="tickerInput" value="AAPL" onchange="changeTicker(this.value)" 
                    class="bg-zinc-100 border border-zinc-200 text-zinc-900 uppercase px-3 py-1 rounded text-sm font-bold focus:border-black outline-none transition-all"
                    placeholder="TICKER">
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- LEFT COLUMN: Chart & Stats -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Chart Card -->
                <div class="bg-white rounded-3xl border border-zinc-200 p-6 shadow-sm">
                        <!-- Range Tabs -->
                    <div class="flex gap-4 mb-6 border-b border-zinc-100 pb-1">
                        <button onclick="updateHistory('1D', this)" class="range-btn active text-blue-600 border-b-[3px] border-blue-600 pb-3 px-1 text-sm font-bold transition-all">1D</button>
                        <button onclick="updateHistory('1W', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">5D</button>
                        <button onclick="updateHistory('1M', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">1M</button>
                        <button onclick="updateHistory('3M', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">6M</button>
                        <button onclick="updateHistory('1Y', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">YTD</button>
                        <button onclick="updateHistory('1Y', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">1Y</button>
                        <button onclick="updateHistory('5Y', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">5Y</button>
                    </div>

                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <h2 class="text-3xl font-bold tracking-tight" id="hist-ticker">AAPL</h2>
                            <div class="text-zinc-500 font-medium">Apple Inc</div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold tracking-tighter mb-1" id="hist-price">...</div>
                            <span class="text-lg font-bold text-green-600 flex items-center justify-end" id="hist-change">...</span>
                        </div>
                    </div>

                    <div class="h-[350px] w-full relative">
                        <canvas id="historyChart" class="relative z-10"></canvas>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Stats -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-zinc-200 p-6 shadow-sm">
                    <h3 class="font-bold text-lg mb-4">Key Statistics</h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between border-b border-zinc-100 pb-2">
                            <span class="text-zinc-500">Open</span>
                            <span class="font-bold">184.22</span>
                        </div>
                        <div class="flex justify-between border-b border-zinc-100 pb-2">
                            <span class="text-zinc-500">High</span>
                            <span class="font-bold">186.50</span>
                        </div>
                        <div class="flex justify-between border-b border-zinc-100 pb-2">
                            <span class="text-zinc-500">Low</span>
                            <span class="font-bold">183.89</span>
                        </div>
                        <div class="flex justify-between border-b border-zinc-100 pb-2">
                            <span class="text-zinc-500">Mkt Cap</span>
                            <span class="font-bold">3.02T</span>
                        </div>
                        <div class="flex justify-between border-b border-zinc-100 pb-2">
                            <span class="text-zinc-500">P/E Ratio</span>
                            <span class="font-bold">32.40</span>
                        </div>
                        <div class="flex justify-between border-b border-zinc-100 pb-2">
                            <span class="text-zinc-500">Div Yield</span>
                            <span class="font-bold">0.52%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ==========================================
    // Google-Like Interaction Logic
    // ==========================================
    const ctxHist = document.getElementById('historyChart').getContext('2d');
    let currentTicker = 'AAPL';
    let currentRange = '1Y';
    let latestData = { price: 0, change: 0, percent: 0 }; 

    // Custom Plugin: Vertical Crosshair Line
    const crosshairPlugin = {
        id: 'crosshair',
        afterDraw: (chart) => {
            if (chart.tooltip?._active?.length) {
                const ctx = chart.ctx;
                const x = chart.tooltip._active[0].element.x;
                const topY = chart.scales.y.top;
                const bottomY = chart.scales.y.bottom;

                ctx.save();
                ctx.beginPath();
                ctx.moveTo(x, topY);
                ctx.lineTo(x, bottomY);
                ctx.lineWidth = 1;
                ctx.strokeStyle = '#d1d5db'; 
                ctx.setLineDash([5, 5]);
                ctx.stroke();
                ctx.restore();
            }
        }
    };
    Chart.register(crosshairPlugin);

    // Gradients
    function getGradient(ctx, color) {
        const g = ctx.createLinearGradient(0, 0, 0, 400);
        g.addColorStop(0, color); 
        g.addColorStop(1, 'rgba(255, 255, 255, 0)');
        return g;
    }

    // Chart Instance
    let histChart = new Chart(ctxHist, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: '#16a34a', 
                backgroundColor: (ctx) => getGradient(ctx.chart.ctx, 'rgba(22, 163, 74, 0.2)'),
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#16a34a',
                pointHoverBorderWidth: 3,
                fill: true,
                tension: 0.05
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { 
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(255,255,255,0.9)',
                    titleColor: '#000',
                    bodyColor: '#000',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '$' + parseFloat(context.parsed.y).toFixed(2);
                        }
                    }
                }
            },
            scales: { 
                x: { display: false }, 
                y: { 
                    position: 'right', 
                    grid: { color: '#f3f4f6', borderDash: [4, 4] }, 
                    ticks: { color: '#9ca3af', font: { size: 11 } }
                } 
            },
            onHover: (e, activeElements) => {
                if (activeElements && activeElements.length > 0) {
                    const index = activeElements[0].index;
                    const dataPoint = histChart.data.datasets[0].data[index];
                    
                    const startPrice = histChart.data.datasets[0].data[0];
                    const change = dataPoint - startPrice;
                    const percent = (change / startPrice) * 100;
                    
                    updateHeaderDisplay(dataPoint, percent);
                } else {
                    updateHeaderDisplay(latestData.price, latestData.percent);
                }
            }
        }
    });

    function updateHeaderDisplay(price, percent) {
            const elPrice = document.getElementById('hist-price');
            const elChange = document.getElementById('hist-change');

            elPrice.innerText = '$' + parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2});
            
            const isPos = percent >= 0;
            elChange.innerHTML = (isPos ? 
            `<i class="fa-solid fa-arrow-trend-up mr-2"></i>` : 
            `<i class="fa-solid fa-arrow-trend-down mr-2"></i>`
            ) + Math.abs(percent).toFixed(2) + '%';
            
            elChange.className = isPos ? 'text-lg font-bold text-green-600 flex items-center justify-end transition-colors' : 'text-lg font-bold text-red-600 flex items-center justify-end transition-colors';
    }

    function changeTicker(val) {
        currentTicker = val.toUpperCase();
        document.getElementById('hist-ticker').innerText = currentTicker;
        updateHistory(currentRange, null);
    }

    window.updateHistory = function(range, btn) {
        currentRange = range;
        if (btn) {
            document.querySelectorAll('.range-btn').forEach(b => {
                b.classList.remove('active', 'text-blue-600', 'border-b-[3px]', 'border-blue-600');
                b.classList.add('text-zinc-500', 'hover:bg-zinc-50');
            });
            btn.classList.add('active', 'text-blue-600', 'border-b-[3px]', 'border-blue-600');
            btn.classList.remove('text-zinc-500', 'hover:bg-zinc-50');
        }

        fetch(`api_history.php?ticker=${currentTicker}&range=${range}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) { return; }

                histChart.data.labels = data.labels;
                histChart.data.datasets[0].data = data.data;
                
                // Color Logic
                const isPositive = data.change_percent >= 0;
                const color = isPositive ? '#16a34a' : '#dc2626'; 
                const bgBase = isPositive ? 'rgba(22, 163, 74, 0.2)' : 'rgba(220, 38, 38, 0.2)';

                histChart.data.datasets[0].borderColor = color;
                histChart.data.datasets[0].pointHoverBorderColor = color; 
                histChart.data.datasets[0].backgroundColor = (ctx) => getGradient(ctx.chart.ctx, bgBase);
                
                histChart.update();

                // Set Latest Data
                const price = parseFloat(data.current_price);
                const percent = parseFloat(data.change_percent);
                latestData = { price, change: 0, percent }; 

                updateHeaderDisplay(price, percent);
            })
            .catch(e => console.error(e));
    }
    
    updateHistory('1Y', document.querySelector('.range-btn.active'));
</script>
