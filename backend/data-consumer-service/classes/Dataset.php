<?php
require_once __DIR__ . '/../config/DatabaseConfig.php';

class Dataset {
    private $conn;

    public function __construct() {
        $this->conn = DatabaseConfig::getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM datasets";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM datasets WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
