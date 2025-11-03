<?php
class Purchase {
    public $id;
    public $user_id;
    public $dataset_id;
    public $type; // Mua / Thuê tháng / Thuê năm
    public $price;
    public $purchased_at;

    private $db;

    public function __construct($db) {
        $this->db = $db; // PDO connection
    }

    // Lưu purchase mới
    public function save() {
        $stmt = $this->db->prepare("
            INSERT INTO purchases (user_id, dataset_id, type, price, purchased_at)
            VALUES (:user_id, :dataset_id, :type, :price, NOW())
        ");
        return $stmt->execute([
            ':user_id' => $this->user_id,
            ':dataset_id' => $this->dataset_id,
            ':type' => $this->type,
            ':price' => $this->price
        ]);
    }

    // Lấy purchase theo user
    public function findByUser($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM purchases WHERE user_id = :user_id ORDER BY purchased_at DESC");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy purchase theo ID
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM purchases WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
