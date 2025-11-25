<?php
require 'config.php';

$sql = file_get_contents('create_stock_price.sql');

try {
    $pdo->exec($sql);
    echo "SQL executed successfully.\n";
} catch (PDOException $e) {
    echo "Error executing SQL: " . $e->getMessage() . "\n";
}
?>