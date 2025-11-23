# Advanced Features Documentation

## 🎯 Overview

This enhanced stock trading system includes advanced database features, triggers, stored procedures, views, and comprehensive UI features.

## 📊 Database Enhancements

### New Tables Added

1. **StockPriceHistory** - Tracks historical stock prices over time
2. **StockPrice** - Current/latest stock prices with market data
3. **Watchlist** - User's stock watchlist with notes
4. **PriceAlert** - Price alerts (above/below/percentage change)
5. **OrderType** - Advanced order types (Limit, Stop-Loss, Stop-Limit)
6. **Dividend** - Dividend information for stocks
7. **UserDividend** - User dividend payments tracking
8. **AuditLog** - Complete audit trail of all actions
9. **Notification** - User notifications system
10. **PortfolioSnapshot** - Daily portfolio performance snapshots

### Database Triggers (6 Triggers)

1. **trg_after_transaction_insert**
   - Automatically updates account balance after buy/sell
   - Logs transaction in audit trail
   - Runs AFTER transaction insert

2. **trg_price_alert_check**
   - Checks price alerts when stock price updates
   - Creates notifications when alerts trigger
   - Marks alerts as triggered
   - Runs AFTER StockPrice update

3. **trg_update_portfolio_snapshot**
   - Automatically creates/updates daily portfolio snapshots
   - Calculates total value, cost, gain/loss
   - Runs AFTER transaction insert

4. **trg_check_balance_before_transaction**
   - Prevents negative balance transactions
   - Validates sufficient funds before buy
   - Runs BEFORE transaction insert

5. **trg_check_shares_before_sell**
   - Validates sufficient shares before sell
   - Prevents selling more shares than owned
   - Runs BEFORE transaction insert

6. **trg_update_price_history**
   - Automatically logs price changes to history
   - Updates price history table
   - Runs AFTER StockPrice update

### Stored Procedures (3 Procedures)

1. **sp_execute_trade**
   - Executes buy/sell trades with validation
   - Handles balance and shares checking
   - Returns transaction result
   - Parameters: user_id, account_id, ticker_symbol, is_buy, num_shares, cost_per_share, exchange_id

2. **sp_get_portfolio_analytics**
   - Calculates comprehensive portfolio statistics
   - Returns total stocks, shares, value, cost, gain/loss
   - Parameters: user_id

3. **sp_calculate_dividends**
   - Calculates dividend payments for users
   - Based on shares owned
   - Parameters: user_id, dividend_id

### Database Views (4 Views)

1. **v_portfolio_summary**
   - Complete portfolio overview for all users
   - Includes stocks owned, total value, gain/loss, cash balance

2. **v_top_performers**
   - Top 10 performing stocks by percentage change
   - Includes price data and volume

3. **v_user_activity**
   - User activity summary
   - Transaction counts, watchlist, alerts statistics

4. **v_stock_performance**
   - Historical stock performance data
   - Daily high/low/average prices and volume

## 🎨 New UI Features

### 1. Watchlist Page (`watchlist.php`)
- Add stocks to watchlist
- View current prices and changes
- Add notes for each stock
- Remove stocks from watchlist
- Real-time price tracking

### 2. Price Alerts Page (`alerts.php`)
- Create price alerts (Above/Below/Change %)
- View all active alerts
- Toggle alerts on/off
- See triggered alerts
- Delete alerts

### 3. Analytics Dashboard (`analytics.php`)
- Comprehensive portfolio analytics
- Top and worst performing stocks
- Transaction statistics
- Portfolio performance history (30 days)
- Gain/loss calculations
- Performance metrics

## 🔧 How to Use Enhanced Features

### Step 1: Import Enhanced Database

After importing the base `database.sql`, import the enhancements:

```bash
mysql -u root -p stock_trading_db < database_enhanced.sql
```

Or using MySQL Workbench:
1. Open MySQL Workbench
2. Connect to database
3. Go to Server → Data Import
4. Select `database_enhanced.sql`
5. Click "Start Import"

### Step 2: Update Stock Prices

To use price alerts and watchlist features, you need to populate stock prices:

```sql
-- Update stock prices (example)
UPDATE StockPrice 
SET current_price = 175.50, 
    previous_close = 174.20,
    day_change = 1.30,
    day_change_percent = 0.75
WHERE ticker_symbol = 'AAPL';
```

### Step 3: Use New Features

1. **Watchlist**: Go to Watchlist page, add stocks you want to monitor
2. **Alerts**: Create price alerts to get notified when stocks hit target prices
3. **Analytics**: View detailed portfolio analytics and performance metrics

## 📈 Advanced Features Usage

### Using Stored Procedures

```sql
-- Execute a trade
CALL sp_execute_trade(1, 1, 'AAPL', 1, 10, 175.50, 2, @result);
SELECT @result;

-- Get portfolio analytics
CALL sp_get_portfolio_analytics(1);

-- Calculate dividends
CALL sp_calculate_dividends(1, 1);
```

### Using Views

```sql
-- View portfolio summary
SELECT * FROM v_portfolio_summary WHERE user_id = 1;

-- View top performers
SELECT * FROM v_top_performers;

-- View user activity
SELECT * FROM v_user_activity WHERE user_id = 1;

-- View stock performance
SELECT * FROM v_stock_performance WHERE ticker_symbol = 'AAPL';
```

### Trigger Examples

Triggers run automatically:
- When you insert a transaction → balance updates automatically
- When stock price changes → alerts check automatically
- When you buy/sell → portfolio snapshot updates automatically

## 🎯 Feature Highlights

### Automatic Features (Triggers)
✅ Auto-update account balance
✅ Auto-check price alerts
✅ Auto-create portfolio snapshots
✅ Auto-validate transactions
✅ Auto-log audit trail
✅ Auto-update price history

### Manual Features (UI)
✅ Watchlist management
✅ Price alert creation
✅ Advanced analytics
✅ Portfolio tracking
✅ Transaction history
✅ Account management

## 🔐 Security Features

- All triggers validate data before operations
- Stored procedures use transactions for data integrity
- Audit log tracks all important changes
- Input validation on all forms
- SQL injection protection with prepared statements

## 📊 Performance Optimizations

- Indexes on frequently queried columns
- Views for complex queries
- Stored procedures for repeated operations
- Efficient trigger logic

## 🚀 Next Steps

1. Import `database_enhanced.sql`
2. Update stock prices in `StockPrice` table
3. Start using watchlist and alerts
4. Explore analytics dashboard
5. Monitor portfolio snapshots

Enjoy the enhanced trading system! 📈

