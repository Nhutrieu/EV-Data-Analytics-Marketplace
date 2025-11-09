<?php
class Database {

    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            // ⚠️ CẤU HÌNH THEO DOCKER, KHÔNG PHẢI XAMPP
            $host   = 'db';             // tên service MySQL trong docker-compose
            $dbname = 'ev_analytics';   // trùng MYSQL_DATABASE
            $user   = 'ev_user';        // trùng MYSQL_USER
            $pass   = 'ev_pass';        // trùng MYSQL_PASSWORD
            $port   = 3306;             // port bên trong container

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            try {
                self::$pdo = new PDO($dsn, $user, $pass);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // echo "✅ DB connected"; // Bật lên test nếu cần
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
