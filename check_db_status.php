<?php
require 'config.php';

function countTable($pdo, $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

echo "Stocks: " . countTable($pdo, 'Stocks') . "\n";
echo "Instruments: " . countTable($pdo, 'Instruments') . "\n";
echo "Market_Data: " . countTable($pdo, 'Market_Data') . "\n"; // Checking old table
echo "Price_History: " . countTable($pdo, 'Price_History') . "\n"; // Checking new table
?>
