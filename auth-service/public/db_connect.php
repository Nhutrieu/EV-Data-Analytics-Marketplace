<?php
$host = getenv('DB_HOST') ?: 'db';      // tên service MySQL trong docker-compose
$db   = getenv('DB_NAME') ?: 'ev_marketplace';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'root';
$port = getenv('DB_PORT') ?: 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}
