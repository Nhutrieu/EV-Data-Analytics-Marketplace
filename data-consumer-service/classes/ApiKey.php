<?php
class ApiKey {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // 🔹 Tạo API key ngẫu nhiên và lưu vào DB
public function createKey($user_id) {
    // ❌ Vô hiệu hóa hoặc xoá các key cũ
    $this->db->prepare("UPDATE api_keys SET status = 'revoked' WHERE user_id = :uid AND status = 'active'")
             ->execute([':uid' => $user_id]);

    // ✅ Tạo key mới
    $key = bin2hex(random_bytes(32)); // 64 ký tự ngẫu nhiên
    $stmt = $this->db->prepare("
        INSERT INTO api_keys (user_id, api_key, status, created_at) 
        VALUES (:uid, :key, 'active', NOW())
    ");
    $stmt->execute([
        ':uid' => $user_id,
        ':key' => $key
    ]);

    return $key;
}


    // 🔹 Kiểm tra API key có hợp lệ không
    public function validateKey($key) {
        $stmt = $this->db->prepare("
            SELECT * FROM api_keys 
            WHERE api_key = :key AND status = 'active'
        ");
        $stmt->execute([':key' => $key]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Vô hiệu hóa 1 key cụ thể
    public function revokeKey($key) {
        $stmt = $this->db->prepare("
            UPDATE api_keys SET status='revoked' WHERE api_key=:key
        ");
        return $stmt->execute([':key' => $key]);
    }

    // 🔹 Lấy danh sách tất cả API key của 1 user
    public function getKeysByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT api_key, status, created_at 
            FROM api_keys WHERE user_id=:uid ORDER BY created_at DESC
        ");
        $stmt->execute([':uid' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
