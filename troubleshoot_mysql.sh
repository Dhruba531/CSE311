#!/bin/bash

# MySQL Troubleshooting Script

MYSQL_PATH="/usr/local/mysql/bin/mysql"

echo "🔍 MySQL Connection Troubleshooting"
echo "===================================="
echo ""

# Test 1: Try without password
echo "Test 1: Trying to connect without password..."
if $MYSQL_PATH -u root -e "SELECT 'Connection successful!' as Status;" 2>/dev/null; then
    echo "✅ SUCCESS: MySQL root has no password"
    echo ""
    echo "You can import the database with:"
    echo "  /usr/local/mysql/bin/mysql -u root stock_trading_db < database_enhanced.sql"
    exit 0
else
    echo "❌ Failed: Root requires a password"
fi

echo ""
echo "Test 2: Checking if MySQL server is running..."
if ps aux | grep -i mysqld | grep -v grep > /dev/null; then
    echo "✅ MySQL server is running"
else
    echo "❌ MySQL server is NOT running"
    echo ""
    echo "To start MySQL:"
    echo "1. Open System Preferences"
    echo "2. Go to MySQL"
    echo "3. Click 'Start MySQL Server'"
    echo ""
    echo "OR use command line:"
    echo "  sudo /usr/local/mysql/support-files/mysql.server start"
    exit 1
fi

echo ""
echo "Test 3: Checking MySQL Workbench connections..."
if [ -d "/Applications/MySQLWorkbench.app" ]; then
    echo "✅ MySQL Workbench is installed"
    echo ""
    echo "💡 TIP: If you've connected to MySQL via Workbench before,"
    echo "   your password might be saved there. Try:"
    echo "   1. Open MySQL Workbench"
    echo "   2. Check your saved connections"
    echo "   3. Use that password"
else
    echo "⚠️  MySQL Workbench not found"
fi

echo ""
echo "=========================================="
echo "Options to proceed:"
echo ""
echo "Option 1: Try importing via MySQL Workbench"
echo "  - Open MySQL Workbench"
echo "  - Connect to your server"
echo "  - File → Open SQL Script"
echo "  - Select database_enhanced.sql"
echo "  - Click Execute"
echo ""
echo "Option 2: Reset MySQL root password"
echo "  (This requires stopping MySQL and using --skip-grant-tables)"
echo ""
echo "Option 3: Create a new MySQL user"
echo "  - Connect as root (if you remember password)"
echo "  - CREATE USER 'trader'@'localhost' IDENTIFIED BY 'your_password';"
echo "  - GRANT ALL PRIVILEGES ON stock_trading_db.* TO 'trader'@'localhost';"
echo ""
echo "Option 4: Check if you have a different MySQL user"
echo "  - Try: mysql -u your_username -p"
echo ""

