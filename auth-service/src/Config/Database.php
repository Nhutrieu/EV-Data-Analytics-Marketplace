<?php
namespace App\Config;

use mysqli;

class Database {
    public static function connect() {
        $conn = new mysqli("localhost", "root", "", "ev_data_analytics_marketplace");

        if ($conn->connect_error) {
            die(json_encode(["status" => "error", "message" => "DB connect failed"]));
        }
        return $conn;
    }
}
