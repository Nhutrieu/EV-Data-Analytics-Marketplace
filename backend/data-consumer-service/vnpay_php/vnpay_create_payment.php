<?php
// ==========================================
// File: vnpay_create_payment.php
// Tích hợp giỏ hàng thực tế sang VNPay
// ==========================================
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ==========================
// Cấu hình VNPay
// ==========================
$vnp_TmnCode   = "NOCSO79D"; // Mã website VNPay
$vnp_HashSecret = "L7HOO3SFZZB7ZSGELCAT2QJZ62B1DJRH"; // Chuỗi bí mật
$vnp_Url       = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // Sandbox
$vnp_Returnurl = "http://127.0.0.1/EV-Data-Analytics-Marketplace/backend/data-consumer-service/vnpay_php/vnpay_return.php";

// ==========================
// Nhận dữ liệu từ frontend (POST JSON)
// ==========================
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];

$vnp_TxnRef    = $data['order_id'] ?? ('ORDER_' . time()); // Mã đơn hàng
$vnp_OrderInfo = $data['order_desc'] ?? 'Thanh toán giỏ hàng EV';
$vnp_Amount    = isset($data['amount']) ? (int)$data['amount'] * 100 : 100000 * 100; // VNPay nhân 100
$vnp_OrderType = 'billpayment';
$vnp_Locale    = 'vn';
$vnp_IpAddr    = $_SERVER['REMOTE_ADDR'];

// ==========================
// Chuẩn bị dữ liệu gửi sang VNPay
// ==========================
$inputData = [
    "vnp_Version"   => "2.1.0",
    "vnp_TmnCode"   => $vnp_TmnCode,
    "vnp_Amount"    => $vnp_Amount,
    "vnp_Command"   => "pay",
    "vnp_CreateDate"=> date('YmdHis'),
    "vnp_CurrCode"  => "VND",
    "vnp_IpAddr"    => $vnp_IpAddr,
    "vnp_Locale"    => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef"    => $vnp_TxnRef
];

// Sắp xếp key theo chuẩn VNPay
ksort($inputData);

// Build query string & hashdata
$query = $hashdata = '';
$i = 0;
foreach ($inputData as $key => $value) {
    $hashdata .= ($i ? '&' : '') . urlencode($key) . '=' . urlencode($value);
    $query    .= urlencode($key) . '=' . urlencode($value) . '&';
    $i = 1;
}

// Tạo secure hash SHA512
$vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

// Hoàn thiện URL thanh toán
$vnp_Url = $vnp_Url . "?" . $query . "vnp_SecureHash=" . $vnp_SecureHash . "&vnp_SecureHashType=SHA512";

// Trả về JSON cho frontend
header('Content-Type: application/json');
echo json_encode([
    'success'    => true,
    'paymentUrl' => $vnp_Url,
    '_debug'     => [
        'amount_sent'   => $vnp_Amount / 100,
        'order_id'      => $vnp_TxnRef,
        'order_info'    => $vnp_OrderInfo,
        'hashdata'      => $hashdata,
        'vnpSecureHash' => $vnp_SecureHash,
        'vnp_Url'       => $vnp_Url
    ]
]);
exit;
