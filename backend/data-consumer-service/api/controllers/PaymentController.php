<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Payment.php';

$config = require_once __DIR__ . '/../../config.php';


class PaymentController {
    private $db;
    private $payment;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->payment = new Payment($this->db, $GLOBALS['config']['PAYOSS_CLIENT_ID'], $GLOBALS['config']['PAYOSS_API_KEY']);
    }

    // Tạo đơn + QR
    public function create() {
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['user_id'] ?? null;
        $items = $input['items'] ?? [];
        $totalAmount = $input['totalAmount'] ?? 0;
        $accountNumber = $input['accountNumber'] ?? null;
        $accountName = $input['accountName'] ?? null;
        $bankCode = $input['bankCode'] ?? null;

        if (!$userId || !$items || !$totalAmount || !$accountNumber || !$accountName || !$bankCode) {
            echo json_encode(["success" => false, "message" => "Thiếu thông tin thanh toán"]);
            return;
        }

        $res = $this->payment->createBulkPayment($userId, $items, $totalAmount, $accountNumber, $accountName, $bankCode);
        echo json_encode([
            "success" => true,
            "order_id" => $res['orderId'],
            "qr" => $res['qr']
        ]);
    }

    // Check trạng thái thanh toán
    public function checkPayment() {
        $orderId = $_GET['order_id'] ?? null;
        if (!$orderId) {
            echo json_encode(["success" => false, "message" => "Thiếu order_id"]);
            return;
        }

        $status = $this->payment->getPaymentStatus($orderId);
        echo json_encode([
            "success" => true,
            "paid" => $status === 'success',
            "status" => $status
        ]);
    }

    // Webhook PayOSS
    public function webhook() {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        $status = $input['status'] ?? null;

        // validate checksum ở đây nếu muốn
        if ($orderId && $status === 'success') {
            $this->payment->markAsPaid($orderId);
        }

        echo json_encode(['success' => true]);
    }
}
