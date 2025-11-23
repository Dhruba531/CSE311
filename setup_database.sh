#!/bin/bash

# MySQL Setup Script for Stock Trading Database
# This script helps you import the database

MYSQL_PATH="/usr/local/mysql/bin/mysql"

echo "📊 Stock Trading Database Setup"
echo "================================"
echo ""

# Check if MySQL exists
if [ ! -f "$MYSQL_PATH" ]; then
    echo "❌ MySQL not found at $MYSQL_PATH"
    echo "Please update MYSQL_PATH in this script with your MySQL location"
    exit 1
fi

echo "MySQL found at: $MYSQL_PATH"
echo ""

# Ask for password
read -sp "Enter MySQL root password (press Enter if no password): " PASSWORD
echo ""

# Import database
if [ -z "$PASSWORD" ]; then
    echo "Importing database (no password)..."
    $MYSQL_PATH -u root < database.sql
    $MYSQL_PATH -u root < database_enhanced.sql
else
    echo "Importing database..."
    $MYSQL_PATH -u root -p"$PASSWORD" < database.sql
    $MYSQL_PATH -u root -p"$PASSWORD" < database_enhanced.sql
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Database imported successfully!"
    echo ""
    echo "Next steps:"
    echo "1. Update config.php if you have a MySQL password"
    echo "2. Run: php -S localhost:8000"
    echo "3. Open: http://localhost:8000/login.php"
else
    echo ""
    echo "❌ Database import failed. Please check:"
    echo "   - MySQL is running"
    echo "   - Username and password are correct"
    echo "   - You have permission to create databases"
fi

