<?php
// market_engine.php
// A simple script to simulate market movement.
// Run this via CLI or cron job: php market_engine.php

require 'config.php';

echo "Opening Market...\n";

try {
    $pdo->beginTransaction();

    // Fetch all stocks
    $stmt = $pdo->query("SELECT ticker_symbol, current_price FROM StockPrice");
    $stocks = $stmt->fetchAll();

    foreach ($stocks as $s) {
        $ticker = $s['ticker_symbol'];
        $old_price = (float)$s['current_price'];
        
        // Random fluctuation between -2.0% and +2.0%
        // We use a stronger bias for volatility
        $percent_change = rand(-200, 200) / 10000; // -0.02 to 0.02
        
        $change = $old_price * $percent_change;
        $new_price = $old_price + $change;

        // Emsure price never drops below 0.01
        if ($new_price < 0.01) $new_price = 0.01;

        // Update DB
        $upd = $pdo->prepare("UPDATE StockPrice SET current_price = ? WHERE ticker_symbol = ?");
        $upd->execute([$new_price, $ticker]);

        echo "$ticker: $" . number_format($old_price, 2) . " -> $" . number_format($new_price, 2) . " (" . ($percent_change * 100) . "%)\n";
    }

    $pdo->commit();
    echo "Market Updated Successfully.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Market Crash (Error): " . $e->getMessage() . "\n";
}
