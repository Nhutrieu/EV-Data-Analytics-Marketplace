<?php
// ===============================================
// File: modules/PaymentModule.php
// ===============================================

require_once __DIR__ . '/../classes/Payment.php';

class PaymentModule {

    /**
     * Xử lý tạo URL thanh toán qua VNPAY
     * @param string $orderId    Mã đơn hàng
     * @param float  $amount     Số tiền thanh toán (VND)
     * @param string $orderInfo  Nội dung đơn hàng
     * @return array             Kết quả [success, paymentUrl hoặc error]
     */
    public static function processPayment($orderId, $amount, $orderInfo) {
        try {
            // Kiểm tra dữ liệu đầu vào
            if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
                return [
                    'success' => false,
                    'message' => 'Số tiền thanh toán không hợp lệ.'
                ];
            }

            // Tạo đối tượng Payment
            $payment = new Payment();

            // Gán thông tin đơn hàng
            $payment->orderId   = $orderId ?: 'ORDER_' . time();
            $payment->amount    = (int)$amount;
            $payment->orderInfo = $orderInfo ?: 'Thanh toán gói dữ liệu EV Marketplace';

            // Gọi hàm tạo URL thanh toán
            $url = $payment->createPaymentUrl();

            if (!$url) {
                return [
                    'success' => false,
                    'message' => 'Không thể tạo URL thanh toán.'
                ];
            }

            // Trả về kết quả thành công
            return [
                'success' => true,
                'paymentUrl' => $url,
                'data' => [
                    'orderId' => $payment->orderId,
                    'amount' => $payment->amount,
                    'orderInfo' => $payment->orderInfo
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi xử lý thanh toán: ' . $e->getMessage()
            ];
        }
    }
}
