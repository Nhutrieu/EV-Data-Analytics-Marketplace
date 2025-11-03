<?php
require_once __DIR__ . '/../../modules/PurchaseModule.php';

class PurchaseController {
    private $module;

    public function __construct() {
        $this->module = new PurchaseModule();
    }

    // API lấy tất cả purchase của user
    public function listUserPurchases($user_id) {
        $data = $this->module->getPurchasesByUser($user_id);
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

    // API tạo purchase mới
    public function createPurchase($user_id, $dataset_id, $type, $price) {
        $success = $this->module->createPurchase($user_id, $dataset_id, $type, $price);
        echo json_encode([
            "success" => $success,
            "message" => $success ? "Purchase created" : "Failed to create purchase"
        ]);
    }

    // API xem purchase theo ID
    public function viewPurchase($id) {
        $data = $this->module->getPurchaseById($id);
        if ($data) {
            echo json_encode(["success" => true, "data" => $data]);
        } else {
            echo json_encode(["success" => false, "message" => "Purchase not found"]);
        }
    }
}
?>
