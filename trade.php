<?php
session_start();
require 'config.php';

$uid = $_SESSION['user_id'] ?? null;
if (!$uid) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action']; // 'buy' or 'sell'
    $ticker = strtoupper($_POST['ticker']);
    $quantity = (int)$_POST['quantity'];
    $orderType = 'MARKET'; // Defaulting to market for Quick Trade widget

    if ($quantity <= 0) {
        $_SESSION['message'] = "Invalid quantity.";
        header("Location: index.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Get Account Balance
        $stmt = $pdo->prepare("SELECT account_id, balance FROM Account WHERE user_id = ? FOR UPDATE");
        $stmt->execute([$uid]);
        $account = $stmt->fetch();

        if (!$account) {
            // Create account if not exists (recover from partial setup)
            $stmt = $pdo->prepare("INSERT INTO Account (user_id, balance) VALUES (?, 10000.00)");
            $stmt->execute([$uid]);
            $account_id = $pdo->lastInsertId();
            $balance = 10000.00;
        } else {
            $account_id = $account['account_id'];
            $balance = $account['balance'];
        }

        // 2. Get Stock Price
        $stmt = $pdo->prepare("SELECT current_price FROM Stocks WHERE ticker = ?");
        $stmt->execute([$ticker]);
        $price = $stmt->fetchColumn();

        if (!$price) {
            // Auto-seed if missing (for demo purposes)
            $price = 150.00; 
            $stmt = $pdo->prepare("INSERT INTO Stocks (ticker, company_name, current_price) VALUES (?, ?, ?)");
            $stmt->execute([$ticker, "$ticker Inc.", $price]);
        }

        $total_cost = $price * $quantity;

        // 3. Execution Logic
        if ($action === 'buy') {
            if ($balance < $total_cost) {
                throw new Exception("Insufficient funds. Need $$total_cost, have $$balance.");
            }
            
            // TransactionRecord (Trigger will handle balance update, but let's be safe and do it here too if trigger fails/not checking)
            // Actually relying on Trigger 'after_trade_execution' from final_schema.sql
            // Trigger logic: IF BUY -> balance - total_amount.
            
            $stmt = $pdo->prepare("INSERT INTO TransactionRecord (user_id, ticker, type, price, quantity, total_amount) VALUES (?, ?, 'BUY', ?, ?, ?)");
            $stmt->execute([$uid, $ticker, $price, $quantity, $total_cost]);
            
            $msg = "Successfully bought $quantity $ticker at $$price.";

        } elseif ($action === 'sell') {
            // Check ownership
            $stmt = $pdo->prepare("SELECT 
                SUM(CASE WHEN type = 'BUY' THEN quantity ELSE 0 END) - 
                SUM(CASE WHEN type = 'SELL' THEN quantity ELSE 0 END) as owned 
                FROM TransactionRecord WHERE user_id = ? AND ticker = ?");
            $stmt->execute([$uid, $ticker]);
            $owned = $stmt->fetchColumn() ?: 0;

            if ($owned < $quantity) {
                 throw new Exception("Insufficient shares. You own $owned.");
            }

            $stmt = $pdo->prepare("INSERT INTO TransactionRecord (user_id, ticker, type, price, quantity, total_amount) VALUES (?, ?, 'SELL', ?, ?, ?)");
            $stmt->execute([$uid, $ticker, $price, $quantity, $total_cost]);

            $msg = "Successfully sold $quantity $ticker at $$price.";
        } else {
            throw new Exception("Invalid action.");
        }

        $pdo->commit();
        $_SESSION['message'] = $msg;
        $_SESSION['msg_type'] = 'success';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['msg_type'] = 'error';
    }

    header("Location: index.php");
    exit;
}
?>
