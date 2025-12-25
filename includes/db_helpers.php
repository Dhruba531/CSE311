<?php
/**
 * Database Helper Functions
 * Common queries used across multiple pages
 */

/**
 * Get user accounts
 */
function getUserAccounts($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT * FROM Accounts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Get US stocks (Instruments)
 */
function getUSStocks($pdo)
{
    $stmt = $pdo->query("
        SELECT * FROM Instruments 
        ORDER BY name
    ");
    return $stmt->fetchAll();
}

/**
 * Get US exchanges
 */
function getUSExchanges($pdo)
{
    $stmt = $pdo->query("SELECT * FROM Exchanges ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get user holdings from Positions
 */
function getUserHoldings($pdo, $user_id)
{
    // Need account_id first. Assuming primary account for now.
    $stmt = $pdo->prepare("
        SELECT i.ticker_symbol, p.total_quantity as shares_held
        FROM Positions p
        JOIN Accounts a ON p.account_id = a.account_id
        JOIN Instruments i ON p.instrument_id = i.instrument_id
        WHERE a.user_id = ? AND p.total_quantity > 0
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

/**
 * Get user's watchlist
 */
function getUserWatchlist($pdo, $user_id)
{
    $stmt = $pdo->prepare("
        SELECT i.*, 0 as previous_close
        FROM Watchlist_Items wi
        JOIN Watchlists w ON wi.watchlist_id = w.watchlist_id
        JOIN Instruments i ON wi.instrument_id = i.instrument_id
        WHERE w.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(); // Might need to group by watchlist if multiple
}

/**
 * Get user's price alerts
 */
function getUserAlerts($pdo, $user_id)
{
    $stmt = $pdo->prepare("
        SELECT pa.*, i.name as company_name, i.current_price
        FROM Price_Alerts pa
        JOIN Instruments i ON pa.instrument_id = i.instrument_id
        WHERE pa.user_id = ?
        ORDER BY pa.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Format currency
 */
function formatCurrency($amount)
{
    return '$' . number_format($amount, 2);
}

/**
 * Format percentage
 */
function formatPercentage($value)
{
    $sign = $value >= 0 ? '+' : '';
    return $sign . number_format($value, 2) . '%';
}

/**
 * Get price change color
 */
function getPriceChangeColor($change)
{
    if ($change > 0)
        return '#22c55e'; // green
    if ($change < 0)
        return '#ef4444'; // red
    return '#94a3b8'; // gray
}

/**
 * Get stock price
 */
function getStockPrice($pdo, $ticker_symbol)
{
    $stmt = $pdo->prepare("SELECT current_price FROM Instruments WHERE ticker_symbol = ?");
    $stmt->execute([$ticker_symbol]);
    $result = $stmt->fetch();
    return $result ? $result['current_price'] : null;
}

/**
 * Execute a trade (Helper version)
 * Note: trade.php has its own implementation. This is for API/Other checks.
 */
function executeTrade($pdo, $user_id, $account_id, $ticker_symbol, $shares, $is_buy, $price)
{
    // Simplified logic for helper usage
    // 1. Get Instrument
    $stmt = $pdo->prepare("SELECT instrument_id FROM Instruments WHERE ticker_symbol = ?");
    $stmt->execute([$ticker_symbol]);
    $instId = $stmt->fetchColumn();
    if (!$instId) throw new Exception("Instrument not found");

    $total_cost = $price * $shares;

    if ($is_buy) {
         // Deduct
         $stmt = $pdo->prepare("UPDATE Accounts SET balance = balance - ?, buying_power = buying_power - ? WHERE account_id = ?");
         $stmt->execute([$total_cost, $total_cost, $account_id]);
         // Insert Order/Trade/Position logic omitted for brevity in helper, strictly use trade.php logic effectively
         // But for completeness:
         $stmt = $pdo->prepare("INSERT INTO Orders (account_id, instrument_id, order_type, side, quantity, status) VALUES (?, ?, 'MARKET', 'BUY', ?, 'FILLED')");
         $stmt->execute([$account_id, $instId, $shares]);
    } else {
         // Add
         $stmt = $pdo->prepare("UPDATE Accounts SET balance = balance + ?, buying_power = buying_power + ? WHERE account_id = ?");
         $stmt->execute([$total_cost, $total_cost, $account_id]);
    }
    return true;
}

/**
 * Get price change arrow
 */
function getPriceChangeArrow($change)
{
    if ($change > 0)
        return '▲';
    if ($change < 0)
        return '▼';
    return '━';
}
