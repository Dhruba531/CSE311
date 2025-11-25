<?php
// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
?>
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
            <li><a href="index.php" <?php echo $current_page === 'index.php' ? 'class="active"' : ''; ?>>Dashboard</a>
            </li>
            <li><a href="stocks.php" <?php echo $current_page === 'stocks.php' ? 'class="active"' : ''; ?>>Stocks</a></li>
            <li><a href="buy_sell.php" <?php echo $current_page === 'buy_sell.php' ? 'class="active"' : ''; ?>>Trade</a>
            </li>
            <li><a href="orders.php" <?php echo $current_page === 'orders.php' ? 'class="active"' : ''; ?>>Orders</a></li>
            <li><a href="portfolio.php" <?php echo $current_page === 'portfolio.php' ? 'class="active"' : ''; ?>>Portfolio</a></li>
            <li><a href="history.php" <?php echo $current_page === 'history.php' ? 'class="active"' : ''; ?>>History</a>
            </li>
            <li><a href="watchlist.php" <?php echo $current_page === 'watchlist.php' ? 'class="active"' : ''; ?>>Watchlist</a></li>
            <li><a href="alerts.php" <?php echo $current_page === 'alerts.php' ? 'class="active"' : ''; ?>>Alerts</a></li>
            <li><a href="analytics.php" <?php echo $current_page === 'analytics.php' ? 'class="active"' : ''; ?>>Analytics</a></li>
            <li><a href="friends.php" <?php echo $current_page === 'friends.php' ? 'class="active"' : ''; ?>>Friends</a>
            </li>
            <li><a href="account.php" <?php echo $current_page === 'account.php' ? 'class="active"' : ''; ?>>Account</a>
            </li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>