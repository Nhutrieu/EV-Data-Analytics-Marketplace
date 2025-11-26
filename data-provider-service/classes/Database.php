<?php

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = getenv('DB_HOST') ?: 'db_provider';
        $db   = getenv('DB_NAME') ?: 'ev_data_marketplace';
        $user = getenv('DB_USER') ?: 'ev_user';
        $pass = getenv('DB_PASS') ?: 'ev_pass';

        $charset = 'utf8mb4';
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$connection = new PDO($dsn, $user, $pass, $options);
            return self::$connection;
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'message' => 'Không kết nối được database',
                'error'   => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
