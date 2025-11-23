# Installing PHP on macOS

You need PHP to run the web application. Here are your options:

## Option 1: Install PHP via Homebrew (Recommended)

If you have Homebrew installed:

```bash
brew install php
```

Then verify installation:
```bash
php -v
```

## Option 2: Install MAMP (Easiest for Beginners)

MAMP includes PHP, MySQL, and Apache:

1. Download MAMP from: https://www.mamp.info/en/downloads/
2. Install and launch MAMP
3. Start the servers
4. PHP will be available at: `/Applications/MAMP/bin/php/php8.x.x/bin/php`

Add to PATH:
```bash
export PATH=$PATH:/Applications/MAMP/bin/php/php8.x.x/bin
```

## Option 3: Use macOS Built-in PHP (if available)

macOS sometimes includes PHP. Check:
```bash
/usr/bin/php -v
```

If it works, you can use `/usr/bin/php` instead of `php`.

## After Installing PHP

1. Import the database (using MySQL Workbench - see IMPORT_INSTRUCTIONS.md)
2. Update `config.php` with your MySQL password
3. Start the server:
   ```bash
   php -S localhost:8000
   ```
4. Open: http://localhost:8000/login.php

