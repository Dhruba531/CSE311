<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

echo "<h1>Database Debugger</h1>";
echo "<p><strong>Connection Type:</strong> " . ($db_connection ?? 'Unknown') . "</p>";

if ($db_connection === 'sqlite') {
    echo "<p><strong>Database File:</strong> " . __DIR__ . '/database.sqlite' . "</p>";
    echo "<p><strong>File Exists:</strong> " . (file_exists(__DIR__ . '/database.sqlite') ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>File Size:</strong> " . (file_exists(__DIR__ . '/database.sqlite') ? filesize(__DIR__ . '/database.sqlite') : '0') . " bytes</p>";
}

echo "<h2>Table Counts</h2>";
$tables = ['Stocks', 'Instruments', 'Users', 'Transactions', 'Price_History', 'Region', 'Exchange', 'Business'];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Table</th><th>Count</th></tr>";

foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $t");
        $count = $stmt->fetchColumn();
        echo "<tr><td>$t</td><td>$count</td></tr>";
    } catch (Exception $e) {
        echo "<tr><td>$t</td><td style='color:red'>Error: " . $e->getMessage() . "</td></tr>";
    }
}
echo "</table>";

echo "<h2>Environment Variables</h2>";
echo "<pre>";
print_r($_ENV);
echo "</pre>";
?>
