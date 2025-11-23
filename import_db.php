<?php
/**
 * Database Import Script
 * This script imports the database.sql file into MySQL
 * 
 * Usage: php import_db.php
 */

echo "📊 Stock Trading Database Import Tool\n";
echo "=====================================\n\n";

// Get database credentials
echo "Enter MySQL connection details:\n";
echo "Host [localhost]: ";
$host = trim(fgets(STDIN)) ?: 'localhost';

echo "Username [root]: ";
$user = trim(fgets(STDIN)) ?: 'root';

echo "Password: ";
$pass = trim(fgets(STDIN));

echo "\nConnecting to MySQL...\n";

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected successfully!\n\n";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("❌ Error: database.sql file not found!\n");
    }
    
    echo "Reading database.sql file...\n";
    $sql = file_get_contents($sqlFile);
    
    if (empty($sql)) {
        die("❌ Error: database.sql file is empty!\n");
    }
    
    echo "Importing database (this may take a moment)...\n\n";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            // Ignore "database already exists" errors
            if (strpos($e->getMessage(), 'database exists') === false) {
                $errorCount++;
                echo "⚠️  Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✅ Import completed!\n";
    echo "   Successful statements: $successCount\n";
    if ($errorCount > 0) {
        echo "   Warnings: $errorCount\n";
    }
    
    // Verify import
    echo "\nVerifying import...\n";
    $pdo->exec("USE stock_trading_db");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ Database 'stock_trading_db' created with " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    
    echo "\n🎉 Setup complete! You can now:\n";
    echo "   1. Update config.php with your MySQL password\n";
    echo "   2. Run: php -S localhost:8000\n";
    echo "   3. Open: http://localhost:8000/login.php\n";
    
} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "   - MySQL server is running\n";
    echo "   - Username and password are correct\n";
    echo "   - You have permission to create databases\n";
    exit(1);
}
?>

