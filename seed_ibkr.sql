-- seed_ibkr.sql
-- Initial Data for Testing

-- 1. Create Exchange
INSERT INTO `Exchanges` (`name`, `timezone`, `opening_time`, `closing_time`) VALUES
('NASDAQ', 'America/New_York', '09:30:00', '16:00:00');

-- 2. Create Instruments
INSERT INTO `Instruments` (`ticker_symbol`, `name`, `exchange_id`, `asset_type`, `current_price`) VALUES
('AAPL', 'Apple Inc.', 1, 'STOCK', 150.00),
('TSLA', 'Tesla Inc.', 1, 'STOCK', 900.00),
('BTC', 'Bitcoin', 1, 'CRYPTO', 45000.00);

-- 3. Create User
INSERT INTO `Users` (`username`, `email`, `password_hash`, `full_name`, `kyc_status`) VALUES
('demo_user', 'demo@example.com', 'hashed_secret', 'Demo Trader', 'VERIFIED');

-- 4. Create Account
INSERT INTO `Accounts` (`user_id`, `currency`, `balance`, `buying_power`) VALUES
(1, 'USD', 100000.00, 100000.00);

-- 5. Mock Market History (Last 3 days for AAPL)
INSERT INTO `Market_Data` (`instrument_id`, `open`, `high`, `low`, `close`, `volume`, `timestamp`) VALUES
(1, 145.00, 148.00, 144.00, 147.50, 5000000, NOW() - INTERVAL 2 DAY),
(1, 147.50, 151.00, 147.00, 150.00, 6000000, NOW() - INTERVAL 1 DAY),
(1, 150.00, 152.00, 149.00, 150.00, 4500000, NOW());

-- 6. Create Initial Positions (Needed for Tax Lots)
INSERT INTO `Positions` (`account_id`, `instrument_id`, `average_entry_price`, `total_quantity`) VALUES
(1, 1, 140.00, 10); -- 10 shares of AAPL at $140

-- 7. Create Adv. Currency Balance
INSERT INTO `Currency_Balances` (`account_id`, `currency_code`, `amount`) VALUES
(1, 'EUR', 500.00);

-- 8. Create Price Alert
INSERT INTO `Price_Alerts` (`user_id`, `instrument_id`, `condition_type`, `price_threshold`) VALUES
(1, 1, 'ABOVE', 155.00);

-- 9. Create Tax Lot
INSERT INTO `Tax_Lots` (`position_id`, `buy_date`, `cost_basis`, `quantity`) VALUES
(1, CURRENT_DATE(), 140.00, 10);

-- 10. News Sentiment
INSERT INTO `News_Sentiment` (`instrument_id`, `score`, `source`) VALUES
(1, 0.85, 'Bloomberg AI');
