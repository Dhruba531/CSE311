<?php
session_start();
require 'config.php';
require_login();

$page_title = 'Community';
require 'includes/header.php';
require 'includes/nav.php';

// Mock Leaderboard (Replace with real logic if Users table has performance data)
$leaders = [
    ['name' => 'Warren B.', 'return' => '+24.5%', 'rank' => 1],
    ['name' => 'Elon M.', 'return' => '+18.2%', 'rank' => 2],
    ['name' => 'You', 'return' => '+0.0%', 'rank' => 999], 
];
?>

<div class="flex-between mb-4">
    <div>
        <h1>XTrade Community</h1>
        <p class="text-muted">Connect with other traders.</p>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Friends List & Leaderboard Column -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Friends List -->
        <div class="card-premium">
            <div class="flex-between mb-2">
                <h3>My Friends</h3>
                <button class="btn-small" style="background:none; color:var(--accent);"><i class="fa-solid fa-plus"></i></button>
            </div>
            <div style="text-align:center; padding: 40px 20px;">
                <div style="width:60px; height:60px; background:#f4f4f5; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:15px; color:#a1a1aa;">
                    <i class="fa-solid fa-user-group" style="font-size:1.5rem;"></i>
                </div>
                <h4 style="font-size:0.95rem; margin-bottom:5px;">No friends yet</h4>
                <p class="text-muted" style="font-size:0.85rem; margin-bottom:20px;">Connect with others to see their trades.</p>
                <button class="btn-primary" style="width:100%; font-size:0.9rem;">Find Traders</button>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="card-premium">
            <h3>Top Traders <span class="text-muted" style="font-size:0.8rem; font-weight:400;">(Monthly)</span></h3>
            <div class="leaderboard-list" style="margin-top:15px;">
                <?php 
                $medals = ['🥇', '🥈', '🥉'];
                foreach($leaders as $index => $l): 
                    $isTop3 = $index < 3;
                    $rankDisplay = $isTop3 ? $medals[$index] : '#' . $l['rank'];
                    $nameColor = $l['name'] === 'You' ? 'var(--accent)' : 'var(--text-primary)';
                    $rowBg = $l['name'] === 'You' ? 'background:rgba(37,99,235,0.05);' : '';
                ?>
                <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 10px; border-bottom:1px solid #f4f4f5; <?= $rowBg ?>">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="width:25px; text-align:center; font-weight:700; color:#71717a; font-size:0.9rem;"><?= $rankDisplay ?></span>
                        <div style="width:32px; height:32px; background:#e4e4e7; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#52525b; font-weight:700; font-size:0.8rem;">
                            <?= substr($l['name'], 0, 1) ?>
                        </div>
                        <span style="font-weight:600; font-size:0.9rem; color:<?= $nameColor ?>"><?= $l['name'] ?></span>
                    </div>
                    <span style="color:#16a34a; font-weight:700; font-size:0.9rem;"><?= $l['return'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Feed -->
    <div class="card-premium col-span-2">
        <div class="flex-between mb-3" style="border-bottom:1px solid #f4f4f5; padding-bottom:15px;">
            <h3>Community Feed</h3>
            <button class="btn-primary" style="font-size:0.85rem; padding: 6px 12px;"><i class="fa-solid fa-pen-to-square"></i> New Post</button>
        </div>
        
        <!-- Post 1 -->
        <div style="padding: 20px 0; border-bottom: 1px solid #f4f4f5;">
            <div style="display:flex; gap:15px;">
                <div style="width:40px; height:40px; background:linear-gradient(135deg, #3b82f6, #8b5cf6); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700;">A</div>
                <div style="flex:1;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span style="font-weight:700; font-size:0.95rem;">Analyst_John</span>
                            <span class="text-muted" style="font-size:0.85rem; margin-left:5px;">@analyst_john</span>
                        </div>
                        <span class="text-muted" style="font-size:0.8rem;">2h</span>
                    </div>
                    <p style="margin-top:8px; font-size:0.95rem; line-height:1.5;">Expect high volatility in Tech sector this week. <span style="color:var(--accent); font-weight:600;">$AAPL</span> looks strong though.</p>
                    <div style="display:flex; gap:20px; margin-top:12px; color:#a1a1aa; font-size:0.85rem;">
                        <span style="cursor:pointer;"><i class="fa-regular fa-heart"></i> 24</span>
                        <span style="cursor:pointer;"><i class="fa-regular fa-comment"></i> 5</span>
                        <span style="cursor:pointer;"><i class="fa-solid fa-share-nodes"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Post 2 -->
        <div style="padding: 20px 0;">
            <div style="display:flex; gap:15px;">
                <div style="width:40px; height:40px; background:linear-gradient(135deg, #f59e0b, #ef4444); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700;">C</div>
                <div style="flex:1;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span style="font-weight:700; font-size:0.95rem;">CryptoKing</span>
                            <span class="text-muted" style="font-size:0.85rem; margin-left:5px;">@cryptoking</span>
                        </div>
                        <span class="text-muted" style="font-size:0.8rem;">5h</span>
                    </div>
                    <p style="margin-top:8px; font-size:0.95rem; line-height:1.5;">Bitcoin testing new resistance levels. <span style="color:var(--accent); font-weight:600;">#BTC</span> 🚀</p>
                    <div style="display:flex; gap:20px; margin-top:12px; color:#a1a1aa; font-size:0.85rem;">
                        <span style="cursor:pointer;"><i class="fa-regular fa-heart"></i> 156</span>
                        <span style="cursor:pointer;"><i class="fa-regular fa-comment"></i> 42</span>
                        <span style="cursor:pointer;"><i class="fa-solid fa-share-nodes"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
