<?php
namespace App\Config;

class Database
{
    private static ?\PDO $pdo = null;

    public static function init(): void
    {
        if (self::$pdo !== null) return;

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $db   = getenv('DB_DATABASE') ?: 'ev_database';
        $user = getenv('DB_USERNAME') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        try {
            self::$pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
            exit;
        }
    }

    public static function getConnection(): \PDO
    {
        if (self::$pdo === null) {
            self::init();
        }
        return self::$pdo;
    }
}
