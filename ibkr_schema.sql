-- ibkr_schema.sql
-- Designed for High Scalability & Financial Accuracy

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ==========================================
-- MODULE A: User & Account Management
-- ==========================================

CREATE TABLE IF NOT EXISTS `Users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `kyc_status` ENUM('PENDING', 'VERIFIED', 'REJECTED') DEFAULT 'PENDING',
  `is_2fa_enabled` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `Accounts` (
  `account_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `currency` CHAR(3) DEFAULT 'USD',
  `balance` DECIMAL(15, 2) DEFAULT 0.00,
  `margin_used` DECIMAL(15, 2) DEFAULT 0.00,
  `buying_power` DECIMAL(15, 2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
);

-- ==========================================
-- MODULE B: Market Data (The Catalog)
-- ==========================================

CREATE TABLE IF NOT EXISTS `Exchanges` (
  `exchange_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) UNIQUE NOT NULL, -- e.g., NYSE, NASDAQ
  `timezone` VARCHAR(50) DEFAULT 'America/New_York',
  `opening_time` TIME DEFAULT '09:30:00',
  `closing_time` TIME DEFAULT '16:00:00'
);

CREATE TABLE IF NOT EXISTS `Instruments` (
  `instrument_id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticker_symbol` VARCHAR(20) UNIQUE NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `exchange_id` INT NOT NULL,
  `asset_type` ENUM('STOCK', 'CRYPTO', 'OPTION', 'ETF') DEFAULT 'STOCK',
  `sector` VARCHAR(50),
  `current_price` DECIMAL(15, 4) DEFAULT 0.0000, -- High precision for prices
  FOREIGN KEY (`exchange_id`) REFERENCES `Exchanges`(`exchange_id`)
);

-- ==========================================
-- MODULE C: Orders & Execution
-- ==========================================

CREATE TABLE IF NOT EXISTS `Orders` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `instrument_id` INT NOT NULL,
  `order_type` ENUM('MARKET', 'LIMIT', 'STOP', 'STOP_LIMIT') NOT NULL,
  `side` ENUM('BUY', 'SELL') NOT NULL,
  `quantity` DECIMAL(15, 4) NOT NULL,
  `price_limit` DECIMAL(15, 4) NULL, -- For Limit orders
  `status` ENUM('PENDING', 'FILLED', 'PARTIALLY_FILLED', 'CANCELLED') DEFAULT 'PENDING',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`account_id`) REFERENCES `Accounts`(`account_id`),
  FOREIGN KEY (`instrument_id`) REFERENCES `Instruments`(`instrument_id`)
);

CREATE TABLE IF NOT EXISTS `Trades` (
  `trade_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `execution_price` DECIMAL(15, 4) NOT NULL,
  `quantity_filled` DECIMAL(15, 4) NOT NULL,
  `commission_fee` DECIMAL(10, 2) DEFAULT 0.00,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `Orders`(`order_id`)
);

-- ==========================================
-- MODULE D: Portfolio & Positions
-- ==========================================

CREATE TABLE IF NOT EXISTS `Positions` (
  `position_id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `instrument_id` INT NOT NULL,
  `average_entry_price` DECIMAL(15, 4) NOT NULL,
  `total_quantity` DECIMAL(15, 4) NOT NULL,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`account_id`) REFERENCES `Accounts`(`account_id`),
  FOREIGN KEY (`instrument_id`) REFERENCES `Instruments`(`instrument_id`),
  UNIQUE(`account_id`, `instrument_id`) -- One position record per instrument per account
);

-- ==========================================
-- MODULE E: Historical Price Data
-- ==========================================

CREATE TABLE IF NOT EXISTS `Market_Data` (
  `market_data_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `instrument_id` INT NOT NULL,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `open` DECIMAL(15, 4) NOT NULL,
  `high` DECIMAL(15, 4) NOT NULL,
  `low` DECIMAL(15, 4) NOT NULL,
  `close` DECIMAL(15, 4) NOT NULL,
  `volume` BIGINT DEFAULT 0,
  FOREIGN KEY (`instrument_id`) REFERENCES `Instruments`(`instrument_id`),
  INDEX(`instrument_id`, `timestamp`) -- Critical for time-series queries
);

-- ==========================================
-- ADVANCED FEATURES
-- ==========================================

CREATE TABLE IF NOT EXISTS `Watchlists` (
  `watchlist_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(50) DEFAULT 'My Watchlist',
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`)
);

CREATE TABLE IF NOT EXISTS `Watchlist_Items` (
  `watchlist_id` INT NOT NULL,
  `instrument_id` INT NOT NULL,
  PRIMARY KEY (`watchlist_id`, `instrument_id`),
  FOREIGN KEY (`watchlist_id`) REFERENCES `Watchlists`(`watchlist_id`) ON DELETE CASCADE,
  FOREIGN KEY (`instrument_id`) REFERENCES `Instruments`(`instrument_id`)
);

CREATE TABLE IF NOT EXISTS `Audit_Log` (
  `log_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `Corporate_Actions` (
  `action_id` INT AUTO_INCREMENT PRIMARY KEY,
  `instrument_id` INT NOT NULL,
  `type` ENUM('DIVIDEND', 'SPLIT', 'MERGER') NOT NULL,
  `amount` DECIMAL(15, 4) NULL, -- Dividend amount or split ratio
  `ex_date` DATE NOT NULL,
  FOREIGN KEY (`instrument_id`) REFERENCES `Instruments`(`instrument_id`)
);


-- ==========================================
-- REQUESTED FEATURES (AI, Multi-Currency, Tax)
-- ==========================================

CREATE TABLE IF NOT EXISTS `News_Sentiment` (
  `sentiment_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `instrument_id` INT NOT NULL,
  `score` DECIMAL(3, 2) NOT NULL, -- -1.00 to +1.00
  `source` VARCHAR(255) NULL,
  `confidence` DECIMAL(5, 4) NULL,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`instrument_id`) REFERENCES `Instruments`(`instrument_id`)
);

CREATE TABLE IF NOT EXISTS `Currency_Balances` (
  `balance_id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `currency_code` CHAR(3) NOT NULL, -- USD, EUR, GBP
  `amount` DECIMAL(15, 2) DEFAULT 0.00,
  FOREIGN KEY (`account_id`) REFERENCES `Accounts`(`account_id`),
  UNIQUE(`account_id`, `currency_code`)
);

CREATE TABLE IF NOT EXISTS `Price_Alerts` (
  `alert_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `instrument_id` INT NOT NULL,
  `condition_type` ENUM('ABOVE', 'BELOW') NOT NULL, -- > or <
  `price_threshold` DECIMAL(15, 4) NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`),
  FOREIGN KEY (`instrument_id`) REFERENCES `Instruments`(`instrument_id`)
);

CREATE TABLE IF NOT EXISTS `Tax_Lots` (
  `tax_lot_id` INT AUTO_INCREMENT PRIMARY KEY,
  `position_id` INT NOT NULL,
  `buy_date` DATE NOT NULL,
  `cost_basis` DECIMAL(15, 4) NOT NULL,
  `quantity` DECIMAL(15, 4) NOT NULL,
  `is_sold` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`position_id`) REFERENCES `Positions`(`position_id`)
);

COMMIT;
