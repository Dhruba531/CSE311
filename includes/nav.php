<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="app-wrapper">
    <!-- Premium Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-text">XTrade <span style="font-weight:300">Pro</span></div>
        </div>
        
        <ul class="nav-menu">
            <!-- Main -->
            <li class="nav-header">Main</li>
            <li class="nav-item">
                <a href="portfolio.php" class="<?= $current_page === 'portfolio.php' || $current_page === 'index.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-wallet"></i> <span>Portfolio</span>
                </a>
            </li>

            <!-- Analysis -->
            <li class="nav-header">Analysis</li>
            <li class="nav-item">
                <a href="stocks.php" class="<?= $current_page === 'stocks.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i> <span>Market</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="news.php" class="<?= $current_page === 'news.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-newspaper"></i> <span>Newsfeed</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="watchlist.php" class="<?= $current_page === 'watchlist.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-star"></i> <span>Watchlist</span>
                </a>
            </li>

            <!-- Trading -->
            <li class="nav-header">Trading</li>
            <li class="nav-item">
                <a href="trade.php" class="<?= $current_page === 'trade.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Trade Stocks</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="crypto.php" class="<?= $current_page === 'crypto.php' ? 'active' : '' ?>">
                    <i class="fa-brands fa-bitcoin"></i> <span>Crypto</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="ipo.php" class="<?= $current_page === 'ipo.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-rocket"></i> <span>IPO Center</span>
                </a>
            </li>

            <!-- Account -->
            <li class="nav-header">Account</li>
            <li class="nav-item">
                <a href="funds.php" class="<?= $current_page === 'funds.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-building-columns"></i> <span>Funds & Banking</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="<?= $current_page === 'reports.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i> <span>Reports & Tax</span>
                </a>
            </li>

            <!-- Social -->
            <li class="nav-header">Social</li>
            <li class="nav-item">
                <a href="social.php" class="<?= $current_page === 'social.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i> <span>Community</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="alerts.php" class="<?= $current_page === 'alerts.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-bell"></i> <span>Alerts</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>

            <?php endif; ?>
        </ul>

        <div class="nav-footer">
            <a href="logout.php" style="color: #ef4444; text-decoration: none; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper (Closed in footer.php) -->
    <main class="main-content">