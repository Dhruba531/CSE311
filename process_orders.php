<?php
// This script would normally run as a Cron Job or Daemon
require 'config.php';

echo "Running Order Processor...\n";

try {
    $pdo->beginTransaction();

    // Fetch pending orders
    $orders = $pdo->query("SELECT * FROM Orders WHERE status = 'pending'")->fetchAll();

    foreach ($orders as $o) {
        $oid = $o['order_id'];
        $uid = $o['user_id'];
        $aid = $o['account_id'];
        $sym = $o['ticker_symbol'];
        $type = $o['order_type'];
        $target = $o['target_price'];
        $qty = $o['num_shares'];
        
        // Get current price
        $stmt_price = $pdo->prepare("SELECT current_price FROM StockPrice WHERE ticker_symbol = ?");
        $stmt_price->execute([$sym]);
        $curr = $stmt_price->fetchColumn();
        
        if (!$curr) continue;
        
        $executed = false;
        
        if ($type == 'limit_buy') {
            if ($curr <= $target) {
                $stmt = $pdo->prepare("INSERT INTO TransactionRecord(user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares)
                             VALUES (?, ?, ?, 1, ?, ?)");
                $stmt->execute([$uid, $aid, $sym, $curr, $qty]);
                
                $diff = ($target - $curr) * $qty;
                if ($diff > 0) {
                    $stmt = $pdo->prepare("UPDATE Account SET balance = balance + ? WHERE user_id = ?");
                    $stmt->execute([$diff, $uid]);
                }
                
                $executed = true;
            }
        } elseif ($type == 'limit_sell') {
            if ($curr >= $target) {
                $stmt_owned = $pdo->prepare("SELECT SUM(IF(is_buy, num_shares, -num_shares)) FROM TransactionRecord WHERE user_id = ? AND ticker_symbol = ?");
                $stmt_owned->execute([$uid, $sym]);
                $owned = $stmt_owned->fetchColumn();
                
                if ($owned >= $qty) {
                    $total_credit = $curr * $qty;
                    $stmt = $pdo->prepare("UPDATE Account SET balance = balance + ? WHERE user_id = ?");
                    $stmt->execute([$total_credit, $uid]);
                    
                    $stmt = $pdo->prepare("INSERT INTO TransactionRecord(user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares)
                                 VALUES (?, ?, ?, 0, ?, ?)");
                    $stmt->execute([$uid, $aid, $sym, $curr, $qty]);
                    $executed = true;
                } else {
                    $stmt = $pdo->prepare("UPDATE Orders SET status = 'cancelled' WHERE order_id = ?");
                    $stmt->execute([$oid]);
                    echo "Order #$oid cancelled: Insufficient shares.\n";
                }
            }
        }
        
        if ($executed) {
            $stmt = $pdo->prepare("UPDATE Orders SET status = 'filled' WHERE order_id = ?");
            $stmt->execute([$oid]);
            echo "Order #$oid executed at $$curr.\n";
        }
    }

    $pdo->commit();
    echo "Done.\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
