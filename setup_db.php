<?php
require_once 'config.php';

// Use global connection setting from config.php (which defaults to 'sqlite' if ENV is missing)
$conn_type = $db_connection;

echo "Running setup for: " . strtoupper($conn_type) . "\n";

try {
    if ($conn_type === 'mysql') {
        // MySQL Path
        $sql = file_get_contents('mysql_final_schema.sql');
    } else {
        // SQLite Path (Default Local)
        $db_file = __DIR__ . '/database.sqlite';
        if (file_exists($db_file)) {
            unlink($db_file);
            echo "Removed existing SQLite database for a fresh setup.\n";
        }
        
        $sql = file_get_contents('final_schema.sql');
    }
    
    $pdo->exec($sql);
    echo "Database schema applied successfully!\n";
    
    // Auto-seed market data
    echo "Starting Market Data Seeder...\n";
    include 'seed_market_data.php';

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
