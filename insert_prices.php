<?php
require 'config.php';

// Insert sample price data for existing stocks
$prices = [
    ['AAPL', 150.00, 148.50],
    ['TATAMOTORS', 450.00, 455.00],
    ['005930.KS', 70000.00, 70000.00],
    ['BABA', 85.00, 88.00],
    ['GOOGL', 2800.00, 2750.00],
    ['AMZN', 3300.00, 3350.00],
    ['LVMUY', 160.00, 158.00],
    ['META', 300.00, 295.00]
];

$stmt = $pdo->prepare("INSERT INTO StockPrice (ticker_symbol, current_price, previous_close) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE current_price = VALUES(current_price), previous_close = VALUES(previous_close)");

foreach ($prices as $price) {
    try {
        $stmt->execute($price);
        echo "Inserted/Updated: {$price[0]}\n";
    } catch (PDOException $e) {
        echo "Error for {$price[0]}: " . $e->getMessage() . "\n";
    }
}

echo "Done!\n";
?>