<?php
require_once __DIR__ . '/../classes/Database.php';

class DatasetModule {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection(); // Kết nối DB
    }

    // Lấy tất cả datasets
    public function getAllDatasets() {
        $stmt = $this->db->query("SELECT * FROM datasets");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy dataset theo ID
    public function getDatasetById($id) {
        $stmt = $this->db->prepare("SELECT * FROM datasets WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Các phương thức khác như create, update, delete sẽ được thêm vào ở đây
}
?>
