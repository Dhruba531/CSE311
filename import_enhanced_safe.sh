#!/bin/bash

# Safe import script for enhanced database
# This script handles existing tables gracefully

MYSQL_PATH="/usr/local/mysql/bin/mysql"

echo "📊 Enhanced Database Import Script"
echo "================================"
echo ""

# Check if MySQL exists
if [ ! -f "$MYSQL_PATH" ]; then
    echo "❌ MySQL not found at $MYSQL_PATH"
    exit 1
fi

echo "MySQL found at: $MYSQL_PATH"
echo ""

# Ask for password
read -sp "Enter MySQL root password (press Enter if no password): " PASSWORD
echo ""

# First, let's check if tables exist and handle them
echo "Checking for existing tables..."

if [ -z "$PASSWORD" ]; then
    # Check what tables exist
    EXISTING_TABLES=$($MYSQL_PATH -u root stock_trading_db -e "SHOW TABLES LIKE 'StockPrice%';" 2>/dev/null | tail -n +2)
    
    if [ ! -z "$EXISTING_TABLES" ]; then
        echo "⚠️  Some enhanced tables already exist."
        echo "Options:"
        echo "1. Drop existing enhanced tables and re-import (recommended)"
        echo "2. Skip import (keep existing tables)"
        read -p "Choose option (1 or 2): " OPTION
        
        if [ "$OPTION" = "1" ]; then
            echo "Dropping existing enhanced tables..."
            $MYSQL_PATH -u root stock_trading_db <<EOF
DROP TABLE IF EXISTS PortfolioSnapshot;
DROP TABLE IF EXISTS Notification;
DROP TABLE IF EXISTS AuditLog;
DROP TABLE IF EXISTS UserDividend;
DROP TABLE IF EXISTS Dividend;
DROP TABLE IF EXISTS OrderType;
DROP TABLE IF EXISTS PriceAlert;
DROP TABLE IF EXISTS Watchlist;
DROP TABLE IF EXISTS StockPrice;
DROP TABLE IF EXISTS StockPriceHistory;
EOF
            echo "✅ Existing tables dropped"
        else
            echo "Skipping import. Existing tables preserved."
            exit 0
        fi
    fi
    
    echo "Importing enhanced database..."
    $MYSQL_PATH -u root stock_trading_db < database_enhanced.sql
else
    # Same logic but with password
    EXISTING_TABLES=$($MYSQL_PATH -u root -p"$PASSWORD" stock_trading_db -e "SHOW TABLES LIKE 'StockPrice%';" 2>/dev/null | tail -n +2)
    
    if [ ! -z "$EXISTING_TABLES" ]; then
        echo "⚠️  Some enhanced tables already exist."
        echo "Options:"
        echo "1. Drop existing enhanced tables and re-import (recommended)"
        echo "2. Skip import (keep existing tables)"
        read -p "Choose option (1 or 2): " OPTION
        
        if [ "$OPTION" = "1" ]; then
            echo "Dropping existing enhanced tables..."
            $MYSQL_PATH -u root -p"$PASSWORD" stock_trading_db <<EOF
DROP TABLE IF EXISTS PortfolioSnapshot;
DROP TABLE IF EXISTS Notification;
DROP TABLE IF EXISTS AuditLog;
DROP TABLE IF EXISTS UserDividend;
DROP TABLE IF EXISTS Dividend;
DROP TABLE IF EXISTS OrderType;
DROP TABLE IF EXISTS PriceAlert;
DROP TABLE IF EXISTS Watchlist;
DROP TABLE IF EXISTS StockPrice;
DROP TABLE IF EXISTS StockPriceHistory;
EOF
            echo "✅ Existing tables dropped"
        else
            echo "Skipping import. Existing tables preserved."
            exit 0
        fi
    fi
    
    echo "Importing enhanced database..."
    $MYSQL_PATH -u root -p"$PASSWORD" stock_trading_db < database_enhanced.sql
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Enhanced database imported successfully!"
    echo ""
    echo "Next steps:"
    echo "1. Update config.php with your MySQL password (if needed)"
    echo "2. Install PHP if not already installed"
    echo "3. Run: php -S localhost:8000"
    echo "4. Open: http://localhost:8000/login.php"
else
    echo ""
    echo "❌ Import failed. Please check:"
    echo "   - MySQL password is correct"
    echo "   - MySQL server is running"
    echo "   - You have permission to create tables"
fi

