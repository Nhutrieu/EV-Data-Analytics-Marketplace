<?php
class DatabaseConfig {
    public static function getConnection() {
        // Trỏ đúng file .env trong cùng thư mục
     $envPath = __DIR__ . '/../.env'; // đi 1 cấp lên từ config → đến data-consumer-service
 // trỏ ra khỏi config folder lên data-consumer-service


        if (!file_exists($envPath)) {
            die("File .env không tồn tại ở $envPath");
        }

        $env = parse_ini_file($envPath);

        $host = $env['DB_HOST'] ?? 'localhost';
        $db   = $env['DB_NAME'] ?? 'ev_analytics';
        $user = $env['DB_USER'] ?? 'root';
        $pass = $env['DB_PASS'] ?? '';

        try {
            $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
}
?>
