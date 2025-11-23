-- Add more dummy data for interactivity

USE stock_trading_db;

-- 1. Add more Regions
INSERT IGNORE INTO Region (region_name) VALUES 
('South America'), ('Australia'), ('Africa');

-- 2. Add more Businesses
INSERT IGNORE INTO Business (company_name, year_established, region_id) VALUES
('Microsoft Corp', 1975, 2),
('Amazon.com Inc', 1994, 2),
('Tesla Inc', 2003, 2),
('Alphabet Inc', 2015, 2),
('Meta Platforms', 2004, 2),
('NVIDIA Corp', 1993, 2),
('Tencent Holdings', 1998, 1),
('Alibaba Group', 1999, 1),
('Toyota Motor', 1937, 1),
('Sony Group', 1946, 1),
('Volkswagen AG', 1937, 3),
('Siemens AG', 1847, 3),
('LVMH', 1987, 3);

-- 3. Add more Stocks
INSERT IGNORE INTO Stock (ticker_symbol, company_name, business_id) VALUES
('MSFT', 'Microsoft Corp', (SELECT business_id FROM Business WHERE company_name = 'Microsoft Corp')),
('AMZN', 'Amazon.com Inc', (SELECT business_id FROM Business WHERE company_name = 'Amazon.com Inc')),
('TSLA', 'Tesla Inc', (SELECT business_id FROM Business WHERE company_name = 'Tesla Inc')),
('GOOGL', 'Alphabet Inc', (SELECT business_id FROM Business WHERE company_name = 'Alphabet Inc')),
('META', 'Meta Platforms', (SELECT business_id FROM Business WHERE company_name = 'Meta Platforms')),
('NVDA', 'NVIDIA Corp', (SELECT business_id FROM Business WHERE company_name = 'NVIDIA Corp')),
('TCEHY', 'Tencent Holdings', (SELECT business_id FROM Business WHERE company_name = 'Tencent Holdings')),
('BABA', 'Alibaba Group', (SELECT business_id FROM Business WHERE company_name = 'Alibaba Group')),
('TM', 'Toyota Motor', (SELECT business_id FROM Business WHERE company_name = 'Toyota Motor')),
('SONY', 'Sony Group', (SELECT business_id FROM Business WHERE company_name = 'Sony Group')),
('VWAGY', 'Volkswagen AG', (SELECT business_id FROM Business WHERE company_name = 'Volkswagen AG')),
('SIEGY', 'Siemens AG', (SELECT business_id FROM Business WHERE company_name = 'Siemens AG')),
('LVMUY', 'LVMH', (SELECT business_id FROM Business WHERE company_name = 'LVMH'));

-- 4. Add more Users
INSERT IGNORE INTO Users (full_name, password_hash, workplace, region_id) VALUES
('Alice Smith', '$2y$10$z8z8z8z8z8z8z8z8z8z8zO1234567890abcdef', 'Google', 2),
('Bob Jones', '$2y$10$z8z8z8z8z8z8z8z8z8z8zO1234567890abcdef', 'Amazon', 2),
('Charlie Brown', '$2y$10$z8z8z8z8z8z8z8z8z8z8zO1234567890abcdef', 'Freelance', 3),
('David Lee', '$2y$10$z8z8z8z8z8z8z8z8z8z8zO1234567890abcdef', 'Samsung', 1),
('Emma Wilson', '$2y$10$z8z8z8z8z8z8z8z8z8z8zO1234567890abcdef', 'Apple', 2);

-- 5. Add Accounts for new users
INSERT INTO Account (user_id, balance) 
SELECT user_id, 1000000.00 FROM Users WHERE full_name IN ('Alice Smith', 'Bob Jones', 'Charlie Brown', 'David Lee', 'Emma Wilson')
ON DUPLICATE KEY UPDATE balance = GREATEST(balance, 1000000.00);

-- 6. Add Stock Prices
INSERT INTO StockPrice (ticker_symbol, exchange_id, current_price, previous_close, day_change, day_change_percent, volume, high_52w, low_52w) VALUES
('MSFT', 2, 375.00, 370.00, 5.00, 1.35, 25000000, 400.00, 300.00),
('AMZN', 2, 145.00, 142.00, 3.00, 2.11, 40000000, 170.00, 100.00),
('TSLA', 2, 240.00, 235.00, 5.00, 2.13, 100000000, 300.00, 150.00),
('GOOGL', 2, 135.00, 134.00, 1.00, 0.75, 20000000, 150.00, 100.00),
('META', 2, 330.00, 325.00, 5.00, 1.54, 15000000, 350.00, 200.00),
('NVDA', 2, 480.00, 470.00, 10.00, 2.13, 50000000, 500.00, 200.00),
('TCEHY', 1, 40.00, 39.50, 0.50, 1.27, 5000000, 50.00, 30.00),
('BABA', 1, 75.00, 74.00, 1.00, 1.35, 10000000, 100.00, 60.00),
('TM', 1, 180.00, 179.00, 1.00, 0.56, 1000000, 200.00, 150.00),
('SONY', 1, 85.00, 84.00, 1.00, 1.19, 800000, 100.00, 70.00),
('VWAGY', 3, 15.00, 14.80, 0.20, 1.35, 500000, 20.00, 10.00),
('SIEGY', 3, 80.00, 79.00, 1.00, 1.27, 400000, 90.00, 60.00),
('LVMUY', 3, 160.00, 158.00, 2.00, 1.27, 300000, 180.00, 140.00)
ON DUPLICATE KEY UPDATE current_price = VALUES(current_price);

-- 7. Add Random Transactions (History)
-- Alice buys MSFT
SET @alice_id = (SELECT user_id FROM Users WHERE full_name = 'Alice Smith' LIMIT 1);
SET @alice_account = (SELECT account_id FROM Account WHERE user_id = @alice_id LIMIT 1);
INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares, exchange_id, transaction_time)
VALUES (@alice_id, @alice_account, 'MSFT', 1, 350.00, 10, 2, DATE_SUB(NOW(), INTERVAL 10 DAY));

-- Bob buys AMZN
SET @bob_id = (SELECT user_id FROM Users WHERE full_name = 'Bob Jones' LIMIT 1);
SET @bob_account = (SELECT account_id FROM Account WHERE user_id = @bob_id LIMIT 1);
INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares, exchange_id, transaction_time)
VALUES (@bob_id, @bob_account, 'AMZN', 1, 130.00, 50, 2, DATE_SUB(NOW(), INTERVAL 15 DAY));

-- Charlie buys TSLA
SET @charlie_id = (SELECT user_id FROM Users WHERE full_name = 'Charlie Brown' LIMIT 1);
SET @charlie_account = (SELECT account_id FROM Account WHERE user_id = @charlie_id LIMIT 1);
INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares, exchange_id, transaction_time)
VALUES (@charlie_id, @charlie_account, 'TSLA', 1, 200.00, 20, 2, DATE_SUB(NOW(), INTERVAL 20 DAY));

-- David buys Samsung
SET @david_id = (SELECT user_id FROM Users WHERE full_name = 'David Lee' LIMIT 1);
SET @david_account = (SELECT account_id FROM Account WHERE user_id = @david_id LIMIT 1);
INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares, exchange_id, transaction_time)
VALUES (@david_id, @david_account, '005930.KS', 1, 60000.00, 5, 1, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Emma buys AAPL
SET @emma_id = (SELECT user_id FROM Users WHERE full_name = 'Emma Wilson' LIMIT 1);
SET @emma_account = (SELECT account_id FROM Account WHERE user_id = @emma_id LIMIT 1);
INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares, exchange_id, transaction_time)
VALUES (@emma_id, @emma_account, 'AAPL', 1, 170.00, 15, 2, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Alice sells some MSFT
INSERT INTO TransactionRecord (user_id, account_id, ticker_symbol, is_buy, cost_per_share, num_shares, exchange_id, transaction_time)
VALUES (@alice_id, @alice_account, 'MSFT', 0, 370.00, 2, 2, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- 8. Add Watchlist items
INSERT IGNORE INTO Watchlist (user_id, ticker_symbol, notes)
VALUES (@alice_id, 'NVDA', 'Watching AI trend');

INSERT IGNORE INTO Watchlist (user_id, ticker_symbol, notes)
VALUES (@bob_id, 'TSLA', 'Volatile but interesting');

-- 9. Add Price Alerts
INSERT IGNORE INTO PriceAlert (user_id, ticker_symbol, alert_type, target_price)
VALUES (@alice_id, 'MSFT', 'ABOVE', 400.00);

