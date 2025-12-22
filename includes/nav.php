<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="nav-container">
        <h1 class="logo">
            <span class="logo-text" style="color: var(--primary); font-family: 'Inter', sans-serif; letter-spacing: -0.5px">StockTrader</span>
        </h1>
        <ul class="nav-menu">
            <li><a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="stocks.php" class="<?php echo $current_page === 'stocks.php' ? 'active' : ''; ?>">Market</a></li>
            <li><a href="portfolio.php" class="<?php echo $current_page === 'portfolio.php' ? 'active' : ''; ?>">Holdings</a></li>
            <li><a href="orders.php" class="<?php echo $current_page === 'orders.php' ? 'active' : ''; ?>" style="opacity: 0.7; pointer-events: none" title="Coming Soon">Orders</a></li>
            <li>
                <a href="logout.php" style="color: var(--danger); font-weight: 500; font-size: 0.9rem">
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>