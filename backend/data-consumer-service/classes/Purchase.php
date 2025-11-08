<?php
// backend/data-consumer-service/classes/Purchase.php

class Purchase {
    public $id;
    public $user_id;
    public $dataset_id;
    public $type;         // "Mua" / "Thuê tháng" / "Thuê năm"
    public $price;
    public $purchased_at;

    // Thêm các thuộc tính cho schema đầy đủ
    public $status;       // 'pending' / 'paid' / ...
    public $order_code;   // dùng cho payOS (có thể null nếu không dùng)
    public $expiry_date;  // nếu là thuê
    public $created_at;

    /** @var PDO */
    private $db;

    public function __construct($db) {
        $this->db = $db; // PDO connection
    }

    /**
     * Lưu purchase mới (luồng không qua payOS)
     * - Mặc định: status = 'paid'
     * - Nếu type là "Thuê tháng"/"Thuê năm" => tự set expiry_date
     */
    public function save() {
        // status: nếu chưa set thì coi như đã thanh toán
        $status = $this->status ?: 'paid';

        // Tính expiry_date nếu là thuê
        $expiryDate = null;
        if ($this->type === 'Thuê tháng') {
            $expiryDate = date('Y-m-d H:i:s', strtotime('+1 month'));
        } elseif ($this->type === 'Thuê năm') {
            $expiryDate = date('Y-m-d H:i:s', strtotime('+1 year'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO purchases 
                (user_id, dataset_id, type, price, status, order_code, purchased_at, expiry_date, created_at)
            VALUES 
                (:user_id, :dataset_id, :type, :price, :status, :order_code, NOW(), :expiry_date, NOW())
        ");

        return $stmt->execute([
            ':user_id'    => $this->user_id,
            ':dataset_id' => $this->dataset_id,
            ':type'       => $this->type,
            ':price'      => $this->price,
            ':status'     => $status,
            ':order_code' => $this->order_code ?? null,
            ':expiry_date'=> $expiryDate,
        ]);
    }

    // Lấy purchases theo user (purchase.js dùng cái này)
    public function findByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM purchases 
            WHERE user_id = :user_id 
            ORDER BY purchased_at DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy purchase theo ID
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM purchases WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kiểm tra user đã có purchase cho dataset này chưa
    public function findExisting($user_id, $dataset_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM purchases 
            WHERE user_id = :uid AND dataset_id = :did 
            ORDER BY purchased_at DESC 
            LIMIT 1
        ");
        $stmt->execute([':uid' => $user_id, ':did' => $dataset_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
