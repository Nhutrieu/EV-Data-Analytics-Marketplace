<?php
require_once __DIR__ . '/../../classes/Payment.php';

class PaymentController {
    public function createPayment() {
        // nhận từ GET hoặc POST (frontend có thể gửi JSON)
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_GET;

        $orderId = $data['order_id'] ?? ('ORDER_' . time());
        $amount = (int)($data['amount'] ?? 100000);
        $orderInfo = $data['order_desc'] ?? ($data['order_info'] ?? 'Thanh toan gio hang');

        $payment = new Payment();
        $payment->orderId = $orderId;
        $payment->amount = $amount;
        $payment->orderInfo = $orderInfo;

        // gọi vnpay_create_payment.php để tạo URL (tùy impl của bạn)
        // nếu Payment::createPaymentUrl() trả URL:
        $url = $payment->createPaymentUrl();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'paymentUrl' => $url]);
    }
}
