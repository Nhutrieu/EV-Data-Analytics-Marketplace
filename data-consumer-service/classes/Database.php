<?php
class Database {

    // Kết nối DB consumer (ev_analytics)
    private static ?PDO $consumerPdo = null;

    // Kết nối DB provider (ev_data_marketplace)
    private static ?PDO $providerPdo = null;

    /**
     * GIỮ NGUYÊN cho code cũ:
     * Database::getConnection() = DB consumer
     */
    public static function getConnection(): PDO {
        return self::getConsumerConnection();
    }

    // 👉 DB chính của consumer (purchases, api_keys, ...)
    public static function getConsumerConnection(): PDO {
        if (self::$consumerPdo === null) {
            // CẤU HÌNH THEO DOCKER
            $host   = getenv('DB_HOST') ?: 'db_consumer';
            $dbname = getenv('DB_NAME') ?: 'ev_analytics';
            $user   = getenv('DB_USER') ?: 'ev_user';
            $pass   = getenv('DB_PASS') ?: 'ev_pass';
            $port   = getenv('DB_PORT') ?: 3306;

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            try {
                self::$consumerPdo = new PDO($dsn, $user, $pass);
                self::$consumerPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("❌ Kết nối DB consumer thất bại: " . $e->getMessage());
            }
        }
        return self::$consumerPdo;
    }

    // 👉 DB provider: nơi chứa bảng datasets mà provider & admin đã thao tác
    public static function getProviderConnection(): PDO {
        if (self::$providerPdo === null) {
            // CẤU HÌNH THEO docker-compose
            $host   = getenv('PROVIDER_DB_HOST') ?: 'db_provider';
            $dbname = getenv('PROVIDER_DB_NAME') ?: 'ev_data_marketplace';
            $user   = getenv('PROVIDER_DB_USER') ?: 'ev_user';
            $pass   = getenv('PROVIDER_DB_PASS') ?: 'ev_pass';
            $port   = getenv('PROVIDER_DB_PORT') ?: 3306;

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            try {
                self::$providerPdo = new PDO($dsn, $user, $pass);
                self::$providerPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("❌ Kết nối DB provider thất bại: " . $e->getMessage());
            }
        }
        return self::$providerPdo;
    }
}
