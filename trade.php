<?php
session_start();
require 'config.php';

$uid = $_SESSION['user_id'] ?? null;
if (!$uid) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = strtolower($_POST['action']); // 'buy' or 'sell'
    // Map dropdown/radio to 'MARKET' or 'LIMIT'
    $orderType = isset($_POST['order_type']) ? strtoupper($_POST['order_type']) : 'MARKET'; 
    $limitPrice = isset($_POST['limit_price']) && $_POST['limit_price'] > 0 ? (float)$_POST['limit_price'] : null;

    $ticker = strtoupper($_POST['ticker_symbol']);
    $quantity = (float)$_POST['quantity'];

    if ($quantity <= 0) {
        $_SESSION['message'] = "Invalid quantity.";
        header("Location: stocks.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Get Account and Stock Info
        $stmt = $pdo->prepare("SELECT account_id, balance FROM Accounts WHERE user_id = ?");
        $stmt->execute([$uid]);
        $account = $stmt->fetch();
        
        if (!$account) { 
            // Auto-create account if missing (DB fix)
            $pdo->prepare("INSERT INTO Accounts (user_id, balance) VALUES (?, 100000)")->execute([$uid]);
            $accountId = $pdo->lastInsertId();
            $balance = 100000;
        } else {
            $accountId = $account['account_id'];
            $balance = $account['balance'];
        }

        $stmt = $pdo->prepare("SELECT current_price FROM Stocks WHERE ticker_symbol = ?");
        $stmt->execute([$ticker]);
        $stock = $stmt->fetch();
        
        if (!$stock) { throw new Exception("Stock not found."); }
        
        // Use Limit Price if specified, else Market Price
        $price = $stock['current_price'];
        if ($orderType == 'LIMIT' && $limitPrice) {
            // For limit orders, we use the user's set price for validation
            $executionPrice = $limitPrice;
        } else {
            $executionPrice = $price;
        }

        $total_cost = $executionPrice * $quantity;

        // 2. Buy/Sell Logic
        if ($action === 'buy') {
            
            // Check Funds
            if ($balance < $total_cost) {
                throw new Exception("Insufficient funds. Need $$total_cost, have $$balance.");
            }

            if ($orderType == 'MARKET') {
                // === MARKET BUY ===
                // Immediate Execution
                
                // 1. Deduct Balance
                $stmt = $pdo->prepare("UPDATE Accounts SET balance = balance - ? WHERE account_id = ?");
                $stmt->execute([$total_cost, $accountId]);
                
                // 2. Update Holdings (Upsert)
                // Calculate new Weighted Average Price
                // SQLite Upsert Syntax: ON CONFLICT(user_id, ticker_symbol) DO UPDATE SET ...
                
                // Fetch existing first
                $stmt = $pdo->prepare("SELECT quantity, avg_price FROM Holdings WHERE user_id = ? AND ticker_symbol = ?");
                $stmt->execute([$uid, $ticker]);
                $holding = $stmt->fetch();
                
                if ($holding) {
                    $newQty = $holding['quantity'] + $quantity;
                    $newAvg = (($holding['avg_price'] * $holding['quantity']) + ($executionPrice * $quantity)) / $newQty;
                    $stmt = $pdo->prepare("UPDATE Holdings SET quantity = ?, avg_price = ? WHERE user_id = ? AND ticker_symbol = ?");
                    $stmt->execute([$newQty, $newAvg, $uid, $ticker]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO Holdings (user_id, ticker_symbol, quantity, avg_price) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$uid, $ticker, $quantity, $executionPrice]);
                }

                // 3. Log Transaction
                $stmt = $pdo->prepare("
                    INSERT INTO Transactions (user_id, ticker_symbol, transaction_type, order_type, status, cost, quantity, account_id)
                    VALUES (?, ?, 'BUY', 'MARKET', 'COMPLETED', ?, ?, ?)
                ");
                $stmt->execute([$uid, $ticker, $executionPrice, $quantity, $accountId]);

                $msg = "Bought $quantity $ticker at market price $$executionPrice.";

            } else {
                // === LIMIT BUY ===
                // Pending Order
                // Verify logic: Current Price vs Limit Price 
                
                // Create Pending Transaction
                $stmt = $pdo->prepare("
                    INSERT INTO Transactions (user_id, ticker_symbol, transaction_type, order_type, status, cost, quantity, account_id)
                    VALUES (?, ?, 'BUY', 'LIMIT', 'PENDING', ?, ?, ?)
                ");
                $stmt->execute([$uid, $ticker, $executionPrice, $quantity, $accountId]); // Cost here is the Target Price

                // Optional: Freeze funds in a real app. For now, we allow it.
                $msg = "Limit Order Placed: Buy $quantity $ticker at $$executionPrice.";
            }

        } elseif ($action === 'sell') {
            
            // Check Holdings
            $stmt = $pdo->prepare("SELECT quantity FROM Holdings WHERE user_id = ? AND ticker_symbol = ?");
            $stmt->execute([$uid, $ticker]);
            $owned = $stmt->fetchColumn() ?: 0;
            
            if ($owned < $quantity) {
                 throw new Exception("Insufficient shares. You own $owned.");
            }

            if ($orderType == 'MARKET') {
                // === MARKET SELL ===
                
                // 1. Add Funds
                $stmt = $pdo->prepare("UPDATE Accounts SET balance = balance + ? WHERE account_id = ?");
                $stmt->execute([$total_cost, $accountId]);
                
                // 2. Reduce Holdings
                $stmt = $pdo->prepare("UPDATE Holdings SET quantity = quantity - ? WHERE user_id = ? AND ticker_symbol = ?");
                $stmt->execute([$quantity, $uid, $ticker]);
                
                // Cleanup zero holdings
                $pdo->prepare("DELETE FROM Holdings WHERE quantity <= 0 AND user_id = ?")->execute([$uid]);

                // 3. Log
                $stmt = $pdo->prepare("
                    INSERT INTO Transactions (user_id, ticker_symbol, transaction_type, order_type, status, cost, quantity, account_id)
                    VALUES (?, ?, 'SELL', 'MARKET', 'COMPLETED', ?, ?, ?)
                ");
                $stmt->execute([$uid, $ticker, $executionPrice, $quantity, $accountId]);

                $msg = "Sold $quantity $ticker at market price $$executionPrice.";

            } else {
                // === LIMIT SELL ===
                // Pending Order
                $stmt = $pdo->prepare("
                    INSERT INTO Transactions (user_id, ticker_symbol, transaction_type, order_type, status, cost, quantity, account_id)
                    VALUES (?, ?, 'SELL', 'LIMIT', 'PENDING', ?, ?, ?)
                ");
                $stmt->execute([$uid, $ticker, $executionPrice, $quantity, $accountId]);
                
                $msg = "Limit Order Placed: Sell $quantity $ticker at $$executionPrice.";
            }
        }

        $pdo->commit();
        $_SESSION['message'] = $msg;
        $_SESSION['msg_type'] = 'success';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['msg_type'] = 'error';
    }

    header("Location: stocks.php");
    exit;
} else {
    // If accessed directly via GET, redirect to market
    header("Location: stocks.php");
    exit;
}
?>
