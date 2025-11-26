<?php
// src/provider_db.php
$config = require __DIR__ . '/config.php';

$db = $config['provider_db'];

try {
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";

    $providerPdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

} catch (PDOException $e) {
    die("❌ Kết nối tới PROVIDER DB thất bại: " . $e->getMessage());
}

return $providerPdo;
