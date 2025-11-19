<?php
// backend/data-consumer-service/modules/PurchaseModule.php

require_once __DIR__ . '/../classes/Purchase.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/ApiKey.php';

class PurchaseModule {
    private $db;
    private $purchase;
    private $apiKey;

    public function __construct() {
        $this->db       = Database::getConnection();
        $this->purchase = new Purchase($this->db);
        $this->apiKey   = new ApiKey($this->db);
    }

    // ✅ Lấy tất cả purchases của user (cho purchase.js)
    public function getPurchasesByUser($user_id) {
        return $this->purchase->findByUser($user_id);
    }

    /**
     * ✅ Tạo purchase mới + cấp API key nếu cần
     * Trả về array:
     *  [
     *    'success' => bool,
     *    'message' => string,
     *    'purchase_id' => int (nếu tạo mới),
     *    'api_key' => string (nếu có)
     *  ]
     */
    public function createPurchase($user_id, $dataset_id, $type, $price) {
        try {
            // Check user đã có purchase cho dataset này chưa
            $existing = $this->purchase->findExisting($user_id, $dataset_id);
            if ($existing) {
                // Đã có purchase rồi -> chỉ trả API key hiện tại
                $api_key = $this->getOrCreateApiKey($user_id);
                return [
                    "success" => true,
                    "message" => "Bạn đã mua/thuê dataset này trước đó.",
                    "purchase_id" => $existing['id'],
                    "api_key" => $api_key
                ];
            }

            // Tạo purchase mới (luồng không qua payOS)
            $this->purchase->user_id    = $user_id;
            $this->purchase->dataset_id = $dataset_id;
            $this->purchase->type       = $type;
            $this->purchase->price      = $price;
            // $this->purchase->status  = 'paid'; // không set thì save() sẽ tự cho là 'paid'

            $success = $this->purchase->save();

            if (!$success) {
                return [
                    "success" => false,
                    "message" => "Không thể tạo purchase mới."
                ];
            }

            // Lấy ID vừa insert
            $purchaseId = (int)$this->db->lastInsertId();

            // Cấp hoặc lấy API key của user
            $api_key = $this->getOrCreateApiKey($user_id);

            return [
                "success"      => true,
                "message"      => "Tạo purchase thành công.",
                "purchase_id"  => $purchaseId,
                "api_key"      => $api_key
            ];

        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Lỗi: " . $e->getMessage()
            ];
        }
    }

    // ✅ Lấy purchase theo ID (cho viewPurchase)
    public function getPurchaseById($id) {
        return $this->purchase->findById($id);
    }

    /**
     * 🔑 Tạo hoặc lấy API key active của user
     */
    private function getOrCreateApiKey($user_id) {
        $stmt = $this->db->prepare("
            SELECT api_key 
            FROM api_keys 
            WHERE user_id = :uid AND status = 'active' 
            LIMIT 1
        ");
        $stmt->execute([':uid' => $user_id]);
        $key = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($key && !empty($key['api_key'])) {
            return $key['api_key'];
        }

        // Nếu chưa có => tạo mới
        // Tùy class ApiKey của bạn: createKey(...) hoặc generateKey(...)
        if (method_exists($this->apiKey, 'createKey')) {
            return $this->apiKey->createKey($user_id);
        }

        return $this->apiKey->generateKey($user_id);
    }
}
?>
