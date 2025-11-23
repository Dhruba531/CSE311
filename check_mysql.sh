#!/bin/bash

# MySQL Connection Check Script

MYSQL_PATH="/usr/local/mysql/bin/mysql"
MYSQLADMIN_PATH="/usr/local/mysql/bin/mysqladmin"

echo "🔍 Checking MySQL Status..."
echo "=========================="
echo ""

# Check if MySQL binaries exist
if [ ! -f "$MYSQL_PATH" ]; then
    echo "❌ MySQL client not found at $MYSQL_PATH"
    exit 1
fi

echo "✅ MySQL client found at: $MYSQL_PATH"
echo ""

# Check if MySQL server is running
echo "Checking if MySQL server is running..."
if $MYSQLADMIN_PATH -u root ping 2>/dev/null | grep -q "mysqld is alive"; then
    echo "✅ MySQL server is running"
else
    echo "❌ MySQL server is NOT running"
    echo ""
    echo "To start MySQL on macOS:"
    echo "1. Open System Preferences"
    echo "2. Go to MySQL"
    echo "3. Click 'Start MySQL Server'"
    echo ""
    echo "OR use command line:"
    echo "sudo /usr/local/mysql/support-files/mysql.server start"
    exit 1
fi

echo ""
echo "Testing connection..."
read -sp "Enter MySQL root password (press Enter if no password): " PASSWORD
echo ""

if [ -z "$PASSWORD" ]; then
    $MYSQL_PATH -u root -e "SELECT 'Connection successful!' as Status;" 2>&1
else
    $MYSQL_PATH -u root -p"$PASSWORD" -e "SELECT 'Connection successful!' as Status;" 2>&1
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Connection successful! You can now import the database."
else
    echo ""
    echo "❌ Connection failed. Please check:"
    echo "   - MySQL password is correct"
    echo "   - MySQL server is running"
    echo "   - You have proper permissions"
fi

