<?php
require 'config.php';
header('Content-Type: application/json');

$ticker = $_GET['ticker'] ?? 'AAPL';
$range = $_GET['range'] ?? '1Y';

// Map range to interval/limit
$interval = '1 DAY'; 
$limit = 365;

switch($range) {
    case '1D': $limit = 1; break; 
    case '1W': $limit = 7; break;
    case '1M': $limit = 30; break;
    case '3M': $limit = 90; break;
    case '1Y': $limit = 365; break;
    case '5Y': $limit = 1825; break;
}

try {
    // Get the most recent price as a baseline
    $stmt = $pdo->prepare("
        SELECT timestamp, close 
        FROM Price_History 
        WHERE ticker_symbol = ?
        ORDER BY timestamp DESC
        LIMIT 1
    ");
    $stmt->execute([$ticker]);
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $basePrice = $latest ? (float)$latest['close'] : 150.00;
    
    // If range is 1D, we generate high-res intraday mock data
    // because our DB only has daily resolution.
    if ($range === '1D') {
        $dataPts = [];
        $labels = [];
        $current = $basePrice * 0.98; // Start slightly lower to show movement
        
        // Simulating 9:30 AM to 4:00 PM (approx 78 5-min intervals)
        $startTime = strtotime('09:30');
        
        for ($i = 0; $i < 78; $i++) {
            $change = (mt_rand(-20, 25) / 10000); // Volatility
            $current *= (1 + $change);
            $dataPts[] = round($current, 2);
            $labels[] = date('H:i', $startTime + ($i * 300));
        }
        
        // Force the last point to be close to actual basePrice for consistency
        $dataPts[count($dataPts)-1] = $basePrice;

        $start = $dataPts[0];
        $end = $basePrice;
        $change = (($end - $start) / $start) * 100;
        
        echo json_encode([
            'ticker' => $ticker,
            'data' => $dataPts,
            'labels' => $labels,
            'current_price' => $end,
            'change_percent' => $change
        ]);
        exit;
    }

    // Normal DB Fetch for > 1D
    $stmt = $pdo->prepare("
        SELECT timestamp, close 
        FROM Price_History 
        WHERE ticker_symbol = ? AND timestamp >= ? 
        ORDER BY timestamp ASC
    ");
    
    // Helper to calculate start date
    $now = new DateTime();
    $startDate = clone $now;
    switch($range) {
        case '1W': $startDate->modify('-7 days'); break;
        case '1M': $startDate->modify('-1 month'); break;
        case '3M': $startDate->modify('-3 months'); break;
        case '1Y': $startDate->modify('-1 year'); break;
        case '5Y': $startDate->modify('-5 years'); break;
        default: $startDate->modify('-1 year');
    }

    $stmt->execute([$ticker, $startDate->format('Y-m-d H:i:s')]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sort asc for chart
    $rows = array_reverse($rows);

    echo json_encode([
        'ticker' => $ticker,
        'data' => array_map(function($d) { return (float)$d['close']; }, $rows),
        'labels' => array_map(function($d) { return date('M d', strtotime($d['timestamp'])); }, $rows),
        'current_price' => $rows ? $rows[count($rows)-1]['close'] : 0,
        'change_percent' => count($rows) > 1 ? (($rows[count($rows)-1]['close'] - $rows[0]['close']) / $rows[0]['close']) * 100 : 0
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
