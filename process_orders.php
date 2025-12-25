<?php
// This script runs as a background job to execute Limit Orders
require 'config.php';

echo "Running Order Processor...\n";

try {
    $pdo->beginTransaction();

    // Fetch pending transactions (Limit Orders)
    // Note: In our system, Limit Orders are stored in Transactions with status='PENDING'
    $stmt = $pdo->query("SELECT * FROM Transactions WHERE status = 'PENDING' AND order_type = 'LIMIT'");
    $orders = $stmt->fetchAll();

    foreach ($orders as $o) {
        $tid = $o['transaction_id'];
        $uid = $o['user_id'];
        $aid = $o['account_id'];
        $sym = $o['ticker_symbol'];
        $side = $o['transaction_type']; // BUY or SELL
        $targetPrice = $o['cost']; // For Limit orders, the 'cost' column stores the Limit Price
        $qty = $o['quantity'];
        
        // Get current market price
        $stmt_price = $pdo->prepare("SELECT current_price FROM Stocks WHERE ticker_symbol = ?");
        $stmt_price->execute([$sym]);
        $curr = $stmt_price->fetchColumn();
        
        if (!$curr) continue;
        
        echo "Processing #$tid ($side $qty $sym @ $targetPrice) - Current: $curr\n";
        
        $executed = false;
        
        if ($side == 'BUY') {
            // BUY LIMIT: Execute if Current Price <= Target
            if ($curr <= $targetPrice) {
                // Check Funds (Funds were not deducted on placement, so check now)
                // Note: Real systems freeze funds on placement. Here we verify balance.
                $stmt = $pdo->prepare("SELECT balance FROM Accounts WHERE account_id = ?");
                $stmt->execute([$aid]);
                $bal = $stmt->fetchColumn();
                
                $total_cost = $curr * $qty;
                
                if ($bal >= $total_cost) {
                    // Deduct Funds
                    $pdo->prepare("UPDATE Accounts SET balance = balance - ? WHERE account_id = ?")->execute([$total_cost, $aid]);
                    
                    // Add Holdings
                    $stmt = $pdo->prepare("SELECT quantity, avg_price FROM Holdings WHERE user_id = ? AND ticker_symbol = ?");
                    $stmt->execute([$uid, $sym]);
                    $holding = $stmt->fetch();
                    
                    if ($holding) {
                        $newQty = $holding['quantity'] + $qty;
                        $newAvg = (($holding['avg_price'] * $holding['quantity']) + ($total_cost)) / $newQty;
                        $pdo->prepare("UPDATE Holdings SET quantity = ?, avg_price = ? WHERE user_id = ? AND ticker_symbol = ?")
                            ->execute([$newQty, $newAvg, $uid, $sym]);
                    } else {
                        $pdo->prepare("INSERT INTO Holdings (user_id, ticker_symbol, quantity, avg_price) VALUES (?, ?, ?, ?)")
                            ->execute([$uid, $sym, $qty, $curr]);
                    }
                    
                    $executed = true;
                } else {
                    echo "Insufficient funds for Order #$tid. Skipping.\n";
                }
            }
        } elseif ($side == 'SELL') {
            // SELL LIMIT: Execute if Current Price >= Target
            if ($curr >= $targetPrice) {
                // Check Shares (We allowed placement even if shares change, need to verify ownership now)
                // Note: Real systems lock shares. 
                $stmt = $pdo->prepare("SELECT quantity FROM Holdings WHERE user_id = ? AND ticker_symbol = ?");
                $stmt->execute([$uid, $sym]);
                $owned = $stmt->fetchColumn() ?: 0;
                
                if ($owned >= $qty) {
                    // Remove Holdings
                    $pdo->prepare("UPDATE Holdings SET quantity = quantity - ? WHERE user_id = ? AND ticker_symbol = ?")
                        ->execute([$qty, $uid, $sym]);
                        
                    $pdo->prepare("DELETE FROM Holdings WHERE quantity <= 0 AND user_id = ?")->execute([$uid]);
                    
                    // Add Funds
                    $credit = $curr * $qty;
                    $pdo->prepare("UPDATE Accounts SET balance = balance + ? WHERE account_id = ?")->execute([$credit, $aid]);
                    
                    $executed = true;
                } else {
                    echo "Insufficient shares for Order #$tid. Cancelling.\n";
                    $pdo->prepare("UPDATE Transactions SET status = 'CANCELLED' WHERE transaction_id = ?")->execute([$tid]);
                }
            }
        }
        
        if ($executed) {
            // Mark Transaction as Completed
            // Update actual execution cost and status
            // Note: We update the existing pending row instead of inserting a new one
            $pdo->prepare("UPDATE Transactions SET status = 'COMPLETED', cost = ? WHERE transaction_id = ?")
                ->execute([$curr, $tid]); // Update 'cost' to actual execution price
                
            echo "Order #$tid EXECUTED at $$curr.\n";
        }
    }

    $pdo->commit();
    echo "Processing Complete.\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
