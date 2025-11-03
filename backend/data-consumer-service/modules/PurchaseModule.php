<?php
require_once __DIR__ . '/../classes/Purchase.php';
require_once __DIR__ . '/../classes/Database.php';

class PurchaseModule {
    private $db;
    private $purchase;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->purchase = new Purchase($this->db);
    }

    // Lấy tất cả purchase của user
    public function getPurchasesByUser($user_id) {
        return $this->purchase->findByUser($user_id);
    }

    // Tạo purchase mới
    public function createPurchase($user_id, $dataset_id, $type, $price) {
        $this->purchase->user_id = $user_id;
        $this->purchase->dataset_id = $dataset_id;
        $this->purchase->type = $type;
        $this->purchase->price = $price;
        return $this->purchase->save();
    }

    // Lấy purchase theo ID
    public function getPurchaseById($id) {
        return $this->purchase->findById($id);
    }
}
?>
