-- Final Schema: CSE311 Requirements + Enhanced Realism

-- 1. Regions
CREATE TABLE IF NOT EXISTS Region (
    region_id INTEGER PRIMARY KEY AUTOINCREMENT,
    region_name TEXT NOT NULL
);

-- 2. Users
CREATE TABLE IF NOT EXISTS Users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    workplace TEXT,
    region_id INTEGER,
    username TEXT UNIQUE,             
    is_2fa_enabled BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

-- 3. Friends_Of (Recursive)
CREATE TABLE IF NOT EXISTS Friends_Of (
    user_id INTEGER,
    friend_id INTEGER,
    status TEXT DEFAULT 'PENDING',
    PRIMARY KEY (user_id, friend_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (friend_id) REFERENCES Users(user_id)
);

-- 4. Accounts
CREATE TABLE IF NOT EXISTS Accounts (
    account_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    currency TEXT DEFAULT 'USD',
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- 5. Business
CREATE TABLE IF NOT EXISTS Business (
    business_id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_name TEXT NOT NULL,
    year_est INTEGER,
    region_id INTEGER,
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

-- 6. Exchange
CREATE TABLE IF NOT EXISTS Exchange (
    exchange_id INTEGER PRIMARY KEY AUTOINCREMENT,
    exchange_name TEXT NOT NULL,
    short_code TEXT,
    region_id INTEGER,
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
);

-- 7. Stocks
CREATE TABLE IF NOT EXISTS Stocks (
    ticker_symbol TEXT PRIMARY KEY,
    business_id INTEGER,
    stock_name TEXT NOT NULL,
    current_price DECIMAL(10, 2) DEFAULT 0.00,
    exchange_id INTEGER,
    FOREIGN KEY (business_id) REFERENCES Business(business_id)
);

-- 8. Traded_On
CREATE TABLE IF NOT EXISTS Traded_On (
    ticker_symbol TEXT,
    exchange_id INTEGER,
    PRIMARY KEY (ticker_symbol, exchange_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol),
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id)
);

-- 9. Transactions (Enhanced)
CREATE TABLE IF NOT EXISTS Transactions (
    record_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    ticker_symbol TEXT NOT NULL,
    
    -- "Side" (Buy/Sell)
    transaction_type TEXT NOT NULL, 
    
    -- "Order_Type" (Market, Limit, Stop-Loss)
    order_type TEXT DEFAULT 'MARKET',
    
    -- "Status" (Pending, Completed, Failed, Cancelled)
    status TEXT DEFAULT 'COMPLETED',
    
    cost DECIMAL(10, 2) NOT NULL,
    quantity INTEGER NOT NULL,
    exchange_id INTEGER,
    date_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    account_id INTEGER,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol),
    FOREIGN KEY (exchange_id) REFERENCES Exchange(exchange_id),
    FOREIGN KEY (account_id) REFERENCES Accounts(account_id)
);

-- 10. Price_History (was Market_Data)
CREATE TABLE IF NOT EXISTS Price_History (
    history_id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticker_symbol TEXT NOT NULL, 
    timestamp DATETIME NOT NULL,
    open DECIMAL(15, 4) NOT NULL,
    high DECIMAL(15, 4) NOT NULL,
    low DECIMAL(15, 4) NOT NULL,
    close DECIMAL(15, 4) NOT NULL,
    volume INTEGER DEFAULT 0,
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 11. Watchlist (Enhanced)
CREATE TABLE IF NOT EXISTS Watchlist (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    ticker_symbol TEXT NOT NULL,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol),
    UNIQUE(user_id, ticker_symbol)
);

-- 12. Holdings (Portfolio Summary)
CREATE TABLE IF NOT EXISTS Holdings (
    holding_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    ticker_symbol TEXT NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 0,
    avg_price DECIMAL(15, 4) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol),
    UNIQUE(user_id, ticker_symbol)
);

-- 13. Audit_Log
CREATE TABLE IF NOT EXISTS Audit_Log (
    log_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    ip_address TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- 14. Dividends
CREATE TABLE IF NOT EXISTS Dividends (
    dividend_id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticker_symbol TEXT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    ex_date DATE NOT NULL,
    payment_date DATE,
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 15. Alerts
CREATE TABLE IF NOT EXISTS Alerts (
    alert_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    ticker_symbol TEXT NOT NULL,
    target_price DECIMAL(10, 2) NOT NULL,
    condition TEXT DEFAULT 'ABOVE', -- 'ABOVE' or 'BELOW'
    status TEXT DEFAULT 'ACTIVE', -- 'ACTIVE', 'TRIGGERED'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (ticker_symbol) REFERENCES Stocks(ticker_symbol)
);

-- 16. Funds_Log
CREATE TABLE IF NOT EXISTS Funds_Log (
    fund_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    transaction_type TEXT NOT NULL, -- 'DEPOSIT', 'WITHDRAW'
    status TEXT DEFAULT 'COMPLETED',
    date_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);
