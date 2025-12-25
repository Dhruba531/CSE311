<?php
session_start();
require 'config.php';
require_login();

$page_title = 'Crypto';
require 'includes/header.php';
require 'includes/nav.php';
// Mock Crypto Data
$coins = [
    ['symbol' => 'BTC', 'name' => 'Bitcoin', 'price' => 97450.00, 'change' => 2.4],
    ['symbol' => 'ETH', 'name' => 'Ethereum', 'price' => 3890.50, 'change' => -1.2],
    ['symbol' => 'SOL', 'name' => 'Solana', 'price' => 145.20, 'change' => 5.7],
    ['symbol' => 'DOGE', 'name' => 'Dogecoin', 'price' => 0.12, 'change' => 0.5],
];
?>

<div class="flex-between mb-4">
    <div>
        <h1>Crypto Trading</h1>
        <p class="text-muted">Trade top cryptocurrencies 24/7.</p>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card-premium col-span-2"> <!-- Full width on small grids logic -->
        <table class="table-minimal">
            <thead>
                <tr>
                    <th>Asset</th>
                    <th>Price</th>
                    <th>24h Change</th>
                    <th>Market Cap</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($coins as $c): ?>
                <tr>
                    <td style="display:flex; align-items:center; gap:10px;">
                        <div style="width:32px; height:32px; background:#f9f9f9; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                            <i class="fa-brands fa-bitcoin" style="color: #f7931a;"></i> <!-- Generic Icon for now -->
                        </div>
                        <div>
                            <div style="font-weight:700;"><?= $c['symbol'] ?></div>
                            <div style="font-size:0.8rem; color:#999;"><?= $c['name'] ?></div>
                        </div>
                    </td>
                    <td style="font-weight:600;">$<?= number_format($c['price'], 2) ?></td>
                    <td style="color: <?= $c['change'] >= 0 ? '#10b981' : '#ef4444' ?>;">
                        <?= $c['change'] >= 0 ? '+' : '' ?><?= $c['change'] ?>%
                    </td>
                    <td>$<?= rand(10, 900) ?>B</td>
                    <td>
                        <button class="btn-primary" style="padding: 5px 15px; font-size: 0.8rem;">Trade</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="card-premium">
        <h3>Why Trade Crypto?</h3>
        <ul style="list-style:none; padding:0; margin-top:20px;">
            <li style="margin-bottom:15px; display:flex; gap:10px;">
                <i class="fa-solid fa-clock" style="color:var(--xtrade-blue);"></i> 24/7 Market Hours
            </li>
            <li style="margin-bottom:15px; display:flex; gap:10px;">
                <i class="fa-solid fa-bolt" style="color:var(--xtrade-blue);"></i> Instant Settlement
            </li>
            <li style="margin-bottom:15px; display:flex; gap:10px;">
                <i class="fa-solid fa-lock" style="color:var(--xtrade-blue);"></i> Secure Wallet
            </li>
        </ul>
    </div>
</div>
<?php // footer check
?>
