-- Enhanced Stock Trading Database with Advanced Features
-- Run this AFTER the base database.sql is imported

USE stock_trading_db;

-- ============================================
-- NEW TABLES
-- ============================================

-- Stock Price History (for tracking price changes over time)
CREATE TABLE IF NOT EXISTS StockPriceHistory (
    price_id INT AUTO_INCREMENT PRIMARY KEY,
    ticker_symbol VARCHAR(10) NOT NULL,
    exchange_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price > 0),
    volume BIGINT DEFAULT 0,
    price_date DATE NOT NULL,
    price_time TIME,
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol) ON DELETE CASCADE,
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id) ON DELETE CASCADE,
    INDEX idx_ticker_date (ticker_symbol, price_date),
    INDEX idx_date (price_date)
);

-- Current Stock Prices (latest prices)
CREATE TABLE IF NOT EXISTS StockPrice (
    ticker_symbol VARCHAR(10) PRIMARY KEY,
    exchange_id INT NOT NULL,
    current_price DECIMAL(10,2) NOT NULL CHECK (current_price > 0),
    previous_close DECIMAL(10,2),
    day_change DECIMAL(10,2) DEFAULT 0,
    day_change_percent DECIMAL(5,2) DEFAULT 0,
    volume BIGINT DEFAULT 0,
    high_52w DECIMAL(10,2),
    low_52w DECIMAL(10,2),
    market_cap BIGINT,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol) ON DELETE CASCADE,
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id) ON DELETE CASCADE
);

-- Watchlist (users can watch stocks)
CREATE TABLE IF NOT EXISTS Watchlist (
    watchlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    added_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol) ON DELETE CASCADE,
    UNIQUE KEY unique_watch (user_id, ticker_symbol),
    INDEX idx_user (user_id)
);

-- Price Alerts (notify users when price reaches target)
CREATE TABLE IF NOT EXISTS PriceAlert (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    alert_type ENUM('ABOVE', 'BELOW', 'CHANGE_PERCENT') NOT NULL,
    target_price DECIMAL(10,2),
    target_percent DECIMAL(5,2),
    is_active BOOLEAN DEFAULT TRUE,
    is_triggered BOOLEAN DEFAULT FALSE,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    triggered_date DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_ticker (ticker_symbol)
);

-- Order Types (Limit Orders, Stop-Loss Orders)
CREATE TABLE IF NOT EXISTS OrderType (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    order_type ENUM('MARKET', 'LIMIT', 'STOP_LOSS', 'STOP_LIMIT') NOT NULL,
    action_type ENUM('BUY', 'SELL') NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    limit_price DECIMAL(10,2),
    stop_price DECIMAL(10,2),
    status ENUM('PENDING', 'EXECUTED', 'CANCELLED', 'EXPIRED') DEFAULT 'PENDING',
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    executed_date DATETIME NULL,
    expiry_date DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES Account(account_id) ON DELETE CASCADE,
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_ticker (ticker_symbol)
);

-- Dividends (track dividend payments)
CREATE TABLE IF NOT EXISTS Dividend (
    dividend_id INT AUTO_INCREMENT PRIMARY KEY,
    ticker_symbol VARCHAR(10) NOT NULL,
    dividend_amount DECIMAL(10,4) NOT NULL CHECK (dividend_amount >= 0),
    ex_dividend_date DATE NOT NULL,
    payment_date DATE,
    record_date DATE,
    declared_date DATE,
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol) ON DELETE CASCADE,
    INDEX idx_ticker (ticker_symbol),
    INDEX idx_ex_date (ex_dividend_date)
);

-- User Dividends (dividends received by users)
CREATE TABLE IF NOT EXISTS UserDividend (
    user_dividend_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dividend_id INT NOT NULL,
    shares_owned INT NOT NULL,
    dividend_amount DECIMAL(10,2) NOT NULL,
    payment_date DATE,
    is_paid BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (dividend_id) REFERENCES Dividend(dividend_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_paid (is_paid)
);

-- Audit Log (track all important changes)
CREATE TABLE IF NOT EXISTS AuditLog (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action_type),
    INDEX idx_created (created_at)
);

-- Notifications (user notifications)
CREATE TABLE IF NOT EXISTS Notification (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
);

-- Portfolio Performance (daily snapshot)
CREATE TABLE IF NOT EXISTS PortfolioSnapshot (
    snapshot_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    snapshot_date DATE NOT NULL,
    total_value DECIMAL(15,2) NOT NULL,
    total_cost DECIMAL(15,2) NOT NULL,
    total_gain_loss DECIMAL(15,2) NOT NULL,
    gain_loss_percent DECIMAL(5,2) NOT NULL,
    cash_balance DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, snapshot_date),
    INDEX idx_user_date (user_id, snapshot_date)
);

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger 1: Auto-update account balance after transaction
DELIMITER //
DROP TRIGGER IF EXISTS trg_after_transaction_insert//
CREATE TRIGGER trg_after_transaction_insert
AFTER INSERT ON TransactionRecord
FOR EACH ROW
BEGIN
    IF NEW.is_buy = 1 THEN
        -- Deduct from balance for buy
        UPDATE Account 
        SET balance = balance - (NEW.num_shares * NEW.cost_per_share)
        WHERE account_id = NEW.account_id;
    ELSE
        -- Add to balance for sell
        UPDATE Account 
        SET balance = balance + (NEW.num_shares * NEW.cost_per_share)
        WHERE account_id = NEW.account_id;
    END IF;
    
    -- Log transaction in audit log
    INSERT INTO AuditLog (user_id, action_type, table_name, record_id, new_values)
    VALUES (NEW.user_id, 'TRANSACTION', 'TransactionRecord', NEW.record_id,
            JSON_OBJECT('ticker', NEW.ticker_symbol, 'shares', NEW.num_shares, 
                       'price', NEW.cost_per_share, 'type', IF(NEW.is_buy, 'BUY', 'SELL')));
END//
DELIMITER ;

-- Trigger 2: Auto-create notification for price alerts
 DELIMITER //
DROP TRIGGER IF EXISTS trg_price_alert_check//
CREATE TRIGGER trg_price_alert_check
AFTER UPDATE ON StockPrice
FOR EACH ROW
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_alert_id INT;
    DECLARE v_user_id INT;
    DECLARE v_alert_type VARCHAR(10);
    DECLARE v_target_price DECIMAL(10,2);
    
    DECLARE alert_cursor CURSOR FOR
        SELECT alert_id, user_id, alert_type, target_price
        FROM PriceAlert
        WHERE ticker_symbol = NEW.ticker_symbol
        AND is_active = TRUE
        AND is_triggered = FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN alert_cursor;
    alert_loop: LOOP
        FETCH alert_cursor INTO v_alert_id, v_user_id, v_alert_type, v_target_price;
        IF done THEN
            LEAVE alert_loop;
        END IF;
        
        -- Check if alert condition is met
        IF (v_alert_type = 'ABOVE' AND NEW.current_price >= v_target_price) OR
           (v_alert_type = 'BELOW' AND NEW.current_price <= v_target_price) THEN
            
            -- Mark alert as triggered
            UPDATE PriceAlert
            SET is_triggered = TRUE, triggered_date = NOW()
            WHERE alert_id = v_alert_id;
            
            -- Create notification
            INSERT INTO Notification (user_id, notification_type, title, message, related_id)
            VALUES (v_user_id, 'PRICE_ALERT', 
                   CONCAT('Price Alert: ', NEW.ticker_symbol),
                   CONCAT('Stock ', NEW.ticker_symbol, ' reached $', NEW.current_price),
                   v_alert_id);
        END IF;
    END LOOP;
    CLOSE alert_cursor;
END//
DELIMITER ;

-- Trigger 3: Auto-update portfolio snapshot on transaction
DELIMITER //
DROP TRIGGER IF EXISTS trg_update_portfolio_snapshot//
CREATE TRIGGER trg_update_portfolio_snapshot
AFTER INSERT ON TransactionRecord
FOR EACH ROW
BEGIN
    DECLARE v_total_value DECIMAL(15,2);
    DECLARE v_total_cost DECIMAL(15,2);
    DECLARE v_cash_balance DECIMAL(15,2);
    
    -- Calculate portfolio value
    SELECT COALESCE(SUM(h.shares_held * sp.current_price), 0),
           COALESCE(SUM(h.shares_held * h.avg_price), 0)
    INTO v_total_value, v_total_cost
    FROM (
        SELECT ticker_symbol,
               SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END) as shares_held,
               AVG(CASE WHEN is_buy THEN cost_per_share END) as avg_price
        FROM TransactionRecord
        WHERE user_id = NEW.user_id
        GROUP BY ticker_symbol
        HAVING shares_held > 0
    ) h
    LEFT JOIN StockPrice sp ON h.ticker_symbol = sp.ticker_symbol;
    
    -- Get cash balance
    SELECT COALESCE(SUM(balance), 0) INTO v_cash_balance
    FROM Account
    WHERE user_id = NEW.user_id;
    
    -- Insert or update snapshot
    INSERT INTO PortfolioSnapshot (user_id, snapshot_date, total_value, total_cost, 
                                   total_gain_loss, gain_loss_percent, cash_balance)
    VALUES (NEW.user_id, CURDATE(), 
           v_total_value, v_total_cost,
           v_total_value - v_total_cost,
           CASE WHEN v_total_cost > 0 THEN ((v_total_value - v_total_cost) / v_total_cost * 100) ELSE 0 END,
           v_cash_balance)
    ON DUPLICATE KEY UPDATE
        total_value = v_total_value,
        total_cost = v_total_cost,
        total_gain_loss = v_total_value - v_total_cost,
        gain_loss_percent = CASE WHEN v_total_cost > 0 THEN ((v_total_value - v_total_cost) / v_total_cost * 100) ELSE 0 END,
        cash_balance = v_cash_balance;
END//
DELIMITER ;

-- Trigger 4: Prevent negative balance
DELIMITER //
DROP TRIGGER IF EXISTS trg_check_balance_before_transaction//
CREATE TRIGGER trg_check_balance_before_transaction
BEFORE INSERT ON TransactionRecord
FOR EACH ROW
BEGIN
    DECLARE v_balance DECIMAL(15,2);
    DECLARE v_required DECIMAL(15,2);
    
    IF NEW.is_buy = 1 THEN
        SELECT balance INTO v_balance
        FROM Account
        WHERE account_id = NEW.account_id;
        
        SET v_required = NEW.num_shares * NEW.cost_per_share;
        
        IF v_balance < v_required THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Insufficient balance for this transaction';
        END IF;
    END IF;
END//
DELIMITER ;

-- Trigger 5: Check shares before sell
DELIMITER //
DROP TRIGGER IF EXISTS trg_check_shares_before_sell//
CREATE TRIGGER trg_check_shares_before_sell
BEFORE INSERT ON TransactionRecord
FOR EACH ROW
BEGIN
    DECLARE v_shares_held INT;
    
    IF NEW.is_buy = 0 THEN
        SELECT COALESCE(SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END), 0)
        INTO v_shares_held
        FROM TransactionRecord
        WHERE user_id = NEW.user_id
        AND ticker_symbol = NEW.ticker_symbol;
        
        IF v_shares_held < NEW.num_shares THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Insufficient shares to sell';
        END IF;
    END IF;
END//
DELIMITER ;

-- Trigger 6: Auto-update stock price history
DELIMITER //
DROP TRIGGER IF EXISTS trg_update_price_history//
CREATE TRIGGER trg_update_price_history
AFTER UPDATE ON StockPrice
FOR EACH ROW
BEGIN
    IF NEW.current_price != OLD.current_price THEN
        INSERT INTO StockPriceHistory (ticker_symbol, exchange_id, price, volume, price_date, price_time)
        VALUES (NEW.ticker_symbol, NEW.exchange_id, NEW.current_price, NEW.volume, CURDATE(), CURTIME())
        ON DUPLICATE KEY UPDATE
            price = NEW.current_price,
            volume = NEW.volume,
            price_time = CURTIME();
    END IF;
END//
DELIMITER ;

-- ============================================
-- STORED PROCEDURES
-- ============================================

-- Procedure 1: Execute a trade transaction
DELIMITER //
DROP PROCEDURE IF EXISTS sp_execute_trade//
CREATE PROCEDURE sp_execute_trade(
    IN p_user_id INT,
    IN p_account_id INT,
    IN p_ticker_symbol VARCHAR(10),
    IN p_is_buy BOOLEAN,
    IN p_num_shares INT,
    IN p_cost_per_share DECIMAL(10,2),
    IN p_exchange_id INT,
    OUT p_result VARCHAR(255)
)
BEGIN
    DECLARE v_balance DECIMAL(15,2);
    DECLARE v_shares_held INT;
    DECLARE v_total_cost DECIMAL(15,2);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_result = 'Transaction failed';
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    SET v_total_cost = p_num_shares * p_cost_per_share;
    
    IF p_is_buy = 1 THEN
        -- Check balance
        SELECT balance INTO v_balance
        FROM Account
        WHERE account_id = p_account_id AND user_id = p_user_id;
        
        IF v_balance < v_total_cost THEN
            SET p_result = 'Insufficient balance';
            ROLLBACK;
        ELSE
            -- Execute buy
            INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, 
                                          cost_per_share, num_shares, exchange_id)
            VALUES (p_user_id, p_account_id, p_ticker_symbol, 1, 
                   p_cost_per_share, p_num_shares, p_exchange_id);
            
            SET p_result = 'Buy order executed successfully';
            COMMIT;
        END IF;
    ELSE
        -- Check shares
        SELECT COALESCE(SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END), 0)
        INTO v_shares_held
        FROM TransactionRecord
        WHERE user_id = p_user_id AND ticker_symbol = p_ticker_symbol;
        
        IF v_shares_held < p_num_shares THEN
            SET p_result = 'Insufficient shares';
            ROLLBACK;
        ELSE
            -- Execute sell
            INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, 
                                          cost_per_share, num_shares, exchange_id)
            VALUES (p_user_id, p_account_id, p_ticker_symbol, 0, 
                   p_cost_per_share, p_num_shares, p_exchange_id);
            
            SET p_result = 'Sell order executed successfully';
            COMMIT;
        END IF;
    END IF;
END//
DELIMITER ;

-- Procedure 2: Get portfolio analytics
DELIMITER //
DROP PROCEDURE IF EXISTS sp_get_portfolio_analytics//
CREATE PROCEDURE sp_get_portfolio_analytics(IN p_user_id INT)
BEGIN
    SELECT 
        COUNT(DISTINCT h.ticker_symbol) as total_stocks,
        SUM(h.shares_held) as total_shares,
        SUM(h.shares_held * sp.current_price) as total_value,
        SUM(h.shares_held * h.avg_price) as total_cost,
        SUM(h.shares_held * sp.current_price) - SUM(h.shares_held * h.avg_price) as total_gain_loss,
        CASE WHEN SUM(h.shares_held * h.avg_price) > 0 
             THEN ((SUM(h.shares_held * sp.current_price) - SUM(h.shares_held * h.avg_price)) / 
                   SUM(h.shares_held * h.avg_price) * 100)
             ELSE 0 END as gain_loss_percent
    FROM (
        SELECT ticker_symbol,
               SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END) as shares_held,
               AVG(CASE WHEN is_buy THEN cost_per_share END) as avg_price
        FROM TransactionRecord
        WHERE user_id = p_user_id
        GROUP BY ticker_symbol
        HAVING shares_held > 0
    ) h
    LEFT JOIN StockPrice sp ON h.ticker_symbol = sp.ticker_symbol;
END//
DELIMITER ;

-- Procedure 3: Calculate dividend payments
DELIMITER //
DROP PROCEDURE IF EXISTS sp_calculate_dividends//
CREATE PROCEDURE sp_calculate_dividends(IN p_user_id INT, IN p_dividend_id INT)
BEGIN
    DECLARE v_ticker_symbol VARCHAR(10);
    DECLARE v_dividend_amount DECIMAL(10,4);
    DECLARE v_shares_held INT;
    
    SELECT ticker_symbol, dividend_amount
    INTO v_ticker_symbol, v_dividend_amount
    FROM Dividend
    WHERE dividend_id = p_dividend_id;
    
    SELECT COALESCE(SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END), 0)
    INTO v_shares_held
    FROM TransactionRecord
    WHERE user_id = p_user_id AND ticker_symbol = v_ticker_symbol;
    
    IF v_shares_held > 0 THEN
        INSERT INTO UserDividend (user_id, dividend_id, shares_owned, dividend_amount)
        VALUES (p_user_id, p_dividend_id, v_shares_held, v_shares_held * v_dividend_amount)
        ON DUPLICATE KEY UPDATE
            shares_owned = v_shares_held,
            dividend_amount = v_shares_held * v_dividend_amount;
    END IF;
END//
DELIMITER ;

-- ============================================
-- VIEWS
-- ============================================

-- View 1: Portfolio Summary
CREATE OR REPLACE VIEW v_portfolio_summary AS
SELECT 
    u.user_id,
    u.full_name,
    COUNT(DISTINCT h.ticker_symbol) as stocks_owned,
    SUM(h.shares_held) as total_shares,
    SUM(h.shares_held * COALESCE(sp.current_price, 0)) as portfolio_value,
    SUM(h.shares_held * h.avg_price) as total_cost,
    SUM(h.shares_held * COALESCE(sp.current_price, 0)) - SUM(h.shares_held * h.avg_price) as gain_loss,
    COALESCE(SUM(a.balance), 0) as cash_balance
FROM Users u
LEFT JOIN (
    SELECT user_id, ticker_symbol,
           SUM(CASE WHEN is_buy THEN num_shares ELSE -num_shares END) as shares_held,
           AVG(CASE WHEN is_buy THEN cost_per_share END) as avg_price
    FROM TransactionRecord
    GROUP BY user_id, ticker_symbol
    HAVING shares_held > 0
) h ON u.user_id = h.user_id
LEFT JOIN StockPrice sp ON h.ticker_symbol = sp.ticker_symbol
LEFT JOIN Account a ON u.user_id = a.user_id
GROUP BY u.user_id, u.full_name;

-- View 2: Top Performers
CREATE OR REPLACE VIEW v_top_performers AS
SELECT 
    s.ticker_symbol,
    s.company_name,
    sp.current_price,
    sp.previous_close,
    sp.day_change,
    sp.day_change_percent,
    sp.volume,
    (sp.current_price - sp.previous_close) / sp.previous_close * 100 as percent_change
FROM Stock s
JOIN StockPrice sp ON s.ticker_symbol = sp.ticker_symbol
ORDER BY sp.day_change_percent DESC
LIMIT 10;

-- View 3: User Activity Summary
CREATE OR REPLACE VIEW v_user_activity AS
SELECT 
    u.user_id,
    u.full_name,
    COUNT(DISTINCT tr.record_id) as total_transactions,
    SUM(CASE WHEN tr.is_buy = 1 THEN tr.num_shares * tr.cost_per_share ELSE 0 END) as total_bought,
    SUM(CASE WHEN tr.is_buy = 0 THEN tr.num_shares * tr.cost_per_share ELSE 0 END) as total_sold,
    COUNT(DISTINCT w.ticker_symbol) as watchlist_count,
    COUNT(DISTINCT pa.alert_id) as active_alerts
FROM Users u
LEFT JOIN TransactionRecord tr ON u.user_id = tr.user_id
LEFT JOIN Watchlist w ON u.user_id = w.user_id
LEFT JOIN PriceAlert pa ON u.user_id = pa.user_id AND pa.is_active = TRUE
GROUP BY u.user_id, u.full_name;

-- View 4: Stock Performance History
CREATE OR REPLACE VIEW v_stock_performance AS
SELECT 
    sph.ticker_symbol,
    s.company_name,
    DATE(sph.price_date) as date,
    MIN(sph.price) as low_price,
    MAX(sph.price) as high_price,
    AVG(sph.price) as avg_price,
    SUM(sph.volume) as total_volume
FROM StockPriceHistory sph
JOIN Stock s ON sph.ticker_symbol = s.ticker_symbol
GROUP BY sph.ticker_symbol, DATE(sph.price_date)
ORDER BY sph.ticker_symbol, DATE(sph.price_date) DESC;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Insert sample stock prices (using ON DUPLICATE KEY UPDATE to allow re-running)
INSERT INTO StockPrice (ticker_symbol, exchange_id, current_price, previous_close, day_change, day_change_percent, volume, high_52w, low_52w)
VALUES
('AAPL', 2, 175.50, 174.20, 1.30, 0.75, 50000000, 198.00, 150.00),
('TATAMOTORS', 1, 450.75, 445.20, 5.55, 1.25, 2000000, 500.00, 300.00),
('005930.KS', 1, 65000.00, 64500.00, 500.00, 0.78, 1000000, 75000.00, 50000.00)
ON DUPLICATE KEY UPDATE
    exchange_id = VALUES(exchange_id),
    current_price = VALUES(current_price),
    previous_close = VALUES(previous_close),
    day_change = VALUES(day_change),
    day_change_percent = VALUES(day_change_percent),
    volume = VALUES(volume),
    high_52w = VALUES(high_52w),
    low_52w = VALUES(low_52w),
    last_updated = CURRENT_TIMESTAMP;

-- Insert sample watchlist (using INSERT IGNORE to allow re-running)
INSERT IGNORE INTO Watchlist (user_id, ticker_symbol, notes)
VALUES
(1, 'AAPL', 'Tech giant, watching for entry point'),
(1, 'TATAMOTORS', 'Auto sector recovery');

-- Insert sample price alerts (using INSERT IGNORE to allow re-running)
INSERT IGNORE INTO PriceAlert (user_id, ticker_symbol, alert_type, target_price, is_active)
VALUES
(1, 'AAPL', 'BELOW', 170.00, TRUE),
(2, 'AAPL', 'ABOVE', 180.00, TRUE);

-- Insert sample dividends (using INSERT IGNORE to allow re-running)
INSERT IGNORE INTO Dividend (ticker_symbol, dividend_amount, ex_dividend_date, payment_date, declared_date)
VALUES
('AAPL', 0.24, '2024-02-09', '2024-02-15', '2024-01-25'),
('TATAMOTORS', 2.50, '2024-03-15', '2024-03-25', '2024-02-28');

