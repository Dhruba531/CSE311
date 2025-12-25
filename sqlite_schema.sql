-- SQLite Compatible Schema

-- Users Table
CREATE TABLE IF NOT EXISTS Users (
  user_id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  full_name TEXT NOT NULL,
  kyc_status TEXT DEFAULT 'PENDING',
  is_2fa_enabled BOOLEAN DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Accounts Table
CREATE TABLE IF NOT EXISTS Accounts (
  account_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  currency TEXT DEFAULT 'USD',
  balance DECIMAL(15, 2) DEFAULT 0.00,
  margin_used DECIMAL(15, 2) DEFAULT 0.00,
  buying_power DECIMAL(15, 2) DEFAULT 0.00,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Exchanges Table
CREATE TABLE IF NOT EXISTS Exchanges (
  exchange_id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT UNIQUE NOT NULL,
  timezone TEXT DEFAULT 'America/New_York',
  opening_time TEXT DEFAULT '09:30:00',
  closing_time TEXT DEFAULT '16:00:00'
);

-- Instruments Table
CREATE TABLE IF NOT EXISTS Instruments (
  instrument_id INTEGER PRIMARY KEY AUTOINCREMENT,
  ticker_symbol TEXT UNIQUE NOT NULL,
  name TEXT NOT NULL,
  exchange_id INTEGER NOT NULL,
  asset_type TEXT DEFAULT 'STOCK',
  sector TEXT,
  current_price DECIMAL(15, 4) DEFAULT 0.0000,
  FOREIGN KEY (exchange_id) REFERENCES Exchanges(exchange_id)
);

-- Orders Table
CREATE TABLE IF NOT EXISTS Orders (
  order_id INTEGER PRIMARY KEY AUTOINCREMENT,
  account_id INTEGER NOT NULL,
  instrument_id INTEGER NOT NULL,
  order_type TEXT NOT NULL,
  side TEXT NOT NULL,
  quantity DECIMAL(15, 4) NOT NULL,
  price_limit DECIMAL(15, 4) NULL,
  status TEXT DEFAULT 'PENDING',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES Accounts(account_id),
  FOREIGN KEY (instrument_id) REFERENCES Instruments(instrument_id)
);

-- Trades Table
CREATE TABLE IF NOT EXISTS Trades (
  trade_id INTEGER PRIMARY KEY AUTOINCREMENT,
  order_id INTEGER NOT NULL,
  execution_price DECIMAL(15, 4) NOT NULL,
  quantity_filled DECIMAL(15, 4) NOT NULL,
  commission_fee DECIMAL(10, 2) DEFAULT 0.00,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES Orders(order_id)
);

-- Positions Table
CREATE TABLE IF NOT EXISTS Positions (
  position_id INTEGER PRIMARY KEY AUTOINCREMENT,
  account_id INTEGER NOT NULL,
  instrument_id INTEGER NOT NULL,
  average_entry_price DECIMAL(15, 4) NOT NULL,
  total_quantity DECIMAL(15, 4) NOT NULL,
  last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES Accounts(account_id),
  FOREIGN KEY (instrument_id) REFERENCES Instruments(instrument_id),
  UNIQUE(account_id, instrument_id)
);

-- Market Data Table (Historical)
CREATE TABLE IF NOT EXISTS Market_Data (
  data_id INTEGER PRIMARY KEY AUTOINCREMENT,
  instrument_id INTEGER NOT NULL,
  timestamp DATETIME NOT NULL,
  open DECIMAL(15, 4) NOT NULL,
  high DECIMAL(15, 4) NOT NULL,
  low DECIMAL(15, 4) NOT NULL,
  close DECIMAL(15, 4) NOT NULL,
  volume INTEGER DEFAULT 0,
  FOREIGN KEY (instrument_id) REFERENCES Instruments(instrument_id)
);

-- Realtime Quotes Table
CREATE TABLE IF NOT EXISTS Realtime_Quotes (
  quote_id INTEGER PRIMARY KEY AUTOINCREMENT,
  instrument_id INTEGER NOT NULL,
  price DECIMAL(15, 4) NOT NULL,
  bid DECIMAL(15, 4) NULL,
  ask DECIMAL(15, 4) NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (instrument_id) REFERENCES Instruments(instrument_id)
);

-- Alerts Table
CREATE TABLE IF NOT EXISTS Alerts (
  alert_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  instrument_id INTEGER NOT NULL,
  target_price DECIMAL(15, 4) NOT NULL,
  condition_type TEXT NOT NULL, -- ABOVE / BELOW
  is_active BOOLEAN DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES Users(user_id),
  FOREIGN KEY (instrument_id) REFERENCES Instruments(instrument_id)
);

-- Watchlists Table
CREATE TABLE IF NOT EXISTS Watchlists (
  watchlist_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  name TEXT DEFAULT 'My Watchlist',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

CREATE TABLE IF NOT EXISTS Watchlist_Items (
  item_id INTEGER PRIMARY KEY AUTOINCREMENT,
  watchlist_id INTEGER NOT NULL,
  instrument_id INTEGER NOT NULL,
  added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (watchlist_id) REFERENCES Watchlists(watchlist_id),
  FOREIGN KEY (instrument_id) REFERENCES Instruments(instrument_id)
);

-- Seed Initial Exchange
INSERT OR IGNORE INTO Exchanges (name, timezone) VALUES ('NASDAQ', 'America/New_York');
INSERT OR IGNORE INTO Exchanges (name, timezone) VALUES ('NYSE', 'America/New_York');
