<?php
// backend/data-consumer-service/classes/Purchase.php

class Purchase {
    public $id;
    public $user_id;
    public $dataset_id;

    // Giá trị nhận từ frontend: "Mua" / "Thuê tháng" / "Thuê năm"
    // hoặc code chuẩn: "buy" / "rent_month" / "rent_year"
    public $type;

    public $price;
    public $purchased_at;

    // Schema đầy đủ
    public $status;       // 'pending' / 'paid' / ...
    public $order_code;   // dùng cho payOS (có thể null nếu không dùng)
    public $expiry_date;  // nếu là thuê
    public $created_at;

    /** @var PDO */
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db; // PDO connection
    }

    /**
     * Chuẩn hoá type để lưu vào DB (phù hợp ENUM / VARCHAR của cột purchases.type)
     * - "Mua" / "buy"          => "buy"
     * - "Thuê tháng" / ...     => "rent_month"
     * - "Thuê năm" / ...       => "rent_year"
     */
    private function normalizeTypeForDb(string $type): string
    {
        $t = mb_strtolower(trim($type));

        // Mua vĩnh viễn
        if ($t === 'mua' || $t === 'buy') {
            return 'buy';
        }

        // Thuê theo tháng
        if (
            $t === 'thuê tháng' ||
            $t === 'thue thang' ||
            $t === 'thue tháng' ||
            $t === 'thuê thang' ||
            $t === 'rent_month'
        ) {
            return 'rent_month';
        }

        // Thuê theo năm
        if (
            $t === 'thuê năm' ||
            $t === 'thue nam' ||
            $t === 'thue năm' ||
            $t === 'thuê nam' ||
            $t === 'rent_year'
        ) {
            return 'rent_year';
        }

        // fallback an toàn
        return 'buy';
    }

    /**
     * Lưu purchase mới (luồng không qua payOS)
     * - Mặc định: status = 'paid'
     * - Nếu type là "Thuê tháng"/"Thuê năm" => tự set expiry_date
     */
    public function save(): bool
    {
        // status: nếu chưa set thì coi như đã thanh toán
        $status = $this->status ?: 'paid';

        // Chuẩn hoá type trước khi lưu DB
        $dbType = $this->normalizeTypeForDb((string)$this->type);

        // Tính expiry_date nếu là thuê
        $expiryDate = null;
        if ($dbType === 'rent_month') {
            $expiryDate = date('Y-m-d H:i:s', strtotime('+1 month'));
        } elseif ($dbType === 'rent_year') {
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
            ':type'       => $dbType,                 // 👈 LƯU CODE CHUẨN
            ':price'      => $this->price,
            ':status'     => $status,
            ':order_code' => $this->order_code ?? null,
            ':expiry_date'=> $expiryDate,
        ]);
    }

    // Lấy purchases theo user (purchase.js dùng cái này)
    public function findByUser(int $user_id): array
    {
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
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM purchases WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Kiểm tra user đã có purchase cho dataset này chưa
    public function findExisting(int $user_id, int $dataset_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM purchases 
            WHERE user_id = :uid AND dataset_id = :did 
            ORDER BY purchased_at DESC 
            LIMIT 1
        ");
        $stmt->execute([
            ':uid' => $user_id,
            ':did' => $dataset_id
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
