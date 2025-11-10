<?php
namespace App\Config;

use mysqli;

class Database {
    public static function connect() {
        $conn = new mysqli(
            "db",                       // Docker service name
            "root",                     // MySQL user
            "rootpassword",             // MySQL password
            "ev_data_analytics_marketplace" // Database name
        );

        if ($conn->connect_error) {
            die(json_encode([
                "status" => "error",
                "message" => "DB connect failed: " . $conn->connect_error
            ]));
        }

        return $conn;
    }
}
