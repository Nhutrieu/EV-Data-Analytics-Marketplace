<?php
/**
 * ========================================
 *  VNPay IPN Handler — Ghi nhận thanh toán
 * ========================================
 *  Quy trình:
 *   1️⃣ VNPay gửi dữ liệu về file này sau khi giao dịch.
 *   2️⃣ Kiểm tra chữ ký (checksum) để tránh giả mạo.
 *   3️⃣ Ghi kết quả thanh toán vào database (bảng payments).
 *   4️⃣ Trả JSON về cho VNPay: "00" nếu thành công.
 */

date_default_timezone_set('Asia/Ho_Chi_Minh');

// ========================
// KẾT NỐI DATABASE
// ========================
require_once __DIR__ . '/../../data-consumer-service/db_connection.php';

// ========================
// CẤU HÌNH VNPAY
// ========================
$vnp_TmnCode   = "NOCSO79D"; // Mã website của bạn tại VNPay
$vnp_HashSecret = "L7HOO3SFZZB7ZSGELCAT2QJZ62B1DJRH"; // Chuỗi bí mật từ VNPay

// ========================
// LẤY DỮ LIỆU GỬI VỀ
// ========================
$inputData = [];
foreach ($_REQUEST as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

if (empty($inputData)) {
    echo json_encode(['RspCode' => '99', 'Message' => 'No input data']);
    exit;
}

// ========================
// XỬ LÝ CHECKSUM
// ========================
$vnp_SecureHash = $inputData['vnp_SecureHash'];
unset($inputData['vnp_SecureHashType']);
unset($inputData['vnp_SecureHash']);
ksort($inputData);

$hashData = urldecode(http_build_query($inputData));
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// ========================
// KIỂM TRA GIAO DỊCH
// ========================
$orderId = $inputData['vnp_TxnRef'] ?? null;
$vnp_Amount = ($inputData['vnp_Amount'] ?? 0) / 100; // VNPay gửi *100
$vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
$vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';
$vnp_BankCode = $inputData['vnp_BankCode'] ?? '';
$now = date('Y-m-d H:i:s');

// ========================
// XỬ LÝ VÀ GHI DATABASE
// ========================
try {
    if ($secureHash === $vnp_SecureHash) {

        // Kiểm tra giao dịch tồn tại chưa
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            // Giao dịch mới → thêm vào DB
            $insert = $pdo->prepare("
                INSERT INTO payments (order_id, money, vnp_response_code, code_vnpay, code_bank, time, note)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $note = ($vnp_ResponseCode == '00') ? 'Thanh toán thành công qua VNPay' : 'Thanh toán thất bại';
            $insert->execute([
                $orderId,
                $vnp_Amount,
                $vnp_ResponseCode,
                $vnp_TransactionNo,
                $vnp_BankCode,
                $now,
                $note
            ]);

            $returnData = [
                'RspCode' => '00',
                'Message' => 'Confirm Success'
            ];
        } else {
            // Giao dịch đã tồn tại → tránh ghi trùng
            $returnData = [
                'RspCode' => '02',
                'Message' => 'Order already confirmed'
            ];
        }
    } else {
        $returnData = [
            'RspCode' => '97',
            'Message' => 'Invalid checksum'
        ];
    }
} catch (Exception $e) {
    $returnData = [
        'RspCode' => '99',
        'Message' => 'Database error: ' . $e->getMessage()
    ];
}

// ========================
// TRẢ KẾT QUẢ VỀ CHO VNPAY
// ========================
header('Content-Type: application/json');
echo json_encode($returnData);
exit;
