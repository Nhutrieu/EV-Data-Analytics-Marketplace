<?php
namespace App\Models;

use App\Config\Database;

class User {
    private $conn;
    public function __construct() {
        $this->conn = Database::connect();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function register($email, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO users(email, password) VALUES (?, ?)");
        $stmt->bind_param("sss", $email, $hash);
        return $stmt->execute();
    }
}
