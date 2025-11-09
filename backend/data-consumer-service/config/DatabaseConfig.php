<?php
class DatabaseConfig {
    public static function getConnection() {
        // Trỏ đúng file .env (đi lên 1 cấp từ thư mục config)
        $envPath = __DIR__ . '/../.env';

        if (!file_exists($envPath)) {
            die("File .env không tồn tại ở $envPath");
        }

        $env = parse_ini_file($envPath);

        // ⚠️ Ưu tiên lấy từ .env, nếu không có thì fallback về config Docker
        $host = $env['DB_HOST'] ?? 'db';             // MẶC ĐỊNH: db (Docker service)
        $db   = $env['DB_NAME'] ?? 'ev_analytics';
        $user = $env['DB_USER'] ?? 'ev_user';
        $pass = $env['DB_PASS'] ?? 'ev_pass';
        $port = $env['DB_PORT'] ?? '3306';

        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
            $conn = new PDO($dsn, $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
}
?>
