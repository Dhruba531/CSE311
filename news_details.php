<?php
session_start();
require 'config.php';
require_login();

// Mock Data (Shared with news.php - ideally would be in a DB or helper)
$news = [
    // Finance/Economy
    ['title' => 'Fed Signals Rate Cuts in Q4', 'source' => 'Bloomberg', 'time' => '2 hours ago', 
     'img' => 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?w=1000&auto=format&fit=crop&q=80', 
     'tag' => 'Economy',
     'content' => 'The Federal Reserve has signaled potential rate cuts coming in the fourth quarter of this year. Analysts predict a 25 basis point reduction as inflation data shows promising signs of cooling down. This move is expected to stimulate activity in the housing and automotive sectors.'],
     
    // Tech/AI
    ['title' => 'Apple Announces New AI Integration', 'source' => 'TechCrunch', 'time' => '4 hours ago', 
     'img' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=1000&auto=format&fit=crop&q=80', 
     'tag' => 'Technology',
     'content' => 'Apple has unveiled its latest artificial intelligence integration across its ecosystem. The new features promise to enhance user productivity and introduce generative capabilities to Siri. Initial market reaction has been positive, with stock prices rising 2% in pre-market trading.'],
     
    // Energy/Oil
    ['title' => 'Oil Prices Surge Amid Geopolitical Tensions', 'source' => 'Reuters', 'time' => '6 hours ago', 
     'img' => 'https://images.unsplash.com/photo-1518458028785-8fbcd101ebb9?w=1000&auto=format&fit=crop&q=80', 
     'tag' => 'Energy',
     'content' => 'Oil prices saw a significant surge today as geopolitical tensions in the Middle East escalated. Brent Crude rose by 3.5% to reach $85 per barrel. Experts warn that prolonged instability could lead to higher fuel prices globally.'],
     
    // Earnings/Finance
    ['title' => 'Tesla Misses Earnings Expectations', 'source' => 'CNBC', 'time' => '8 hours ago', 
     'img' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=1000&auto=format&fit=crop&q=80', 
     'tag' => 'Earnings',
     'content' => 'Tesla reported Q3 earnings that fell short of analyst expectations, citing supply chain disruptions and price cuts. Revenue came in at $23.35 billion versus the expected $24.1 billion. The stock is down 4% in after-hours trading.'],
];

$id = $_GET['id'] ?? 0;
$article = $news[$id] ?? $news[0];

$page_title = $article['title'];
require 'includes/header.php';
require 'includes/nav.php';
?>

<div class="mb-4">
    <a href="news.php" style="text-decoration:none; color:var(--text-secondary); font-size:0.9rem; display:flex; align-items:center; gap:0.5rem;">
        <i class="fas fa-arrow-left"></i> Back to News
    </a>
</div>

<article style="max-width: 800px; margin: 0 auto;">
    <div style="height: 400px; border-radius: 16px; overflow: hidden; margin-bottom: 24px;">
        <img src="<?= $article['img'] ?>" style="width:100%; height:100%; object-fit: cover;" alt="Cover Image">
    </div>
    
    <div style="text-align: center; margin-bottom: 32px;">
        <span style="background: var(--xtrade-blue); color:white; padding: 4px 12px; border-radius: 99px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
            <?= $article['tag'] ?>
        </span>
        <h1 style="font-size: 2.5rem; margin: 16px 0; line-height: 1.2;"><?= $article['title'] ?></h1>
        <div style="color: var(--text-secondary); font-size: 0.95rem;">
            By <strong><?= $article['source'] ?></strong> • <?= $article['time'] ?>
        </div>
    </div>
    
    <div class="card-premium" style="line-height: 1.8; font-size: 1.1rem; color: #3f3f46;">
        <p><?= $article['content'] ?></p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
    </div>
</article>

<?php require 'includes/footer.php'; ?>
