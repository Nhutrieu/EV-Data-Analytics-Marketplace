<?php
require_once __DIR__ . '/../modules/PaymentModule.php';

class Payment {
    private $db;
    private $module;

    public function __construct($db, $clientId, $apiKey) {
        $this->db = $db;
        $this->module = new PaymentModule($clientId, $apiKey);
    }

    // Tạo đơn + QR
    public function createBulkPayment($userId, $items, $totalAmount, $accountNumber, $accountName, $bankCode) {
        $stmt = $this->db->prepare("INSERT INTO payments (user_id, amount, status, created_at) VALUES (?, ?, 'pending', NOW())");
        $stmt->execute([$userId, $totalAmount]);
        $orderId = $this->db->lastInsertId();

        $stmt2 = $this->db->prepare("INSERT INTO purchases (user_id, dataset_id, type, price, expiry_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        foreach ($items as $item) {
            $expiry = $item['expiry_date'] ?? null;
            $stmt2->execute([
                $userId,
                $item['id'],
                $item['selectedType'],
                $item['price'],
                $expiry
            ]);
        }

        // Tạo QR PayOSS thật
        $qr = $this->module->createPayOSSQR($orderId, $totalAmount, $accountNumber, $accountName, $bankCode);

        return [
            "orderId" => $orderId,
            "qr" => $qr
        ];
    }

    public function getPaymentStatus($orderId) {
        $stmt = $this->db->prepare("SELECT status FROM payments WHERE id=?");
        $stmt->execute([$orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['status'] ?? 'pending';
    }

    public function markAsPaid($orderId) {
        $stmt = $this->db->prepare("UPDATE payments SET status='success' WHERE id=?");
        $stmt->execute([$orderId]);
    }
}
