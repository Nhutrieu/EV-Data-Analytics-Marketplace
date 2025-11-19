<?php
// backend/data-consumer-service/api/controllers/PurchaseController.php

require_once __DIR__ . '/../../modules/PurchaseModule.php';

class PurchaseController {
    /** @var PurchaseModule */
    private $module;

    public function __construct() {
        $this->module = new PurchaseModule();
        header('Content-Type: application/json');
    }

    // ✅ API: lấy tất cả purchases của user (cho purchase.js)
    public function listUserPurchases($user_id) {
        $data = $this->module->getPurchasesByUser($user_id);

        echo json_encode([
            "success" => true,
            "data"    => $data
        ]);
    }

    // ✅ API: tạo purchase mới (nếu bạn còn dùng API này ngoài payOS)
    public function createPurchase($user_id, $dataset_id, $type, $price) {
        $result = $this->module->createPurchase($user_id, $dataset_id, $type, $price);

        // Đảm bảo luôn là array có 'success'
        if (!is_array($result)) {
            $result = [
                "success" => (bool)$result,
                "message" => $result ? "Purchase created" : "Failed to create purchase"
            ];
        }

        echo json_encode($result);
    }

    // ✅ API: xem 1 purchase theo ID
    public function viewPurchase($id) {
        $data = $this->module->getPurchaseById($id);

        if ($data) {
            echo json_encode([
                "success" => true,
                "data"    => $data
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Purchase not found"
            ]);
        }
    }
}
?>
