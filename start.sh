#!/bin/bash

echo "🚀 Starting Stock Trading Application..."
echo ""
echo "Checking prerequisites..."

# Check PHP
if command -v php &> /dev/null; then
    PHP_CMD=$(which php)
    echo "✅ PHP found at: $PHP_CMD"
else
    echo "❌ PHP not found. Please install PHP first."
    exit 1
fi

# Check MySQL
if ps aux | grep -i mysqld | grep -v grep > /dev/null; then
    echo "✅ MySQL server is running"
else
    echo "⚠️  MySQL server is not running"
    echo "   Please start MySQL first"
fi

echo ""
echo "Starting PHP development server..."
echo ""
echo "📍 Application URLs:"
echo "   Landing Page: http://localhost:8000/index_landing.php"
echo "   Login:        http://localhost:8000/login.php"
echo "   Register:     http://localhost:8000/register.php"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

cd "$(dirname "$0")"
$PHP_CMD -S localhost:8000 router.php

