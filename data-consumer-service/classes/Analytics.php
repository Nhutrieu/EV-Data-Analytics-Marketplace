<?php
// Đảm bảo Database.php được require
require_once __DIR__ . '/Database.php';

class Analytics {

    private $db;

    public function __construct() {
        // Database::getConnection() phải trả PDO
        $this->db = Database::getConnection();
    }

    public function getAllPackages() {
        $stmt = $this->db->query("SELECT * FROM analytics_packages");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPackageById($id) {
        $stmt = $this->db->prepare("SELECT * FROM analytics_packages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPackageData($id) {
        $stmt = $this->db->prepare("
            SELECT d.* 
            FROM analytics_data ad
            JOIN datasets d ON ad.dataset_id = d.id
            WHERE ad.analytics_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
