-- add_dummy_data.sql

INSERT INTO Region (region_name) VALUES ('North America'), ('Europe'), ('Asia');

INSERT INTO Exchange (exchange_name, region_id) VALUES ('NYSE', 1), ('NASDAQ', 1);

INSERT INTO Business (business_name, region_id) VALUES ('Tech', 1), ('Finance', 1);

INSERT INTO Stock (ticker_symbol, company_name, business_id) VALUES 
('AAPL', 'Apple Inc.', 1),
('GOOGL', 'Alphabet Inc.', 1),
('MSFT', 'Microsoft Corp.', 1),
('TSLA', 'Tesla Inc.', 1),
('AMZN', 'Amazon.com Inc.', 1);

INSERT INTO StockPrice (ticker_symbol, current_price, previous_close) VALUES 
('AAPL', 150.00, 148.00),
('GOOGL', 2800.00, 2750.00),
('MSFT', 300.00, 295.00),
('TSLA', 900.00, 880.00),
('AMZN', 3400.00, 3350.00);
