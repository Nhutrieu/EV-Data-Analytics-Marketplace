<?php
namespace Src\Config;

use mysqli;

class Database {
    public static function connect() {
        $host = '127.0.0.1'; // hoặc 'localhost' nếu MySQL chạy trên XAMPP
        $user = 'root';
        $pass = ''; // nếu bạn dùng XAMPP mặc định
        $db   = 'ev-data-analytics-marketplace';

        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}
