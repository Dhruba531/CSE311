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
    $stmt = $pdo->prepare("SELECT * FROM Account WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Get US stocks with prices
 */
function getUSStocks($pdo)
{
    $stmt = $pdo->query("
        SELECT s.*, sp.current_price, b.region_id, r.region_name
        FROM Stock s 
        LEFT JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol 
        LEFT JOIN Business b ON s.business_id = b.business_id
        LEFT JOIN Region r ON b.region_id = r.region_id
        WHERE r.region_name = 'North America'
        ORDER BY s.company_name
    ");
    return $stmt->fetchAll();
}

/**
 * Get US exchanges
 */
function getUSExchanges($pdo)
{
    $stmt = $pdo->query("
        SELECT e.* 
        FROM Exchange e 
        JOIN Region r ON e.region_id = r.region_id 
        WHERE r.region_name = 'North America'
        ORDER BY e.exchange_name
    ");
    return $stmt->fetchAll();
}

/**
 * Get user holdings
 */
function getUserHoldings($pdo, $user_id)
{
    $stmt = $pdo->prepare("
        SELECT ticker_symbol, SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END) as shares_held
        FROM TransactionRecord
        WHERE user_id = ?
        GROUP BY ticker_symbol
        HAVING shares_held > 0
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
        SELECT w.*, s.company_name, sp.current_price, sp.previous_close
        FROM Watchlist w
        JOIN Stock s ON w.ticker_symbol = s.ticker_symbol
        LEFT JOIN StockPrice sp ON w.ticker_symbol = sp.ticker_symbol
        WHERE w.user_id = ?
        ORDER BY w.added_date DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Get user's price alerts
 */
function getUserAlerts($pdo, $user_id)
{
    $stmt = $pdo->prepare("
        SELECT pa.*, s.company_name, sp.current_price
        FROM PriceAlert pa
        JOIN Stock s ON pa.ticker_symbol = s.ticker_symbol
        LEFT JOIN StockPrice sp ON pa.ticker_symbol = sp.ticker_symbol
        WHERE pa.user_id = ?
        ORDER BY pa.created_date DESC
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
