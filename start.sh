#!/bin/bash

echo "🚀 Starting Stock Trading Application..."
echo ""
echo "Step 1: Make sure MySQL is running"
echo "Step 2: Import database.sql if you haven't already:"
echo "   mysql -u root -p < database.sql"
echo ""
echo "Step 3: Starting PHP server..."
echo "   Access the app at: http://localhost:8000/login.php"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php -S localhost:8000

