<?php
require_once 'config.php';

// Stocks to seed with Real Names
$tickers = [
    'AAPL' => 'Apple Inc.',
    'TSLA' => 'Tesla, Inc.',
    'SPY'  => 'SPDR S&P 500 ETF Trust',
    'BTC'  => 'Bitcoin USD',
    'NVDA' => 'NVIDIA Corporation',
    'MSFT' => 'Microsoft Corporation',
    'AMZN' => 'Amazon.com, Inc.',
    'GOOGL'=> 'Alphabet Inc.',
    'META' => 'Meta Platforms, Inc.',
    'NFLX' => 'Netflix, Inc.'
];
$years = 5;
$today = new DateTime();
$start = (clone $today)->modify("-$years years");

echo "Seeding Market Data & CSE311 Tables...\n";

try {
    $pdo->beginTransaction();

    // Determine INSERT syntax based on DB type
    // $db_connection is available from config.php
    $insertIgnore = ($db_connection === 'mysql') ? 'INSERT IGNORE' : 'INSERT OR IGNORE';

    // 1. Seed Basic CSE311 requirements (Region, Exchange, Business)
    // -----------------------------------------------------------
    
    // Region
    $pdo->prepare("$insertIgnore INTO Region (region_name) VALUES ('North America')")->execute();
    $stmt = $pdo->prepare("SELECT region_id FROM Region WHERE region_name = 'North America'");
    $stmt->execute();
    $regionId = $stmt->fetchColumn();

    // Exchange
    $pdo->prepare("$insertIgnore INTO Exchange (exchange_name, short_code, region_id) VALUES ('NASDAQ', 'NSDQ', ?)")->execute([$regionId]);
    $stmt = $pdo->prepare("SELECT exchange_id FROM Exchange WHERE short_code = 'NSDQ'");
    $stmt->execute();
    $exchId = $stmt->fetchColumn();

    // 2. Loop Tickers to Create Business & Stocks
    // -----------------------------------------------------------
    foreach ($tickers as $ticker => $name) {
        echo "Processing $ticker...\n";
        
        // Create Business
        $pdo->prepare("$insertIgnore INTO Business (company_name, year_est, region_id) VALUES (?, 1990, ?)")->execute(["$name", $regionId]);
        $stmt = $pdo->prepare("SELECT business_id FROM Business WHERE company_name = ?");
        $stmt->execute(["$name"]);
        $bizId = $stmt->fetchColumn();

        // Create Stock
        // SQLite upsert or ignore
        $stmt = $pdo->prepare("SELECT ticker_symbol FROM Stocks WHERE ticker_symbol = ?");
        $stmt->execute([$ticker]);
        if (!$stmt->fetch()) {
            $pdo->prepare("INSERT INTO Stocks (ticker_symbol, business_id, stock_name, exchange_id, current_price) VALUES (?, ?, ?, ?, 0)")
                ->execute([$ticker, $bizId, $name, $exchId]);
            
            // Seed Traded_On
            $pdo->prepare("INSERT INTO Traded_On (ticker_symbol, exchange_id) VALUES (?, ?)")->execute([$ticker, $exchId]);
        }

        // 3. Generate Historical Market Data
        // -----------------------------------------------------------
        $currentDate = clone $start;
        $price = ($ticker === 'BTC') ? 20000 : 100; // Base prices
        
        $inserts = [];
        $batchSize = 500;
        $count = 0;
        $lastClose = $price;

        while ($currentDate <= $today) {
            // Skip weekends for non-crypto
            if ($ticker !== 'BTC' && $currentDate->format('N') >= 6) {
                $currentDate->modify('+1 day');
                continue;
            }

            // Random walk
            $change = (mt_rand(-30, 35) / 1000); 
            $price *= (1 + $change);
            
            $open = $price;
            $high = $price * (1 + mt_rand(0, 20)/1000);
            $low = $price * (1 - mt_rand(0, 20)/1000);
            $close = $price * (1 + mt_rand(-10, 10)/1000);
            $volume = mt_rand(100000, 10000000);

            // Format for SQLite Insert
            $inserts[] = "('$ticker', '" . $currentDate->format('Y-m-d H:i:s') . "', $open, $high, $low, $close, $volume)";
            
            $count++;
            $lastClose = $close;

            if ($count % $batchSize === 0) {
                 $sql = "INSERT INTO Price_History (ticker_symbol, timestamp, open, high, low, close, volume) VALUES " . implode(',', $inserts);
                 $pdo->exec($sql);
                 $inserts = [];
            }

            $currentDate->modify('+1 day');
        }

        if (!empty($inserts)) {
            $sql = "INSERT INTO Price_History (ticker_symbol, timestamp, open, high, low, close, volume) VALUES " . implode(',', $inserts);
            $pdo->exec($sql);
        }
        
        // Update current price in Stocks table
        $pdo->prepare("UPDATE Stocks SET current_price = ? WHERE ticker_symbol = ?")->execute([$lastClose, $ticker]);
    }

    $pdo->commit();
    echo "Done! CSE311 Schema Seeded.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?>
