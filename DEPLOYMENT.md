# Local Deployment Guide

## ✅ Quick Start

Your application is now deployed locally! Here's how to access it:

### Access URLs:
- **Landing Page**: http://localhost:8000/
- **Login Page**: http://localhost:8000/login.php
- **Register Page**: http://localhost:8000/register.php
- **Dashboard** (after login): http://localhost:8000/index.php

## 🚀 Starting the Server

### Option 1: Using the Start Script (Recommended)
```bash
cd /Users/dhrubasaha/MySQL
./start.sh
```

### Option 2: Manual Start
```bash
cd /Users/dhrubasaha/MySQL
/opt/homebrew/bin/php -S localhost:8000 router.php
```

### Option 3: Using PHP Directly
```bash
cd /Users/dhrubasaha/MySQL
php -S localhost:8000 router.php
```

## 🛑 Stopping the Server

Press `Ctrl+C` in the terminal where the server is running.

Or kill the process:
```bash
pkill -f "php -S localhost:8000"
```

## 📋 Prerequisites Check

✅ **PHP**: Installed at `/opt/homebrew/bin/php`
✅ **MySQL**: Server is running
✅ **Database**: Make sure `stock_trading_db` is imported

## 🔧 Configuration

The application uses environment variables from `.env` file:
- `DB_HOST`: Database host (default: localhost)
- `DB_NAME`: Database name (default: stock_trading_db)
- `DB_USER`: Database user
- `DB_PASS`: Database password
- `DB_CHARSET`: Character set (default: utf8mb4)

## 🎨 Features

- **Modern Dark Theme**: Beautiful landing page with dark design
- **Responsive Design**: Works on desktop and mobile
- **Full CRUD Operations**: Create, Read, Update, Delete stocks
- **Advanced Features**: Watchlist, Price Alerts, Analytics
- **Database Triggers**: Automatic balance updates
- **Security**: CSRF protection, password hashing

## 🐛 Troubleshooting

### Server won't start
- Check if port 8000 is already in use: `lsof -i :8000`
- Kill existing process: `pkill -f "php -S localhost:8000"`

### Database connection error
- Verify MySQL is running: `ps aux | grep mysqld`
- Check `.env` file has correct credentials
- Make sure database `stock_trading_db` exists

### 404 errors
- Make sure you're using `router.php` when starting the server
- Check file permissions: `chmod +x start.sh`

## 📝 Next Steps

1. Open http://localhost:8000/ in your browser
2. Click "Get Started" to register a new account
3. Start trading stocks!

Enjoy your stock trading platform! 📈

