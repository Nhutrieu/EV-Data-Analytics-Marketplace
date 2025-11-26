<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../modules/AnalyticsModule.php';

class AnalyticsController {
    private $module;
    private $db;

    public function __construct() {
        // Lấy PDO connection từ class Database
        $this->db = Database::getConnection(); 
        $this->module = new AnalyticsModule($this->db);
    }

    // ✅ 1. Lấy toàn bộ dữ liệu từ bảng analytics_data
    public function listAnalyticsData() {
        $stmt = $this->db->query("SELECT * FROM analytics_data ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

    // ✅ 2. Lấy dữ liệu analytics_data theo package ID
    public function getPackageData($id) {
        $stmt = $this->db->prepare("SELECT * FROM analytics_data WHERE analytics_id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

    // ✅ 3. Lấy danh sách gói phân tích
    public function listPackages() {
        $stmt = $this->db->query("SELECT * FROM analytics_packages");
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $packages
        ]);
    }

    // ✅ 4. Lấy chi tiết gói theo ID
    public function viewPackage($id) {
        $stmt = $this->db->prepare("SELECT * FROM analytics_packages WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $package
        ]);
    }
}
