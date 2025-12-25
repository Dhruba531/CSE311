-- MySQL Compatible Final Schema

SET FOREIGN_KEY_CHECKS = 0;

-- Drop all tables to ensure clean schema update
DROP TABLE IF EXISTS Funds_Log, Alerts, Dividends, Audit_Log, Holdings, Watchlist, Price_History, Transactions, Traded_On, Stocks, Exchange, Business, Accounts, Friends_Of, Users, Region;

-- 1. Region
CREATE TABLE IF NOT EXISTS Region (
    region_id INT AUTO_INCREMENT PRIMARY KEY,
    region_name VARCHAR(50) NOT NULL UNIQUE
);

-- 2. Users
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    region_id INT,
    workplace VARCHAR(100),
    is_2fa_enabled TINYINT(1) DEFAULT 0,
    two_factor_code VARCHAR(6),
    two_factor_expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

-- 3. Friends_Of
CREATE TABLE IF NOT EXISTS Friends_Of (
    user_id INT,
    friend_id INT,
    status ENUM('PENDING', 'ACCEPTED', 'BLOCKED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, friend_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (friend_id) REFERENCES Users(user_id)
);

-- 4. Accounts
CREATE TABLE IF NOT EXISTS Accounts (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    buying_power DECIMAL(15, 2) DEFAULT 0.00,
    account_type ENUM('CASH', 'MARGIN') DEFAULT 'CASH',
    currency VARCHAR(3) DEFAULT 'USD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- 5. Business
CREATE TABLE IF NOT EXISTS Business (
    business_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    year_est INT,
    region_id INT,
    sector VARCHAR(50),
    industry VARCHAR(50),
    description TEXT,
    website VARCHAR(255),
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

-- 6. Exchange
CREATE TABLE IF NOT EXISTS Exchange (
    exchange_id INT AUTO_INCREMENT PRIMARY KEY,
    exchange_name VARCHAR(50) NOT NULL UNIQUE,
    short_code VARCHAR(10) NOT NULL, -- NYSE, NASDAQ
    region_id INT,
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

-- 7. Stocks (Replaces Instruments)
CREATE TABLE IF NOT EXISTS Stocks (
    ticker_symbol VARCHAR(10) PRIMARY KEY,
    business_id INT,
    exchange_id INT,
    stock_name VARCHAR(100) NOT NULL,
    current_price DECIMAL(10, 2) DEFAULT 0.00,
    prev_close DECIMAL(10, 2) DEFAULT 0.00,
    market_cap DECIMAL(20, 2),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES Business(business_id),
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id)
);

-- 8. Traded_On (Junction)
CREATE TABLE IF NOT EXISTS Traded_On (
    ticker_symbol VARCHAR(10),
    exchange_id INT,
    PRIMARY KEY (ticker_symbol, exchange_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol),
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id)
);

-- 9. Transactions (Enhanced)
CREATE TABLE IF NOT EXISTS Transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    transaction_type ENUM('BUY', 'SELL', 'DEPOSIT', 'WITHDRAW') NOT NULL, 
    order_type ENUM('MARKET', 'LIMIT', 'STOP') DEFAULT 'MARKET',
    quantity DECIMAL(10, 4) DEFAULT 0,
    cost DECIMAL(15, 2) NOT NULL, -- execution price or total amount
    status ENUM('PENDING', 'COMPLETED', 'FAILED', 'CANCELLED') DEFAULT 'PENDING',
    date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (account_id) REFERENCES Accounts(account_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 10. Price_History (Renamed from Market_Data)
CREATE TABLE IF NOT EXISTS Price_History (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    ticker_symbol VARCHAR(10) NOT NULL,
    open DECIMAL(10, 2),
    high DECIMAL(10, 2),
    low DECIMAL(10, 2),
    close DECIMAL(10, 2),
    volume BIGINT,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 11. Watchlist (Simple)
CREATE TABLE IF NOT EXISTS Watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, ticker_symbol),
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 12. Holdings (New - Replaces Positions)
CREATE TABLE IF NOT EXISTS Holdings (
    holding_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    quantity DECIMAL(10, 4) NOT NULL DEFAULT 0,
    avg_price DECIMAL(10, 2) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(user_id, ticker_symbol),
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 13. Audit_Log
CREATE TABLE IF NOT EXISTS Audit_Log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action TEXT NOT NULL,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- 14. Dividends
CREATE TABLE IF NOT EXISTS Dividends (
    dividend_id INT AUTO_INCREMENT PRIMARY KEY,
    ticker_symbol VARCHAR(10) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    ex_date DATE NOT NULL,
    payment_date DATE,
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 15. Alerts
CREATE TABLE IF NOT EXISTS Alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    target_price DECIMAL(10, 2) NOT NULL,
    condition_type ENUM('ABOVE', 'BELOW') DEFAULT 'ABOVE',
    status ENUM('ACTIVE', 'TRIGGERED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 16. Funds_Log
CREATE TABLE IF NOT EXISTS Funds_Log (
    fund_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    transaction_type ENUM('DEPOSIT', 'WITHDRAW') NOT NULL,
    status ENUM('PENDING', 'COMPLETED', 'FAILED') DEFAULT 'COMPLETED',
    date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

SET FOREIGN_KEY_CHECKS = 1;
