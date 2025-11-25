
-- Create StockPrice table if it doesn't exist
CREATE TABLE IF NOT EXISTS StockPrice (
    ticker_symbol VARCHAR(10) PRIMARY KEY,
    current_price DECIMAL(10, 2) NOT NULL,
    previous_close DECIMAL(10, 2),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ticker_symbol) REFERENCES Stock(ticker_symbol) ON DELETE CASCADE
);
