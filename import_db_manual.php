<?php
require 'config.php';

function run_sql_file($pdo, $filename)
{
    echo "Importing $filename...\n";
    if (!file_exists($filename)) {
        echo "File not found: $filename\n";
        return;
    }

    $lines = file($filename);
    $buffer = '';
    $delimiter = ';';

    foreach ($lines as $line) {
        $trimLine = trim($line);

        // Skip comments
        if (strpos($trimLine, '--') === 0 || strpos($trimLine, '#') === 0 || $trimLine === '') {
            continue;
        }

        // Handle DELIMITER command
        if (preg_match('/^DELIMITER\s+(\S+)/i', $trimLine, $matches)) {
            $delimiter = $matches[1];
            continue;
        }

        $buffer .= $line;

        // Check if the statement ends with the current delimiter
        if (substr(trim($buffer), -strlen($delimiter)) === $delimiter) {
            // Remove delimiter from the end
            $sql = substr(trim($buffer), 0, -strlen($delimiter));

            if (trim($sql) !== '') {
                try {
                    $pdo->exec($sql);
                } catch (PDOException $e) {
                    echo "Error executing SQL: " . substr($sql, 0, 100) . "...\n";
                    echo "Message: " . $e->getMessage() . "\n";
                }
            }
            $buffer = '';
        }
    }

    // Execute any remaining buffer
    if (trim($buffer) !== '') {
        try {
            $pdo->exec($buffer);
        } catch (PDOException $e) {
            // Often just empty lines or comments left
        }
    }
    echo "Finished $filename\n";
}

try {
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS stock_trading_db");
    $pdo->exec("USE stock_trading_db");

    // Order matters
    run_sql_file($pdo, 'database.sql');
    run_sql_file($pdo, 'database_enhanced.sql');
    run_sql_file($pdo, 'add_dummy_data.sql');
    run_sql_file($pdo, 'add_more_triggers.sql');

    echo "Database setup complete!\n";

} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}
