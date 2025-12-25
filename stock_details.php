<?php
session_start();
require 'config.php';

// Get ticker from URL or default to AAPL
$ticker = isset($_GET['ticker']) ? strtoupper($_GET['ticker']) : 'AAPL';

    // Fetch Stock Data from New Schema (Stocks)
    // Map columns to match existing variable usage
    $stmt = $pdo->prepare("
        SELECT s.ticker_symbol as ticker, s.stock_name as company_name, s.current_price, 
               'Technology' as sector, -- Default sector or join if needed
               e.exchange_name, e.short_code
        FROM Stocks s
        LEFT JOIN Exchange e ON s.exchange_id = e.exchange_id
        WHERE s.ticker_symbol = ?
    ");
    $stmt->execute([$ticker]);
    $stock = $stmt->fetch();

if (!$stock) {
    // Fallback if not found (shouldn't happen if seed is good, but just in case)
    $stock = [
        'ticker' => $ticker,
        'company_name' => $ticker . ' Inc.',
        'current_price' => 150.00,
        'sector' => 'Technology'
    ];
}

$balance = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT balance FROM Accounts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $balance = $stmt->fetchColumn() ?: 0;
}

// XTrade Mock Data
$stats = [
    'open' => number_format($stock['current_price'] * 0.98, 2),
    'high' => number_format($stock['current_price'] * 1.02, 2),
    'low' => number_format($stock['current_price'] * 0.97, 2),
    'vol' => '3.31M',
    'prev_close' => number_format($stock['current_price'] * 0.99, 2),
    'turnover' => '--',
    'w52_high' => number_format($stock['current_price'] * 1.5, 2),
    'w52_low' => number_format($stock['current_price'] * 0.6, 2),
    'mkt_cap' => '1.48B',
    'pe' => '17.22'
];

$change = $stock['current_price'] - (float)$stats['prev_close'];
$changePercent = ($change / (float)$stats['prev_close']) * 100;
$isUp = $change >= 0;
$sign = $isUp ? '+' : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $ticker; ?> - XTrade</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* XTrade Theme Setup */
        :root {
            --xtrade-blue: #1E58EB; 
            --xtrade-bg: #f9f9f9;
            --xtrade-card: #ffffff;
            --xtrade-text-main: #191919;
        }

        body {
            background: var(--xtrade-bg) !important;
            color: var(--xtrade-text-main) !important;
            font-family: 'Roboto', sans-serif !important;
            min-height: 100vh;
        }

        /* Top Header Area (The Blue Bar) */
        .xtrade-header-bg {
            background-color: var(--xtrade-blue);
            color: white;
            padding: 20px 60px 80px 60px;
            position: relative;
        }

        .xtrade-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .logo h2 { color: white !important; font-size: 1.5rem; margin: 0; }
        .nav-link { 
            color: white; opacity: 0.8; text-decoration: none; margin-left: 20px; font-weight: 500; font-size: 0.95rem; 
            transition: 0.3s;
        }
        .nav-link:hover { opacity: 1; }
        .nav-btn-outline {
            border: 1px solid white; color: white; padding: 6px 16px; border-radius: 4px;
            margin-left: 20px; text-decoration: none; font-size: 0.9rem; transition: 0.3s;
        }
        .nav-btn-filled {
            background: #ffffff; color: var(--xtrade-blue); padding: 7px 16px; border-radius: 4px;
            margin-left: 10px; text-decoration: none; font-size: 0.9rem; font-weight: 500;
        }

        .stock-header-grid {
            display: grid;
            grid-template-columns: 1.2fr 2fr;
            align-items: flex-start;
        }

        .stock-identity {
            font-family: 'Roboto', sans-serif;
        }
        .stock-identity h1 { 
            font-size: 5rem; 
            font-weight: 100; /* Thinner font */
            margin: 0; 
            line-height: 1.1; 
            color: #ffffff;
            letter-spacing: 1px;
        }
        .stock-identity .company-name { 
            font-size: 1.4rem; 
            font-weight: 400; 
            margin-top: 5px; 
            color: #ffffff;
            opacity: 0.9; 
        }
        .stock-identity .exchange { 
            font-size: 0.85rem; 
            color: #e0e0e0; 
            opacity: 0.8; 
            margin-top: 5px; 
            text-transform: uppercase;
            font-weight: 500;
        }
        
        .price-large {
            font-size: 5rem; 
            font-weight: 500; 
            margin-top: 15px;
            color: #ff5252; /* Matching the Red/Orange from screenshot */
            line-height: 1;
            letter-spacing: -1px;
        }
        
        .change-large {
            font-size: 1.5rem; 
            font-weight: 500; 
            margin-top: 10px;
            color: #4ade80; /* Bright Green */
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .closed-text {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
            margin-top: 8px;
            font-weight: 400;
        }

        /* Stats Grid in Header */
        .header-stats-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px 40px;
            background: transparent; text-align: left; margin-top: 10px;
        }
        .stat-pair {
            display: flex; justify-content: space-between; font-size: 0.9rem;
            border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px;
        }
        .stat-pair span:first-child { opacity: 0.7; }
        .stat-pair span:last-child { font-weight: 500; }

        /* Main Content Grid */
        .content-body-grid {
            max-width: 1400px; margin: 0 auto; padding: 40px 0; background: white;
            display: grid;
            grid-template-columns: 60px 1fr 340px;
            gap: 0; 
        }

        /* 1. Social Sidebar */
        .social-sidebar {
            display: flex; flex-direction: column; align-items: center; gap: 20px;
            padding-top: 50px; border-right: 1px solid #f0f0f0;
        }
        .social-icon {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #999; border: 1px solid #eee; cursor: pointer; transition: 0.2s;
        }
        .social-icon:hover { color: var(--xtrade-blue); border-color: var(--xtrade-blue); }

        /* 2. Chart Area */
        .main-chart-col { padding: 0 40px; }
        .chart-tabs { display: flex; gap: 20px; margin-bottom: 10px; }
        .c-tab {
            font-weight: 700; color: #191919; cursor: pointer; padding: 5px 10px;
            border-radius: 4px; font-size: 0.9rem;
        }
        .c-tab.active { background: var(--xtrade-blue); color: white; }
        .chart-container-box { position: relative; height: 500px; width: 100%; }

        /* 3. Right Info Col */
        .info-col { padding: 0 30px; border-left: 1px solid #f0f0f0; }
        .promo-card {
            background-image: linear-gradient(135deg, #001f5c, #0047bb);
            color: white; padding: 20px; border-radius: 8px; margin-bottom: 30px;
            position: relative; overflow: hidden;
        }
        .promo-card h3 { margin: 0 0 10px 0; font-size: 1.1rem; }
        .btn-promo {
            background: white; color: var(--xtrade-blue); padding: 5px 15px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 600; text-decoration: none; display: inline-block;
        }

        .btn-trade-outline {
            border: 1px solid var(--xtrade-blue); color: var(--xtrade-blue);
            background: white; padding: 8px 16px; border-radius: 4px; 
            font-weight: 500; margin-top: 20px; cursor: pointer; width: 100%;
        }
        .btn-trade-outline:hover { background: #f0f5ff; }

        /* Trade Modal */
        .trade-modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .trade-modal {
            background: white; padding: 30px; width: 400px; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

    </style>
</head>
<body>

    <div class="xtrade-header-bg">
        <div class="xtrade-nav">
            <div class="logo">
                <a href="index.php" style="text-decoration:none;"><h2 style="font-weight:700;"><i class="fas fa-chart-network"></i> XTrade</h2></a>
            </div>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php" class="nav-link">Dashboard</a>
                    <a href="portfolio.php" class="nav-link">Portfolio</a>
                    <a href="trade.php" class="nav-link">Trade</a>
                    <a href="logout.php" class="nav-btn-filled" style="color:red;">Sign Out</a>
                <?php else: ?>
                    <a href="#" class="nav-link">Trade</a>
                    <a href="#" class="nav-link">Analytics</a>
                    <a href="#" class="nav-link">Market Data</a>
                    <a href="#" class="nav-link">Support</a>
                    <a href="register.php" class="nav-btn-outline">Sign up</a>
                    <a href="login.php" class="nav-btn-filled">Log in</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="stock-header-grid">
            <!-- Left Side: Ticker, Price, Actions -->
            <div class="stock-identity">
                <h1><?php echo $stock['ticker']; ?></h1>
                <div class="company-name"><?php echo $stock['company_name']; ?></div>
                <div class="exchange">NYSE</div>
                
                <div class="price-large">
                    <?php echo number_format($stock['current_price'], 2); ?>
                </div>
                <div class="change-large">
                    <span>139.09</span> <!-- Mock Prev Close for design match -->
                    <span><?php echo $sign . number_format($change, 2); ?> (<?php echo $sign . number_format($changePercent, 2); ?>%)</span>
                </div>
                <div class="closed-text">
                    Closed: <?php echo date('H:i'); ?> EST
                </div>
            </div>

            <!-- Right Side: Stats Grid -->
            <div style="padding-top: 10px; padding-left: 40px;">
                <div class="header-stats-grid">
                    <div class="stat-pair"><span>OPEN</span> <span><?php echo $stats['open']; ?></span></div>
                    <div class="stat-pair"><span>PREV CLOSE</span> <span><?php echo $stats['prev_close']; ?></span></div>
                    <div class="stat-pair"><span>HIGH</span> <span><?php echo $stats['high']; ?></span></div>
                    <div class="stat-pair"><span>LOW</span> <span><?php echo $stats['low']; ?></span></div>
                    <div class="stat-pair"><span>VOLUME</span> <span><?php echo $stats['vol']; ?></span></div>
                    <div class="stat-pair"><span>TURNOVER</span> <span><?php echo $stats['turnover']; ?></span></div>
                    <div class="stat-pair"><span>52 WK HIGH</span> <span><?php echo $stats['w52_high']; ?></span></div>
                    <div class="stat-pair"><span>52 WK LOW</span> <span><?php echo $stats['w52_low']; ?></span></div>
                    <div class="stat-pair"><span>MKT CAP</span> <span><?php echo $stats['mkt_cap']; ?></span></div>
                    <div class="stat-pair"><span>P/E (TTM)</span> <span><?php echo $stats['pe']; ?></span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3-Column Layout: Social | Chart | Info -->
    <div class="content-body-grid">
        
        <!-- 1. Left: Social Icons -->
        <div class="social-sidebar">
            <div class="social-icon"><i class="fab fa-facebook-f"></i></div>
            <div class="social-icon"><i class="fab fa-twitter"></i></div>
            <div class="social-icon"><i class="fab fa-linkedin-in"></i></div>
            <div class="social-icon"><i class="fab fa-reddit-alien"></i></div>
        </div>

        <!-- 2. Middle: Interactive Chart -->
        <div class="main-chart-col">
            <div class="chart-tabs">
                <div class="c-tab active">1D</div>
                <div class="c-tab">5D</div>
                <div class="c-tab">1M</div>
                <div class="c-tab">3M</div>
                <div class="c-tab">1Y</div>
                <div class="c-tab">5Y</div>
            </div>
            
            <div class="chart-container-box">
                <canvas id="stockChart"></canvas>
            </div>
        </div>

        <!-- 3. Right: Info Side -->
        <div class="info-col">
            <!-- Promo Banner -->
            <div class="promo-card">
                <h3>XTrade Premium</h3>
                <p>Advanced tools for the modern trader. Real-time data and zero commission.</p>
                <a href="#" class="btn-promo">Get Started</a>
                <i class="fas fa-rocket" style="position:absolute; right:-20px; bottom:-20px; font-size:5rem; opacity:0.2;"></i>
            </div>

            <!-- About Section -->
            <div class="about-section">
                <h3 style="font-size:1.2rem; margin-bottom:15px; display:flex; justify-content:space-between;">
                    About <?php echo $stock['ticker']; ?> 
                    <span style="font-size:0.8rem; font-weight:400; color:#999; cursor:pointer;">More <i class="fas fa-chevron-down"></i></span>
                </h3>
                <p style="font-size:0.9rem; color:#666; line-height:1.6;">
                    <?php echo $stock['company_name']; ?> is a leading company in the <?php echo $stock['sector'] ?? 'General'; ?> sector. 
                    It engages in the design, manufacture, and marketing of products worldwide. The company's segments include 
                    North America, Europe, and Asia Pacific.
                </p>
                
                <button onclick="openTrade()" class="btn-trade-outline">Trade Stocks</button>
            </div>
        </div>

    </div>

    <!-- Trade Modal (Hidden) -->
    <div id="tradeModalOverlay" class="trade-modal-overlay">
        <div class="trade-modal">
            <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                <h3 style="margin:0;">Trade <?php echo $stock['ticker']; ?></h3>
                <button onclick="closeTrade()" style="background:none; border:none; font-size:1.2rem; cursor:pointer;">&times;</button>
            </div>
            <form action="trade.php" method="POST">
                <input type="hidden" name="ticker" value="<?php echo $stock['ticker']; ?>">
                <input type="hidden" name="price" value="<?php echo $stock['current_price']; ?>">
                <input type="hidden" name="action" value="buy">

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-size:0.9rem;">Quantity</label>
                    <input type="number" name="quantity" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" required placeholder="0">
                </div>
                
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:20px; color:#666;">
                    <span>Price</span>
                    <span>$<?php echo number_format($stock['current_price'], 2); ?></span>
                </div>
                
                <button type="submit" style="width:100%; background:var(--xtrade-blue); color:white; padding:12px; border:none; border-radius:4px; font-weight:bold; cursor:pointer;">Submit Order</button>
            </form>
        </div>
    </div>


    <!-- Platform Showcase Section -->
    <style>
        .showcase-section {
            background: #fff;
            padding: 80px 0;
            border-top: 1px solid #f0f0f0;
        }
        .showcase-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
        }
        .showcase-header {
            text-align: center;
            margin-bottom: 60px;
        }
        .showcase-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #111;
        }
        .showcase-header p {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .showcase-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 60px;
            align-items: center;
            margin-bottom: 80px;
        }
        
        .showcase-grid.reversed {
            direction: rtl; /* simple way to flip order visually, need to reset text dir */
        }
        .showcase-grid.reversed > * {
            direction: ltr;
        }

        .showcase-text { padding: 20px; }
        .showcase-text h3 { font-size: 1.8rem; margin-bottom: 15px; color: #111; }
        .showcase-text p { font-size: 1rem; color: #666; line-height: 1.6; margin-bottom: 25px; }
        .showcase-text ul { list-style: none; padding: 0; }
        .showcase-text li { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; color: #444; }
        .showcase-text i { color: var(--xtrade-blue); }

        .showcase-img {
            position: relative;
        }
        .showcase-img img {
            width: 100%;
            height: auto;
            display: block;
            /* No shadow/border for clean look as they are mockups */
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.1));
            transition: transform 0.3s;
        }
        .showcase-img img:hover { transform: translateY(-5px); }
        
        /* Mobile/Tablet Grid */
        .mobile-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 60px;
        }
        .mobile-card { text-align: center; }
        .mobile-card img { max-width: 100%; height: auto; margin-bottom: 20px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1)); }
        .mobile-card h4 { font-size: 1.2rem; margin-bottom: 10px; }
        .mobile-card p { font-size: 0.9rem; color: #666; }

    </style>

    <section class="showcase-section">
        <div class="showcase-container">
            <div class="showcase-header">
                <h2>Professional Tools for Every Trader</h2>
                <p>Whether you're active on the desktop or trading on the go, XTrade gives you the power to seize opportunities.</p>
            </div>

            <!-- Feature 1: Desktop -->
            <div class="showcase-grid">
                <div class="showcase-text">
                    <h3>Advanced Desktop Terminal</h3>
                    <p>Designed for institutional-grade performance. Customize your workspace with over 50+ technical indicators, real-time Level 2 data, and rapid order execution.</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Multi-monitor support</li>
                        <li><i class="fas fa-check-circle"></i> Advanced charting package</li>
                        <li><i class="fas fa-check-circle"></i> Real-time news streaming</li>
                    </ul>
                </div>
                <div class="showcase-img">
                    <img src="assets/images/desktop-terminal.png" alt="Desktop Trading Terminal">
                </div>
            </div>

            <!-- Feature 2: Laptop (Reversed) -->
            <div class="showcase-grid reversed">
                <div class="showcase-text">
                    <h3>Power on the Go</h3>
                    <p>Take the full power of the XTrade terminal with you. Our laptop-optimized interface ensures you never miss a beat, even when you're away from your desk.</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Cloud-synced workspaces</li>
                        <li><i class="fas fa-check-circle"></i> Battery-optimized performance</li>
                    </ul>
                </div>
                <div class="showcase-img">
                    <img src="assets/images/laptop-pro.png" alt="Laptop Trading">
                </div>
            </div>
            
             <!-- Feature 3: Mobile Trio -->
            <div class="showcase-header" style="margin-top:100px; margin-bottom: 40px;">
                <h2>Trade From Anywhere</h2>
                <p>Top-rated mobile apps for iOS and Android.</p>
            </div>

            <div class="mobile-grid">
                 <div class="mobile-card">
                    <img src="assets/images/mobile-app.png" alt="Mobile Portfolio">
                    <h4>Portfolio Management</h4>
                    <p>Track your holdings and performance in real-time.</p>
                </div>
                <div class="mobile-card">
                    <img src="assets/images/tablet-dashboard.png" alt="Tablet Analytics">
                    <h4>Tablet Analytics</h4>
                    <p>Deep dive into charts on your iPad or Android Tablet.</p>
                </div>
                <div class="mobile-card">
                    <img src="assets/images/mobile-alerts.png" alt="Smart Alerts">
                    <h4>Smart Alerts</h4>
                    <p>Get instant notifications on price movements.</p>
                </div>
            </div>

        </div>
    </section>
        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 60px;
        }
        .footer-disclaimer {
            font-size: 0.8rem;
            color: #999;
            line-height: 1.6;
            margin-bottom: 50px;
            max-width: 900px;
        }
        .footer-disclaimer a { color: var(--xtrade-blue); text-decoration: none; }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr) 1.5fr; /* 5 cols + contact/social */
            gap: 40px;
        }
        .footer-col h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        .footer-col a {
            display: block;
            font-size: 0.85rem;
            color: #666;
            text-decoration: none;
            margin-bottom: 12px;
            transition: 0.2s;
        }
        .footer-col a:hover { color: var(--xtrade-blue); }

        .contact-col { text-align: left; }
        .social-links { margin-top: 20px; }
        .social-links a { display: inline-block; margin-right: 15px; font-size: 1.2rem; color: #333; }
        .social-links a:hover { color: var(--xtrade-blue); }
    </style>

    <footer class="xtrade-footer">
        <div class="footer-container">
            <div class="footer-disclaimer">
                XTrade offers <?php echo $stock['company_name']; ?> stock information, including NYSE: <?php echo $stock['ticker']; ?> 
                real-time market quotes, financial reports, professional analyst ratings, in-depth charts, corporate actions, 
                <?php echo $stock['ticker']; ?> stock news, and many more online research tools to help you make informed decisions. 
                <a href="#">Trade stocks</a> for 0 commission on the web version for easy and convenient access, or download the XTrade app and trade on the go.
                You can practice and explore trading <?php echo $stock['ticker']; ?> stock methods without spending real money on the virtual <a href="#">paper trading platform</a>.
            </div>

            <div class="footer-grid">
                <div class="footer-col">
                    <h4>XTrade Products</h4>
                    <a href="#">Futures</a>
                    <a href="#">Options</a>
                    <a href="#">Stocks & Fractional Shares</a>
                    <a href="#">ETFs</a>
                    <a href="#">Buy & Sell Crypto</a>
                    <a href="#">XTrade Advisors</a>
                    <a href="#">Learn</a>
                </div>
                <div class="footer-col">
                    <h4>About XTrade</h4>
                    <a href="#">Investor Relations</a>
                    <a href="#">Careers</a>
                    <a href="#">BrokerCheck</a>
                    <a href="#">Pricing</a>
                    <a href="#">Blog</a>
                    <a href="#">Script Editor</a>
                </div>
                <div class="footer-col">
                    <h4>FAQs</h4>
                    <a href="#">Account & Login</a>
                    <a href="#">Retirement</a>
                    <a href="#">Documents & Taxes</a>
                </div>
                <div class="footer-col">
                    <h4>Terms & Conditions</h4>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Business Continuity Plan</a>
                    <a href="#">Disclosures</a>
                    <a href="#">Data Disclaimer</a>
                </div>
                <div class="footer-col">
                    <h4>Cookie</h4>
                    <a href="#">Cookie Setting</a>
                </div>
                <div class="footer-col contact-col">
                    <h4>Contact us</h4>
                    <a href="mailto:support@xtrade.com">support@xtrade.com</a>
                    <a href="#">+1 (888) 555-0199</a>
                    
                    <div style="margin-top:30px;">
                        <h4 style="margin-bottom:10px;">Follow us on</h4>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a> <!-- X icon/twitter -->
                            <a href="#"><i class="fab fa-youtube"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 60px; font-size: 0.75rem; color: #ccc; text-align: center;">
                &copy; <?php echo date('Y'); ?> XTrade Financial LLC. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Chart.js and Modal Scripts ... -->
    <script>
        function openTrade() {
            document.getElementById('tradeModalOverlay').style.display = 'flex';
        }
        function closeTrade() {
            document.getElementById('tradeModalOverlay').style.display = 'none';
        }

        // --- Chart & Data Logic ---
        
        const ctx = document.getElementById('stockChart').getContext('2d');
        let currentPrice = <?php echo $stock['current_price']; ?>;
        
        // Data Generators
        function generateData(points, volatility) {
            let data = [];
            let p = currentPrice;
            for(let i=0; i<points; i++) {
                let change = (Math.random() - 0.5) * volatility;
                p += change;
                data.push(p);
            }
            return data;
        }

        function generateLabels(points, type) {
            let labels = [];
            let now = new Date();
            for(let i=0; i<points; i++) {
                if(type === '1D') {
                    // Time labels (9:30 to 16:00 spaced)
                    let totalMin = 390; 
                    let step = totalMin / points;
                    let m = 9*60 + 30 + (i * step);
                    let h = Math.floor(m / 60);
                    let min = Math.floor(m % 60);
                    labels.push(`${h}:${min.toString().padStart(2,'0')}`);
                } else {
                    // Date labels
                    let d = new Date();
                    d.setDate(d.getDate() - (points - i));
                    labels.push(`${d.getMonth()+1}/${d.getDate()}`);
                }
            }
            return labels;
        }

        // Init Chart
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(30, 88, 235, 0.2)'); // Blue tint
        gradient.addColorStop(1, 'rgba(30, 88, 235, 0.0)');

        const stockChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: generateLabels(78, '1D'),
                datasets: [{
                    label: 'Price',
                    data: generateData(78, 0.5),
                    borderColor: '#1E58EB', // XTrade Blue
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false }, 
                    tooltip: { 
                        mode: 'index', 
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toFixed(2);
                            }
                        }
                    } 
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 6, autoSkip: true } },
                    y: { position: 'right', grid: { color: '#f5f5f5', borderDash: [5, 5] } }
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false },
                animation: { duration: 800 } 
            }
        });

        // Tab Switching Logic
        const tabs = document.querySelectorAll('.c-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // UI Update
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Data Update
                const range = tab.innerText;
                let points = 78;
                let vol = 0.5;
                
                if(range === '5D') { points = 50; vol = 1.2; }
                if(range === '1M') { points = 30; vol = 2.5; }
                if(range === '3M') { points = 90; vol = 5.0; }
                if(range === '1Y') { points = 120; vol = 10.0; }
                if(range === '5Y') { points = 200; vol = 20.0; }

                // Update Chart
                stockChart.data.labels = generateLabels(points, range);
                stockChart.data.datasets[0].data = generateData(points, vol);
                
                // Change color based on trend (First vs Last)
                const start = stockChart.data.datasets[0].data[0];
                const end = stockChart.data.datasets[0].data[points-1];
                const color = end >= start ? '#00b06a' : '#ff3b3b'; // Green or Red
                
                stockChart.data.datasets[0].borderColor = color;
                
                // Update Gradient
                let newGrad = ctx.createLinearGradient(0, 0, 0, 400);
                newGrad.addColorStop(0, color === '#00b06a' ? 'rgba(0, 176, 106, 0.2)' : 'rgba(255, 59, 59, 0.2)');
                newGrad.addColorStop(1, 'rgba(255, 255, 255, 0)');
                stockChart.data.datasets[0].backgroundColor = newGrad;

                stockChart.update();
            });
        });

        // Live Price Simulation
        const priceEl = document.querySelector('.price-large');
        const changeEl = document.querySelector('.change-large');

        setInterval(() => {
            // Random small fluctuation
            let move = (Math.random() - 0.5) * 0.15;
            currentPrice += move;
            
            // Update Text
            priceEl.innerText = numberFormat(currentPrice);
            
            // Flash Effect
            priceEl.style.color = move >= 0 ? '#4ade80' : '#ff5252';
            setTimeout(() => {
                // Revert to main color or keep? Let's keep based on day change
                // For logic simplicity, we'll just flash briefly or leave it
            }, 300);

            // Update Chart Live (Only if 1D tab is active)
            if(document.querySelector('.c-tab.active').innerText === '1D') {
                 // Push new point? Or just update last?
                 // Updating last point for smoothness
                 let len = stockChart.data.datasets[0].data.length;
                 stockChart.data.datasets[0].data[len-1] = currentPrice;
                 stockChart.update('none'); // Update without full re-render animation
            }

        }, 2000);

        function numberFormat(num) {
            return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

    </script>

</body>
</html>
