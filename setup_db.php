<?php
require 'config.php';

try {
    $sql = file_get_contents('database_enhanced.sql');
    $pdo->exec($sql);
    echo "Database updated successfully.\n";
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
