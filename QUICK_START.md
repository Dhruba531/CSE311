# Quick Start Guide

## Step 1: Import Database (Choose One Method)

### Method A: MySQL Workbench (Easiest - No PHP needed)
1. Open MySQL Workbench
2. Connect to MySQL (enter password)
3. Go to **Server** → **Data Import** (`Cmd+Shift+I`)
4. Select "Import from Self-Contained File"
5. Browse to: `/Users/dhrubasaha/MySQL/database.sql`
6. Create new schema: `stock_trading_db`
7. Click "Start Import"

### Method B: Command Line
```bash
/usr/local/mysql/bin/mysql -u root -p < database.sql
```
(Enter your MySQL password when prompted)

## Step 2: Update Database Config

Edit `config.php` and add your MySQL password:
```php
$pass = 'your_mysql_password_here';
```

## Step 3: Install PHP (If Not Installed)

**Option A: Homebrew**
```bash
brew install php
```

**Option B: MAMP**
- Download from https://www.mamp.info
- Install and start MAMP servers

## Step 4: Start the Application

```bash
cd /Users/dhrubasaha/MySQL
php -S localhost:8000
```

## Step 5: Open in Browser

Go to: **http://localhost:8000/login.php**

## Step 6: Register and Start Trading!

1. Click "Register here" on login page
2. Create your account
3. You'll get $10,000 starting balance
4. Start trading!

---

**Need Help?**
- Database import: See `IMPORT_INSTRUCTIONS.md`
- PHP installation: See `INSTALL_PHP.md`
- Full documentation: See `README.md`

