<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db_connection = $_ENV['DB_CONNECTION'] ?? 'sqlite';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    if ($db_connection === 'sqlite') {
        $db_file = __DIR__ . '/database.sqlite';
        $dsn = "sqlite:$db_file";
        $pdo = new PDO($dsn, null, null, $options);
        // Enable foreign keys for SQLite
        $pdo->exec("PRAGMA foreign_keys = ON;");
    } else {
        $host = $_ENV['DB_HOST'];
        $db = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $charset = $_ENV['DB_CHARSET'];

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $pdo = new PDO($dsn, $user, $pass, $options);
    }
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}

// Helper function to check login status
function is_logged_in() {
    return isset($_SESSION['user_id']);
}


// SMTP Configuration (For Real Email)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');     // REPLACE with your email
define('SMTP_PASS', 'your_app_password');        // REPLACE with your App Password
define('SMTP_FROM', 'noreply@xtrade.com');
define('SMTP_FROM_NAME', 'XTrade Security');

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}