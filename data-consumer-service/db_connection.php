<?php
// ================================
// Database Connection (PDO)
// ================================

$host    = 'db_consumer';             // ❗ tên service MySQL trong docker-compose, KHÔNG phải localhost
$db      = 'ev_analytics';   // trùng MYSQL_DATABASE
$user    = 'ev_user';        // trùng MYSQL_USER
$pass    = 'ev_pass';        // trùng MYSQL_PASSWORD
$charset = 'utf8mb4';
$port    = 3306;             // port bên trong container

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "✅ Connected to DB"; // có thể bật để test
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
