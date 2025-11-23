# How to Import Database Using MySQL Workbench

Since you have MySQL Workbench installed, this is the easiest method:

## Steps:

1. **Open MySQL Workbench**
   - Launch the application from your Applications folder

2. **Connect to MySQL Server**
   - Click on your local MySQL connection (usually named "Local instance MySQL" or similar)
   - Enter your root password when prompted

3. **Import the Database**
   - Go to **Server** → **Data Import** (or press `Cmd+Shift+I`)
   - Select **"Import from Self-Contained File"**
   - Click the folder icon and navigate to:
     `/Users/dhrubasaha/MySQL/database.sql`
   - Under **"Default Schema to be Imported To"**, click **"New"**
   - Enter schema name: `stock_trading_db`
   - Click **"Create Schema"**
   - Make sure `stock_trading_db` is selected
   - Click **"Start Import"** button at the bottom right

4. **Verify Import**
   - In the left sidebar, refresh the schemas
   - You should see `stock_trading_db` with all tables

5. **Done!** 
   - Now you can start the PHP server and use the application

