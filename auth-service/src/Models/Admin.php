<?php
namespace Src\Models;

use Src\Config\Database;

class Admin {
    private $conn;
    public function __construct() {
        $this->conn = (new Database())->connect();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }
}
