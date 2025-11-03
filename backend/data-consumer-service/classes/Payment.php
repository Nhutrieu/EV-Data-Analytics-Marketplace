<?php
// File: classes/Payment.php
class Payment {
    public $orderId;
    public $amount;
    public $orderInfo;

    public function createPaymentUrl() {
        // include config và class VNPay
        require_once __DIR__ . '/../vnpay_php/config.php';
        require_once __DIR__ . '/../vnpay_php/vnpay_create_payment.php';

        $vnp = new VnpayCreatePayment();
        $vnp->setOrderId($this->orderId);
        $vnp->setAmount($this->amount);        // VNPay nhận số nguyên (đơn vị VNĐ)
        $vnp->setOrderInfo($this->orderInfo);

        return $vnp->createPaymentUrl();
    }

    // Có thể thêm các hàm validate, log, check IPN ở đây nếu cần
}
