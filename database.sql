-- database.sql
CREATE DATABASE IF NOT EXISTS stock_trading_db;
USE stock_trading_db;

-- Region Table
CREATE TABLE Region (
    region_id INT AUTO_INCREMENT PRIMARY KEY,
    region_name VARCHAR(50) NOT NULL UNIQUE
);

-- Business (Company) Table
CREATE TABLE Business (
    business_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL UNIQUE,
    year_established YEAR,
    region_id INT,
    FOREIGN KEY (region_id) REFERENCES Region(region_id) ON DELETE SET NULL
);

-- Exchange Table
CREATE TABLE Exchange (
    exchange_id INT AUTO_INCREMENT PRIMARY KEY,
    exchange_name VARCHAR(100) NOT NULL,
    short_code VARCHAR(10) NOT NULL UNIQUE,
    region_id INT,
    FOREIGN KEY (region_id) REFERENCES Region(region_id) ON DELETE SET NULL
);

-- Stock Table
CREATE TABLE Stock (
    ticker_symbol VARCHAR(10) PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    business_id INT,
    FOREIGN KEY (business_id) REFERENCES Business(business_id) ON DELETE CASCADE
);

-- Junction: Stock listed on multiple Exchanges
CREATE TABLE traded_on (
    ticker_symbol VARCHAR(10),
    exchange_id INT,
    PRIMARY KEY (ticker_symbol, exchange_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol) ON DELETE CASCADE,
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id) ON DELETE CASCADE
);

-- Users Table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    workplace VARCHAR(100),
    region_id INT,
    FOREIGN KEY (region_id) REFERENCES Region(region_id) ON DELETE SET NULL
);

-- Account Table (One user → Many accounts)
CREATE TABLE Account (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00 CHECK (balance >= 0),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Friends Table (Self-referencing)
CREATE TABLE friends_of (
    user_id INT,
    friend_id INT,
    friend_name VARCHAR(100),
    PRIMARY KEY (user_id, friend_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    CHECK (user_id != friend_id)
);

-- Transaction History
CREATE TABLE TransactionRecord (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    is_buy BOOLEAN NOT NULL, -- TRUE = Buy, FALSE = Sell
    cost_per_share DECIMAL(10,2) NOT NULL CHECK (cost_per_share > 0),
    num_shares INT NOT NULL CHECK (num_shares > 0),
    exchange_id INT NOT NULL,
    transaction_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES Account(account_id) ON DELETE CASCADE,
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol),
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id)
);

-- Sample Data
INSERT INTO Region (region_name) VALUES ('Asia'), ('North America'), ('Europe');

INSERT INTO Business (company_name, year_established, region_id) VALUES
('Apple Inc.', 1976, 2),
('Tata Motors', 1945, 1),
('Samsung Electronics', 1969, 1);

INSERT INTO Exchange (exchange_name, short_code, region_id) VALUES
('Dhaka Stock Exchange', 'DSE', 1),
('New York Stock Exchange', 'NYSE', 2),
('NASDAQ', 'NASDAQ', 2);

INSERT INTO Stock (ticker_symbol, company_name, business_id) VALUES
('AAPL', 'Apple Inc.', 1),
('TATAMOTORS', 'Tata Motors', 2),
('005930.KS', 'Samsung Electronics', 3);

INSERT INTO traded_on VALUES
('AAPL', 2), ('AAPL', 3),
('TATAMOTORS', 1);

-- Users (password = password123 hashed with password_hash())
INSERT INTO Users (full_name, password_hash, workplace, region_id) VALUES
('Foysal Mahamud', '$2y$10$z8z8z8z8z8z8z8z8z8z8zO1234567890abcdef', 'NSU', 1),
('Dhruba Saha', '$2y$10$z8z8z8z8z8z8z8z8z8z8zO1234567890abcdef', 'NSU', 1);

INSERT INTO Account (user_id, balance) VALUES (1, 50000.00), (2, 30000.00);