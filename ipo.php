<?php
session_start();
require 'config.php';
require_login();

$page_title = 'IPO Center';
require 'includes/header.php';
require 'includes/nav.php';
// Mock Data for IPOs
$upcoming = [
    ['ticker' => 'STRIPE', 'name' => 'Stripe Inc.', 'date' => '2025-11-15', 'price' => '$55.00 - $60.00', 'shares' => '20M', 'status' => 'Open'],
    ['ticker' => 'SPACEX', 'name' => 'SpaceX', 'date' => '2025-12-01', 'price' => '$80.00 - $90.00', 'shares' => '15M', 'status' => 'Coming Soon'],
    ['ticker' => 'DATAB', 'name' => 'Databricks', 'date' => '2025-10-30', 'price' => '$40.00 - $45.00', 'shares' => '10M', 'status' => 'Closed'],
];

$recent = [
    ['ticker' => 'ARM', 'name' => 'Arm Holdings', 'date' => '2023-09-14', 'price' => '$51.00', 'return' => '+24.5%'],
    ['ticker' => 'RDDT', 'name' => 'Reddit', 'date' => '2024-03-21', 'price' => '$34.00', 'return' => '+45.2%'],
];
?>

<div class="flex-between mb-4">
    <div>
        <h1>IPO Center</h1>
        <p class="text-muted">Participate in Initial Public Offerings before they hit the market.</p>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Upcoming Section -->
    <div class="card-premium col-span-2">
        <h3>Upcoming IPOs</h3>
        <table class="table-minimal">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Expected Date</th>
                    <th>Price Range</th>
                    <th>Shares</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming as $ipo): ?>
                <tr>
                    <td>
                        <div style="font-weight:700;"><?= $ipo['ticker'] ?></div>
                        <div style="font-size:0.8rem; color:#999;"><?= $ipo['name'] ?></div>
                    </td>
                    <td><?= date('M d, Y', strtotime($ipo['date'])) ?></td>
                    <td><?= $ipo['price'] ?></td>
                    <td><?= $ipo['shares'] ?></td>
                    <td>
                        <span class="badge" style="
                            background: <?= $ipo['status'] == 'Open' ? '#dcfce7' : ($ipo['status'] == 'Closed' ? '#fee2e2' : '#f3f4f6') ?>;
                            color: <?= $ipo['status'] == 'Open' ? '#15803d' : ($ipo['status'] == 'Closed' ? '#991b1b' : '#374151') ?>;
                        ">
                            <?= $ipo['status'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($ipo['status'] == 'Open'): ?>
                            <button class="btn-primary" style="padding: 5px 15px; font-size: 0.8rem;">Subscribe</button>
                        <?php else: ?>
                            <button class="btn-primary" disabled style="opacity:0.5; padding: 5px 15px; font-size: 0.8rem;">Details</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Listings -->
    <div class="card-premium">
        <h3>Recent Listings</h3>
        <table class="table-minimal">
            <thead>
                <tr>
                    <th>Symbol</th>
                    <th>Offer Price</th>
                    <th>Return</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $itm): ?>
                <tr>
                    <td>
                        <div style="font-weight:bold;"><?= $itm['ticker'] ?></div>
                        <div style="font-size:0.8rem; color:#999;"><?= $itm['name'] ?></div>
                    </td>
                    <td><?= $itm['price'] ?></td>
                    <td style="color: #10b981; font-weight:700;"><?= $itm['return'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php // placeholder for footer or end logic
?>
