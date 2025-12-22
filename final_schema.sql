-- final_schema.sql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Users & Security
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_2fa_enabled` BOOLEAN DEFAULT FALSE,
  `otp_secret_key` VARCHAR(255) NULL, -- Stores the TOTP secret
  `backup_codes` JSON NULL,           -- Stores recovery codes
  `last_login_ip` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `Account` (
  `account_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `balance` DECIMAL(15, 2) DEFAULT 0.00,
  `currency` VARCHAR(10) DEFAULT 'USD',
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- Core Trading Entities
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Stocks` (
  `ticker` VARCHAR(10) PRIMARY KEY,
  `company_name` VARCHAR(100) NOT NULL,
  `current_price` DECIMAL(10, 2) NOT NULL,
  `sector` VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS `MarketHours` (
    `is_open` BOOLEAN DEFAULT TRUE
);

-- --------------------------------------------------------
-- Advanced Trading Features
-- --------------------------------------------------------

-- Limit Orders: Execute only when price conditions are met
CREATE TABLE IF NOT EXISTS `LimitOrders` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `ticker` VARCHAR(10) NOT NULL,
  `order_type` ENUM('BUY_LIMIT', 'SELL_LIMIT', 'STOP_LOSS') NOT NULL,
  `target_price` DECIMAL(10, 2) NOT NULL,
  `quantity` INT NOT NULL,
  `status` ENUM('PENDING', 'FILLED', 'CANCELLED') DEFAULT 'PENDING',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`),
  FOREIGN KEY (`ticker`) REFERENCES `Stocks`(`ticker`)
);

-- Dividends: Record payout history
CREATE TABLE IF NOT EXISTS `Dividends` (
  `dividend_id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticker` VARCHAR(10) NOT NULL,
  `amount_per_share` DECIMAL(10, 4) NOT NULL,
  `ex_dividend_date` DATE NOT NULL,
  `payout_date` DATE NOT NULL,
  FOREIGN KEY (`ticker`) REFERENCES `Stocks`(`ticker`)
);

-- Transaction History
CREATE TABLE IF NOT EXISTS `TransactionRecord` (
  `transaction_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `ticker` VARCHAR(10) NOT NULL,
  `type` ENUM('BUY', 'SELL', 'DIVIDEND') NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `quantity` INT NOT NULL,
  `total_amount` DECIMAL(15, 2) NOT NULL, -- Calculated as price * quantity
  `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`),
  FOREIGN KEY (`ticker`) REFERENCES `Stocks`(`ticker`)
);

-- --------------------------------------------------------
-- Triggers
-- --------------------------------------------------------

DELIMITER $$

-- Trigger: Automatically update Account Balance after a Transaction is recorded
CREATE TRIGGER `after_trade_execution`
AFTER INSERT ON `TransactionRecord`
FOR EACH ROW
BEGIN
    IF NEW.type = 'BUY' THEN
        UPDATE `Account` 
        SET `balance` = `balance` - NEW.total_amount 
        WHERE `user_id` = NEW.user_id;
    ELSEIF NEW.type = 'SELL' OR NEW.type = 'DIVIDEND' THEN
        UPDATE `Account` 
        SET `balance` = `balance` + NEW.total_amount 
        WHERE `user_id` = NEW.user_id;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- Seed Data (Mock)
-- --------------------------------------------------------

INSERT INTO `Stocks` (`ticker`, `company_name`, `current_price`, `sector`) VALUES
('AAPL', 'Apple Inc.', 150.00, 'Technology'),
('TSLA', 'Tesla Inc.', 900.00, 'Automotive'),
('GOOGL', 'Alphabet Inc.', 2800.00, 'Technology'),
('AMZN', 'Amazon.com', 3400.00, 'Consumer Cyclical');
