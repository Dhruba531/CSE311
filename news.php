<?php
session_start();
require 'config.php';
require_login();

$page_title = 'Market News';
require 'includes/header.php';
require 'includes/nav.php';
// Mock News Data
$news = [
    // Finance/Economy
    ['title' => 'Fed Signals Rate Cuts in Q4', 'source' => 'Bloomberg', 'time' => '2 hours ago', 
     'img' => 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?w=400&auto=format&fit=crop&q=60', 
     'tag' => 'Economy'],
     
    // Tech/AI
    ['title' => 'Apple Announces New AI Integration', 'source' => 'TechCrunch', 'time' => '4 hours ago', 
     'img' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&auto=format&fit=crop&q=60', 
     'tag' => 'Technology'],
     
    // Energy/Oil
    ['title' => 'Oil Prices Surge Amid Geopolitical Tensions', 'source' => 'Reuters', 'time' => '6 hours ago', 
     'img' => 'https://images.unsplash.com/photo-1518458028785-8fbcd101ebb9?w=400&auto=format&fit=crop&q=60', 
     'tag' => 'Energy'],
     
    // Earnings/Finance
    ['title' => 'Tesla Misses Earnings Expectations', 'source' => 'CNBC', 'time' => '8 hours ago', 
     'img' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=400&auto=format&fit=crop&q=60', 
     'tag' => 'Earnings'],
];
?>

<div class="flex-between mb-4">
    <h1>Market News</h1>
</div>

<div class="dashboard-grid">
    <!-- Featured Story -->
    <a href="news_details.php?id=0" class="card-premium col-span-2" style="text-decoration:none; padding:0; overflow:hidden; transition: transform 0.2s; display:block;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
        <div style="height: 300px; background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?w=1000&auto=format&fit=crop&q=80'); background-size: cover; position: relative; color: white; display:flex; align-items:flex-end; padding: 30px;">
            <div>
                <span style="background: var(--accent); padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 700;">TOP STORY</span>
                <h2 style="font-size: 2rem; margin: 10px 0;">Global Markets Rally as Inflation Cools Down</h2>
                <div style="font-size: 0.9rem; opacity: 0.9;">By Financial Times • 1 hour ago</div>
            </div>
        </div>
    </a>

    <!-- Latest News List -->
    <div class="card-premium">
        <h3>Latest Headlines</h3>
        <div style="display:flex; flex-direction:column; gap: 20px; margin-top: 15px;">
            <?php foreach($news as $k => $n): ?>
            <a href="news_details.php?id=<?= $k ?>" style="display:flex; gap: 15px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; align-items:center; text-decoration:none; color:inherit; transition: background 0.2s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                 <div style="width: 80px; height: 60px; border-radius: 8px; overflow:hidden; flex-shrink:0;">
                    <img src="<?= $n['img'] ?>" style="width:100%; height:100%; object-fit:cover;" alt="News Image">
                 </div>
                 <div>
                     <div style="font-size: 0.75rem; color: var(--xtrade-blue); font-weight:700;"><?= strtoupper($n['tag']) ?></div>
                     <h4 style="margin: 3px 0; font-size: 0.95rem; line-height: 1.4; color: var(--text-primary);"><?= $n['title'] ?></h4>
                     <div style="font-size: 0.8rem; color: #71717a;"><?= $n['source'] ?> • <?= $n['time'] ?></div>
                 </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php // footer placeholder check
?>
