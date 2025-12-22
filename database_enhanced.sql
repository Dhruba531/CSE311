-- database_enhanced.sql

CREATE TABLE IF NOT EXISTS Region (
    region_id INT AUTO_INCREMENT PRIMARY KEY,
    region_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    workplace VARCHAR(100),
    region_id INT,
    region_id INT,
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

CREATE TABLE IF NOT EXISTS Account (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

CREATE TABLE IF NOT EXISTS Business (
    business_id INT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(100),
    region_id INT,
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

CREATE TABLE IF NOT EXISTS Stock (
    ticker_symbol VARCHAR(10) PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    business_id INT,
    FOREIGN KEY (business_id) REFERENCES Business(business_id)
);

CREATE TABLE IF NOT EXISTS StockPrice (
    ticker_symbol VARCHAR(10) PRIMARY KEY,
    current_price DECIMAL(10, 2) NOT NULL,
    previous_close DECIMAL(10, 2),
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol)
);

CREATE TABLE IF NOT EXISTS Exchange (
    exchange_id INT AUTO_INCREMENT PRIMARY KEY,
    exchange_name VARCHAR(100) NOT NULL,
    region_id INT,
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

CREATE TABLE IF NOT EXISTS TransactionRecord (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    is_buy TINYINT(1) NOT NULL,
    cost_per_share DECIMAL(10, 2) NOT NULL,
    num_shares INT NOT NULL,
    exchange_id INT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (account_id) REFERENCES Account(account_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol),
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id)
);

CREATE TABLE IF NOT EXISTS Watchlist (
    watchlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol)
);

CREATE TABLE IF NOT EXISTS PriceAlert (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol)
);

CREATE TABLE IF NOT EXISTS FundTransaction (
    fund_transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    transaction_reference_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- Fullstack Enhancements: 2FA, Orders, History

-- 1. Add 2FA to Users (Check if exists first in production, but here we can just add if not present using a safe alter or just re-create if dev)
-- Since this is a setup script, we will just add the columns to the Users table definition above if it was a fresh install, 
-- but to be safe for an existing DB, we use ALTER statements here for existing tables.

-- ALTER TABLE Users ADD COLUMN two_factor_secret VARCHAR(255) NULL;
-- ALTER TABLE Users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0;

-- 2. Orders Table for Limit/Stop functionality
CREATE TABLE IF NOT EXISTS Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    order_type ENUM('limit_buy', 'limit_sell', 'stop_loss', 'take_profit') NOT NULL,
    target_price DECIMAL(10, 2) NOT NULL,
    num_shares INT NOT NULL,
    status ENUM('pending', 'filled', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (account_id) REFERENCES Account(account_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol)
);

-- 3. Portfolio Snapshot for History Graphs
CREATE TABLE IF NOT EXISTS PortfolioSnapshot (
    snapshot_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_value DECIMAL(15, 2) NOT NULL,
    cash_balance DECIMAL(15, 2) NOT NULL,
    invested_value DECIMAL(15, 2) NOT NULL,
    snapshot_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);
