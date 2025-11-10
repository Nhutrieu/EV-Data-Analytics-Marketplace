<?php
// backend/data-provider-service/config/Database.php

class Database
{
    public static function getConnection(): PDO
    {
        $host = 'localhost';
        $db   = 'ev_data_provider';   // đúng tên DB bạn tạo cho provider
        $user = 'root';               // user XAMPP mặc định
        $pass = '';                   // mật khẩu (thường rỗng nếu bạn chưa set)
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // quăng exception khi lỗi
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch dạng associative array
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, $user, $pass, $options);
    }
}
