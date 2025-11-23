# Stock Trading Database Project - Setup Guide

## Prerequisites
- PHP 7.4 or higher
- MySQL/MariaDB
- Web server (Apache/Nginx) OR PHP built-in server

## Step-by-Step Setup

### 1. Set Up MySQL Database

First, make sure MySQL is running on your system.

#### Option A: Using the Setup Script (Recommended for macOS)
```bash
cd /Users/dhrubasaha/MySQL
./setup_database.sh
```
This script will prompt you for your MySQL password.

#### Option B: Using MySQL Command Line (macOS)
If MySQL is installed at `/usr/local/mysql/bin/mysql`:
```bash
/usr/local/mysql/bin/mysql -u root -p < database.sql
```

Or add MySQL to your PATH first:
```bash
export PATH=$PATH:/usr/local/mysql/bin
mysql -u root -p < database.sql
```

#### Option C: Using MySQL Workbench or phpMyAdmin
1. Open MySQL Workbench or phpMyAdmin
2. Create a new database connection
3. Import the `database.sql` file

### 2. Configure Database Connection

Edit `config.php` and update these values if needed:
```php
$host = 'localhost';      // Your MySQL host
$db   = 'stock_trading_db'; // Database name
$user = 'root';           // Your MySQL username
$pass = '';               // Your MySQL password (leave empty if no password)
```

**Note:** If you have a MySQL password, update the `$pass` variable in `config.php`.

### 3. Start PHP Server

Navigate to the project directory and run:

```bash
cd /Users/dhrubasaha/MySQL
php -S localhost:8000
```

Or if you're using XAMPP/MAMP:
- Place the project folder in `htdocs` (XAMPP) or `htdocs` (MAMP)
- Access via `http://localhost/MySQL/`

### 4. Access the Application

Open your web browser and go to:
```
http://localhost:8000/login.php
```

### 5. Login or Register

**Option 1: Register a New Account**
- Click "Register here" on the login page
- Fill in your details
- You'll get $10,000 starting balance automatically

**Option 2: Use Existing Test Accounts**
The database includes sample users, but you'll need to set proper passwords. Better to register a new account.

## Default Test Data

The database comes with:
- 3 Regions: Asia, North America, Europe
- 3 Businesses: Apple Inc., Tata Motors, Samsung Electronics
- 3 Exchanges: DSE, NYSE, NASDAQ
- 3 Stocks: AAPL, TATAMOTORS, 005930.KS

## Features Available

1. **Dashboard** - Overview of your portfolio and recent activity
2. **Stocks** - Manage stocks (Create, Read, Update, Delete)
3. **Trade** - Buy and sell stocks
4. **Portfolio** - View your holdings with gain/loss
5. **History** - Complete transaction history
6. **Account** - Manage accounts, deposit, withdraw
7. **Friends** - Add and manage friends

## Troubleshooting

### Database Connection Error
- Make sure MySQL is running
- Check username/password in `config.php`
- Verify database `stock_trading_db` exists

### Page Not Found
- Make sure you're in the correct directory when running PHP server
- Check the URL (should be `http://localhost:8000/login.php`)

### Session Errors
- Make sure PHP sessions are enabled
- Check file permissions

## Quick Test

1. Register a new account
2. Go to Account page and create an account (or use the default one)
3. Go to Stocks page to see available stocks
4. Go to Trade page to buy some stocks
5. Check Portfolio to see your holdings

Enjoy trading! 📈

